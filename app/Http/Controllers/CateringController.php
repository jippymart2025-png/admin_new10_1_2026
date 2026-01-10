<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CateringController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('catering.index');
    }

    public function edit($id)
    {
        return view('catering.edit')->with('id', $id);
    }
}
