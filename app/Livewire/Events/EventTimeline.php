<?php

namespace App\Livewire\Events;

use App\Models\ConnectionEvent;
use App\Models\Router;
use Livewire\Component;

class EventTimeline extends Component
{
    public ?int $routerId = null;
    public string $filter = 'all'; // 'all', 'cell_changed', 'band_changed', 'alerts'
    public int $limit = 5;

    protected $listeners = [
        'signal-reading-recorded' => '$refresh',
        'router-switched' => 'switchRouter',
    ];

    public function mount(?int $routerId = null)
    {
        if ($routerId) {
            $this->routerId = $routerId;
        } else {
            $active = Router::getActive();
            $this->routerId = $active ? $active->id : null;
        }
    }

    public function switchRouter(int $routerId)
    {
        $this->routerId = $routerId;
    }

    public function setFilter(string $filter)
    {
        $this->filter = $filter;
    }

    public function clearEvents()
    {
        if ($this->routerId) {
            ConnectionEvent::where('router_id', $this->routerId)->delete();
            $this->dispatch('notify', message: 'Connection event timeline cleared.', type: 'info');
        }
    }

    public function render()
    {
        $query = ConnectionEvent::query();

        if ($this->routerId) {
            $query->where('router_id', $this->routerId);
        }

        if ($this->filter === 'cell_changed') {
            $query->where('event_type', 'cell_changed');
        } elseif ($this->filter === 'band_changed') {
            $query->where('event_type', 'band_changed');
        } elseif ($this->filter === 'alerts') {
            $query->whereIn('event_type', ['signal_weak', 'signal_excellent', 'disconnected']);
        }

        $events = $query->orderBy('occurred_at', 'desc')->take($this->limit)->get();

        return view('livewire.events.event-timeline', [
            'events' => $events,
        ]);
    }
}
