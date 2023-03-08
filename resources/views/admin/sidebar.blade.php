<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
        <img src="/assets/images/dashboard.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
             style="opacity: .8">
        <span class="brand-text font-weight-light">Administrator</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="/assets/images/man.png" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">Admin</a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <router-link to="/cmss" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            Dashboard
                        </p>
                    </router-link>
                </li>
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-chalkboard-teacher"></i>
                        <p>
                            Dosen
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <router-link to="/cmss/lecture" class="nav-link pl-5">
                                <p>Data</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/cmss/lecture-file" class="nav-link pl-5">
                                <p>Dokumen</p>
                            </router-link>
                        </li>
                    </ul>
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
                            <router-link to="/cmss/resident" class="nav-link pl-5">
                                <p>Data</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/cmss/presences" class="nav-link pl-5">
                                <p>Presensi</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/cmss/presence-resume" class="nav-link pl-5">
                                <p>Presensi Harian</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/cmss/presence-ilmiah-resume" class="nav-link pl-5">
                                <p>Resume Presensi</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/cmss/stase-plots" class="nav-link pl-5">
                                <p>Stase Residen</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/cmss/exams" class="nav-link pl-5">
                                <p>Ujian</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/cmss/letters" class="nav-link pl-5">
                                <p>Dokumen</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/cmss/student-logs" class="nav-link pl-5">
                                <p>Log Book</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/cmss/download" class="nav-link pl-5">
                                <p>Download</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/cmss/student-regs" class="nav-link pl-5">
                                <p>Calon PPDS</p>
                            </router-link>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            CBT
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <router-link to="/object/categories" class="nav-link pl-5">
                                <p>Kategori</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/object/quizzes" class="nav-link pl-5">
                                <p>Bank Soal</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/object/quiz-sections" class="nav-link pl-5">
                                <p>Paket Ujian</p>
                            </router-link>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <router-link to="/cmss/open-exam" class="nav-link">
                        <i class="nav-icon fas fa-door-open"></i>
                        <p>Ujian Terbuka</p>
                    </router-link>
                </li>
                <li class="nav-item">
                    <router-link to="/cmss/activity" class="nav-link">
                        <i class="nav-icon fas fa-calendar-alt"></i>
                        <p>Agenda</p>
                    </router-link>
                </li>

                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-chart-pie"></i>
                        <p>
                            Data Master
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <router-link to="/cmss/letter-records" class="nav-link pl-5">
                                <p>Surat</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/cmss/stase" class="nav-link pl-5">
                                <p>Stase</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/cmss/task" class="nav-link pl-5">
                                <p>Task</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/cmss/user" class="nav-link pl-5">
                                <p>Admin</p>
                            </router-link>
                        </li>
                    </ul>
                </li>
{{--                --}}
                <li class="nav-item">
                    <a href="{{route('logout')}}" class="nav-link" onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">
                        <i class="fa fa-power-off nav-icon danger"></i>
                        <p>Logout</p>
                    </a>
                </li>
                <form action="{{route('logout')}}" id="logout-form" method="POST" style="display: none">
                    @csrf
                </form>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
