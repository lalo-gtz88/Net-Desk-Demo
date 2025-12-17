<?php

namespace App\Listeners;

use App\Events\CambiosTicket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegistrarCambiosTicket
{
    public function handle(CambiosTicket $event): void
    {
        $old = $event->ticketOld;
        $new = $event->ticketNew;

        $userId = Auth::id() ?? null;
        $mensajes = [];

        $this->compararCampo($mensajes, 'Tema', $old->tema, $new->tema, $userId);
        $this->compararCampo($mensajes, 'Descripción', $old->descripcion, $new->descripcion, $userId);
        $this->compararCampo($mensajes, 'Teléfono', $old->telefono, $new->telefono, $userId);
        $this->compararCampo($mensajes, 'Departamento', $old->departamento, $new->departamento, $userId);
        $this->compararCampo($mensajes, 'IP', $old->ip, $new->ip, $userId);
        $this->compararCampo($mensajes, 'Edificio', $old->edificio, $new->edificio, $userId);
        $this->compararCampo($mensajes, 'Usuario de red', $old->usuario_red, $new->usuario_red, $userId);
        $this->compararCampo($mensajes, 'Autoriza', $old->autoriza, $new->autoriza, $userId);
        $this->compararCampo($mensajes, 'Prioridad', $old->prioridad, $new->prioridad, $userId);
        $this->compararCampo($mensajes, 'Categoría', $old->categoria, $new->categoria, $userId);
        $this->compararCampo($mensajes, 'Status', $old->status, $new->status, $userId);
        $this->compararCampo($mensajes, 'Usuario', $old->reporta, $new->reporta, $userId);
        $this->compararCampo($mensajes, 'Unidad', $old->unidad, $new->unidad, $userId);
        $this->compararCampo($mensajes, 'Correo', $old->email, $new->email, $userId);

        if ($old->fecha_atencion !== $new->fecha_atencion) {
            $mensajes[] = [
                'comentario' => sprintf(
                    'Fecha de atención cambió: %s → %s',
                    Carbon::parse($old->fecha_atencion)->format('d/m/Y'),
                    Carbon::parse($new->fecha_atencion)->format('d/m/Y')
                ),
                'usuario' => $userId
            ];
        }

        $this->compararBooleano($mensajes, 'correo electrónico nuevo', $old->correo_nuevo, $new->correo_nuevo, $userId);
        $this->compararBooleano($mensajes, 'SIGA', $old->siga, $new->siga, $userId);
        $this->compararBooleano($mensajes, 'TURNOS', $old->turnos, $new->turnos, $userId);
        $this->compararBooleano($mensajes, 'NAV', $old->nav, $new->nav, $userId);
        $this->compararBooleano($mensajes, 'impresora', $old->impresora, $new->impresora, $userId);
        $this->compararBooleano($mensajes, 'SMAR', $old->smar, $new->smar, $userId);
        $this->compararBooleano($mensajes, 'ESET', $old->eset, $new->eset, $userId);
        $this->compararBooleano($mensajes, 'Office', $old->office, $new->office, $userId);

        if ($old->asignado !== $new->asignado) {
            $mensajes[] = [
                'comentario' => 'Asignación de ticket actualizada',
                'usuario' => $userId
            ];
        }

        foreach ($mensajes as $mensaje) {
            DB::table('seguimientos')->insert([
                'notas'      => $mensaje['comentario'],
                'ticket'     => $new->id,
                'usuario'    => $mensaje['usuario'],
                'print'      => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function compararCampo(array &$mensajes, string $label, $old, $new, $userId): void
    {
        if ($old !== $new) {
            $mensajes[] = [
                'comentario' => "{$label} cambió: {$old} → {$new}",
                'usuario' => $userId
            ];
        }
    }

    private function compararBooleano(array &$mensajes, string $label, bool $old, bool $new, $userId): void
    {
        if ($old !== $new) {
            $mensajes[] = [
                'comentario' => $new
                    ? "Se requiere {$label}"
                    : "No se requiere {$label}",
                'usuario' => $userId
            ];
        }
    }
}
