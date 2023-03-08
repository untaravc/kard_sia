<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;

class LamptkesController extends Controller
{
    use Kriteria\K1Trait;
    use Kriteria\K2Trait;
    use Kriteria\K3Trait;
    use Kriteria\K4Trait;
    use Kriteria\K5Trait;
    use Kriteria\K6Trait;
    use Kriteria\K7Trait;
    use Kriteria\K8Trait;
    use Kriteria\K9Trait;

    public function index()
    {
        $k1 = $this->k1();
        $k2 = $this->k2();
        $k3 = $this->k3();
        $k4 = $this->k4();
        $k5 = $this->k5();
        $k6 = $this->k6();
        $k7 = $this->k7();
        $k8 = $this->k8();
        $k9 = $this->k9();
        $swot = $this->swot();

        return view('home.lamptkes.index', compact(
            'k1',
            'k2',
            'k3',
            'k4',
            'k5',
            'k6',
            'k7',
            'k8',
            'k9',
            'swot'
        ));
    }

    private function swot()
    {
        $data = [
            [
                'code'    => '',
                'title'   => 'ANALISIS SWOT (IFAS DAN EFAS) PRODI JANTUNG DAN PEMBULUH DARAH FK-KMK UGM',
                'content' => [
                    [
                        'title' => 'Lampiran',
                        'link'  => "https://drive.google.com/drive/folders/1wHLwNR83_eXtBo1nif7cI_eT_N5Ubu2R?usp=sharing"
                    ],
                ]
            ],
        ];

        return $data;
    }
}
