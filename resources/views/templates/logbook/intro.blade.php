<div>
    <h5 style="text-align: center">PETUNJUK UMUM PENGGUNAAN BUKU LOG</h5>
    Tujuan
    <ol>
        <li>Mendokumentasikan semua kegiatan pendidikan</li>
        <li>Mendokumentasikan semua hasil evaluasi</li>
        <li>Sebagai syarat kenaikan tahapan, ujian local, ujian nasional</li>
    </ol>

    Petunjuk
    <ol>
        <li>Logbook diisikan melalui Web Sistem</li>
        <li>Logbook dimintakan konfirmasi oleh supervisor terpilih</li>
        <li>Logbook dapat dicetak secara mandiri per stase maupun rekapitulasi</li>
    </ol>
</div>
<div class="pagebreak"></div>
<div>
    <h5 style="text-align: center">KODE ETIK PROFESIONAL PESERTA PPDS</h5>

    <p>
        Sebagai peserta PPDS-1 (Program Pendidikan Dokter Spesialis-1) Ilmu Penyakit Jantung dan Pembuluh Darah saya memahami bahwa saya memiliki banyak keuntungan dan kehormatan dalam mempelajari ilmu kedokteran. Selama pendidikan, saya menyadari besarnya tanggung jawab terhadap kesehatan seseorang.  Keadaan tersebut memerlukan standar etika dan empati yang tinggi. Dalam mengikuti semua aktivitas pendidikan saya akan berpegang teguh pada Kode Etik Kedokteran Indonesia. Lebih spesifik, saya senantiasa akan menerapkan prinsip-prinsip yang tertulis di bawah ini dalam pendidikan akademik, klinik, dan penelitian. Saya akan berusaha untuk mempertahankan semangat dan mematuhi kode ini selama pendidikan saya dan sepanjang karir dokter saya.
    </p>
        <b>Kejujuran</b>
    <ol>
        <li>Saya akan senantiasa berupaya mempertahankan standar kejujuran yang tinggi.</li>
        <li>Saya akan bersikap jujur terhadap pasien dan akan melaporkan secara akurat riwayat penyakit pasien, penemuan fisik, hasil pemeriksaan laboratorium, pemeriksaan penunjang lain dan informasi lain dalam pelayanan kesehatan. </li>
        <li>Dalam melakukan penelitian saya akan bersikap tanpa bias (obyektif), melaporkan hasilnya dengan jujur, memberikan penghargaan terhadap ide penelitian yang dilakukan oleh orang lain dan menghormati obyek penelitian sesuai dengan etika penelitian.</li>
    </ol>

    <b>Kerahasiaan</b>
    <ol>
        <li>Saya akan menjaga kerahasiaan sebagai kewajiban utama dalam penyelenggaraan pelayanan pasien.</li>
        <li>Saya akan membatasi pembahasan tentang pasien dengan anggota tim penyelenggara kesehatan di depan umum (tidak di dalam lift, lorong, kantin).</li>
        <li>Saya akan bersikap jujur terhadap pasien dan akan melaporkan secara akurat riwayat pasien, temuan tanda fisik, hasil tes dan informasi lain pelayanan kesehatan pasien, sesuai dengan keinginan pasien.</li>
    </ol>
    <b>Penghargaan terhadap individu lain</b>
    <ol>
        <li>Saya akan berupaya mempertahankan suasana yang kondusif dalam memberi pelayanan kepada pasien dan pembelajaran terhadap pasien.</li>
        <li>Saya akan memperlakukan pasien dan keluarga pasien dengan hormat, baik pada saat berhadapan langsung dengan pasien dan keluarganya, atau saat pembahasan antar anggota tim pelayanan kesehatan.
        </li>
        <li>Saat berinteraksi dengan pasien, saya akan menjaga dan menghargai privasi mereka.</li>
        <li>Saat berinteraksi dengan anggota tim kesehatan, saya akan bersikap kooperatif.</li>
        <li>Saya tidak mentoleransi diskriminasi ras, agama, jenis kelamin, orientasi seksual, umur, kecacatan atau status sosial dalam proses pelayanan pasien.</li>
        <li>Saya akan menilai kolega secara adil dan berusaha menyelesaikan semua konflik, dan memperlakukan secara baik setiap orang yang terlibat.</li>
        <li>Saya akan selalu menghormati guru-guru saya.</li>
    </ol>

    <b>Tanggung jawab</b>
    <ol>
        <li>Saya akan menempatkan pasien sebagai prioritas utama dalam tugas klinik saya.</li>
        <li>Saya akan mengenali keterbatasan dan meminta konsultasi/merujuk bila tingkat pengalaman saya tidak cukup untuk mengatasi sendiri.</li>
        <li>Saya akan bersikap profesional dalam tingkah laku, berbicara dan berpenampilan pada saat berhadapan dengan pasien, di dalam kelas dan tempat pelayanan kesehatan</li>
        <li>Saya tidak akan menggunakan alkohol atau obat-obat yang dapat mengganggu tugas profesi saya.</li>
        <li>Saya tidak menyalahgunakan jabatan klinik saya untuk mendapatkan hubungan romantik atau seksual dengan pasien atau anggota keluarga.</li>
    </ol>
