<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $res = [
      'status'  => false,
      'text'    => 'Success',
      'data'    => null,
    ];

    public $response = [
      'status'  => true,
      'text'    => 'Success',
      'data'    => null,
      'result'    => null,
    ];
}
