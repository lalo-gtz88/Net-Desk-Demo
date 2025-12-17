<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class EditarTicket extends Controller
{
    
    function index($id)  {

        $uniqueId = $id;
        $ticket = $this->getTicket($id);
        
        return view('editar-ticket', compact('uniqueId', 'ticket'));
    }

    function getTicket($id) {
        
        return Ticket::find($id);
    }
}
