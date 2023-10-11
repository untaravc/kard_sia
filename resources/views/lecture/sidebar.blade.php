<!-- Main Sidebar Container -->

<aside class="main-sidebar sidebar-light-cyan elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
        <img src="/assets/images/dashboard.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
             style="opacity: .8">
        <span class="brand-text font-weight-light">Laman DOSEN</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                @if(Auth::guard('lecture')->check() && isset(Auth::guard('lecture')->user()->lectureProfile))
                    <img src="/storage/{{Auth::guard('lecture')->user()->lectureProfile['image']}}" onerror="this.onerror=null;this.src='/assets/images/man.png';" class="img-circle elevation-2" alt="User Image">
                @endif
            </div>
            <div class="info">
                <a href="#" class="d-block">{{Auth::guard('lecture')->user()->name}}</a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <router-link to="/dosen/" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            Dashboard
                        </p>
                    </router-link>
                </li>
                <li class="nav-item">
                    <router-link to="/dosen/activities" class="nav-link">
                        <i class="nav-icon fas fa-calendar-alt"></i>
                        <p>Agenda</p>
                    </router-link>
                </li>
                <li class="nav-item">
                    <router-link to="/dosen/letters" class="nav-link">
                        <i class="nav-icon fas fa-envelope"></i>
                        <p>Dokumen</p>
                    </router-link>
                </li>
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            Residen
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <router-link to="/dosen/resident" class="nav-link pl-5">
                                <p>Data</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/dosen/presences" class="nav-link pl-5">
                                <p>Presensi</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/dosen/stase-plots" class="nav-link pl-5">
                                <p>Stase Residen</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/dosen/resident-logs" class="nav-link pl-5">
<<<<<<< HEAD
                                <p>Log book approval</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/dosen/student-logs" class="nav-link pl-5">
=======
>>>>>>> 3a0ad237c4a32c5d3821fb143edff98da043fa9c
                                <p>Log book</p>
                            </router-link>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a href="/dosen-logout" class="nav-link" onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">
                        <i class="fa fa-power-off nav-icon danger"></i>
                        <p>Logout</p>
                    </a>
                </li>
                <form action="/dosen-logout" id="logout-form" method="POST" style="display: none">
                    @csrf
                </form>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
