@extends('layouts.admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Delivery Order</h6>
    </div>
    <div class="card-body">
        @if(\Session::has('error'))
        <div class="alert alert-danger">
            <span>{{ \Session::get('error') }}</span>
            <button type="button" class="close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @elseif(\Session::has('success'))
        <div class="alert alert-success">
            <span>{{ \Session::get('success') }}</span>
            <button type="button" class="close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
        <form action="{{route('delivery_order.store')}}" method="POST">
            @csrf
            {{-- <div class="mb-3">
                <label for="exampleFormControlInput1">Purchase Order</label>
                <div class="input-group">
                    <select class="form-control" name="id_po" value="{{old('id_po')}}" required>
                        <option value="">....</option>
                        @foreach($po as $p)
                        <option value="{{$p->id}}">
                            {{ \Carbon\Carbon::parse($p->tgl_po)->format('Y-m-d')}}/{{$p->no_po}}/{{$p->nama_spl}}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="validation"></div>
            @error('id_po')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror --}}
            <div class="mb-3">
                <label for="exampleFormControlInput1">Date</label>
                <input class="form-control" id="exampleFormControlInput1" name="tgl_do" type="date" required>
            </div>
            <div class="validation"></div>
            @error('tgl_do')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label>Number</label>
                <div class="input-group">
                    <input class="form-control" id="no_do" name="no_do" type="text" placeholder="Input DO Number" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <input type="checkbox" id="autoGenerate"> Auto
                        </div>
                    </div>
                </div>
            </div>
            <div class="validation"></div>
            @error('no_do')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            {{-- <div class="mb-3">
                <label for="exampleFormControlInput1">Attachment Status</label>
                <select class="form-control" name="status_lmpr_do" value="{{old('status_lmpr_do')}}" required>
                    <option value="">....</option>
                    <option value="OK">OK</option>
                    <option value="HOLD">HOLD</option>
                </select>
            </div>
            <div class="validation"></div>
            @error('status_lmpr_do')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror --}}
            <div class="mb-3">
                <label for="exampleFormControlInput1">Shipping Via</label>
                <select class="form-control" name="shipping_via" value="{{old('shipping_via')}}" required>
                    <option value="">....</option>
                    <option value="HANDCARRY">HANDCARRY</option>
                    <option value="EKSPEDISI">EKSPEDISI</option>
                </select>
            </div>
            <div class="validation"></div>
            @error('shipping_via')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="mb-3">
                <label for="exampleFormControlInput1">Note</label>
                <textarea class="form-control" id="exampleFormControlInput1" name="reason_do" type="text"
                    placeholder="Input Note" required></textarea>
            </div>
            <div class="validation"></div>
            @error('reason_do')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="{{route('delivery_order.index')}}" class="btn btn-dark">Back</a>
        </form>
    </div>
</div>
<script>
    $(document).on('change', '#autoGenerate', function () {
        if ($(this).is(':checked')) {
    
            let tgl = $('input[name="tgl_do"]').val();
    
            if (!tgl) {
                alert("Please select DO Date first!");
                $(this).prop('checked', false);
                return;
            }
    
            $.ajax({
                url: '/delivery-order/autogen',
                data: { tgl_do: tgl },
                success: function(res) {
                    $('#no_do').val(res.no_do);
                }
            });
    
        } else {
            $('#no_do').val('');
        }
    });
</script>    
@endsection