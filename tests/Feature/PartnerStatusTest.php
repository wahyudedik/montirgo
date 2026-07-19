<?php

use App\Console\Commands\AutoOfflinePartners;
use App\Events\OrderStatusChanged;
use App\Models\Order;
use App\Models\Partner;
use App\Services\GeolocationService;
use App\Services\LocationTrackingService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

// ============================================================
// Partner Model: isCurrentlyOperating()
// ============================================================

test('isCurrentlyOperating returns true when no schedule is set', function () {
    $partner = Partner::factory()->create([
        'operational_schedule' => null,
    ]);

    expect($partner->isCurrentlyOperating())->toBeTrue();
});

test('isCurrentlyOperating returns true when within schedule', function () {
    $now = now('Asia/Jakarta');
    $hour = $now->format('H:i');
    $dayMap = [0 => 'sun', 1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat'];
    $todayKey = $dayMap[$now->dayOfWeek];

    $partner = Partner::factory()->create([
        'operational_schedule' => [
            $todayKey => ['open' => '00:00', 'close' => '23:59'],
        ],
    ]);

    expect($partner->isCurrentlyOperating())->toBeTrue();
});

test('isCurrentlyOperating returns false when outside schedule', function () {
    $now = now('Asia/Jakarta');
    $dayMap = [0 => 'sun', 1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat'];
    $todayKey = $dayMap[$now->dayOfWeek];

    $partner = Partner::factory()->create([
        'operational_schedule' => [
            $todayKey => ['open' => '02:00', 'close' => '03:00'],
        ],
    ]);

    // Pastikan waktu sekarang TIDAK di dalam range 02:00-03:00
    $currentTime = $now->format('H:i');
    if ($currentTime >= '02:00' && $currentTime <= '03:00') {
        // Jika kebetulan jam 02-03, skip test ini
        $this->markTestSkipped('Waktu sekarang kebetulan dalam range 02:00-03:00');
    }

    expect($partner->isCurrentlyOperating())->toBeFalse();
});

test('isCurrentlyOperating returns false when today is not in schedule', function () {
    $partner = Partner::factory()->create([
        'operational_schedule' => [
            'mon' => ['open' => '08:00', 'close' => '17:00'],
            'tue' => ['open' => '08:00', 'close' => '17:00'],
        ],
    ]);

    // Hari Minggu (sun) tidak ada di schedule
    $now = now('Asia/Jakarta');
    $dayMap = [0 => 'sun', 1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat'];
    $todayKey = $dayMap[$now->dayOfWeek];

    if ($todayKey === 'mon' || $todayKey === 'tue') {
        $this->markTestSkipped('Hari ini adalah Mon/Tue, schedule tersedia');
    }

    expect($partner->isCurrentlyOperating())->toBeFalse();
});

test('isCurrentlyOperating returns false when today schedule is null', function () {
    $partner = Partner::factory()->create([
        'operational_schedule' => [
            'mon' => ['open' => '08:00', 'close' => '17:00'],
            'wed' => null,
        ],
    ]);

    $now = now('Asia/Jakarta');
    $dayMap = [0 => 'sun', 1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat'];
    $todayKey = $dayMap[$now->dayOfWeek];

    if ($todayKey === 'mon') {
        $this->markTestSkipped('Hari ini adalah Monday, schedule tersedia');
    }

    // Jika hari ini Rabu dengan null schedule, harusnya false
    // Jika hari lain yang tidak ada di schedule, juga false
    expect($partner->isCurrentlyOperating())->toBeFalse();
});

// ============================================================
// toggleOnline GPS Validation
// ============================================================

test('partner can toggle online with valid GPS coordinates', function () {
    $partner = Partner::factory()->create([
        'partner_status' => 'offline',
        'is_online' => false,
        'operational_schedule' => null, // 24/7
    ]);
    $token = $partner->user->createToken('test')->plainTextToken;

    $this->mock(LocationTrackingService::class, function ($mock) {
        $mock->shouldReceive('updatePartnerLocation')->once();
    });

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/partner/toggle-online', [
            'current_lat' => -6.150000,
            'current_lng' => 106.800000,
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'is_online' => true,
        ]);

    $this->assertDatabaseHas('partners', [
        'id' => $partner->id,
        'partner_status' => 'online',
        'is_online' => true,
    ]);
});

test('partner cannot toggle online without GPS coordinates', function () {
    $partner = Partner::factory()->create([
        'partner_status' => 'offline',
        'is_online' => false,
    ]);
    $token = $partner->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/partner/toggle-online');

    $response->assertStatus(422)
        ->assertJson([
            'message' => 'GPS harus aktif saat ingin Online. Sertakan current_lat dan current_lng.',
        ]);
});

test('partner cannot toggle online with invalid GPS format', function () {
    $partner = Partner::factory()->create([
        'partner_status' => 'offline',
        'is_online' => false,
    ]);
    $token = $partner->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/partner/toggle-online', [
            'current_lat' => 'not-a-number',
            'current_lng' => 'also-not-a-number',
        ]);

    $response->assertStatus(422);
});

