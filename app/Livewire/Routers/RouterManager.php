<?php

namespace App\Livewire\Routers;

use App\Models\Router;
use App\Services\DataProviders\ZltRouterDataProvider;
use App\Services\SignalDataService;
use Livewire\Component;

class RouterManager extends Component
{
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $routerId = null;
    public ?int $confirmingDeleteId = null;
    public ?string $loginError = null;

    public string $name = 'Home LTE Gateway';
    public string $model = 'ZLT P11X';
    public string $driver = 'zlt'; // 'zlt', 'manual'
    public string $ip_address = '192.168.0.1';
    public string $username = 'admin';
    public string $password = '1234';
    public string $firmware_version = '';
    public string $hardware_version = '';
    public string $modem_version = '';
    public string $build_date = '';
    public string $status = 'connected';
    public string $description = '';
    public bool $is_active = false;

    // Sensitive fields (optional entry)
    public string $imei = '';
    public string $imsi = '';
    public string $iccid = '';
    public string $mac_address = '';

    protected function rules(): array
    {
        return [
            'name'             => 'required|string|max:100',
            'model'            => 'required|string|max:100',
            'driver'           => 'required|in:zlt,manual',
            'ip_address'       => 'nullable|string|max:50',
            'username'         => 'nullable|string|max:50',
            'password'         => 'nullable|string|max:100',
            'firmware_version' => 'nullable|string|max:50',
            'hardware_version' => 'nullable|string|max:50',
            'modem_version'    => 'nullable|string|max:50',
            'build_date'       => 'nullable|string|max:30',
            'status'           => 'required|in:connected,disconnected,idle,weak',
            'description'      => 'nullable|string|max:500',
            'imei'             => 'nullable|string|max:30',
            'imsi'             => 'nullable|string|max:30',
            'iccid'            => 'nullable|string|max:30',
            'mac_address'      => 'nullable|string|max:30',
        ];
    }

    public function openCreateModal()
    {
        $this->reset([
            'routerId', 'name', 'model', 'driver', 'ip_address', 'username', 'password',
            'firmware_version', 'hardware_version', 'modem_version', 'build_date',
            'status', 'description', 'imei', 'imsi', 'iccid', 'mac_address', 'is_active',
            'loginError'
        ]);
        $this->name = 'LTE Router Gateway';
        $this->model = 'ZLT P11X';
        $this->driver = 'zlt';
        $this->ip_address = '192.168.0.1';
        $this->username = 'admin';
        $this->password = '1234';
        $this->status = 'connected';
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEditModal(int $id)
    {
        $this->loginError = null;
        $router = Router::findOrFail($id);
        $this->routerId = $router->id;
        $this->name = $router->name;
        $this->model = $router->model;
        $this->driver = $router->driver ?? 'zlt';
        $this->ip_address = $router->ip_address ?? '192.168.0.1';
        $this->username = $router->username ?? 'admin';
        $this->password = (string) $router->password;
        $this->firmware_version = $router->firmware_version ?? '';
        $this->hardware_version = $router->hardware_version ?? '';
        $this->modem_version = $router->modem_version ?? '';
        $this->build_date = $router->build_date ?? '';
        $this->status = $router->status;
        $this->description = $router->description ?? '';
        $this->is_active = $router->is_active;
        $this->imei = $router->imei ?? '';
        $this->imsi = $router->imsi ?? '';
        $this->iccid = $router->iccid ?? '';
        $this->mac_address = $router->mac_address ?? '';

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(ZltRouterDataProvider $zltProvider)
    {
        $validated = $this->validate();
        $this->loginError = null;

        // 1. Live Router Verification Gate
        if ($this->driver === 'zlt') {
            $ip = $this->ip_address ?: '192.168.0.1';
            $check = $zltProvider->verifyCredentials($ip, $this->username, $this->password);

            if (! $check['success']) {
                $this->loginError = $check['error'] ?? 'Admin Login Failed';
                $this->dispatch('notify', message: $this->loginError, type: 'error');
                return;
            }

            // Auto-enrich specs if returned
            if (! empty($check['deviceInfo'])) {
                $d = $check['deviceInfo'];
                $validated['model'] = $d['name'] ?? $validated['model'];
                $validated['firmware_version'] = $d['version'] ?? $validated['firmware_version'];
                $validated['hardware_version'] = $d['hwversion'] ?? $validated['hardware_version'];
                $validated['modem_version'] = $d['modversion'] ?? $validated['modem_version'];
                $validated['imei'] = $d['imei'] ?? $validated['imei'];
                $validated['imsi'] = $d['imsi'] ?? $validated['imsi'];
                $validated['iccid'] = $d['iccid'] ?? $validated['iccid'];
                $validated['mac_address'] = $d['mac'] ?? $validated['mac_address'];
            }
        }

        // 2. Save Router
        if ($this->isEditing && $this->routerId) {
            $router = Router::findOrFail($this->routerId);
            $router->update($validated);
            $this->dispatch('notify', message: 'Router configuration updated and verified!', type: 'success');
        } else {
            $count = Router::count();
            $validated['user_id'] = auth()->id();
            $validated['is_active'] = ($count === 0);
            $router = Router::create($validated);
            $this->dispatch('notify', message: 'Router admin login verified & saved successfully!', type: 'success');
        }

        $this->showModal = false;
    }

    public function syncLiveTelemetry(int $id, SignalDataService $signalService)
    {
        $router = Router::findOrFail($id);
        try {
            $reading = $signalService->fetchAndRecordTelemetry($router);
            $this->dispatch('notify', message: "Live telemetry synced! RSRP: {$reading->rsrp} dBm, SINR: {$reading->sinr} dB", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: "Failed to query router at {$router->ip_address}: " . $e->getMessage(), type: 'error');
        }
    }

    public function makeActive(int $id)
    {
        $router = Router::findOrFail($id);
        $router->setActive();
        $this->dispatch('notify', message: "Switched active router to {$router->name}!", type: 'success');
    }

    public function confirmDelete(int $id)
    {
        $this->confirmingDeleteId = $id;
    }

    public function cancelDelete()
    {
        $this->confirmingDeleteId = null;
    }

    public function deleteConfirmed()
    {
        if (! $this->confirmingDeleteId) {
            return;
        }

        $router = Router::findOrFail($this->confirmingDeleteId);
        $wasActive = $router->is_active;
        $name = $router->name;
        $router->delete();

        if ($wasActive) {
            $next = Router::first();
            if ($next) {
                $next->setActive();
            }
        }

        $this->confirmingDeleteId = null;
        $this->dispatch('notify', message: "Router '{$name}' deleted successfully.", type: 'success');
    }

    public function render()
    {
        $query = Router::query();
        if (\Illuminate\Support\Facades\Schema::hasTable('signal_readings')) {
            $query->withCount('signalReadings');
        }

        return view('livewire.routers.router-manager', [
            'routers' => $query->latest()->get(),
            'activeRouter' => Router::getActive(),
        ])->layout('layouts.app', ['title' => 'Router Management']);
    }
}
