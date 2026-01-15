@extends('layouts.admin')

@section('content')

<div class="container-fluid">

    {{-- =======================
        ROW 1 : EXECUTIVE KPI
    ======================= --}}
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('purchase_order.index') }}">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                Pemesanan Barang
                            </div>
                            <div class="h4 font-weight-bold">{{$total_po}}</div>
                        </div>
                        <i class="fas fa-file-signature fa-2x text-gray-300"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('delivery_order.index') }}">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                Pengiriman Barang
                            </div>
                            <div class="h4 font-weight-bold">{{$total_do}}</div>
                        </div>
                        <i class="fas fa-truck fa-2x text-gray-300"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- =======================
        FILTER PERIODE
    ======================= --}}
    <div class="card shadow mb-4">
        <div class="card-body d-flex align-items-center flex-wrap">

            {{-- BULANAN --}}
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" class="custom-control-input" id="chkMonthly" checked>
                <label class="custom-control-label font-weight-bold" for="chkMonthly">
                    Bulanan
                </label>
            </div>

            <select id="filterMonth" class="form-control form-control-sm mr-2" style="width:140px">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                @endfor
            </select>

            <select id="filterYearMonthly" class="form-control form-control-sm mr-4" style="width:120px">
                @for ($y = now()->year - 2; $y <= now()->year; $y++)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>

            {{-- TAHUNAN --}}
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" class="custom-control-input" id="chkYearly">
                <label class="custom-control-label font-weight-bold" for="chkYearly">
                    Tahunan
                </label>
            </div>

            <select id="filterYearOnly" class="form-control form-control-sm mr-4" style="width:120px" disabled>
                @for ($y = now()->year - 5; $y <= now()->year; $y++)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>

            <button id="btnApplyFilter" class="btn btn-sm btn-primary">
                <i class="fas fa-filter"></i> Terapkan
            </button>
        </div>
    </div>

    {{-- =======================
        FAST & SLOW MOVING (SIDE BY SIDE)
    ======================= --}}
    <div class="row">

        {{-- FAST MOVING --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header font-weight-bold">
                    ⚡ Top 10 Fast Moving Products
                    <small class="text-muted d-block">
                        Produk cepat keluar, perhatikan restock
                    </small>
                </div>
                <div class="card-body">
                    <canvas id="fastMovingChart" height="200"></canvas>
                </div>
            </div>
        </div>

        {{-- SLOW MOVING --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header font-weight-bold">
                    🐢 Top 10 Slow Moving Products
                    <small class="text-muted d-block">
                        Produk lambat bergerak, disarankan promo
                    </small>
                </div>
                <div class="card-body">
                    <canvas id="slowMovingChart" height="200"></canvas>
                </div>
            </div>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    let fastChart = null;
    let slowChart = null;

    /* =========================
       FORCE CHECKLIST LOGIC
       (HANYA 1 MODE BOLEH AKTIF)
    ========================== */

    function setMonthlyMode() {
        $('#chkMonthly').prop('checked', true);
        $('#chkYearly').prop('checked', false);

        // ENABLE MONTHLY
        $('#filterMonth').prop('disabled', false);
        $('#filterYearMonthly').prop('disabled', false);

        // DISABLE YEARLY
        $('#filterYearOnly').prop('disabled', true);
    }

    function setYearlyMode() {
        $('#chkMonthly').prop('checked', false);
        $('#chkYearly').prop('checked', true);

        // DISABLE MONTHLY
        $('#filterMonth').prop('disabled', true);
        $('#filterYearMonthly').prop('disabled', true);

        // ENABLE YEARLY
        $('#filterYearOnly').prop('disabled', false);
    }

    // EVENT CHECKLIST
    $('#chkMonthly').on('change', function () {
        if (this.checked) {
            setMonthlyMode();
        } else {
            // cegah kondisi dua-duanya off
            setMonthlyMode();
        }
    });

    $('#chkYearly').on('change', function () {
        if (this.checked) {
            setYearlyMode();
        } else {
            // cegah kondisi dua-duanya off
            setYearlyMode();
        }
    });

    /* ======================
       APPLY FILTER
       (SCRIPT ASLI KAMU)
    ====================== */
    $('#btnApplyFilter').on('click', function () {

        const mode = $('#chkMonthly').is(':checked') ? 'monthly' : 'yearly';

        const data = {
            mode  : mode,
            month : $('#filterMonth').val(),
            year  : mode === 'monthly'
                ? $('#filterYearMonthly').val()
                : $('#filterYearOnly').val()
        };

        $.get("{{ route('dashboard.chart.fastslow') }}", data, function (res) {

            if (fastChart) fastChart.destroy();
            if (slowChart) slowChart.destroy();

            /* ======================
               FAST MOVING
            ====================== */
            fastChart = new Chart(document.getElementById('fastMovingChart'), {
                type: 'bar',
                data: {
                    labels: res.fast.labels,
                    datasets: [
                        {
                            label: 'Product In',
                            data: res.fast.inbound,
                            backgroundColor: 'rgba(54,185,204,0.7)'
                        },
                        {
                            label: 'Product Out',
                            data: res.fast.outbound,
                            backgroundColor: 'rgba(246,194,62,0.7)'
                        }
                    ]
                },
                options: {
                    responsive:true,
                    interaction:{ mode:'index', intersect:false },
                    scales:{ y:{ beginAtZero:true } }
                }
            });

            /* ======================
               SLOW MOVING
            ====================== */
            slowChart = new Chart(document.getElementById('slowMovingChart'), {
                type: 'bar',
                data: {
                    labels: res.slow.labels,
                    datasets: [
                        {
                            label: 'Product In',
                            data: res.slow.inbound,
                            backgroundColor: 'rgba(54,185,204,0.7)'
                        },
                        {
                            label: 'Product Out',
                            data: res.slow.outbound,
                            backgroundColor: 'rgba(246,194,62,0.7)'
                        }
                    ]
                },
                options: {
                    responsive:true,
                    interaction:{ mode:'index', intersect:false },
                    scales:{ y:{ beginAtZero:true } }
                }
            });

        });
    });

    /* ======================
       INIT PAGE
    ====================== */
    $(document).ready(function () {
        setMonthlyMode();          // DEFAULT AMAN
        $('#btnApplyFilter').click();
    });
</script>

@endsection
