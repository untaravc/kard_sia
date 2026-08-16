<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registration {{$registration->name}}</title>
    <style>
        body {
            font-size: 12px;
            font-family: Arial, sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        table th,
        table td {
            border: 1px solid #6b6b6b;
            padding: 2px;
            text-align: left;
            vertical-align: top;
        }

        table thead {
            background-color: #6b6b6b;
        }

        table thead th {
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #6b6b6b;
        }

        .p-8 {
            padding: 24px;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .mb-1 {
            margin-bottom: 4px;
        }

        .mb-2 {
            margin-bottom: 8px;
        }

        .mb-4 {
            margin-bottom: 16px;
        }

        h4 {
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 8px;
        }

        h5 {
            font-size: 14px;
            font-weight: 600;
            margin: 0 0 6px;
        }

        .ms-2 {
            margin-left: 8px;
        }

        .d-flex {
            display: flex;
        }

        .align-items-center {
            align-items: center;
        }

        .justify-content-end {
            justify-content: flex-end;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin-left: -6px;
            margin-right: -6px;
        }

        .col-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding-left: 6px;
            padding-right: 6px;
            box-sizing: border-box;
        }

        .col-12 {
            flex: 0 0 100%;
            max-width: 100%;
            padding-left: 6px;
            padding-right: 6px;
            box-sizing: border-box;
        }
    </style>
</head>

<body>
<div class="d-flex align-items-center">
    <div>
        <img style="width: 100px" src="/assets/images/logo-ugm.png" alt="">
    </div>
    <div style="font-size: 14px">
        <p class="mb-0">FORMULIR PENDAFTARAN</p>
        <p class="mb-0">PROGRAM PENDIDIKAN DOKTER SPESIALIS I</p>
        <p class="mb-0">Program Studi Jantung dan Pembuluh Darah, Fakultas Kedokteran, Kesehatan Masyarakat, dan
            Keperawatan, UNIVERSITAS</p>
    </div>
</div>
<div style="border-top: 1px solid gray; border-bottom: 2px solid gray; height: 4px; margin-top: 10px"></div>
<div class="p-8">
    <h4>I. Data Pribadi</h4>
    <div class="d-flex">
        <table class="mb-4">
            <tr>
                <td width="30%">Nama</td>
                <td colspan="3">{{ $registration->name }}</td>
            </tr>
            <tr>
                <td>Janis Kelamin</td>
                <td colspan="3">{{ $registration->gender == 'M' ? 'Laki-laki' : 'Perempuan' }}</td>
            </tr>
            <tr>
                <td>Tempat tanggal Lahir</td>
                <td>{{ $registration->birth_place }}</td>
                <td>{{ $registration->birth_date }}</td>
                <td>{{ $registration->age }} tahun</td>
            </tr>
            <tr>
                <td>Agama</td>
                <td colspan="3">{{ ucfirst($registration->religion) }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td colspan="3">{{ $registration->origin_address }}</td>
            </tr>
        </table>
        <div class="ms-2">
            @if(!empty($registration->image_uri))
                <img src="{{$registration->image_uri}}" style="max-width: 30mm" alt="">
            @endif
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-6 mb-2">
            <h5 class="mb-1">Ayah</h5>
            <table>
                <tr>
                    <td width="30%">Nama</td>
                    <td colspan="3">{{ $registration->father_name }}</td>
                </tr>
                <tr>
                    <td width="30%">Pekerjaan</td>
                    <td colspan="3">{{ $registration->father_job }}</td>
                </tr>
                <tr>
                    <td width="30%">Alamat</td>
                    <td colspan="3">{{ $registration->father_address }}</td>
                </tr>
            </table>
        </div>
        <div class="col-6 mb-2">
            <h5 class="mb-1">Ibu</h5>
            <table>
                <tr>
                    <td width="30%">Nama</td>
                    <td colspan="3">{{ $registration->mother_name }}</td>
                </tr>
                <tr>
                    <td width="30%">Pekerjaan</td>
                    <td colspan="3">{{ $registration->mother_job }}</td>
                </tr>
            </table>
        </div>

    </div>
    <div class="row mb-2">
        <div class="col-6">
            <h5 class="mb-1">Pasangan</h5>
            <table>
                <tr>
                    <td width="30%">Nama</td>
                    <td colspan="3">{{ $registration->spouse_name }}</td>
                </tr>
                <tr>
                    <td width="30%">Pekerjaan</td>
                    <td colspan="3">{{ $registration->spouse_job }}</td>
                </tr>
            </table>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-12">
        <h5 class="mb-1">Anak</h5>
        </div>
        @foreach ($children as $child)
            <div class="col-6">
                <table>
                    <tr>
                        <td width="30%">Nama</td>
                        <td colspan="3">{{ $child->name }}</td>
                    </tr>
                    <tr>
                        <td width="30%">Tahun Lahir</td>
                        <td colspan="3">{{ $child->year }}</td>
                    </tr>
                </table>
            </div>
        @endforeach
    </div>
    <h4>II. Instansi Asal</h4>
    <div class="row mb-4">
        <div class="col-12">
            <table>
                <tr>
                    <td width="30%">NIP / NRP</td>
                    <td colspan="3">{{ $registration->institution_user_id }}</td>
                </tr>
                <tr>
                    <td width="30%">Nama</td>
                    <td colspan="3">{{ $registration->institution_name }}</td>
                </tr>
                <tr>
                    <td width="30%">Alamat</td>
                    <td colspan="3">{{ $registration->institution_address }}</td>
                </tr>
            </table>
        </div>
    </div>


    <h4>III. Asal Pendidikan</h4>
    <div class="row mb-4">
        <div class="col-6">
            <table>
                <tr>
                    <td style="width: 40%">Asal Universitas</td>
                    <td>{{ $registration->origin_university }}</td>
                </tr>
                <tr>
                    <td>Status Akreditasi</td>
                    <td>{{ $registration->origin_university_accreditation }}</td>
                </tr>
                <tr>
                    <td>Tahun Masuk</td>
                    <td>{{ $registration->s1_init_year }}</td>
                </tr>
                <tr>
                    <td>Index Prestasi S1</td>
                    <td>{{ $registration->ip_s1 }}</td>
                </tr>
                <tr>
                    <td>Index Prestasi Profesi</td>
                    <td>{{ $registration->ip_profession }}</td>
                </tr>
                <tr>
                    <td>Index Prestasi Komulatif</td>
                    <td>{{ $registration->ip_commulative }}</td>
                </tr>
            </table>
        </div>
        <div class="col-6">
            <table>
                @foreach ($scores as $i => $score)
                    <tr>
                        <td style="width: 50%">{{ $score->name }}</td>
                        <td>{{ $score->desc }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>

    <h4>IV. Pendidikan Tambahan/Kursus/Penataran/Seminar</h4>
    <div class="row mb-4">
        <div class="col-12">
            <table>
                <tr>
                    <th style="width: 40px">No</th>
                    <th style="width: 300px">Nama</th>
                    <th>Tempat</th>
                    <th>Bidang</th>
                    <th>Tahun</th>
                    <th>Lama Pendidikan</th>
                </tr>
                @foreach ($educations as $i => $education)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $education->name }}</td>
                        <td>{{ $education->place }}</td>
                        <td>{{ $education->desc }}</td>
                        <td>{{ $education->year }}</td>
                        <td>{{ $education->duration }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>

    <h4>V. Riwayat Pekerjaan</h4>
    <div class="row mb-4">
        <div class="col-12">
            <table>
                <tr>
                    <th style="width: 40px">No</th>
                    <th style="width: 200px">Tempat Kerja</th>
                    <th>Durasi</th>
                    <th>Jabatan</th>
                    <th>Tahun</th>
                </tr>
                @foreach ($jobs as $i => $job)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $job->name }}</td>
                        <td>{{ $job->duration }}</td>
                        <td>{{ $job->desc }}</td>
                        <td>{{ $job->year }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>

    <h4>VI. Kegiatan Ilmiah (Penelitian/Publikasi/Ceramah) yang Pernah Dilakukan</h4>
    <div class="row mb-4">
        <div class="col-12">
            <table>
                <tr>
                    <th style="width: 40px">No</th>
                    <th style="width: 200px">Nama Agenda/Kegiatan</th>
                    <th>Kegiatan</th>
                    <th>Tahun</th>
                </tr>
                @foreach ($scientifics as $i => $scientific)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $scientific->name }}</td>
                        <td>{{ $scientific->desc }}</td>
                        <td>{{ $scientific->year }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>

    <h4>VII. Kegiatan dalam Organisasi Profesi/Masyarakat</h4>
    <div class="row mb-4">
        <div class="col-12">
            <table>
                <tr>
                    <th style="width: 40px">No</th>
                    <th style="width: 200px">Organisasi</th>
                    <th>Kegiatan</th>
                    <th>Tahun</th>
                </tr>
                @foreach ($organisations as $i => $organisation)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $organisation->name }}</td>
                        <td>{{ $organisation->desc }}</td>
                        <td>{{ $organisation->year }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>

    <h4>VIII. Penghargaan dari Pemerintah/Swasta</h4>
    <div class="row mb-4">
        <div class="col-12">
            <table>
                <tr>
                    <th style="width: 40px">No</th>
                    <th style="width: 200px">Jenis penghargaan</th>
                    <th>Judul Kegiatan</th>
                    <th>Tahun</th>
                </tr>
                @foreach ($achievements as $i => $achievement)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $achievement->name }}</td>
                        <td>{{ $achievement->desc }}</td>
                        <td>{{ $achievement->year }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>

    <h4>IX. Narasumber</h4>
    <div class="row mb-4">
        <div class="col-12">
            <table>
                <tr>
                    <th style="width: 40px">No</th>
                    <th style="width: 200px">Nama</th>
                    <th>Jabatan</th>
                    <th>Alamat, Telepon</th>
                </tr>
                @foreach ($recommendations as $i => $recommendation)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $recommendation->name }}</td>
                        <td>{{ $recommendation->desc }}</td>
                        <td>{{ $recommendation->contact }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-end">
        <div style="width: 250px">
            <div style="margin-bottom: 70px">Pendaftar</div>
            {{ $registration->name }}
        </div>
    </div>
</div>
</body>

</html>
