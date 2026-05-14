                <!-- Topbar -->
                <nav class="navbar navbar-expand topbar mb-4 static-top shadow" style="background-color:#f5f6f7;">

                    <!-- Sidebar Toggle (mobile only) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Datetime widget — kiri navbar -->
                    <div class="d-none d-sm-flex align-items-center mr-auto ml-2">
                        <div style="
                            display:flex;align-items:stretch;
                            background:#fff;
                            border:1px solid #e3e6f0;
                            border-radius:10px;
                            box-shadow:0 1px 4px rgba(0,0,0,.06);
                            overflow:hidden;
                        ">
                            {{-- Bagian tanggal --}}
                            <div style="
                                display:flex;flex-direction:column;justify-content:center;align-items:center;
                                padding:6px 14px;
                                border-right:1px solid #e3e6f0;
                                min-width:130px;
                            ">
                                <div id="nb-day" style="font-size:.68rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#4e73df;"></div>
                                <div style="display:flex;align-items:baseline;gap:4px;margin-top:1px;">
                                    <span id="nb-date" style="font-size:1.25rem;font-weight:800;color:#2d3748;line-height:1;font-variant-numeric:tabular-nums;"></span>
                                    <span style="display:flex;flex-direction:column;justify-content:center;">
                                        <span id="nb-month" style="font-size:.72rem;font-weight:600;color:#555;line-height:1.1;"></span>
                                        <span id="nb-year"  style="font-size:.68rem;color:#999;line-height:1.1;"></span>
                                    </span>
                                </div>
                            </div>
                            {{-- Bagian jam --}}
                            <div style="
                                display:flex;align-items:center;justify-content:center;
                                padding:6px 16px;
                                gap:2px;
                            ">
                                <i class="fas fa-clock" style="font-size:.75rem;color:#a0aec0;margin-right:6px;"></i>
                                <span id="nb-hh" style="font-size:1.1rem;font-weight:700;color:#2d3748;font-variant-numeric:tabular-nums;"></span>
                                <span class="nb-sep" style="font-size:1.1rem;font-weight:700;color:#4e73df;margin:0 1px;line-height:1;">:</span>
                                <span id="nb-mm" style="font-size:1.1rem;font-weight:700;color:#2d3748;font-variant-numeric:tabular-nums;"></span>
                                <span class="nb-sep" style="font-size:1.1rem;font-weight:700;color:#4e73df;margin:0 1px;line-height:1;">:</span>
                                <span id="nb-ss" style="font-size:1.1rem;font-weight:700;color:#a0aec0;font-variant-numeric:tabular-nums;"></span>
                            </div>
                        </div>
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
                        function pad(n) { return ('0' + n).slice(-2); }
                        function tick() {
                            var now = new Date();
                            document.getElementById('nb-day').textContent   = HARI[now.getDay()];
                            document.getElementById('nb-date').textContent  = pad(now.getDate());
                            document.getElementById('nb-month').textContent = BULAN[now.getMonth()];
                            document.getElementById('nb-year').textContent  = now.getFullYear();
                            document.getElementById('nb-hh').textContent    = pad(now.getHours());
                            document.getElementById('nb-mm').textContent    = pad(now.getMinutes());
                            document.getElementById('nb-ss').textContent    = pad(now.getSeconds());
                        }
                        tick();
                        setInterval(tick, 1000);
                    })();
                </script>