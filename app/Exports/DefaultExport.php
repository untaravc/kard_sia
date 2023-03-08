<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DefaultExport implements FromView, ShouldAutoSize {
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function view(): View {
        $data = $this->data;

        return view($data['template'], [
            'dataContent' => $data['dataContent'],
            'query'       => $data['query'],
        ]);
    }
}
