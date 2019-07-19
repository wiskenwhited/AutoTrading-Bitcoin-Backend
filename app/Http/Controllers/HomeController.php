<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use App\Models\UserPackage;
use GrahamCampbell\Flysystem\Facades\Flysystem;
use GrahamCampbell\Flysystem\FlysystemManager;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function pageWeb(FlysystemManager $flysystem, Request $request)
    {
        return view('web');
    }
}
