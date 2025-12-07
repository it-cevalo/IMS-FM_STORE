<!-- Sidebar -->
<ul class="navbar-nav bg-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{route('dashboard')}}">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-chart-bar"></i>
        </div>
        <div class="sidebar-brand-text mx-3">IMS</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="{{route('dashboard')}}">
            <i class="fas fa-fw fa-home"></i>
            <span>Dashboard</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        MENU
    </div>

    @if(Auth::user()->position == 'SUPERADMIN')
    <!-- Nav Item - Master -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseMaster"
            aria-expanded="true" aria-controls="collapseMaster">
            <i class="fas fa-fw fa-folder"></i>
            <span>Master</span>
        </a>
        <div id="collapseMaster" class="collapse" aria-labelledby="headingMaster" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{route('product_unit.index')}}">Unit of Measure (UOM)</a>
                <a class="collapse-item" href="{{route('product_type.index')}}">Product Type</a>
                <a class="collapse-item" href="{{route('sku.index')}}">SKU</a>
                <a class="collapse-item" href="{{route('product.index')}}">Product</a>
                {{-- <a class="collapse-item" href="{{route('customers.index')}}">Customer</a> --}}
                <a class="collapse-item" href="{{route('suppliers.index')}}">Supplier</a>
                <a class="collapse-item" href="{{route('stores.index')}}">Store</a>
                <a class="collapse-item" href="{{route('warehouses.index')}}">Warehouse</a>
                <a class="collapse-item" href="{{route('couriers.index')}}">Courier</a>
                <div class="collapse-divider"></div>
            </div>
        </div>
    </li>

    <!-- Nav Item - Stock Opname -->
    <li class="nav-item">
        <a class="nav-link" href="{{route('stock_opname.index')}}">
            <i class="fas fa-fw fa-database"></i>
            <span>Stock Opname</span></a>
    </li>

    <!-- Nav Item - Transaction -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTransaction"
            aria-expanded="true" aria-controls="collapseTransaction">
            <i class="fas fa-fw fa-list"></i>
            <span>Transaction</span>
        </a>
        <div id="collapseTransaction" class="collapse" aria-labelledby="headingTransaction"
            data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{route('purchase_order.index')}}">Purchase Order</a>
                <a class="collapse-item" href="{{route('delivery_order.index')}}">Delivery Order</a>
                {{-- <a class="collapse-item" href="{{route('product_transfer.index')}}">Product Transfer</a> --}}
                <div class="collapse-divider"></div>
            </div>
        </div>
    </li>

    <!-- Nav Item - Report -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseReport"
            aria-expanded="true" aria-controls="collapseReport">
            <i class="fas fa-fw fa-file"></i>
            <span>Report</span>
        </a>
        <div id="collapseReport" class="collapse" aria-labelledby="headingReport" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{route('report_product.index')}}">Product</a>
                <a class="collapse-item" href="{{route('report_invoicing.index')}}">Invoicing</a>
                <a class="collapse-item" href="{{route('report_customer.index')}}">Customer</a>
                <a class="collapse-item" href="{{route('report_stock_mutation.index')}}">Stock Mutation</a>
                <div class="collapse-divider"></div>
            </div>
        </div>
    </li>

    <!-- Nav Item - Utilities -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
            aria-expanded="true" aria-controls="collapseUtilities">
            <i class="fas fa-fw fa-wrench"></i>
            <span>Utilities</span>
        </a>
        <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">User Security:</h6>
                <a class="collapse-item" href="{{route('users.index')}}">User</a>
                <a class="collapse-item" href="{{route('roles.index')}}">Roles</a>
            </div>
        </div>
    </li>
    @endif

    <!-- Logout -->
    <li class="nav-item">
        <form id="logout-form" action="{{ route('logout') }}" method="POST">
            @csrf
        </form>
        <a href="#" class="nav-link" onclick="event.preventDefault();
        document.getElementById('logout-form').submit();">
            <i class="fas fa-fw fa-sign-out-alt"></i>
            <span>Sign Out</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>
<!-- End of Sidebar -->