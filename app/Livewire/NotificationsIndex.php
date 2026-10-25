<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class NotificationsIndex extends Component
{
    use WithPagination;

    public function leer(string $id): void
    {
        auth()->user()->notifications()->whereKey($id)->firstOrFail()->markAsRead();
        $this->dispatch('notifications-read');
    }

    public function leerTodas(): void
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
        $this->dispatch('notifications-read');
    }

    public function render()
    {
        return view('livewire.notifications-index', [
            'notificaciones' => auth()->user()->notifications()->latest()->paginate(20),
        ]);
    }
}
