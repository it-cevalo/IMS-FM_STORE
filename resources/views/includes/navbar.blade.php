                <!-- Topbar -->
                <nav class="navbar navbar-expand topbar mb-4 static-top shadow" style="background-color:#f5f6f7;">

                    <!-- Sidebar Toggle (mobile only) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Datetime — satu baris, kiri navbar -->
                    <div class="d-none d-sm-flex align-items-center mr-auto ml-2" style="gap:6px;color:#555;font-size:.85rem;">
                        <i class="fas fa-calendar-alt" style="color:#4e73df;"></i>
                        <span id="nb-datetime" style="font-variant-numeric:tabular-nums;"></span>
                    </div>

                    <!-- Kanan navbar -->
                    <ul class="navbar-nav align-items-center">

                        {{-- Tombol Panduan --}}
                        <li class="nav-item mr-3" title="Panduan halaman ini">
                            <button onclick="helpPanel.open()" style="
                                background:#4e73df;
                                border:none;
                                border-radius:50%;
                                width:32px;height:32px;
                                color:#fff;
                                font-weight:700;
                                font-size:1rem;
                                line-height:32px;
                                cursor:pointer;
                                display:flex;align-items:center;justify-content:center;
                            " title="Panduan">?</button>
                        </li>

                        <li class="nav-item">
                            <img src="{{asset('assets/img/logo_customer.png')}}" width="60px" height="60px" style="display:block;"/>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->
                <script>
                    (function () {
                        var HARI  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                        var BULAN = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                        var el = document.getElementById('nb-datetime');
                        function pad(n) { return ('0' + n).slice(-2); }
                        function tick() {
                            var now = new Date();
                            el.textContent =
                                HARI[now.getDay()] + ', ' +
                                pad(now.getDate()) + ' ' + BULAN[now.getMonth()] + ' ' + now.getFullYear() +
                                '  —  ' +
                                pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
                        }
                        tick();
                        setInterval(tick, 1000);
                    })();
                </script>