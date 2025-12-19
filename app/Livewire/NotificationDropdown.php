<?php

namespace App\Livewire;

use App\Models\Notification;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;

class NotificationDropdown extends Component
{
    public bool $isOpen = false;
    public Collection $notifications;
    public int $unreadCount = 0;


    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function toggleDropdown(): void
    {
        $this->isOpen = !$this->isOpen;

        if ($this->isOpen) {
            $this->markAllAsRead();
        }
    }

    public function closeDropdown(): void
    {
        $this->isOpen = false;
    }

    public function loadNotifications(): void
    {
        $notifications = Notification::where('read', false)->orderBy('created_at', 'desc')->get();
        $this->notifications = $notifications;
    }

    public function markAllAsRead(): void
    {
        Notification::where('read', false)->update([
            'read' => true,
            'read_at' => now(),
        ]);

        $this->loadNotifications();
    }



    public function render(): View
    {
        return view('livewire.notification-dropdown');
    }
}