test('partner cannot toggle online with out-of-range coordinates', function () {
    $partner = Partner::factory()->create([
        'partner_status' => 'offline',
        'is_online' => false,
    ]);
    $token = $partner->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/partner/toggle-online', [
            'current_lat' => 100.0, // Max lat is 90
            'current_lng' => 200.0, // Max lng is 180
        ]);

    $response->assertStatus(422);
});

test('partner can toggle offline without GPS coordinates', function () {
    $partner = Partner::factory()->create([
        'partner_status' => 'online',
        'is_online' => true,
    ]);
    $token = $partner->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/partner/toggle-online');

    $response->assertStatus(200)
        ->assertJson([
            'is_online' => false,
        ]);

    $this->assertDatabaseHas('partners', [
        'id' => $partner->id,
        'partner_status' => 'offline',
        'is_online' => false,
    ]);
});

test('partner can toggle online even outside operating hours with warning', function () {
    $partner = Partner::factory()->create([
        'partner_status' => 'offline',
        'is_online' => false,
    ]);
    $token = $partner->user->createToken('test')->plainTextToken;

    // Set schedule yang pasti TIDAK mencakup waktu sekarang
    $now = now('Asia/Jakarta');
    $dayMap = [0 => 'sun', 1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat'];
    $todayKey = $dayMap[$now->dayOfWeek];

    // Set jam tutup 1 jam sebelum jam buka agar pasti di luar jadwal
    $hour = (int) $now->format('H');
    $closedStart = str_pad((string) (($hour + 1) % 24), 2, '0', STR_PAD_LEFT).':00';
    $closedEnd = str_pad((string) (($hour + 2) % 24), 2, '0', STR_PAD_LEFT).':00';

    $partner->update([
        'operational_schedule' => [
            $todayKey => ['open' => $closedStart, 'close' => $closedEnd],
        ],
    ]);
    $partner->refresh();

    $this->mock(LocationTrackingService::class, function ($mock) {
        $mock->shouldReceive('updatePartnerLocation')->once();
    });

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/partner/toggle-online', [
            'current_lat' => -6.150000,
            'current_lng' => 106.800000,
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'is_online' => true,
            'outside_operating_hours' => true,
        ]);
});

test('partner toggle online updates last_active_at', function () {
    $partner = Partner::factory()->create([
        'partner_status' => 'offline',
        'is_online' => false,
        'last_active_at' => null,
    ]);
    $token = $partner->user->createToken('test')->plainTextToken;

    $this->mock(LocationTrackingService::class, function ($mock) {
        $mock->shouldReceive('updatePartnerLocation')->once();
    });

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/partner/toggle-online', [
            'current_lat' => -6.150000,
            'current_lng' => 106.800000,
        ]);

    $partner->refresh();
    expect($partner->last_active_at)->not->toBeNull();
});

test('unapproved partner cannot toggle online', function () {
    $partner = Partner::factory()->pending()->create([
        'partner_status' => 'offline',
        'is_online' => false,
    ]);
    $token = $partner->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/partner/toggle-online', [
            'current_lat' => -6.150000,
            'current_lng' => 106.800000,
        ]);

    $response->assertStatus(403);
});

// ============================================================
// Auto-Online after Order Completion (API)
// ============================================================

test('partner is set to online after completing order via API', function () {
    Event::fake([OrderStatusChanged::class]);

    $partner = Partner::factory()->create([
        'partner_status' => 'in_progress',
        'is_online' => true,
    ]);
    $token = $partner->user->createToken('test')->plainTextToken;

    // Buat order yang sedang in_progress
    $order = Order::factory()->create([
        'partner_id' => $partner->id,
        'status' => 'in_progress',
    ]);

    $this->mock(PaymentService::class, function ($mock) {
        $mock->shouldReceive('processCompletion')->once();
    });

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/partner/orders/{$order->id}/status", [
            'status' => 'completed',
            'service_fee' => 150000,
        ]);

    $response->assertStatus(200);

    // Partner harus kembali ke online
    $partner->refresh();
    expect($partner->partner_status)->toBe('online')
        ->and($partner->is_available)->toBeTrue();
});

