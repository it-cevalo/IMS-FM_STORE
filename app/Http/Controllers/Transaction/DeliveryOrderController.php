<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tdo;
use App\Models\Tpo;
use App\Models\Tpo_Detail;
use App\Models\Hdo;
use App\Imports\DeliveryOrderImport;
use Storage, Excel, Response, Auth;
use Yajra\DataTables\Facades\DataTables;

class DeliveryOrderController extends Controller
{
    public function index()
    {
        $delivery_order = Tdo::with(['po'])->latest('id')->paginate(5);
        return view('pages.transaction.delivery_order.delivery_order_index',compact('delivery_order'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }
    public function data(Request $request)
    {
        if (Auth::user()->position !== 'SUPERADMIN') {
            abort(403);
        }

        $query = Tdo::with(['po'])->select('tdos.*');

        return DataTables::eloquent($query)
            ->addColumn('po_id', fn($d) => $d->po->id ?? null)
            ->addColumn('code_spl', fn($d) => $d->po->code_spl ?? '-')
            ->addColumn('nama_spl', fn($d) => $d->po->nama_spl ?? '-')
            ->addColumn('file', fn($d) => $d->file ? '<a href="'.route('delivery_order.downloadDO', $d->id).'">'.$d->file.'</a>' : 'No File')
            ->addColumn('action', function ($d) {
                return '
                    <a href="'.route('delivery_order.history', $d->id).'" class="btn btn-sm btn-success"><i class="fa fa-history"></i></a>
                    <a href="'.route('delivery_order.edit', $d->id).'" class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></a>
                    <!-- <a href="#" id="approveBtn'.$d->id.'" onclick="approveOrder('.$d->id.')" class="btn btn-sm btn-info"><i class="fa fa-check"></i></a> -->
                    <form method="POST" action="'.route('delivery_order.delete', $d->id).'" style="display:inline;">
                        '.csrf_field().method_field('DELETE').'
                        <button type="submit" class="btn btn-sm btn-danger show-alert-delete-box"><i class="fa fa-trash"></i></button>
                    </form>
                ';
            })
            ->rawColumns(['file', 'action'])
            ->make(true);
    }
    
    public function search(Request $request)
    {
        $delivery_order = Tdo::with(['po'])->where('nama_cust', 'LIKE', request()->search.'%')
                                ->orWhere('code_cust', 'LIKE' , request()->search.'%')
                                ->orWhere('tgl_po', 'LIKE' , request()->search.'%')
                                ->orWhere('tgl_do', 'LIKE' , request()->search.'%')
                                ->orWhere('no_po', 'LIKE' , request()->search.'%')
                                ->orWhere('no_so', 'LIKE' , request()->search.'%')
                                ->orWhere('no_do', 'LIKE' , request()->search.'%')
                                ->orWhere('status_lmpr_do', 'LIKE' ,request()->search.'%')
                                ->orWhere('reason_do', 'LIKE' ,request()->search.'%')
                                ->orderBy('tgl_do','desc')
                                ->paginate();
        $no  = ($delivery_order->currentPage()*$delivery_order->perPage())-$delivery_order->perPage()+1;
        return view('pages.transaction.delivery_order.delivery_order_index',compact('delivery_order','no'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    public function history($id){
        $delivery_order_his = Hdo::where('id_do',$id)->get();
        // dd($delivery_order_his);
        return view('pages.transaction.delivery_order.delivery_order_history',compact('delivery_order_his'));
    }

	public function upload(Request $request) 
	{
		// validasi
		$this->validate($request, [
			'file' => 'required|mimes:xlsx'
		]);
 
		// menangkap file excel
		$file = $request->file('file');
 
		// membuat nama file unik
		$nama_file = rand().$file->getClientOriginalName();
 
		// upload ke folder file_do di dalam folder public
		$file->move('file_do',$nama_file);
 
		// import data
		Excel::import(new DeliveryOrderImport, public_path('/file_do/'.$nama_file));
 
		// notifikasi dengan session
		// Session::flash('sukses','Data PO Berhasil Diimport!');
 
		// alihkan halaman kembali
		return redirect()->route('delivery_order.index');
	}

    public function uploadDO(Request $request) 
	{
		// validasi
		$this->validate($request, [
			'file'  => 'required|mimes:pdf',
            'id_do' => 'required'
		],[
            'file.required' => 'Please Input Your DO File',
            'id_do.required' => 'Please Select Your DO',
        ]);
 
		// menangkap file excel
		$file = $request->file('file');
 
		// membuat nama file unik
		$nama_file = rand().$file->getClientOriginalName();
 
		// upload ke folder file_do di dalam folder public
		$file->move('file_do/berkas_do',$nama_file);
 
		// notifikasi dengan session
		// Session::flash('sukses','Data PO Berhasil Diimport!');
 
		// alihkan halaman kembali
        Tdo::where('id',$request->id_do)->update([
			'file' => $nama_file,
            'upload_date_at' => date("Y-m-d H:i:s")
		]);
 
		return redirect()->back();
	}

    public function downloadDO($id)
    {
        $DO = Tdo::select(['file'])->where('id',$id)->first();
        $filename = $DO->file;
        // dd($filename);exit; 
        $filepath = public_path('file_do/berkas_do/'.$filename);
        return Response::download($filepath); 
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $po = Tpo::get();
        return view('pages.transaction.delivery_order.delivery_order_create',compact('po'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'id_po'             => 'required',
            'status_lmpr_do'    => 'required',
            'tgl_do'            => 'required',
            'no_do'             => 'required|unique:tdos,no_do|regex:/(^([a-zA-Z0-9_\-\s]+)(\d+)?$)/u',
            'reason_do'         => 'required|regex:/(^([a-zA-Z0-9_\-\s]+)(\d+)?$)/u',
            'shipping_via'      => 'required'
        ],[
            'id_po.required' => 'Please Select PO',
            'status_lmpr_do.required' => 'Please Select Attachment Status',
            'tgl_do.required' => 'Please Select Date',
            'no_do.required' => 'Please Fill DO Number',
            'no_do.unique' => 'This Number has been taken! Please Input with another DO Number',
            'no_do.regex' => 'Character input is not allowed! Please input without symbol ',
            'reason_do.required' => 'Please Input Reason',
            'reason_do.regex' => 'Character input is not allowed! Please input without symbol ',
            'shipping_via.required' => 'Please Select Shipping Via',
        ]);

        $po = Tpo::select([/*'code_cust','nama_cust',*/'tgl_po','no_so','no_po', 'id_supplier'])->where('id',$request->id_po)->first();
        // $code_cust  = $po->code_cust;
        // $nama_cust  = $po->nama_cust;
        $id_spl     = $po->id_supplier;
        $tgl_po     = $po->tgl_po;
        $no_po      = $po->no_po;
        $no_so      = $po->no_so;

        $do = Tdo::create([
            'id_po'             => $request->id_po,
            'id_supplier'       => $request->id_spl,
            'code_cust'         => /*$code_cust*/ '',
            'nama_cust'         => /*$nama_cust*/ '',
            'tgl_po'            => $tgl_po,
            'no_po'             => $no_po,
            'no_so'             => $no_so,
            'no_do'             => $request->no_do,
            'tgl_do'            => $request->tgl_do,
            'status_lmpr_do'    => $request->status_lmpr_do,
            'reason_do'         => $request->reason_do,
            'shipping_via'      => $request->shipping_via,
            'flag_approve'      => 'N',
            'approve_date'      => '1970-01-01',
            'approve_by'        => ''
        ]);

        $do = Tdo::select('id')->latest()->first();
        $id_do = $do->id;

        $do_his = Hdo::create([
            'id_supplier'        => $id_spl,
            'id_do'              => $id_do,
            'id_po'              => $request->id_po,
            'code_cust'          => /*$code_cust*/ '',
            'nama_cust'          => /*$nama_cust*/ '',
            'no_do'              => $request->no_do,
            'tgl_do'             => $request->tgl_do,
            'reason_do'          => $request->reason_do
        ]);

        if ($do && $do_his) {
            return redirect()
                ->route('delivery_order.index')
                ->with([
                    'success' => 'New DO has been created successfully'
                ]);
        } else {
            return redirect()
                ->back()
                ->withInput()
                ->with([
                    'error' => 'Some problem occurred, please try again'
                ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $delivery_order = Tdo::findOrFail($id);
        $po             = Tpo::get();
        $purchase_order = Tdo::select('id_po')->where('id',$id)->first();
        $id_po          = $purchase_order->id_po;
        $po_dtl         = Tpo_Detail::where('id_po',$id_po)->get();
        $status_lmpr_do = [
            '....' => '....',
            'OK' => 'OK',
            'HOLD' => 'HOLD'
        ];
        $shipping_via = [
            '....' => '....',
            'HANDCARRY' => 'HANDCARRY',
            'EKSPEDISI' => 'EKSPEDISI'
        ];
        return view('pages.transaction.delivery_order.delivery_order_show',compact('delivery_order','po','po_dtl','status_lmpr_do', 'shipping_via'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $delivery_order = Tdo::findOrFail($id);
        $po = Tpo::get();
        $status_lmpr_do = [
            '....' => '....',
            'OK' => 'OK',
            'HOLD' => 'HOLD'
        ];
        $shipping_via = [
            '....' => '....',
            'HANDCARRY' => 'HANDCARRY',
            'EKSPEDISI' => 'EKSPEDISI'
        ];
        return view('pages.transaction.delivery_order.delivery_order_edit',compact('delivery_order','po','status_lmpr_do', 'shipping_via'));

    }

    public function approve(Request $request, $id)
    {
        $approve_by = Auth::user()->username;
        // dd($approve_by);
        
        $approve = Tdo::find($id);
        
        if (!$approve) {
            return response()->json(['error' => 'delivery order not found']);
        }

        try {
            $approve->approve_by = $approve_by;
            $approve->approve_date = date('Y-m-d');
            $approve->flag_approve = "Y";
            
            $updated = $approve->save();
            
            if ($updated) {
                return response()->json(['success' => true]);
            } else {
                return response()->json(['error' => 'approval failed']);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'approval failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'status_lmpr_do'    => 'required',
            'shipping_via'      => 'required',
            'reason_do'         => 'required'
        ],[
            'status_lmpr_do.required'   => 'Please Fill Attachment Status',
            'reason_do.required'        => 'Please Fill Reason DO',
            'shipping_via.required'     => 'Please Fill Shipping Via'
        ]);

        Tdo::whereId($id)->update($validatedData);

        $po = Tpo::select(['code_cust','nama_cust'])->where('id',$request->id_po)->first();
        $code_cust  = $po->code_cust;
        $nama_cust  = $po->nama_cust;
        
        $delivery_order = Tdo::select('id')->where('code_cust',$code_cust)->latest()->first();
        $id_do          = $delivery_order->id;
        // dd($id_po);exit();

        $delivery_order_his = Hdo::create([
            'id_do'              => $id_do,
            'id_po'              => $request->id_po,
            'code_cust'          => /*$code_cust*/ '',
            'nama_cust'          => /*$nama_cust*/ '',
            'no_do'              => $request->no_do,
            'tgl_do'             => $request->tgl_do,
            'reason_do'          => $request->reason_do
        ]);

        return redirect('/delivery_order')->with('success', 'Delivery Order is successfully updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    
    public function delete2($id)
    {
        // dd("Masuk");
        $data = Tdo::with(['po'])->find($id);
        $data->delete();
        return redirect()->route('delivery_order.index');
    }

    public function bin2()
    {
        $data   ['data']      = Tdo::with(['po'])->onlyTrashed()->paginate(5);
        return view('pages.transaction.delivery_order.delivery_order_bin', $data)->with('i', (request()->input('page', 1) - 1) * 5);
    }

    public function bin2Data(Request $request)
    {
        if ($request->ajax()) {
            $data = Tdo::onlyTrashed()->with(['po']);
            return DataTables::of($data)
                ->addColumn('code_spl', fn($row) => $row->po->code_spl ?? '-')
                ->addColumn('nama_spl', fn($row) => $row->po->nama_spl ?? '-')
                ->addColumn('action', function ($row) {
                    return '<button data-id="' . $row->id . '" class="btn btn-sm btn-primary btn-rollback"><i class="fa fa-undo"></i></button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }
    
    public function rollbackPost(Request $request)
    {
        try {
            $id = $request->id;
            $tdo = Tdo::onlyTrashed()->where('id', $id)->first();
    
            if (!$tdo) {
                return response()->json(['message' => 'Data tidak ditemukan atau sudah aktif.'], 404);
            }
    
            $tdo->restore();
            return response()->json(['message' => 'Data berhasil dipulihkan.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memulihkan data.'], 500);
        }
    }

    // public function destroy($id)
    // {
    //     $courier = MCourier::findOrFail($id);
    //     $courier->delete();

    //     return redirect('/couriers')->with('success', 'Courier is successfully deleted');
    // }
}