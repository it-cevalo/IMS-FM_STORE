@extends('layouts.admin')

@section('content')

<div class="container-fluid">

    {{-- =======================
        ROW 1 : EXECUTIVE KPI
    ======================= --}}
    <div class="row">

        {{-- PURCHASE ORDER --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('purchase_order.index') }}">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                Purchase Order
                            </div>
                            <div class="h4 font-weight-bold">
                                {{ $total_po }}
                            </div>
                        </div>
                        <i class="fas fa-file-signature fa-2x text-gray-300"></i>
                    </div>
                </div>
            </a>
        </div>

        {{-- DELIVERY ORDER --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('delivery_order.index') }}">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                Delivery Order
                            </div>
                            <div class="h4 font-weight-bold">
                                {{ $total_do }}
                            </div>
                        </div>
                        <i class="fas fa-truck fa-2x text-gray-300"></i>
                    </div>
                </div>
            </a>
        </div>

    </div>

    {{-- =======================
        ROW 2 : INBOUND vs OUTBOUND CHART
    ======================= --}}
    <div class="row">

        <div class="col-md-8 mb-4">
            <div class="card shadow position-relative">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold">Product In vs Product Out (Monthly)</span>

                    <div class="d-flex align-items-center">
                        <button id="btnRefresh" class="btn btn-sm btn-outline-primary mr-2">
                            <i class="fas fa-sync"></i>
                        </button>

                        <select id="filterMonth" class="form-control form-control-sm mr-2">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                </option>
                            @endfor
                        </select>

                        <select id="filterYear" class="form-control form-control-sm">
                            @for ($y = now()->year - 2; $y <= now()->year; $y++)
                                <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="card-body">
                    <canvas id="inOutChart" style="min-height:300px"></canvas>
                </div>
            </div>
        </div>

        {{-- SUMMARY --}}
        <div class="col-md-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header font-weight-bold">Month Summary</div>
                <div class="card-body">
                    <p>Product In : <strong id="sumInbound">0</strong></p>
                    <p>Product Out : <strong id="sumOutbound">0</strong></p>
                    <p id="sumBalance" class="text-danger">
                        Balance : <strong>0</strong>
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
let chart = null;
let isLoading = false;

function loadChart() {

    if (isLoading) return; // cegah request numpuk
    isLoading = true;

    const month = $('#filterMonth').val();
    const year  = $('#filterYear').val();

    $.ajax({
        url: "{{ route('dashboard.chart.inout') }}",
        type: 'GET',
        data: { month, year },

        success: function (res) {

            // ===== UPDATE SUMMARY =====
            $('#sumInbound').text(res.summary.inbound);
            $('#sumOutbound').text(res.summary.outbound);
            $('#sumBalance').html(
                `Balance : <strong>${res.summary.balance}</strong>`
            );

            // ===== RENDER CHART =====
            if (chart) {
                chart.destroy();
            }

            chart = new Chart(document.getElementById('inOutChart'), {
                type: 'bar',
                data: {
                    labels: res.labels,
                    datasets: [
                        {
                            label: 'Product In',
                            data: res.inbound,
                            backgroundColor: 'rgba(54, 185, 204, 0.6)'
                        },
                        {
                            label: 'Product Out',
                            data: res.outbound,
                            backgroundColor: 'rgba(246, 194, 62, 0.6)'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        },

        complete: function () {
            isLoading = false;
        },

        error: function () {
            isLoading = false;
            console.error('Gagal memuat data grafik');
        }
    });
}

$(document).ready(function () {

    // FIRST LOAD
    loadChart();

    // FILTER CHANGE
    $('#filterMonth, #filterYear').on('change', function () {
        loadChart();
    });

    // MANUAL REFRESH
    $('#btnRefresh').on('click', function () {
        loadChart();
    });

    // AUTO REFRESH 1 MENIT (SILENT)
    setInterval(loadChart, 60000);
});
</script>
@endsection
