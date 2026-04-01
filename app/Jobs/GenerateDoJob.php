<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\TdoScanStaging;
use App\Models\Tdo;
use App\Models\Tdo_Detail;
use App\Models\TProductOutbound;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GenerateDoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $batchSize = 50;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("Starting GenerateDoJob for User ID: {$this->userId}");

        // 1. Mark all OPEN records as PROCESSING to lock them for this job
        $lockId = uniqid('job_');
        $affected = TdoScanStaging::where('status', 'OPEN')
            ->update([
                'status' => 'PROCESSING',
                'locked_by' => $lockId,
                'lock_expired_at' => Carbon::now()->addHours(1)
            ]);

        if ($affected === 0) {
            Log::info("No OPEN records found to process.");
            return;
        }

        Log::info("Locked {$affected} records for processing.");

        // 2. Process in chunks to avoid memory issues and long transactions
        TdoScanStaging::where('status', 'PROCESSING')
            ->where('locked_by', $lockId)
            ->chunkById($this->batchSize, function ($items) use ($lockId) {
                
                // Group items by session_id to create DO per session
                $groupedBySession = $items->groupBy('session_id');

                foreach ($groupedBySession as $sessionId => $sessionItems) {
                    DB::beginTransaction();
                    try {
                        // Create or Get DO Header for this session_id
                        // Note: If a session spans across multiple chunks, we might need to find the existing DO
                        $noDo = $this->generateNoDo();
                        
                        // Check if DO already created for this session in this job run
                        $tdo = Tdo::where('reason_do', 'LIKE', "%SESSION: {$sessionId}%")
                                  ->where('status_do', 'OPEN')
                                  ->whereDate('created_at', Carbon::today())
                                  ->first();

                        if (!$tdo) {
                            $tdo = Tdo::create([
                                'tgl_do' => Carbon::now(),
                                'no_do' => $noDo,
                                'shipping_via' => 'EKSPEDISI',
                                'status_do' => 'OPEN',
                                'do_source' => 'REGULAR',
                                'flag_approve' => 'Y',
                                'reason_do' => "GENERATED FROM SCAN STAGING. SESSION: {$sessionId}",
                                'created_at' => Carbon::now(),
                                'created_by' => $this->userId,
                            ]);
                        }

                        // Group by SKU for TdoDetail
                        $groupedBySku = $sessionItems->groupBy('sku');

                        foreach ($groupedBySku as $sku => $skuItems) {
                            // Create TdoDetail
                            $detail = Tdo_Detail::create([
                                'id_do' => $tdo->id,
                                'sku' => $sku,
                                'qty' => $skuItems->count(),
                                'seq' => 1,
                                'created_at' => Carbon::now(),
                                'created_by' => $this->userId,
                            ]);

                            // Create TProductOutbound for each QR
                            foreach ($skuItems as $item) {
                                TProductOutbound::create([
                                    'id_do' => $tdo->id,
                                    'id_do_detail' => $detail->id,
                                    'id_product' => $item->id_product,
                                    'sku' => $item->sku,
                                    'qr_code' => $item->qr_code,
                                    'qty' => 1,
                                    'out_at' => Carbon::now(),
                                    'created_at' => Carbon::now(),
                                    'outbound_source' => 'REGULAR',
                                    'created_by' => $this->userId,
                                ]);

                                // Update Staging Status to PROCESSED
                                TdoScanStaging::where('id', $item->id)->update([
                                    'status' => 'PROCESSED',
                                    'updated_at' => Carbon::now()
                                ]);
                            }
                        }

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Error processing Session {$sessionId} in chunk: " . $e->getMessage());
                        // Optional: Mark these specific items as FAILED so they can be retried
                        TdoScanStaging::whereIn('id', $sessionItems->pluck('id'))
                            ->update(['status' => 'OPEN', 'locked_by' => null]);
                    }
                }
            });

        Log::info("Finished GenerateDoJob for User ID: {$this->userId}");
    }

    private function generateNoDo()
    {
        $today = Carbon::now()->format('Ymd');
        $count = Tdo::whereDate('created_at', Carbon::today())->count();
        return 'DO-' . $today . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }
}
