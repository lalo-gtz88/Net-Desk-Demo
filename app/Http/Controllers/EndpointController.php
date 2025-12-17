<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EndpointController extends Controller
{


    public function create()
    {
        return view('create-endpoint');
    }

    public function edit($id = null)
    {
        return view('create-endpoint', ['id' => $id]);
    }


    public function listar()
    {
        return view('endpoints-list');
    }
}