</div>
<div class="pagebreak"></div>
<style>
    .id-table td{
        vertical-align: top
    }
</style>
<div>
    <table style="width: 100%">
        <tr>
            <td style="width: 23%; vertical-align: top">
                <div style="padding: 20px">
                    @if(isset($photo))
                        <div class="bg-profile" style="background-image: url('data:image/jpg;base64, {{$photo}}')"></div>
                    @else
                        <div class="bg-profile">-</div>
                    @endif
                </div>
            </td>
            <td>
                <div style="background-color: #e1f2ff; border-radius: 5px; padding: 10px">
                    <div style="font-size: 15px; font-weight: 700; margin-bottom: 5px">
                        {{$student['name']}}
                    </div>
                    <div style="font-size: 12px;">
                        @if($student['studentProfile'])
                            <i>{{$student['studentProfile']['code']}}</i>
                        @endif
                    </div>
                </div>
                <div style="background-color: #e1f2ff; border-radius: 5px; padding: 10px; margin-top: 5px">
                    <table style="width: 98%;" class="id-table">
                        <tr>
                            <td>Inisial</td>
                            <td>:</td>
                            <td>{{$student['studentProfile'] ? $student['studentProfile']['initial'] : '' }}</td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td>:</td>
                            <td>{{$student['studentProfile'] ? $student['studentProfile']->address : '' }}</td>
                        </tr>
                        <tr>
                            <td>Kota</td>
                            <td>:</td>
                            <td>{{$student['studentProfile'] ? $student['studentProfile']->city : '' }}</td>
                        </tr>
                        <tr>
                            <td>Kode Pos</td>
                            <td>:</td>
                            <td>{{$student['studentProfile'] ? $student['studentProfile']->postal_code : '' }}</td>
                        </tr>
                        <tr>
                            <td>No Telp</td>
                            <td>:</td>
                            <td>{{$student['studentProfile'] ? $student['studentProfile']->phone : '' }}</td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>:</td>
                            <td>{{$student['email'] }}</td>
                        </tr>
                        <tr>
                            <td>Tempat, Tanggal Lahir</td>
                            <td>:</td>
                            <td>
                                {{$student['studentProfile'] ? $student['studentProfile']->pob : '' }},
                                {{$student['studentProfile'] ? date('d M Y', strtotime($student['studentProfile']->dob)) : '' }}
                            </td>
                        </tr>
                        <tr>
                            <td>Pendidikan S1</td>
                            <td>:</td>
                            <td>{{$student['studentProfile'] ? $student['studentProfile']->undergraduate : '' }}</td>
                        </tr>
                        <tr>
                            <td>Tanggal Lulus S1</td>
                            <td>:</td>
                            <td>{{$student['studentProfile'] ? $student['studentProfile']->graduated_at : '' }}</td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>:</td>
                            <td>{{$student['studentProfile'] ? $student['studentProfile']->degree : '' }}</td>
                        </tr>
                        <tr>
                            <td>Pembimbing</td>
                            <td>:</td>
                            <td>{{$student['studentProfile'] && $student['studentProfile']->lecture ? $student['studentProfile']->lecture->name : '' }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</div>
<div class="pagebreak"></div>
