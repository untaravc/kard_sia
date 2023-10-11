<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4" style="background-color: #000000">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
        <img src="/assets/images/dashboard.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
             style="opacity: .8">
        <span class="brand-text font-weight-light">Laman RESIDEN</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="/assets/images/man.png" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{Auth::guard('student')->user()->name}}</a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
                     with font-awesome or any other icon font library -->
                <li class="nav-item">
                    <router-link to="/resident" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            Dasbor
                        </p>
                    </router-link>
                </li>
                <li class="nav-item">
                    <router-link to="/resident/score" class="nav-link">
                        <i class="nav-icon fas fa-clipboard-check"></i>
                        <p>Nilai</p>
                    </router-link>
                </li>
                <li class="nav-item">
                    <router-link to="/resident/logbooks" class="nav-link">
                        <i class="nav-icon fas fa-edit"></i>
                        <p>Log Book</p>
                    </router-link>
                </li>
                <li class="nav-item">
                    <router-link to="/resident/letters" class="nav-link">
                        <i class="nav-icon fas fa-envelope"></i>
                        <p>Dokumen</p>
                    </router-link>
                </li>
                <li class="nav-item">
                    <router-link to="/resident/activity" class="nav-link">
                        <i class="nav-icon fas fa-running"></i>
                        <p>Aktivitas</p>
                    </router-link>
                </li>
                <li class="nav-item">
                    <a href="/resident-logout" class="nav-link" onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">
                        <i class="fa fa-power-off nav-icon danger"></i>
                        <p>Keluar</p>
                    </a>
                </li>
                <form action="/resident-logout" id="logout-form" method="POST" style="display: none">
                    @csrf
                </form>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
