<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTelegramNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $ticketId
    ) {}

    public function handle(): void
    {
        Log::info("Enviando notificación Telegram para ticket #{$this->ticketId}");

        $ticket = Ticket::find($this->ticketId);

        if (!$ticket) {
            Log::warning("Ticket no encontrado. ID: {$this->ticketId}");
            return;
        }

        $service = app(TelegramService::class);

        $descripcion = html_entity_decode(
            strip_tags(
                mb_strimwidth($ticket->descripcion, 0, 300, '...')
            )
        );

        // Técnico asignado
        $assignedUser = User::find($ticket->asignado);

        if ($assignedUser?->telegram) {
            $service->sendMessage(
                $assignedUser->telegram,
                "📌 <b>Nuevo ticket asignado: #{$ticket->id} - {$ticket->tema}</b>\n{$descripcion}"
            );
        }

        // Usuarios con permiso global
        $usuarios = User::permission('Recibir notificación de todos los tickets')
            ->whereNotNull('telegram')
            ->get();

        foreach ($usuarios as $user) {

            if ($assignedUser && $user->id === $assignedUser->id) {
                continue;
            }

            $mensaje = $assignedUser
                ? "📣 <b>Nuevo ticket asignado: #{$ticket->id} - {$ticket->tema}</b>\nAsignado: {$assignedUser->name}\n{$descripcion}"
                : "📣 <b>Nuevo ticket creado: #{$ticket->id} - {$ticket->tema}</b>\n{$descripcion}";

            $service->sendMessage($user->telegram, $mensaje);
        }
    }
}
