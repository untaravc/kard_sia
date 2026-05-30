<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <title>Dokumentasi - SIA Kardiologi</title>
    <style>
        body {
            font-size: 14px;
        }

        .docs-sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            border-right: 1px solid #e5e7eb;
            background: #f8fafc;
            padding: 24px 16px;
        }

        .docs-sidebar .docs-brand {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 16px;
            color: #0f172a;
        }

        .docs-sidebar .docs-group-title {
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
            margin: 14px 0 4px;
        }

        .docs-sidebar .docs-group-children {
            list-style: none;
            padding-left: 12px;
            margin: 0;
            border-left: 1px solid #e2e8f0;
        }

        .docs-sidebar .nav-link {
            color: #334155;
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 13px;
        }

        .docs-sidebar .nav-link:hover {
            background: #e2e8f0;
        }

        .docs-sidebar .docs-empty {
            padding: 5px 10px;
            font-size: 12px;
            font-style: italic;
            color: #94a3b8;
        }

        .docs-content {
            padding: 32px 40px;
            max-width: 900px;
        }

        .docs-section {
            margin-bottom: 48px;
            scroll-margin-top: 16px;
        }

        .docs-section-group {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
        }

        .docs-section h2 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
            color: #0f172a;
        }
    </style>
</head>
<body>
@php
    // Daftar dokumentasi, dikelompokkan per peran (tree view).
    // Tambahkan entri baru pada 'children' grup yang sesuai untuk membuat halaman dokumentasi baru.
    $groups = [
        [
            'title' => 'Umum',
            'children' => [
                ['id' => 'pendaftaran', 'title' => 'Pendaftaran', 'view' => 'docs.registration'],
                ['id' => 'login', 'title' => 'Masuk / Login', 'view' => 'docs.login'],
            ],
        ],
        [
            'title' => 'Dosen',
            'children' => [
                ['id' => 'dosen-pratinjau-logbook', 'title' => 'Pratinjau Logbook Residen', 'view' => 'docs.dosen.lecture-logbook'],
                ['id' => 'dosen-presensi-agenda', 'title' => 'Presensi Agenda Ilmiah', 'view' => 'docs.dosen.lecture-activities'],
                ['id' => 'dosen-penilaian', 'title' => 'Penilaian', 'view' => 'docs.dosen.scoring'],
                ['id' => 'dosen-perbarui-profil', 'title' => 'Perbarui Profil', 'view' => 'docs.dosen.profile'],
            ],
        ],
        [
            'title' => 'Mahasiswa',
            'children' => [
                ['id' => 'mahasiswa-pengisian-logbook', 'title' => 'Pengisian Logbook', 'view' => 'docs.mahasiswa.logbook'],
                ['id' => 'mahasiswa-presensi-agenda', 'title' => 'Presensi Agenda', 'view' => 'docs.mahasiswa.agenda'],
                ['id' => 'mahasiswa-penilaian', 'title' => 'Penilaian', 'view' => 'docs.mahasiswa.scoring'],
            ],
        ],
        [
            'title' => 'Administrator',
            'children' => [
                ['id' => 'admin-data-dosen', 'title' => 'Data Dosen', 'view' => 'docs.admin.lectures'],
                ['id' => 'admin-data-mahasiswa', 'title' => 'Data Mahasiswa', 'view' => 'docs.admin.students'],
                ['id' => 'admin-data-stase', 'title' => 'Data Stase', 'view' => 'docs.admin.stases'],
                ['id' => 'admin-data-tugas-stase', 'title' => 'Data Tugas Stase', 'view' => 'docs.admin.stase-tasks'],
                ['id' => 'admin-agenda', 'title' => 'Agenda', 'view' => 'docs.admin.activities'],
                ['id' => 'admin-surat', 'title' => 'Surat', 'view' => 'docs.admin.letters'],
            ],
        ],
    ];
@endphp
<div class="container-fluid">
    <div class="row">
        {{-- Sidebar: tree view per peran --}}
        <nav class="col-12 col-md-4 col-lg-3 docs-sidebar">
            <div class="docs-brand">Dokumentasi</div>
            @foreach($groups as $group)
                <div class="docs-group-title">{{ $group['title'] }}</div>
                <ul class="docs-group-children">
                    @forelse($group['children'] as $child)
                        <li class="nav-item">
                            <a class="nav-link" href="#{{ $child['id'] }}">{{ $child['title'] }}</a>
                        </li>
                    @empty
                        <li class="docs-empty">Belum ada dokumentasi.</li>
                    @endforelse
                </ul>
            @endforeach
        </nav>

        {{-- Konten --}}
        <main class="col-12 col-md-8 col-lg-9">
            <div class="docs-content">
                @foreach($groups as $group)
                    @foreach($group['children'] as $child)
                        <section id="{{ $child['id'] }}" class="docs-section">
                            <div class="docs-section-group">{{ $group['title'] }}</div>
                            <h2>{{ $child['title'] }}</h2>
                            @include($child['view'])
                        </section>
                    @endforeach
                @endforeach
            </div>
        </main>
    </div>
</div>
</body>
</html>
