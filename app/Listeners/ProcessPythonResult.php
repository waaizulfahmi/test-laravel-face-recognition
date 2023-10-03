<?php

namespace App\Listeners;

use App\Events\WebSocketEvent;
use App\Http\Controllers\MLApiController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;


class ProcessPythonResult
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(WebSocketEvent $event): void
    {
        $result = $event->message;

    // Kirim hasil ke controller menggunakan HTTP request
        // $response = Http::post('/api/testing', [
        //     'result' => $result,
        // ]);

        $controller = new MLApiController();
        $controller->test($result);
    }
}
