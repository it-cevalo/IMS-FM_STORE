<ul class="navbar-nav bg-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    {{-- BRAND --}}
    <a class="sidebar-brand d-flex align-items-center justify-content-center"
       href="{{ route('dashboard') }}">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-chart-bar"></i>
        </div>
        <div class="sidebar-brand-text mx-3">IMS</div>
    </a>

    <hr class="sidebar-divider my-0">

    {{-- DASHBOARD (OPTIONAL: BISA JUGA FULL DARI DB) --}}
    <li class="nav-item">
        <a class="nav-link" href="{{ route('dashboard') }}">
            <i class="fas fa-fw fa-home"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">MENU</div>

    {{-- MENU DINAMIS BERDASARKAN ROLE --}}
    @foreach($menus[null] ?? [] as $menu)
    

        {{-- MENU TANPA CHILD --}}
        @if(empty($menus[$menu->menu_id]))
            <li class="nav-item">
                <a class="nav-link"
                   href="{{ $menu->route_name ? route($menu->route_name) : '#' }}">
                    <i class="{{ $menu->icon }}"></i>
                    <span>{{ $menu->name }}</span>
                </a>
            </li>
        @else
            {{-- MENU PARENT --}}
            <li class="nav-item">
                <a class="nav-link collapsed"
                   href="#"
                   data-toggle="collapse"
                   data-target="#collapse{{ $menu->menu_id }}"
                   aria-expanded="false">
                    <i class="{{ $menu->icon }}"></i>
                    <span>{{ $menu->name }}</span>
                </a>

                <div id="collapse{{ $menu->menu_id }}"
                     class="collapse"
                     data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">

                        @foreach($menus[$menu->menu_id] as $child)
                            <a class="collapse-item"
                               href="{{ $child->route_name ? route($child->route_name) : '#' }}">
                                {{ $child->name }}
                            </a>
                        @endforeach

                    </div>
                </div>
            </li>
        @endif

    @endforeach

    <hr class="sidebar-divider d-none d-md-block">

    {{-- LOGOUT --}}
    <li class="nav-item">
        <form id="logout-form" action="{{ route('logout') }}" method="POST">
            @csrf
        </form>
        <a href="#" class="nav-link"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fas fa-fw fa-sign-out-alt"></i>
            <span>Sign Out</span>
        </a>
    </li>

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>