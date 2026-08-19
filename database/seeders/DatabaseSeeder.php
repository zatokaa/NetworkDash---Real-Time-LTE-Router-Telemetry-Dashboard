<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $admin = User::firstOrCreate(
            ['email' => 'admin@networkdash.local'],
            [
                'name' => 'System Administrator',
                'password' => \Illuminate\Support\Facades\Hash::make('admin1234'),
                'email_verified_at' => now(),
            ]
        );

        $router = \App\Models\Router::firstOrCreate(
            ['name' => 'Home LTE Router'],
            [
                'user_id' => $admin->id,
                'model' => 'ZLT P11X',
                'ip_address' => '192.168.8.1',
                'firmware_version' => '6.4.2.25',
                'hardware_version' => 'TZ7.821.172',
                'modem_version' => 'P705A_1.0.9_210901',
                'build_date' => '2022-11-08',
                'description' => 'Primary 4G LTE Gateway Router',
                'status' => 'connected',
                'is_active' => true,
            ]
        );

        if ($router->signalReadings()->count() === 0) {
            $service = new \App\Services\SignalDataService();
            
            // Seed a series of 10 readings over the last 30 minutes
            $sampleReadings = [
                ['rsrp' => -92.0, 'rssi' => -66.0, 'rsrq' => -14.0, 'sinr' => 10.0, 'minsAgo' => 30],
                ['rsrp' => -90.0, 'rssi' => -64.0, 'rsrq' => -13.0, 'sinr' => 11.0, 'minsAgo' => 25],
                ['rsrp' => -89.0, 'rssi' => -63.0, 'rsrq' => -12.0, 'sinr' => 12.0, 'minsAgo' => 20],
                ['rsrp' => -88.0, 'rssi' => -62.0, 'rsrq' => -12.0, 'sinr' => 13.0, 'minsAgo' => 15],
                ['rsrp' => -87.0, 'rssi' => -61.0, 'rsrq' => -11.0, 'sinr' => 14.0, 'minsAgo' => 10],
                ['rsrp' => -88.0, 'rssi' => -62.0, 'rsrq' => -12.0, 'sinr' => 14.0, 'minsAgo' => 5],
                ['rsrp' => -86.0, 'rssi' => -60.0, 'rsrq' => -10.0, 'sinr' => 15.0, 'minsAgo' => 2],
                ['rsrp' => -88.0, 'rssi' => -62.0, 'rsrq' => -12.0, 'sinr' => 14.0, 'minsAgo' => 0],
            ];

            foreach ($sampleReadings as $sample) {
                $service->recordReading($router, [
                    'recorded_at' => now()->subMinutes($sample['minsAgo']),
                    'rsrp' => $sample['rsrp'],
                    'rssi' => $sample['rssi'],
                    'rsrq' => $sample['rsrq'],
                    'sinr' => $sample['sinr'],
                    'band' => 'B40',
                    'bandwidth' => '20 MHz',
                    'earfcn' => 39146,
                    'transmission_mode' => 'TM8',
                    'tx_power' => 23.0,
                    'rrc_state' => 'Connected',
                    'mcs' => 24,
                    'cqi' => 10,
                    'enodeb' => '2994',
                    'cell_id' => '2',
                    'global_cell_id' => 'BB202',
                    'physical_cell_id' => '11',
                ]);
            }
        }

        if ($router->connectionEvents()->count() === 0) {
            \App\Models\ConnectionEvent::create([
                'router_id' => $router->id,
                'event_type' => 'connected',
                'title' => 'LTE Radio Link Established',
                'description' => 'Attached to Band 40 carrier network with 20 MHz bandwidth.',
                'previous_value' => 'Disconnected',
                'new_value' => 'Connected (B40)',
                'severity' => 'success',
                'occurred_at' => now()->subMinutes(35),
            ]);

            \App\Models\ConnectionEvent::create([
                'router_id' => $router->id,
                'event_type' => 'cell_changed',
                'title' => 'Initial Cell Sector Lock',
                'description' => 'Locked to serving eNodeB 2994 sector 2 (PCI 11).',
                'previous_value' => 'Scanning',
                'new_value' => 'Cell 2 (eNB 2994)',
                'severity' => 'info',
                'occurred_at' => now()->subMinutes(32),
            ]);

            \App\Models\ConnectionEvent::create([
                'router_id' => $router->id,
                'event_type' => 'signal_excellent',
                'title' => 'Signal Quality Reached Excellent',
                'description' => 'Optimal signal conditions detected (SINR: 15.0 dB, RSRP: -86.0 dBm).',
                'previous_value' => 'Good',
                'new_value' => 'Excellent',
                'severity' => 'success',
                'occurred_at' => now()->subMinutes(2),
            ]);
        }
    }
}
