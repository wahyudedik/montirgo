<?php

namespace Database\Seeders;

use App\Models\Advertisement;
use App\Models\CallLog;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\InsurancePartner;
use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\OrderPhoto;
use App\Models\Partner;
use App\Models\PartnerService;
use App\Models\PartnerSubscription;
use App\Models\Payment;
use App\Models\Review;
use App\Models\ServiceCostItem;
use App\Models\Sparepart;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WalletBalance;
use App\Models\WalletTransaction;
use App\Models\WithdrawRequest;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Admin ──────────────────────────────────────
        $admin = User::factory()->admin()->create([
            'name' => 'Admin MontirGo',
            'email' => 'admin@montirgo.id',
            'phone' => '081234567890',
            'password' => Hash::make('password'),
        ]);

        // ─── Customers (10) ─────────────────────────────
        $customers = collect();
        $customerData = [
            ['name' => 'Budi Santoso', 'email' => 'budi@email.com', 'phone' => '081234567891'],
            ['name' => 'Siti Rahayu', 'email' => 'siti@email.com', 'phone' => '081234567892'],
            ['name' => 'Ahmad Fauzi', 'email' => 'ahmad@email.com', 'phone' => '081234567893'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@email.com', 'phone' => '081234567894'],
            ['name' => 'Rizki Pratama', 'email' => 'rizki@email.com', 'phone' => '081234567895'],
            ['name' => 'Anisa Putri', 'email' => 'anisa@email.com', 'phone' => '081234567896'],
            ['name' => 'Farhan Maulana', 'email' => 'farhan@email.com', 'phone' => '081234567897'],
            ['name' => 'Rina Wati', 'email' => 'rina@email.com', 'phone' => '081234567898'],
            ['name' => 'Dimas Aditya', 'email' => 'dimas@email.com', 'phone' => '081234567899'],
            ['name' => 'Maya Sari', 'email' => 'maya@email.com', 'phone' => '081234567800'],
        ];

        foreach ($customerData as $data) {
            $customer = User::factory()->customer()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make('password'),
                'location_lat' => fake()->latitude(-6.25, -6.15),
                'location_lng' => fake()->longitude(106.80, 106.90),
            ]);
            $customers->push($customer);

            // Vehicle untuk setiap customer
            Vehicle::factory()->create(['user_id' => $customer->id]);

            // Wallet
            WalletBalance::create([
                'user_id' => $customer->id,
                'balance' => fake()->randomElement([0, 25000, 50000, 75000, 100000]),
                'total_income' => 0,
            ]);
        }

        // ─── Partners (10 approved + 3 pending) ─────────
        $partnerData = [
            [
                'workshop_name' => 'Bengkel Jaya Motor',
                'address' => 'Jl. Raya Mojokerto No. 15',
                'lat' => -6.2100, 'lng' => 106.8450,
                'desc' => 'Bengkel motor spesialis Honda & Yamaha. Melayani servis ringan hingga overhaul.',
                'hours' => '07:00 - 17:00',
            ],
            [
                'workshop_name' => 'AutoCare Bengkel Mobil',
                'address' => 'Jl. Benteng Pancasila No. 8',
                'lat' => -6.2080, 'lng' => 106.8500,
                'desc' => 'Bengkel umum mobil, spesialis mesin injeksi dan transmisi matic.',
                'hours' => '08:00 - 18:00',
            ],
            [
                'workshop_name' => 'Bengkel Bang Juri',
                'address' => 'Jl. Kertanegara No. 22',
                'lat' => -6.2150, 'lng' => 106.8380,
                'desc' => 'Bengkel 24 jam untuk darurat. Melayani semua jenis kendaraan.',
                'hours' => '00:00 - 23:59',
            ],
            [
                'workshop_name' => 'Sinar Baru Motor',
                'address' => 'Jl. Gajah Mada No. 45',
                'lat' => -6.2050, 'lng' => 106.8520,
                'desc' => 'Bengkel ekonomis, spesialis servis harian dan ganti oli.',
                'hours' => '07:30 - 16:30',
            ],
            [
                'workshop_name' => 'Prima Auto Service',
                'address' => 'Jl. Veteran No. 30',
                'lat' => -6.2120, 'lng' => 106.8400,
                'desc' => 'Bengkel premium dengan peralatan modern. Diagnostik komputer lengkap.',
                'hours' => '08:00 - 17:00',
            ],
            [
                'workshop_name' => 'Bengkel Mas Tri',
                'address' => 'Jl. Pahlawan No. 12',
                'lat' => -6.2180, 'lng' => 106.8550,
                'desc' => 'Bengkel keluarga, ramah dan terjangkau. Spesialis motor matic.',
                'hours' => '07:00 - 16:00',
            ],
            [
                'workshop_name' => 'Djaya Motor Service',
                'address' => 'Jl. Wijaya Kusuma No. 7',
                'lat' => -6.2020, 'lng' => 106.8350,
                'desc' => 'Bengkel resmi berpengalaman 15 tahun. Sparepart original.',
                'hours' => '08:00 - 17:00',
            ],
            [
                'workshop_name' => 'Bengkel Ketok Ajaib',
                'address' => 'Jl. Ahmad Yani No. 55',
                'lat' => -6.2200, 'lng' => 106.8480,
                'desc' => 'Spesialis body repair dan cat kendaraan. Hasil sempurna.',
                'hours' => '08:00 - 16:00',
            ],
            [
                'workshop_name' => 'Top Brake Service',
                'address' => 'Jl. Diponegoro No. 18',
                'lat' => -6.2070, 'lng' => 106.8420,
                'desc' => 'Spesialis rem dan kaki-kaki. Brake shop terpercaya.',
                'hours' => '08:00 - 17:00',
            ],
            [
                'workshop_name' => 'Power Battery Center',
                'address' => 'Jl. Kartini No. 33',
                'lat' => -6.2130, 'lng' => 106.8510,
                'desc' => 'Center aki dan kelistrikan kendaraan. Garansi 6 bulan.',
                'hours' => '07:30 - 17:30',
            ],
        ];

        $partners = collect();
        foreach ($partnerData as $idx => $data) {
            $user = User::factory()->partner()->create([
                'name' => 'Partner '.($idx + 1),
                'email' => 'partner'.($idx + 1).'@email.com',
                'phone' => '08223456'.str_pad($idx + 1, 4, '0', STR_PAD_LEFT),
                'password' => Hash::make('password'),
                'location_lat' => $data['lat'],
                'location_lng' => $data['lng'],
            ]);

            $partner = Partner::create([
                'user_id' => $user->id,
                'workshop_name' => $data['workshop_name'],
                'workshop_address' => $data['address'],
                'workshop_lat' => $data['lat'],
                'workshop_lng' => $data['lng'],
                'status' => 'approved',
                'rating_avg' => fake()->randomFloat(2, 3.5, 5.0),
                'total_orders' => fake()->numberBetween(10, 200),
                'is_online' => fake()->boolean(70),
                'is_available' => true,
                'approved_at' => now()->subDays(rand(30, 180)),
            ]);
            $partners->push($partner);

            // Services untuk setiap partner
            $services = [
                ['service_name' => 'Servis Mesin', 'category' => 'engine', 'base_price' => 150000, 'description' => 'Perbaikan dan perawatan mesin'],
                ['service_name' => 'Servis Kelistrikan', 'category' => 'electrical', 'base_price' => 100000, 'description' => 'Perbaikan sistem kelistrikan'],
                ['service_name' => 'Ganti Ban', 'category' => 'tire', 'base_price' => 50000, 'description' => 'Penggantian ban dan tambal'],
                ['service_name' => 'Ganti Aki', 'category' => 'battery', 'base_price' => 80000, 'description' => 'Penggantian aki kendaraan'],
                ['service_name' => 'Ganti Oli', 'category' => 'oil', 'base_price' => 60000, 'description' => 'Ganti oli mesin + filter'],
                ['service_name' => 'Servis Rem', 'category' => 'brake', 'base_price' => 75000, 'description' => 'Perawatan dan perbaikan rem'],
                ['service_name' => 'Tune Up', 'category' => 'engine', 'base_price' => 120000, 'description' => 'Tune up lengkap mesin'],
                ['service_name' => 'Overhaul', 'category' => 'engine', 'base_price' => 500000, 'description' => 'Bongkar total mesin'],
            ];
            foreach ($services as $service) {
                PartnerService::create(array_merge($service, ['partner_id' => $partner->id]));
            }

            // Wallet untuk partner
            $earned = fake()->numberBetween(500000, 8000000);
            $withdrawn = fake()->numberBetween(0, $earned / 2);
            WalletBalance::create([
                'user_id' => $user->id,
                'balance' => $earned - $withdrawn,
                'total_income' => $earned,
            ]);

            // Spareparts untuk partner
            $spareparts = [
                ['name' => 'Oli Mesin 1L', 'category' => 'oil', 'price' => 45000, 'stock' => fake()->numberBetween(5, 50)],
                ['name' => 'Filter Oli', 'category' => 'filter', 'price' => 25000, 'stock' => fake()->numberBetween(10, 30)],
                ['name' => 'Busi NGK', 'category' => 'ignition', 'price' => 15000, 'stock' => fake()->numberBetween(10, 40)],
                ['name' => 'Kampas Rem Depan', 'category' => 'brake', 'price' => 35000, 'stock' => fake()->numberBetween(5, 20)],
                ['name' => 'Aki GS Astra', 'category' => 'battery', 'price' => 350000, 'stock' => fake()->numberBetween(2, 10)],
            ];
            foreach ($spareparts as $sparepart) {
                Sparepart::create(array_merge($sparepart, [
                    'partner_id' => $partner->id,
                    'description' => $sparepart['name'].' original berkualitas',
                    'is_active' => true,
                ]));
            }
        }

        // Pending partners (3)
        $pendingPartners = Partner::factory()->pending()->count(3)->create();
        foreach ($pendingPartners as $pp) {
            WalletBalance::create([
                'user_id' => $pp->user_id,
                'balance' => 0,
                'total_income' => 0,
            ]);
        }

        // ─── Partner Subscriptions ──────────────────────
        $plans = ['basic', 'pro', 'enterprise'];
        $planPrices = ['basic' => 99000, 'pro' => 299000, 'enterprise' => 599000];
        $planFeatures = [
            'basic' => ['priority_dispatch' => false, 'promo_spot' => false, 'analytics' => false],
            'pro' => ['priority_dispatch' => true, 'promo_spot' => false, 'analytics' => true],
            'enterprise' => ['priority_dispatch' => true, 'promo_spot' => true, 'analytics' => true],
        ];

        foreach ($partners->take(5) as $idx => $partner) {
            $plan = $plans[$idx % 3];
            PartnerSubscription::create([
                'partner_id' => $partner->id,
                'plan' => $plan,
                'amount' => $planPrices[$plan],
                'status' => 'active',
                'started_at' => now()->subDays(rand(10, 90)),
                'expires_at' => now()->addDays(rand(30, 300)),
                'features' => $planFeatures[$plan],
            ]);
        }

        // ─── Orders (50) — mix of statuses ──────────────
        $orders = collect();
        $statuses = ['pending', 'dispatching', 'accepted', 'on_the_way', 'arrived', 'in_progress', 'completed', 'cancelled'];
        $statusWeights = [2, 3, 3, 3, 2, 5, 25, 7]; // weighted toward completed

        $serviceTypes = [
            'Ganti Oli', 'Servis Mesin', 'Servis Kelistrikan', 'Ganti Ban',
            'Ganti Aki', 'Servis Rem', 'Tune Up', 'Overhaul',
            'Servis CVT', 'Perbaikan Rantai',
        ];

        $problemDescriptions = [
            'Motor mogok tiba-tiba di jalan, mesin tidak bisa di-starter',
            'Mobil bunyi berisik di bagian depan, kemungkinan bearing roda',
            'Aki habis, lampu indikator menyala merah',
            'Ban bocor, ada paku menancap di ban belakang',
            'Rem depan berdecit keras saat ditekan',
            'Mesin panas berlebihan (overheat), air radiator berkurang',
            'Oli mesin sudah hitam dan berkurang, perlu ganti segera',
            'CVT motor matic bergetar di RPM rendah',
            'Lampu utama mati sebelah, kelistrikan bermasalah',
            'Motor tersengat listrik saat mesin hidup',
            'Transmisi matic selip saat akselerasi',
            'Suara mesin klek-klek-klek saat langsam',
            'Spedometer dan indikator mati total',
            'Rem belakang kurang pakem, sudah diganti kampas tapi masih blong',
            'Rantai motor sering lepas, gear sudah aus',
        ];

        for ($i = 0; $i < 50; $i++) {
            $statusIndex = $this->weightedRandom($statusWeights);
            $status = $statuses[$statusIndex];
            $customer = $customers->random();
            $partner = $partners->random();
            $calloutFee = fake()->randomElement([15000, 20000, 25000, 30000]);
            $serviceFee = in_array($status, ['completed']) ? fake()->numberBetween(50000, 500000) : 0;
            $totalAmount = $calloutFee + $serviceFee;
            $commissionRate = config('services.montirgo.additional_commission_rate', 0.10);
            $platformCommission = round($calloutFee * 0.20 + ($serviceFee > 0 ? $serviceFee * $commissionRate : 0), 2);
            $partnerEarning = $totalAmount - $platformCommission;

            $createdAt = fake()->dateTimeBetween('-30 days', 'now');

            $orderData = [
                'user_id' => $customer->id,
                'partner_id' => $partner->id,
                'service_type' => fake()->randomElement($serviceTypes),
                'problem_description' => fake()->randomElement($problemDescriptions),
                'location_lat' => fake()->latitude(-6.25, -6.15),
                'location_lng' => fake()->longitude(106.80, 106.90),
                'location_address' => fake()->streetAddress().', Mojokerto',
                'status' => $status,
                'callout_fee' => $calloutFee,
                'service_fee' => $serviceFee,
                'total_amount' => $totalAmount,
                'platform_commission' => $platformCommission,
                'partner_earning' => $partnerEarning,
                'payment_method' => fake()->randomElement(['cash', 'cash', 'cash', 'qris', 'wallet']),
                'payment_status' => $status === 'completed' ? 'paid' : ($status === 'cancelled' ? 'refunded' : 'unpaid'),
                'is_sos' => fake()->boolean(10),
                'created_at' => $createdAt,
            ];

            // Status timestamps — using columns that exist in the orders table
            $baseTime = Carbon::parse($createdAt);
            if (in_array($status, ['dispatching', 'accepted', 'on_the_way', 'arrived', 'in_progress', 'completed'])) {
                $orderData['dispatch_started_at'] = $baseTime->addSeconds(rand(10, 120));
            }
            if (in_array($status, ['accepted', 'on_the_way', 'arrived', 'in_progress', 'completed'])) {
                $orderData['started_at'] = Carbon::parse($orderData['dispatch_started_at'])->addMinutes(rand(1, 5));
            }
            if (in_array($status, ['completed'])) {
                $orderData['completed_at'] = Carbon::parse($orderData['started_at'])->addMinutes(rand(15, 120));
                $orderData['paid_at'] = $orderData['completed_at'];
            }
            if ($status === 'cancelled') {
                $orderData['cancelled_at'] = $baseTime->addSeconds(rand(5, 300));
                $orderData['cancel_reason'] = fake()->randomElement([
                    'Ganti pikiran', 'Sudah diperbaiki sendiri', 'Order salah', 'Terlalu lama menunggu',
                ]);
            }

            $order = Order::create($orderData);
            $orders->push($order);

            // Payment record untuk completed orders
            if ($status === 'completed') {
                Payment::create([
                    'order_id' => $order->id,
                    'method' => $order->payment_method,
                    'provider' => $order->payment_method === 'cash' ? null : 'midtrans',
                    'transaction_id' => $order->payment_method !== 'cash' ? 'TXN-'.strtoupper(uniqid()) : null,
                    'amount' => $totalAmount,
                    'status' => 'paid',
                    'paid_at' => $orderData['paid_at'],
                    'metadata' => $order->payment_method !== 'cash' ? [
                        'payment_type' => $order->payment_method,
                        'status_code' => '200',
                    ] : null,
                ]);
            }

            // Service cost items untuk completed orders
            if ($status === 'completed' && $serviceFee > 0) {
                $itemCount = fake()->numberBetween(1, 4);
                $remainingFee = $serviceFee;
                for ($j = 0; $j < $itemCount; $j++) {
                    $isLast = $j === $itemCount - 1;
                    $itemName = fake()->randomElement([
                        'Servis Mesin Ringan', 'Ganti Oli + Filter', 'Tambal Ban Tubeless',
                        'Ganti Kampas Rem', 'Tune Up Ringan', 'Servis CVT',
                        'Ganti Busi', 'Ganti Aki', 'Servis Kelistrikan',
                    ]);
                    $itemType = in_array($itemName, ['Ganti Oli + Filter', 'Ganti Kampas Rem', 'Ganti Busi', 'Ganti Aki'])
                        ? 'sparepart'
                        : 'service';
                    $qty = fake()->numberBetween(1, 3);
                    $subtotal = $isLast ? $remainingFee : (int) ($remainingFee / ($itemCount - $j));
                    $unitPrice = (int) ($subtotal / $qty);

                    ServiceCostItem::create([
                        'order_id' => $order->id,
                        'name' => $itemName,
                        'type' => $itemType,
                        'unit_price' => $unitPrice,
                        'quantity' => $qty,
                        'subtotal' => $subtotal,
                    ]);

                    $remainingFee -= $subtotal;
                }
            }

            // Order photos untuk completed/in_progress orders
            if (in_array($status, ['completed', 'in_progress'])) {
                $partnerUser = $partner->user;
                OrderPhoto::create([
                    'order_id' => $order->id,
                    'photo_url' => 'order-photos/before-'.$order->id.'.jpg',
                    'type' => 'before',
                    'caption' => 'Kondisi sebelum servis',
                    'uploaded_by' => $partnerUser->id,
                ]);
                if ($status === 'completed') {
                    OrderPhoto::create([
                        'order_id' => $order->id,
                        'photo_url' => 'order-photos/after-'.$order->id.'.jpg',
                        'type' => 'after',
                        'caption' => 'Kondisi setelah servis',
                        'uploaded_by' => $partnerUser->id,
                    ]);
                }
            }

            // Reviews untuk completed orders (80% have reviews)
            if ($status === 'completed' && fake()->boolean(80)) {
                $rating = fake()->randomElement([3, 4, 4, 5, 5, 5]);
                $comments = [
                    'Servis bagus, mekanik ramah dan profesional. Terima kasih!',
                    'Motor saya kembali normal setelah diservis. Sangat recommended.',
                    'Lumayan, tapi agak lama pengerjaannya. Hasilnya oke.',
                    'Puas dengan hasilnya, harga sesuai dengan kualitas.',
                    'Mekanik datang tepat waktu dan langsung kerja. Mantap!',
                    'Sparepart original, harga transparan. Top!',
                    'Ban sudah diganti dengan baik, terima kasih.',
                    'Mesin sekarang halus lagi, terima kasih bengkelnya.',
                ];
                $review = Review::create([
                    'order_id' => $order->id,
                    'user_id' => $customer->id,
                    'partner_id' => $partner->id,
                    'rating' => $rating,
                    'comment' => fake()->randomElement($comments),
                    'replied_at' => fake()->boolean(50) ? now()->subHours(rand(1, 48)) : null,
                    'partner_reply' => fake()->boolean(50) ? 'Terima kasih atas reviewnya! Senang bisa membantu. 🙏' : null,
                ]);

                // Update partner rating
                $partner->update([
                    'rating_avg' => Review::where('partner_id', $partner->id)->avg('rating'),
                    'total_orders' => Order::where('partner_id', $partner->id)->where('status', 'completed')->count(),
                ]);
            }

            // Call logs untuk orders yang sudah accepted (30%)
            if (in_array($status, ['accepted', 'on_the_way', 'arrived', 'in_progress', 'completed']) && fake()->boolean(30)) {
                $startedAt = $orderData['started_at'] ?? $baseTime->addMinutes(5);
                $duration = fake()->numberBetween(15, 300);
                CallLog::create([
                    'order_id' => $order->id,
                    'caller_id' => $customer->id,
                    'receiver_id' => $partner->user_id,
                    'status' => 'completed',
                    'started_at' => $startedAt,
                    'ended_at' => Carbon::parse($startedAt)->addSeconds($duration),
                    'duration_seconds' => $duration,
                ]);
            }

            // Chat rooms untuk orders yang sudah accepted (60%)
            if (in_array($status, ['accepted', 'on_the_way', 'arrived', 'in_progress', 'completed']) && fake()->boolean(60)) {
                $chat = Chat::create([
                    'order_id' => $order->id,
                    'user_id' => $customer->id,
                    'partner_id' => $partner->id,
                    'is_active' => $status !== 'completed',
                    'last_message_at' => now()->subMinutes(rand(1, 60)),
                ]);

                // Chat messages
                $messages = [
                    ['sender' => $customer->id, 'text' => 'Halo, saya sudah di lokasi yang ditandai di maps ya.'],
                    ['sender' => $partner->user_id, 'text' => 'Baik pak/bu, saya sedang dalam perjalanan. Estimasi 10 menit.'],
                    ['sender' => $customer->id, 'text' => 'Oke, ditunggu. Terima kasih.'],
                ];
                if (fake()->boolean(50)) {
                    $messages[] = ['sender' => $partner->user_id, 'text' => 'Sudah tiba di lokasi, mobil/Honda Beat warna apa ya?'];
                }

                foreach ($messages as $msg) {
                    ChatMessage::create([
                        'chat_id' => $chat->id,
                        'sender_id' => $msg['sender'],
                        'message' => $msg['text'],
                        'is_read' => true,
                    ]);
                }
            }
        }

        // ─── Wallet Transactions ────────────────────────
        foreach ($partners as $partner) {
            $partnerOrders = Order::where('partner_id', $partner->id)
                ->where('status', 'completed')
                ->get();

            foreach ($partnerOrders as $po) {
                WalletTransaction::create([
                    'user_id' => $partner->user_id,
                    'order_id' => $po->id,
                    'type' => 'income',
                    'amount' => $po->partner_earning,
                    'description' => 'Pendapatan order '.$po->code,
                ]);
            }
        }

        // ─── Withdraw Requests ──────────────────────────
        foreach ($partners->take(5) as $partner) {
            WithdrawRequest::create([
                'user_id' => $partner->user_id,
                'amount' => fake()->numberBetween(100000, 500000),
                'bank_name' => fake()->randomElement(['BCA', 'Mandiri', 'BRI', 'BNI']),
                'bank_account_number' => fake()->numerify('##########'),
                'bank_account_name' => $partner->user->name,
                'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
                'processed_at' => fake()->boolean(70) ? now()->subDays(rand(1, 7)) : null,
            ]);
        }

        // ─── Advertisements (8) ─────────────────────────
        $adData = [
            ['title' => ' oli Motul 7100 — Mesin Lebih Halus', 'position' => 'banner', 'target' => 'https://motul.com/promo-7100'],
            ['title' => 'Aki GS Astra — Daya Tahan Maksimal', 'position' => 'sidebar', 'target' => 'https://gsastra.com/products'],
            ['title' => 'Ban Michelin Pilot Street — Grip Terbaik', 'position' => 'feed', 'target' => 'https://michelin.co.id/pilot-street'],
            ['title' => 'Filter Ohi AHM — Original Parts', 'position' => 'banner', 'target' => 'https://ahm-parts.com/filter'],
            ['title' => 'Promo Servis CVT — Diskon 30%', 'position' => 'popup', 'target' => null],
            ['title' => 'Busi NGK Iridium — Performa Maksimal', 'position' => 'sidebar', 'target' => 'https://ngk.com/iridium'],
            ['title' => 'Asuransi Kendaraan — Klaim Mudah', 'position' => 'feed', 'target' => 'https://asuransiku.com/kendaraan'],
            ['title' => 'MontirGo Premium — Prioritas Dispatch', 'position' => 'banner', 'target' => null],
        ];

        foreach ($adData as $ad) {
            Advertisement::create([
                'title' => $ad['title'],
                'image_path' => 'advertisements/ad-'.strtolower(str_replace(' ', '-', trim($ad['title']))).'.jpg',
                'target_url' => $ad['target'],
                'position' => $ad['position'],
                'start_date' => fake()->dateTimeBetween('-30 days', 'now'),
                'end_date' => fake()->dateTimeBetween('now', '+60 days'),
                'is_active' => fake()->boolean(80),
                'impressions' => fake()->numberBetween(100, 5000),
                'clicks' => fake()->numberBetween(5, 500),
            ]);
        }

        // ─── Insurance Partners (3) ─────────────────────
        $insuranceData = [
            ['name' => 'Asuransi MobilKu', 'code' => 'MOBILKU'],
            ['name' => 'Allianz AutoGuard', 'code' => 'ALLIANZ'],
            ['name' => 'AXA Motor Shield', 'code' => 'AXA'],
        ];
        foreach ($insuranceData as $ins) {
            InsurancePartner::create([
                'name' => $ins['name'],
                'code' => $ins['code'],
                'api_key' => 'ins_'.strtolower($ins['code']).'_key_'.Str::random(20),
                'api_secret' => Str::random(40),
                'api_url' => 'https://api.'.strtolower($ins['code']).'.co.id/v1',
                'config' => ['webhook_url' => url('/api/v1/insurance/webhook/claim-update')],
                'status' => 'active',
            ]);
        }

        // ─── Notifications Log (sample) ─────────────────
        foreach ($customers->take(5) as $customer) {
            $notifiableOrders = Order::where('user_id', $customer->id)->take(3)->get();
            foreach ($notifiableOrders as $no) {
                NotificationLog::create([
                    'user_id' => $customer->id,
                    'type' => 'order_status',
                    'title' => 'Status Order Diperbarui',
                    'body' => 'Order '.$no->code.' status: '.$no->status,
                    'data' => ['order_id' => $no->id, 'status' => $no->status],
                    'status' => fake()->randomElement(['sent', 'delivered']),
                ]);
            }
        }

        // ─── Summary ────────────────────────────────────
        $this->command->info('✅ Seeder berhasil dijalankan!');
        $this->command->info('');
        $this->command->info('📊 Data Summary:');
        $this->command->info('   Admin:      1 user (admin@montirgo.id)');
        $this->command->info('   Customers:  '.$customers->count().' users');
        $this->command->info('   Partners:   '.$partners->count().' approved + '.$pendingPartners->count().' pending');
        $this->command->info('   Orders:     50 orders (mix statuses)');
        $this->command->info('   Reviews:    '.Review::count().' reviews');
        $this->command->info('   Chats:      '.Chat::count().' rooms');
        $this->command->info('   Ads:        '.Advertisement::count().' advertisements');
        $this->command->info('');
        $this->command->info('🔑 Login Credentials:');
        $this->command->info('   Admin:    admin@montirgo.id / password');
        $this->command->info('   Customer: budi@email.com / password');
        $this->command->info('   Partner:  partner1@email.com / password');
    }

    /**
     * Weighted random selection from an array of weights.
     */
    private function weightedRandom(array $weights): int
    {
        $total = array_sum($weights);
        $random = mt_rand(1, $total);
        $sum = 0;

        foreach ($weights as $index => $weight) {
            $sum += $weight;
            if ($random <= $sum) {
                return $index;
            }
        }

        return count($weights) - 1;
    }
}
