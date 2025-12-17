<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TicketsController extends Controller
{


    public function index()
    {

        return view('tickets');
    }

    public function newTicket()
    {

        return view('new-ticket');
    }

    public function copy($id)
    {

        $uniqueId = $id;
        return view('copy-ticket', compact('uniqueId'));
    }

    public function enviarPorMail($id)
    {

        $ticketId = $id;
        return view('enviar-correo-ticket', compact('ticketId'));
    }
}
