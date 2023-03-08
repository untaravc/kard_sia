<style>
    table tr td{
        vertical-align: top;
    }
</style>
<div style="width: 100%; text-align: center">
    <h4 style="margin: 0">Data Pribadi</h4>

    <div style="text-align: center; margin-top: 25px">
        <div style="width: 120px; height: 160px; margin: 0 auto;">
            <img width="100%" src="data:image/jpg;base64, {{$img}}" alt="">
        </div>
    </div>

    <div style="display: flex; justify-content: center; margin-top: 10px">
        <div style="width: 80%">
            <table style="width: 100%">
                <tr>
                    <td>Nama</td>
                    <td>:</td>
                    <td>{{$data->name}}</td>
                </tr>
                <tr>
                    <td>Inisial</td>
                    <td>:</td>
                    <td>{{$data->studentProfile ? $data->studentProfile->initial : '' }}</td>
                </tr>
                <tr>
                    <td>No Reg</td>
                    <td>:</td>
                    <td>{{$data->studentProfile ? $data->studentProfile->code : '' }}</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>{{$data->studentProfile ? $data->studentProfile->address : '' }}</td>
                </tr>
                <tr>
                    <td>Kota</td>
                    <td>:</td>
                    <td>{{$data->studentProfile ? $data->studentProfile->city : '' }}</td>
                </tr>
                <tr>
                    <td>Kode Pos</td>
                    <td>:</td>
                    <td>{{$data->studentProfile ? $data->studentProfile->postal_code : '' }}</td>
                </tr>
                <tr>
                    <td>No Telp</td>
                    <td>:</td>
                    <td>{{$data->studentProfile ? $data->studentProfile->phone : '' }}</td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>:</td>
                    <td>{{$data->email }}</td>
                </tr>
                <tr>
                    <td>Tempat, Tanggal Lahir</td>
                    <td>:</td>
                    <td>
                        {{$data->studentProfile ? $data->studentProfile->pob : '' }},
                        {{$data->studentProfile ? date('d M Y', strtotime($data->studentProfile->dob)) : '' }}
                    </td>
                </tr>
                <tr>
                    <td>Pendidikan S1</td>
                    <td>:</td>
                    <td>{{$data->studentProfile ? $data->studentProfile->undergraduate : '' }}</td>
                </tr>
                <tr>
                    <td>Tanggal Lulus S1</td>
                    <td>:</td>
                    <td>{{$data->studentProfile ? $data->studentProfile->graduated_at : '' }}</td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>:</td>
                    <td>{{$data->studentProfile ? $data->studentProfile->degree : '' }}</td>
                </tr>
                <tr>
                    <td>Pembimbing</td>
                    <td>:</td>
                    <td>{{$data->studentProfile && $data->studentProfile->lecture ? $data->studentProfile->lecture->name : '' }}</td>
                </tr>
            </table>
        </div>
    </div>

</div>