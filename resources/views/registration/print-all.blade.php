<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registrations</title>
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
            padding: 1px 2px;
            text-align: left;
            vertical-align: top;
        }

        table thead {
            background-color: #6b6b6b;
        }

        table thead th {
            font-weight: 600;
            color: #333;
            border-bottom: 1px solid #6b6b6b;
        }

        .p-8 {
            padding: 24px;
        }

        h6 {
            font-size: 14px;
            font-weight: 600;
            margin: 0 0 6px;
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

        .ms-2 {
            margin-left: 8px;
        }

        .d-flex {
            display: flex;
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

        @media print {
            .page {
                break-after: page;
            }
        }
    </style>
</head>

<body>
@foreach($registrations as $registration)
    <div class="p-8 page">
        <h6>I. Data Pribadi</h6>
        <div class="d-flex">
            <table class="mb-2">
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
                    <td colspan="3" style="font-size: 10px">{{ $registration->origin_address }}</td>
                </tr>
            </table>
            <div class="ms-2">
                @if(!empty($registration->image_uri))
                    <img src="{{$registration->image_uri}}" style="max-width: 20mm" alt="">
                @endif
            </div>
        </div>
        <div class="row mb-1">
            <div class="col-6 mb-1">
                <h6 class="mb-1">Ayah</h6>
                <table>
                    <tr>
                        <td width="30%">Nama</td>
                        <td colspan="3">{{ $registration->father_name }}</td>
                    </tr>
                    <tr>
                        <td width="30%">Pekerjaan</td>
                        <td colspan="3">{{ $registration->father_job }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-6 mb-1">
                <h6 class="mb-1">Ibu</h6>
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
        <div class="row mb-1">
            <div class="col-6">
                @if($registration->spouse_name)
                    <h6 class="mb-1">Pasangan</h6>
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
                @else
                    <h6 class="mb-1">Pasangan - </h6>
                @endif
            </div>
        </div>
        <div class="row mb-2">
            @if(isset($registration['children']) && count($registration['children']) > 0)
            <div class="col-12">
                <h6 class="mb-1">Anak</h6>
            </div>
                @foreach ($registration['children'] as $child)
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
            @else
            <div class="col-6">
                <h6 class="mb-1">Anak -</h6>
            </div>
            @endif
        </div>

        @if($registration->institution_name)
            <h6>II. Instansi Asal</h6>
            <div class="row mb-2">
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
        @else
            <h6>II. Instansi Asal -</h6>
        @endif

        <h6>III. Asal Pendidikan</h6>
        <div class="row mb-2">
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
                    @if(isset($registration['scores']))
                        @foreach ($registration['scores'] as $i => $score)
                            <tr>
                                <td style="width: 50%">{{ $score->name }}</td>
                                <td>{{ $score->desc }}</td>
                            </tr>
                        @endforeach
                    @endif
                </table>
            </div>
        </div>

        <h6>IV. Pendidikan Tambahan/Kursus/Penataran/Seminar</h6>
        <div class="row mb-2">
            <div class="col-12">
                <table>
                    <tr>
                        <th style="width: 40px">No</th>
                        <th>Nama</th>
                        <th>Tempat</th>
                        <th>Bidang</th>
                        <th>Tahun</th>
                        <th>Lama Pendidikan</th>
                    </tr>
                    @if(isset($registration['educations']))
                        @foreach ($registration['educations'] as $i => $education)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    @if(strlen($education->name) > 50)
                                        {{ substr($education->name, 0, 50) }} ..
                                    @else
                                        {{$education->name}}
                                    @endif
                                </td>
                                <td>{{ $education->place }}</td>
                                <td>{{ $education->desc }}</td>
                                <td>{{ $education->year }}</td>
                                <td>{{ $education->duration }}</td>
                            </tr>
                        @endforeach
                    @endif
                </table>
            </div>
        </div>

        <h6>V. Riwayat Pekerjaan</h6>
        <div class="row mb-2">
            <div class="col-12">
                <table>
                    <tr>
                        <th style="width: 40px">No</th>
                        <th style="width: 200px">Tempat Kerja</th>
                        <th>Durasi</th>
                        <th>Jabatan</th>
                        <th>Tahun</th>
                    </tr>
                    @if(isset($registration['jobs']))
                        @foreach ($registration['jobs'] as $i => $job)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $job->name }}</td>
                                <td>{{ $job->duration }}</td>
                                <td>{{ $job->desc }}</td>
                                <td>{{ $job->year }}</td>
                            </tr>
                        @endforeach
                    @endif
                </table>
            </div>
        </div>

        <h6>VI. Kegiatan Ilmiah (Penelitian/Publikasi/Ceramah) yang Pernah Dilakukan</h6>
        <div class="row mb-2">
            <div class="col-12">
                <table>
                    <tr>
                        <th style="width: 40px">No</th>
                        <th>Judul</th>
                        <th>Kegiatan</th>
                        <th>Tahun</th>
                    </tr>
                    @if(isset($registration['scientifics']))
                        @foreach ($registration['scientifics'] as $i => $scientific)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    @if(strlen($scientific->name) > 70)
                                        {{ substr($scientific->name, 0, 70) }} ..
                                    @else
                                        {{$scientific->name}}
                                    @endif
                                </td>
                                <td>{{ $scientific->desc }}</td>
                                <td>{{ $scientific->year }}</td>
                            </tr>
                        @endforeach
                    @endif
                </table>
            </div>
        </div>

        <h6>VII. Kegiatan dalam Organisasi Profesi/Masyarakat</h6>
        <div class="row mb-2">
            <div class="col-12">
                <table>
                    <tr>
                        <th style="width: 40px">No</th>
                        <th>Organisasi</th>
                        <th>Kegiatan</th>
                        <th>Tahun</th>
                    </tr>
                    @if(isset($registration['organisations']))
                        @foreach ($registration['organisations'] as $i => $organisation)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $organisation->name }}</td>
                                <td>
                                    @if(strlen($organisation->desc) > 120)
                                        {{ substr($organisation->desc, 0, 120) }} ..
                                    @else
                                        {{$organisation->desc}}
                                    @endif
                                </td>
                                <td>{{ $organisation->year }}</td>
                            </tr>
                        @endforeach
                    @endif
                </table>
            </div>
        </div>

        <h6>VIII. Penghargaan dari Pemerintah/Swasta</h6>
        <div class="row mb-2">
            <div class="col-12">
                <table>
                    <tr>
                        <th style="width: 40px">No</th>
                        <th>Jenis penghargaan</th>
                        <th>Judul Kegiatan</th>
                        <th>Tahun</th>
                    </tr>
                    @if(isset($registration['achievements']))
                        @foreach ($registration['achievements'] as $i => $achievement)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $achievement->name }}</td>
                                <td>{{ $achievement->desc }}</td>
                                <td>{{ $achievement->year }}</td>
                            </tr>
                        @endforeach
                    @endif
                </table>
            </div>
        </div>

        <h6>IX. Narasumber</h6>
        <div class="row mb-2">
            <div class="col-12">
                <table>
                    <tr>
                        <th style="width: 40px">No</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                    </tr>
                    @if(isset($registration['recommendations']))
                        @foreach ($registration['recommendations'] as $i => $recommendation)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $recommendation->name }}</td>
                                <td>{{ $recommendation->desc }}</td>
                            </tr>
                        @endforeach
                    @endif
                </table>
            </div>
        </div>
        <h6>X. Tujuan Setelah Lulus</h6>
        @if($registration->graduate_place)
            <table>
                <tr>
                    <td>Tujuan</td>
                    <td>{{ $registration->graduate_place }}</td>
                </tr>
                <tr>
                    <td>Alasan</td>
                    <td>{{ $registration->graduate_reason }}</td>
                </tr>
            </table>
        @else
            <h6 class="mb-1"></h6>
        @endif
    </div>
@endforeach
</body>

</html>
