<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notificaciones = $request->user()->notifications()->latest()->paginate(20);
        return view('notificaciones.index', compact('notificaciones'));
    }

    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();
        $items = $user->notifications()->latest()->limit(10)->get()->map(function (DatabaseNotification $notification) {
            $data = $notification->data;
            return [
                'id' => $notification->id,
                'titulo' => (string) ($data['titulo'] ?? 'Notificación'),
                'mensaje' => (string) ($data['mensaje'] ?? ''),
                'tipo' => (string) ($data['tipo'] ?? 'GENERAL'),
                'prioridad' => (string) ($data['prioridad'] ?? 'normal'),
                'url' => route('notificaciones.abrir', $notification->id),
                'leida' => $notification->read_at !== null,
                'fecha' => $notification->created_at?->locale('es')->diffForHumans(),
            ];
        });
        return response()->json([
            'no_leidas' => $user->unreadNotifications()->count(),
            'notificaciones' => $items,
        ])->header('Cache-Control', 'no-store');
    }

    public function abrir(Request $request, string $id): RedirectResponse
    {
        $notification = $this->findOwned($request, $id);
        $notification->markAsRead();
        $data = $notification->data;
        $url = (string) ($data['url'] ?? '');
        // Impide redirecciones a otras páginas/sitios.
        if (!str_starts_with($url, '/') || str_starts_with($url, '//') || str_contains($url, "\\")) {
            $url = route('notificaciones.index');
        }
        return redirect()->to($url);
    }

    public function leer(Request $request, string $id): RedirectResponse|JsonResponse
    {
        $this->findOwned($request, $id)->markAsRead();
        return $this->result($request, 'Notificación marcada como leída.');
    }

    public function leerTodas(Request $request): RedirectResponse|JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);
        return $this->result($request, 'Notificaciones marcadas como leídas.');
    }

    private function findOwned(Request $request, string $id): DatabaseNotification
    {
        return $request->user()->notifications()->whereKey($id)->firstOrFail();
    }

    private function result(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message,
                'no_leidas' => $request->user()->unreadNotifications()->count()]);
        }
        return back()->with('success', $message);
    }
}
