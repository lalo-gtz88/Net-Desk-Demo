<?php

namespace App\Jobs;

use App\Models\Endpoint;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTelegramMonitoreo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $equipoID
    ) {}

    public function handle(): void
    {
        $equipo = Endpoint::find($this->equipoID);

        if (!$equipo) {
            Log::warning("Equipo no encontrado. ID: {$this->equipoID}");
            return;
        }

        $ip         = long2ip($equipo->ip);
        $tipo       = $equipo->tipo ?? 'Desconocido';
        $ubicacion  = $equipo->ubicacion ?? 'Sin ubicación';
        $descripcion = $equipo->nombre;

        Log::info("Notificación Telegram monitoreo - {$ip} ({$equipo->status})");

        $estado = $equipo->status === 'up'
            ? ["✅ UP", "🟢 Estado: EN LÍNEA"]
            : ["⚠️ DOWN", "🔴 Estado: SIN CONEXIÓN"];

        $mensaje = "<b>🚨 ALERTA DE MONITOREO - {$estado[0]}</b>\n" .
            "🖧 Descripción: {$descripcion}\n" .
            "🚩 Ubicación: {$ubicacion}\n" .
            "💻 Tipo: {$tipo}\n" .
            "🌐 IP: {$ip}\n" .
            "{$estado[1]}";

        $service = app(TelegramService::class);

        $usuarios = User::permission('Recibir notificación del estado de los equipos en red')
            ->whereNotNull('telegram')
            ->get();

        foreach ($usuarios as $user) {
            $service->sendMessage($user->telegram, $mensaje);
        }
    }
}