test('partner status not changed if already online after completion', function () {
    Event::fake([OrderStatusChanged::class]);

    $partner = Partner::factory()->create([
        'partner_status' => 'online',
        'is_online' => true,
        'is_available' => true,
    ]);
    $token = $partner->user->createToken('test')->plainTextToken;

    $order = Order::factory()->create([
        'partner_id' => $partner->id,
        'status' => 'in_progress',
    ]);

    $this->mock(PaymentService::class, function ($mock) {
        $mock->shouldReceive('processCompletion')->once();
    });

    $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/partner/orders/{$order->id}/status", [
            'status' => 'completed',
            'service_fee' => 100000,
        ]);

    $partner->refresh();
    expect($partner->partner_status)->toBe('online')
        ->and($partner->is_available)->toBeTrue();
});

// ============================================================
// Auto-Offline Scheduled Command
// ============================================================

test('auto-offline command marks stale partners as offline', function () {
    // Partner yang last_active_at > 10 menit lalu
    $stalePartner = Partner::factory()->create([
        'partner_status' => 'online',
        'is_online' => true,
        'last_active_at' => now()->subMinutes(15),
    ]);

    // Partner yang masih aktif
    $activePartner = Partner::factory()->create([
        'partner_status' => 'online',
        'is_online' => true,
        'last_active_at' => now()->subMinutes(3),
    ]);

    $this->artisan(AutoOfflinePartners::class)
        ->assertExitCode(0);

    $stalePartner->refresh();
    $activePartner->refresh();

    expect($stalePartner->partner_status)->toBe('offline')
        ->and($stalePartner->is_online)->toBeFalse();

    expect($activePartner->partner_status)->toBe('online')
        ->and($activePartner->is_online)->toBeTrue();
});

test('auto-offline command marks partners with null last_active_at as offline', function () {
    $partner = Partner::factory()->create([
        'partner_status' => 'online',
        'is_online' => true,
        'last_active_at' => null,
    ]);

    $this->artisan(AutoOfflinePartners::class)
        ->assertExitCode(0);

    $partner->refresh();
    expect($partner->partner_status)->toBe('offline')
        ->and($partner->is_online)->toBeFalse();
});

test('auto-offline dry-run does not change data', function () {
    $partner = Partner::factory()->create([
        'partner_status' => 'online',
        'is_online' => true,
        'last_active_at' => now()->subMinutes(15),
    ]);

    $this->artisan(AutoOfflinePartners::class, ['--dry-run' => true])
        ->assertExitCode(0);

    $partner->refresh();
    expect($partner->partner_status)->toBe('online')
        ->and($partner->is_online)->toBeTrue();
});

test('auto-offline command skips non-online partners', function () {
    $partner = Partner::factory()->create([
        'partner_status' => 'in_progress',
        'is_online' => true,
        'last_active_at' => now()->subMinutes(15),
    ]);

    $this->artisan(AutoOfflinePartners::class)
        ->assertExitCode(0);

    $partner->refresh();
    expect($partner->partner_status)->toBe('in_progress');
});

test('auto-offline command uses custom threshold', function () {
    // Partner yang 5 menit lalu aktif
    $partner = Partner::factory()->create([
        'partner_status' => 'online',
        'is_online' => true,
        'last_active_at' => now()->subMinutes(5),
    ]);

    // Default threshold 10 menit — tidak harus offline
    $this->artisan(AutoOfflinePartners::class)
        ->assertExitCode(0);

    $partner->refresh();
    expect($partner->partner_status)->toBe('online');

    // Threshold 3 menit — harus offline
    $this->artisan(AutoOfflinePartners::class, ['--threshold' => 3])
        ->assertExitCode(0);

    $partner->refresh();
    expect($partner->partner_status)->toBe('offline');
});

// ============================================================
// PartnerResource: New Fields
// ============================================================

