<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TdoScanStaging;
use DB;

class TdoScanStagingController extends Controller
{
    public function index()
    {
        return view('pages.transaction.tdo_scan_staging.tdo_scan_staging_index');
    }

    public function datatable()
    {
        $data = DB::table('tdo_scan_staging')
            ->where('status', 'OPEN')
            ->select(
                DB::raw('DATE(created_at) as tgl_scan'),
                DB::raw('COUNT(DISTINCT session_id) as jumlah_sesi'),
                DB::raw('COUNT(id) as total_scan'),
                DB::raw('COUNT(DISTINCT created_by) as jumlah_user')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderByDesc(DB::raw('DATE(created_at)'))
            ->get();

        return response()->json(['data' => $data]);
    }

    public function detail()
    {
        $data = TdoScanStaging::with(['product', 'creator'])
            ->where('status', 'OPEN')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function($item) {
                return $item->created_at->format('Y-m-d');
            });

        return view('pages.transaction.tdo_scan_staging.tdo_scan_staging_detail', compact('data'));
    }

    public function detailByDate($tgl)
    {
        $rows = TdoScanStaging::with(['product', 'creator'])
            ->whereDate('created_at', $tgl)
            ->where('status', 'OPEN')
            ->get()
            ->groupBy('session_id');

        return view('pages.transaction.tdo_scan_staging.tdo_scan_staging_detail_by_date', compact('tgl', 'rows'));
    }

    public function generateDoByDate(Request $request)
    {
        $tgl = $request->tgl;
        try {
            $no_do = $this->processGenerateDo($tgl);
            return redirect()->route('delivery_order.index')->with('success', 'Berhasil membuat DO ' . $no_do);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses data: ' . $e->getMessage());
        }
    }

    public function generateDoBatch(Request $request)
    {
        $dates = $request->dates; // Array of dates
        if (empty($dates)) {
            return redirect()->back()->with('error', 'Pilih minimal satu tanggal.');
        }

        $success_count = 0;
        $errors = [];

        foreach ($dates as $tgl) {
            try {
                $this->processGenerateDo($tgl);
                $success_count++;
            } catch (\Exception $e) {
                $errors[] = "Gagal memproses $tgl: " . $e->getMessage();
            }
        }

        if ($success_count > 0 && empty($errors)) {
            return redirect()->route('delivery_order.index')->with('success', "Berhasil membuat $success_count DO.");
        } elseif ($success_count > 0) {
            return redirect()->route('delivery_order.index')->with('success', "Berhasil membuat $success_count DO. Ada beberapa error: " . implode(', ', $errors));
        } else {
            return redirect()->back()->with('error', 'Gagal memproses data: ' . implode(', ', $errors));
        }
    }

    public function dispatchGenerateDo(Request $request)
    {
        // Check if there are any OPEN records
        $count = TdoScanStaging::where('status', 'OPEN')->count();
        if ($count == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data OPEN untuk diproses.'
            ]);
        }

        // Check if already processing
        $processingCount = TdoScanStaging::where('status', 'PROCESSING')->count();
        if ($processingCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Proses generate sedang berjalan di background.'
            ]);
        }

        \App\Jobs\GenerateDoJob::dispatch(auth()->id() ?? 1);

        return response()->json([
            'success' => true,
            'message' => 'Job generate DO telah dikirim ke antrian (Queue). Silakan cek berkala.'
        ]);
    }

    public function checkStatus()
    {
        $open = TdoScanStaging::where('status', 'OPEN')->count();
        $processing = TdoScanStaging::where('status', 'PROCESSING')->count();

        return response()->json([
            'open' => $open,
            'processing' => $processing
        ]);
    }

    private function processGenerateDo($tgl)
    {
        $items = TdoScanStaging::whereDate('created_at', $tgl)
            ->where('status', 'OPEN')
            ->get();

        if ($items->isEmpty()) {
            throw new \Exception("Tidak ada data staging untuk tanggal $tgl.");
        }

        DB::beginTransaction();
        try {
            // 1. Ambil sequence DO terakhir untuk nomor DO
            $last_do = DB::table('tdos')
                ->whereDate('tgl_do', now())
                ->count();
            
            $no_do = 'DO-' . now()->format('Ymd') . '-' . str_pad($last_do + 1, 3, '0', STR_PAD_LEFT);

            // 2. Insert Header DO
            $id_do = DB::table('tdos')->insertGetId([
                'tgl_do' => now(),
                'no_do' => $no_do,
                'shipping_via' => 'EKSPEDISI',
                'do_source' => 'REGULAR',
                'flag_approve' => 'Y',
                'reason_do' => 'GENERATED FROM SCAN STAGING DATE ' . $tgl,
                'created_at' => now(),
                'created_by' => auth()->id() ?? 1,
            ]);

            // 3. Grouping Items per SKU untuk tdo_detail
            $groupedBySku = $items->groupBy('sku');

            foreach ($groupedBySku as $sku => $skuItems) {
                $firstItem = $skuItems->first();
                $id_do_detail = DB::table('tdo_detail')->insertGetId([
                    'id_do' => $id_do,
                    'sku' => $sku,
                    'qty' => $skuItems->count(),
                    'seq' => 1,
                    'created_at' => now(),
                    'created_by' => auth()->id() ?? 1,
                ]);

                // 4. Insert per Item ke tproduct_outbound
                foreach ($skuItems as $item) {
                    DB::table('tproduct_outbound')->insert([
                        'id_do' => $id_do,
                        'id_do_detail' => $id_do_detail,
                        'id_product' => $item->id_product,
                        'sku' => $item->sku,
                        'qr_code' => $item->qr_code,
                        'qty' => 1,
                        'out_at' => now(),
                        'created_at' => now(),
                        'outbound_source' => 'REGULAR',
                        'created_by' => auth()->id() ?? 1,
                    ]);

                    // Update Status Staging
                    TdoScanStaging::where('id', $item->id)->update(['status' => 'COMPLETED']);
                }
            }

            DB::commit();
            return $no_do;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
