                <!-- Topbar -->
                <nav class="navbar navbar-expand topbar mb-4 static-top shadow" style="background-color:#f5f6f7;">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    {{-- <!-- Topbar Search -->
                    <div
                        class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <div class="text-center text-gray-900">Welcome {{ Auth::user()->name }}</div>
                        <div class="text-center text-gray-900" id="date-time"></div>
                    </div> --}}

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                                aria-labelledby="searchDropdown">
                                <form class="form-inline mr-auto w-100 navbar-search">
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light border-0 small"
                                            placeholder="Search for..." aria-label="Search"
                                            aria-describedby="basic-addon2">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button">
                                                <i class="fas fa-search fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </li>

                        <img src="{{asset('assets/img/logo_customer.png')}}" width="70px" height="70px"/>

                        <!-- Nav Item - User Information -->
                        <!-- <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ Auth::user()->name }}</span>
                            </a>
                            Dropdown - User Information
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                {{-- <a class="dropdown-item" href="#">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Settings
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Activity Log
                                </a> --}}
                            </div>
                        </li> -->

                    </ul>

                </nav><br/>
                <!-- End of Topbar -->
                <script>
		        	// Function to format 1 in 01
		        	// const zeroFill = n => {
		        	// 	return ('0' + n).slice(-2);
		        	// }
                
		        	// // Creates interval
		        	// const interval = setInterval(() => {
		        	// 	// Get current time
		        	// 	const now = new Date();
                    
		        	// 	// Format date as in mm/dd/aaaa hh:ii:ss
		        	// 	const dateTime = zeroFill((now.getMonth() + 1)) + '/' + zeroFill(now.getUTCDate()) + '/' + now.getFullYear() + ' ' + zeroFill(now.getHours()) + ':' + zeroFill(now.getMinutes()) + ':' + zeroFill(now.getSeconds());
                    
		        	// 	// Display the date and time on the screen using div#date-time
		        	// 	document.getElementById('date-time').innerHTML = dateTime;
		        	// }, 1000);
		        </script>