test('partner profile response includes operational_schedule and last_active_at', function () {
    $schedule = [
        'mon' => ['open' => '08:00', 'close' => '17:00'],
        'tue' => ['open' => '08:00', 'close' => '17:00'],
    ];
    $partner = Partner::factory()->create([
        'operational_schedule' => $schedule,
        'last_active_at' => now(),
    ]);
    $token = $partner->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/partner/profile');

    $response->assertStatus(200)
        ->assertJsonPath('data.operational_schedule.mon.open', '08:00')
        ->assertJsonPath('data.operational_schedule.mon.close', '17:00')
        ->assertJsonStructure([
            'data' => [
                'last_active_at',
                'operational_schedule',
                'is_operating',
            ],
        ]);
});

test('partner profile response includes is_operating based on current time', function () {
    $now = now('Asia/Jakarta');
    $dayMap = [0 => 'sun', 1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat'];
    $todayKey = $dayMap[$now->dayOfWeek];

    $partner = Partner::factory()->create([
        'operational_schedule' => [
            $todayKey => ['open' => '00:00', 'close' => '23:59'],
        ],
    ]);
    $token = $partner->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/partner/profile');

    $response->assertStatus(200)
        ->assertJsonPath('data.is_operating', true);
});

// ============================================================
// Update operational_schedule via API
// ============================================================

test('partner can update operational schedule', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $schedule = [
        'mon' => ['open' => '08:00', 'close' => '17:00'],
        'tue' => ['open' => '08:00', 'close' => '17:00'],
        'wed' => ['open' => '08:00', 'close' => '17:00'],
    ];

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson('/api/v1/partner/profile', [
            'operational_schedule' => $schedule,
        ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('partners', [
        'id' => $partner->id,
    ]);

    $partner->refresh();
    expect($partner->operational_schedule)->toBe($schedule);
});

test('operational schedule validation rejects invalid time format', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson('/api/v1/partner/profile', [
            'operational_schedule' => [
                'mon' => ['open' => 'invalid-time', 'close' => '17:00'],
            ],
        ]);

    $response->assertStatus(422);
});

test('partner can update service_radius', function () {
    $partner = Partner::factory()->create([
        'service_radius' => 30,
    ]);
    $token = $partner->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson('/api/v1/partner/profile', [
            'service_radius' => 15,
        ]);

    $response->assertStatus(200);

    $partner->refresh();
    expect($partner->service_radius)->toBe(15);
});

// ============================================================
// GeolocationService: Operating Hours Filter
// ============================================================

test('findNearbyAvailablePartners excludes partners outside operating hours', function () {
    // SQLite doesn't support MySQL math functions (acos, radians, etc.)
    if (config('database.default') === 'sqlite') {
        $this->markTestSkipped('Requires MySQL/PostgreSQL with acos() function');
    }
    // Partner yang buka 24/7 (null schedule)
    $openPartner = Partner::factory()->create([
        'partner_status' => 'online',
        'is_online' => true,
        'is_available' => true,
        'workshop_lat' => '-6.1500',
        'workshop_lng' => '106.8000',
        'service_radius' => 30,
        'operational_schedule' => null,
    ]);

    // Partner yang tutup (schedule tidak mencakup waktu sekarang)
    $now = now('Asia/Jakarta');
    $dayMap = [0 => 'sun', 1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat'];
    $todayKey = $dayMap[$now->dayOfWeek];
    $hour = (int) $now->format('H');
    $closedStart = str_pad((string) (($hour + 1) % 24), 2, '0', STR_PAD_LEFT).':00';
    $closedEnd = str_pad((string) (($hour + 2) % 24), 2, '0', STR_PAD_LEFT).':00';

    $closedPartner = Partner::factory()->create([
        'partner_status' => 'online',
        'is_online' => true,
        'is_available' => true,
        'workshop_lat' => '-6.1500',
        'workshop_lng' => '106.8000',
        'service_radius' => 30,
        'operational_schedule' => [
            $todayKey => ['open' => $closedStart, 'close' => $closedEnd],
        ],
    ]);

    // Pastikan waktu sekarang tidak di dalam range
    $currentTime = $now->format('H:i');
    if ($currentTime >= $closedStart && $currentTime <= $closedEnd) {
        $this->markTestSkipped('Waktu sekarang kebetulan dalam range jadwal tutup');
    }

    $service = app(GeolocationService::class);
    $results = $service->findNearbyAvailablePartners(
        '-6.1500',
        '106.8000',
        30,
    );

    $partnerIds = $results->pluck('id')->toArray();
    expect($partnerIds)->toContain($openPartner->id)
        ->not->toContain($closedPartner->id);
});
