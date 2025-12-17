<?php

namespace App\Http\Controllers;

use App\Models\DetalleDiagnostico;
use App\Models\Diagnostico;
use App\Models\Seguimiento;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Adapter\PDFLib;
use Illuminate\Support\Facades\DB;

class PDFController extends Controller
{
    public function viewDocTicket($id)
    {
        // --- Ticket demo ---
        $ticket = (object)[
            'id' => $id,
            'tema' => 'Demo Ticket',
            'descripcion' => 'Descripción de ejemplo para demo',
            'telefono' => '555-1234',
            'departamento' => 'Soporte',
            'ip' => '192.168.0.10',
            'tecnico' => (object)['name'=>'Juan', 'lastname'=>'Perez'],
            'edificio' => 'Demo Building',
            'reporta' => 'Demo User',
            'usuario_red' => 'demo_user',
            'autoriza' => 'Demo Manager',
            'userCreador' => (object)['name'=>'Admin','lastname'=>'Demo'],
            'prioridad' => 'Media',
            'categoria' => 'Soporte',
            'status' => 'Abierto',
            'created_at' => now(),
            'updated_at' => now(),
            'seguimientos' => [
                (object)['userComment'=>'Comentario de ejemplo 1'],
                (object)['userComment'=>'Comentario de ejemplo 2']
            ],
        ];

        $data = [
            'id' => $ticket->id,
            'tema' => $ticket->tema,
            'descripcion' => $ticket->descripcion,
            'telefono' => $ticket->telefono,
            'departamento' => $ticket->departamento,
            'ip' => $ticket->ip,
            'asignado' => $ticket->tecnico->name . ' ' . $ticket->tecnico->lastname,
            'edificio' => $ticket->edificio,
            'reporta' => $ticket->reporta,
            'usuario_red' => $ticket->usuario_red,
            'autoriza' => $ticket->autoriza,
            'creador' => $ticket->userCreador->name . ' ' . $ticket->userCreador->lastname,
            'prioridad' => $ticket->prioridad,
            'categoria' => $ticket->categoria,
            'status' => $ticket->status,
            'created_at' => $ticket->created_at,
            'updated_at' => $ticket->updated_at,
            'comentarios' => $ticket->seguimientos,
        ];

        // Generar PDF demo (requiere que la vista exista, puedes dejarla simple)
        $pdf = Pdf::loadView('pdf.ticket', $data)->setPaper('Letter');
        return $pdf->stream('ticket_demo_' . $ticket->id . '.pdf');
    }
}

