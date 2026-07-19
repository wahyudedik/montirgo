<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/reverb (REVERB) - v1
- laravel/sanctum (SANCTUM) - v4
- laravel/boost (BOOST) - v2
- laravel/breeze (BREEZE) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- laravel-echo (ECHO) - v2
- alpinejs (ALPINEJS) - v3
- tailwindcss (TAILWINDCSS) - v3

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd at `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs. Never run commands to serve the site. It is always available.
- Use the `herd` CLI to manage services, PHP versions, and sites (e.g. `herd sites`, `herd services:start <service>`, `herd php:list`). Run `herd list` to discover all available commands.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>

<!-- ============================================================
     PROJECT KNOWLEDGE GRAPH — MontirGo
     File ini berfungsi sebagai peta pengetahuan agar AI dapat
     memahami struktur proyek tanpa perlu membaca semua file.
     Terakhir diperbarui: 2026-07-18
     ============================================================ -->

# MontirGo — Knowledge Graph

## Apa Itu MontirGo?

MontirGo adalah platform **on-demand mechanic emergency service** yang menghubungkan pengendara yang mogok/kendala di jalan dengan mekanik/bengkel terdekat secara real-time. Fokus utama: solusi cepat, transparan, dan andal untuk situasi darurat kendaraan.

**Target pasar awal:** Mojokerto dan sekitarnya.

---

## Arsitektur Sistem (3 Komponen Utama)

```
montirgo/
├── app/                    # Laravel Backend (API + Web)
├── mobile_montirgo/        # React Native Monorepo (Mobile Apps)
│   ├── packages/
│   │   ├── shared/         # Komponen & logic shared (customer + partner)
│   │   ├── customer/       # Aplikasi mobile untuk pelanggan
│   │   └── partner/        # Aplikasi mobile untuk bengkel/mekanik
│   └── ...
├── resources/views/        # Blade templates (Admin Web + Customer/Partner Web)
├── routes/                 # Route definitions
├── database/               # Migrations & seeders
├── config/                 # Laravel config files
├── tests/                  # Pest tests
└── plans/                  # Dokumentasi arsitektur & roadmap
```

---

## Backend (Laravel 13 + PHP 8.4)

### Models & Database Schema

| Model | Table | Keterangan |
|-------|-------|------------|
| [`User`](app/Models/User.php) | `users` | Role: customer, partner, admin. FCM token untuk push notification. |
| [`Partner`](app/Models/Partner.php) | `partners` | Data bengkel: nama, alamat, GPS, workshop_category (motorcycle/car/both), service_radius, partner_status (draft/pending/approved/rejected/suspended), dokumen (KTP, foto, lisensi), profile_completion, rating. |
| [`PartnerService`](app/Models/PartnerService.php) | `partner_services` | Layanan yang ditawarkan partner dengan vehicle_category (motorcycle/car/both). |
| [`Mechanic`](app/Models/Mechanic.php) | `mechanics` | Mekanik per bengkel: nama, ahli (expertise: motorcycle/car/both), sertifikasi, status aktif. |
| [`Symptom`](app/Models/Symptom.php) | `symptoms` | Gejala/diagnosis wizard: kode, deskripsi, kategori kendaraan, link ke kemungkinan penyebab & solusi. |
| [`Vehicle`](app/Models/Vehicle.php) | `vehicles` | Kendaraan pelanggan: brand, model, tahun, plat, tipe (motorcycle/car/suv/truck/other), subcategory. |
| [`Order`](app/Models/Order.php) | `orders` | Inti aplikasi. Status: pending→dispatching→accepted→on_the_way→arrived→in_progress→completed/cancelled. |
| [`OrderPhoto`](app/Models/OrderPhoto.php) | `order_photos` | Foto before/after perbaikan. |
| [`Payment`](app/Models/Payment.php) | `payments` | Record pembayaran: method (qris/ewallet/bank_transfer), status, amount. |
| [`ServiceCostItem`](app/Models/ServiceCostItem.php) | `service_cost_items` | Rincian biaya servis (item perbaikan/sparepart) per order. |
| [`Review`](app/Models/Review.php) | `reviews` | Rating & komentar pelanggan untuk partner. |
| [`Chat`](app/Models/Chat.php) | `chats` | Chat room per order. |
| [`ChatMessage`](app/Models/ChatMessage.php) | `chat_messages` | Pesan chat: text atau image. |
| [`WalletBalance`](app/Models/WalletBalance.php) | `wallet_balances` | Saldo dompet partner. |
| [`WalletTransaction`](app/Models/WalletTransaction.php) | `wallet_transactions` | Transaksi wallet: earning, withdrawal, refund. |
| [`WithdrawRequest`](app/Models/WithdrawRequest.php) | `withdraw_requests` | Permintaan penarikan dana partner. |
| [`NotificationLog`](app/Models/NotificationLog.php) | `notifications_log` | Log notifikasi yang dikirim. |
| [`Advertisement`](app/Models/Advertisement.php) | `advertisements` | Iklan otomotif dengan tracking impression/click. |
| [`CallLog`](app/Models/CallLog.php) | `call_logs` | Log panggilan telepon dalam aplikasi. |
| [`Sparepart`](app/Models/Sparepart.php) | `spareparts` | Stok suku cadang partner. |
| [`UserFcmToken`](app/Models/UserFcmToken.php) | `user_fcm_tokens` | Token FCM per device untuk push notification multi-device. |
| [`InsurancePartner`](app/Models/InsurancePartner.php) | `insurance_partners` | Mitra asuransi kendaraan. |
| [`InsuranceClaim`](app/Models/InsuranceClaim.php) | `insurance_claims` | Klaim asuransi dari order. |

### Services

| Service | File | Keterangan |
|---------|------|------------|
| [`PaymentService`](app/Services/PaymentService.php) | Hitung biaya, proses pembayaran, konfirmasi, refund. Callout fee Rp30.000, komisi 5-10% dari biaya servis. Buat Snap Token via Midtrans. |
| [`MidtransService`](app/Services/MidtransService.php) | Wrapper Midtrans Snap API. Buat Snap Token, verifikasi webhook signature, cek status transaksi, proses refund. |
| [`DispatchService`](app/Services/DispatchService.php) | Auto-dispatch order ke partner terdekat dengan eskalasi radius (5→30km), filter workshop_category & symptom matching. |
| [`ChatService`](app/Services/ChatService.php) | Kirim pesan chat, notifikasi real-time via Reverb/WebSocket. |
| [`WalletService`](app/Services/WalletService.php) | Kelola saldo dompet partner, kredit earning, proses withdraw. |
| [`NotificationService`](app/Services/NotificationService.php) | Push notification via FCM, notifikasi in-app. |
| [`EmergencyService`](app/Services/EmergencyService.php) | Handle order SOS darurat. |
| [`GeolocationService`](app/Services/GeolocationService.php) | Hitung jarak, cari partner terdekat, matching kategori kendaraan & gejala. |
| [`FileUploadService`](app/Services/FileUploadService.php) | Upload, replace, delete file dokumen partner (KTP, foto bengkel, lisensi). |
| [`VehicleService`](app/Services/VehicleService.php) | Kelola data kendaraan pelanggan. |
| [`CaptchaService`](app/Services/CaptchaService.php) | Validasi CAPTCHA saat register/login. |
| [`LocationTrackingService`](app/Services/LocationTrackingService.php) | Update & broadcast lokasi partner real-time. |
| [`ReviewService`](app/Services/ReviewService.php) | Buat & kelola review/rating. |
| [`AnalyticsService`](app/Services/AnalyticsService.php) | Statistik dashboard admin. |

### Routes

| File | Keterangan |
|------|------------|
| [`routes/api.php`](routes/api.php) | REST API v1 untuk mobile apps. Auth: Sanctum. Endpoint: auth, orders, chat, wallet, partner, mechanics, symptoms, SOS, insurance, payment webhook, admin partner management. |
| [`routes/web.php`](routes/web.php) | Web routes untuk Customer & Partner portal (Blade). |
| [`routes/admin.php`](routes/admin.php) | Admin panel routes (users, partners, orders, withdrawals, advertisements). |
| [`routes/auth.php`](routes/auth.php) | Authentication routes (login, register, password reset via Breeze). |
| [`routes/channels.php`](routes/channels.php) | Broadcast channel authorization. |

### API Endpoints Utama (v1)

**Public:**
- `POST /v1/auth/register`, `POST /v1/auth/login`
- `GET /v1/partners/nearby`
- `GET /v1/symptoms` (diagnosis wizard)
- `GET /v1/insurance/partners`
- `POST /v1/payment/webhook` (Midtrans callback — signature verified)
- `GET /v1/payment/status/{orderCode}`

**Protected (Sanctum):**
- Auth: `POST /auth/logout`, `GET|PATCH /auth/profile`, `POST /auth/location`, `GET /auth/profile-completion`
- Orders: `GET|POST /orders`, `GET /orders/{order}`, `PATCH /orders/{order}/cancel`
- Partner Orders: `GET /partner/orders`, `PATCH /partner/orders/{order}/accept|reject|status`
- Partner Profile: `GET|PATCH /partner/profile`, `POST /partner/toggle-online|toggle-availability|location|status`, `GET /partner/profile-completion`
- Mechanics: `GET|POST /partner/mechanics`, `PATCH|DELETE /partner/mechanics/{mechanic}`
- Partner Services: `GET|POST /partner/services`, `PATCH|DELETE /partner/services/{service}`, `PATCH /partner/services/{service}/toggle`
- Partner Spareparts: `GET|POST /partner/spareparts`, `GET|PATCH|DELETE /partner/spareparts/{sparepart}`, `PATCH /partner/spareparts/{sparepart}/toggle`
- Chat: `GET /orders/{order}/chat`, `POST /orders/{order}/chat/send`, `GET /orders/{order}/chat/poll`
- Wallet: `GET /wallet`, `GET /wallet/transactions`, `POST /wallet/withdraw`, `GET /wallet/withdraw/history`
- Reviews: `GET|POST /reviews`, `GET /reviews/stats`, `GET /partners/{partner}/reviews`, `GET /partner/reviews`, `POST /reviews/{review}/reply`
- Notifications: `GET /notifications`, `POST /notifications/read-all`, `POST /fcm-token`
- Vehicles: `GET|POST /vehicles`, `GET|PUT|DELETE /vehicles/{vehicle}`, `PATCH /vehicles/{vehicle}/default`
- SOS: `POST /sos`
- Insurance: `POST /orders/{order}/insurance-claim`, `GET /insurance-claims/{claim}/status`
- Payment: `POST /orders/{order}/pay` (customer — buat Snap Token callout fee)
- Payment: `POST /partner/orders/{order}/service-fee-pay` (partner — buat Snap Token service fee)
- Ads: `GET /ads`, `GET /ads/{advertisement}`, `POST /ads/{advertisement}/impression|click`
- Admin: `GET /admin/dashboard/stats|revenue-chart|order-status|top-partners`
- Admin Partners: `GET /admin/partners`, `GET /admin/partners/{partner}`, `PATCH /admin/partners/{partner}/approve|reject|suspend`

### Web Routes

**Customer** (`/customer`): orders (CRUD), sos, chat, reviews, history, profile
**Partner** (`/partner`): orders (accept/reject/status), chat, wallet, reviews, spareparts, service-cost, mechanics, photos
**Admin** (`/admin`): dashboard, users, partners (approve/reject/suspend with workshop_category filter), orders, withdraws, advertisements

### Config Penting

| File | Keterangan |
|------|------------|
| [`config/maps.php`](config/maps.php) | API key Google Maps |
| [`config/reverb.php`](config/reverb.php) | WebSocket config (Laravel Reverb) |
| [`config/sanctum.php`](config/sanctum.php) | Sanctum token expiry & guard |
| [`config/midtrans.php`](config/midtrans.php) | Midtrans Snap API config (client/server key, sandbox/production) |
| [`config/services.php`](config/services.php) | Third-party service config (FCM, Midtrans, payment gateway) |

---

## Mobile Apps (React Native Monorepo)

### Tech Stack

- **React Native** (Expo-compatible) + TypeScript
- **Yarn Workspaces** monorepo
- **React Navigation v7** (Native Stack + Bottom Tabs)
- **Zustand** untuk state management
- **Axios** untuk HTTP client
- **react-native-vector-icons** (Ionicons) untuk ikon
- **react-native-maps** untuk peta
- **@react-native-async-storage/async-storage** untuk persistensi token

### Monorepo Structure

```
mobile_montirgo/
├── package.json              # Root workspace config
├── packages/
│   ├── shared/               # Shared package (@montirgo/shared)
│   │   └── src/
│   │       ├── api/          # HTTP client & API functions
│   │       │   ├── client.ts         # Axios instance, interceptors
│   │       │   ├── auth.api.ts       # Login, register, profile
│   │       │   ├── order.api.ts      # CRUD orders
│   │       │   ├── chat.api.ts       # Chat operations
│   │       │   ├── partner.api.ts    # Partner operations
│   │       │   ├── wallet.api.ts     # Wallet operations
│   │       │   └── notification.api.ts # Notifications
│   │       ├── components/
│   │       │   ├── ui/       # Reusable UI components
│   │       │   │   ├── Avatar.tsx, Badge.tsx, Button.tsx, Card.tsx
│   │       │   │   ├── EmptyState.tsx, ErrorBoundary.tsx
│   │       │   │   ├── Input.tsx, LoadingSpinner.tsx, Modal.tsx
│   │       │   ├── order/    # OrderCard.tsx, StatusBadge.tsx
│   │       │   └── maps/     # MapView.tsx, Marker.tsx, RouteLine.tsx
│   │       ├── constants/    # Konstanta aplikasi
│   │       │   ├── colors.ts         # COLORS object (primary, gray, success, warning, error, info)
│   │       │   ├── config.ts         # API_BASE_URL, MAPS_API_KEY
│   │       │   ├── order-status.ts   # Status order mapping
│   │       │   ├── payment-methods.ts # Metode pembayaran config
│   │       │   ├── sos-categories.ts # Kategori SOS
│   │       │   └── typography.ts     # Font sizes & weights
│   │       ├── hooks/        # Custom hooks
│   │       │   ├── useAuth.ts        # Auth state & actions
│   │       │   ├── useDebounce.ts
│   │       │   ├── useLocation.ts    # GPS location
│   │       │   ├── useOrder.ts       # Order operations
│   │       │   └── useRealtime.ts    # WebSocket/Reverb connection
│   │       ├── stores/       # Zustand stores
│   │       │   ├── auth.store.ts     # Token, user, isAuthenticated
│   │       │   └── location.store.ts # Current location state
│   │       ├── types/        # TypeScript type definitions
│   │       │   └── index.ts  # Semua shared types (User, Partner, Order, Payment, Chat, dll)
│   │       └── utils/        # Utility functions
│   │           ├── formatters.ts     # Currency, date formatting
│   │           ├── location.ts       # Distance calculation
│   │           ├── permissions.ts    # Location permission
│   │           ├── storage.ts        # AsyncStorage wrappers
│   │           └── validators.ts     # Form validation
│   │
│   ├── customer/             # Customer App (@montirgo/customer)
│   │   ├── src/
│   │   │   ├── App.tsx       # Entry point
│   │   │   ├── navigation/
│   │   │   │   ├── index.tsx       # RootNavigator (auth check)
│   │   │   │   ├── AuthStack.tsx   # Login, Register, ForgotPassword
│   │   │   │   └── MainTabs.tsx    # Home, Orders, Chat, Profile (4 tabs)
│   │   │   ├── screens/
│   │   │   │   ├── auth/           # LoginScreen, RegisterScreen, ForgotPasswordScreen
│   │   │   │   ├── home/           # HomeScreen (dashboard + quick actions)
│   │   │   │   ├── order/          # NewOrderScreen, OrderListScreen, OrderDetailScreen
│   │   │   │   ├── tracking/       # TrackingScreen (realtime map)
│   │   │   │   ├── chat/           # ChatListScreen, ChatRoomScreen
│   │   │   │   ├── review/         # ReviewFormScreen
│   │   │   │   ├── sos/            # SOSScreen (emergency)
│   │   │   │   └── profile/        # ProfileScreen, EditProfileScreen, SettingsScreen, VehicleListScreen, VehicleFormScreen
│   │   │   └── assets/images/      # favicon.png, logo-rm.png, logo.png
│   │   └── package.json
│   │
│   └── partner/              # Partner App (@montirgo/partner)
│       ├── src/
│       │   ├── App.tsx
│       │   ├── navigation/
│       │   │   ├── index.tsx       # RootNavigator
│       │   │   ├── AuthStack.tsx   # Login, Register
│       │   │   └── MainTabs.tsx    # Dashboard, Orders, Incoming, Wallet, Profile (5 tabs)
│       │   ├── screens/
│       │   │   ├── auth/           # LoginScreen, RegisterScreen
│       │   │   ├── dashboard/      # DashboardScreen (stats + incoming orders)
│       │   │   ├── order/          # OrderListScreen, OrderDetailScreen, IncomingOrderScreen, ServiceCostScreen
│       │   │   ├── chat/           # ChatRoomScreen
│       │   │   ├── profile/        # ProfileScreen, EditProfileScreen, SettingsScreen
│       │   │   └── wallet/         # WalletScreen, WithdrawScreen
│       │   └── assets/images/
│       └── package.json
```

### Navigation Structure

**Customer Tabs:** Home → Orders → Chat → Profile
**Partner Tabs:** Dashboard → Orders → Incoming → Wallet → Profile

### Shared Types Penting

Didefinisikan di [`mobile_montirgo/packages/shared/src/types/index.ts`](mobile_montirgo/packages/shared/src/types/index.ts):

- `UserRole`: customer | partner | admin
- `OrderStatus`: pending → dispatching → accepted → on_the_way → arrived → in_progress → completed | cancelled
- `PaymentMethod`: qris | ewallet | bank_transfer
- `PaymentStatus`: pending | paid | failed | refunded
- `ServiceType`: service | emergency | maintenance | inspection
- `VehicleType`: motorcycle | car | suv | truck | other
- `VehicleSubcategory`: sedan | hatchback | mpv | suv_car | pickup | van | sport_bike | scooter | matic | bebek | lainnya (detail per tipe)
- `WorkshopCategory`: motorcycle | car | both
- `PartnerStatus`: draft | pending | approved | rejected | suspended (verifikasi)
- `PartnerOperationalStatus`: offline | online | on_the_way | in_progress | resting | closed
- `MechanicExpertise`: motorcycle | car | both
- `SOSCategory`: flat_tire | dead_battery | out_of_fuel | locked_keys | overheat
- `Mechanic`: id, partner_id, name, phone, expertise, certifications, is_active
- `Symptom`: id, code, label, description, vehicle_category, possible_causes, suggested_solutions, severity

### Icon System

Semua ikon menggunakan **Ionicons** via `react-native-vector-icons/Ionicons`. Gunakan `Icon` component dari `react-native-vector-icons/Ionicons`. Contoh:
```tsx
import Icon from 'react-native-vector-icons/Ionicons';
<Icon name="construct-outline" size={24} color={COLORS.primary} />
```

**Tidak ada emoji** di seluruh codebase mobile dan website. Semua visual menggunakan Ionicons (mobile) atau inline SVG (website).

---

## Model Bisnis & Pembayaran (Sesuai Brief Bisnis)

### Prinsip Utama
MontirGo seperti **Gojek/Grab** untuk layanan otomotif. Model bisnis **transaction-based**. **TIDAK ada subscription/langganan** untuk mitra bengkel — semua fitur gratis.

### Sumber Pendapatan Platform
1. **Callout Fee** (Biaya Panggilan): **Rp30.000** per order — dibayar user saat buat order
2. **Komisi Biaya Perbaikan**: **5-10%** dari total biaya servis — dipotong otomatis dari pembayaran user

### Metode Pembayaran (User)

| Metode | Deskripsi | Status |
|--------|-----------|--------|
| **QRIS** | Scan QR Code | Type didefinisikan, belum integrasi gateway |
| **Virtual Account** | Transfer Bank VA | Type didefinisikan, belum integrasi gateway |
| **Transfer Bank** | Manual transfer | Type didefinisikan, belum integrasi gateway |
| **E-Wallet** | GoPay, OVO, Dana | Type didefinisikan, belum integrasi gateway |

**Payment gateway**: Midtrans / Xendit

### Alur Pembayaran

```
User buat order → Bayar Callout Fee (Rp30.000)
→ Sistem dispatch ke bengkel terdekat (radius 5-30km, timeout 60s)
→ Bengkel terima → Mekanik on_the_way → arrived → in_progress
→ Mekanik input estimasi → User setuju → Servis → Input biaya akhir
→ User bayar melalui aplikasi
→ Platform potong komisi (5-10%)
→ Saldo bengkel bertambah → Withdraw ke rekening
```

### Alur Dana
```
User Bayar → Payment Gateway (Midtrans/Xendit) → Platform
→ Komisi platform dipotong otomatis (5-10%)
→ Sisa dana masuk saldo bengkel
→ Bengkel withdraw ke rekening bank
```

### Google Play Store Policy
**MontirGo AMAN** — Layanan adalah jasa fisik (mekanik datang ke lokasi). Payment gateway (Midtrans/Xendit) adalah third-party processor, bukan in-app purchase. **Tidak perlu Google Play Billing Library.**

### Yang TIDAK Ada (Sesuai Brief)
- **Tidak ada subscription/langganan** untuk mitra bengkel
- **Tidak ada cash payment** — semua pembayaran melalui aplikasi
- **Tidak ada fitur premium berbayar** untuk bengkel
- **Tidak ada Google Play Billing** — tidak diperlukan

---

## Key Business Logic

### Order Flow
```
User buat order → Bayar Callout Fee (Rp30.000)
→ System dispatch ke bengkel terdekat (radius escalation 5-30km, timeout 60s)
→ Bengkel terima → Mekanik on_the_way → arrived → in_progress
→ Mekanik input estimasi → User setuju → Servis → Input biaya akhir
→ User bayar → Platform potong komisi (5-10%) → Saldo bengkel bertambah
→ User rating & review
```

### Fee Calculation
- **Callout Fee**: Rp30.000 tetap (dibayar user di awal)
- **Platform Komisi**: 5-10% dari total biaya servis (dipotong otomatis)
- **Dana bengkel**: total servis - komisi platform

### SOS Emergency
Kategori: flat_tire, dead_battery, out_of_fuel, locked_keys, overheat
→ Auto-dispatch dengan prioritas tinggi, workflow sama seperti order normal

---

## File Referensi Cepat

### Untuk Memahami Bisnis Logic
- [`FEATURES.md`](FEATURES.md) — Spesifikasi lengkap fitur & model bisnis
- [`plans/ARCHITECTURE-MOBILE.md`](plans/ARCHITECTURE-MOBILE.md) — Arsitektur detail mobile
- [`REST-API.md`](REST-API.md) — Dokumentasi lengkap REST API
- [`ROADMAP-CUSTOMER-MOBILE.md`](ROADMAP-CUSTOMER-MOBILE.md) — Roadmap customer app
- [`ROADMAP-PARTNER-MOBILE.md`](ROADMAP-PARTNER-MOBILE.md) — Roadmap partner app
- [`plans/ROADMAP.md`](plans/ROADMAP.md) — Roadmap umum

### Untuk Memahami Database
- Lihat `database/migrations/` — Setiap file = 1 tabel dengan schema lengkap
- Lihat `app/Models/` — Setiap model = 1 tabel dengan relasi & method

### Untuk Memahami API
- [`routes/api.php`](routes/api.php) — Semua API endpoints
- [`REST-API.md`](REST-API.md) — Dokumentasi request/response

### Untuk Memahami Mobile
- [`mobile_montirgo/packages/shared/src/types/index.ts`](mobile_montirgo/packages/shared/src/types/index.ts) — Semua TypeScript types
- [`mobile_montirgo/packages/shared/src/constants/`](mobile_montirgo/packages/shared/src/constants/) — Konstanta warna, config, status, dll
- [`mobile_montirgo/packages/shared/src/api/client.ts`](mobile_montirgo/packages/shared/src/api/client.ts) — HTTP client setup

### Untuk Memahami Konfigurasi
- [`config/maps.php`](config/maps.php) — Google Maps API key
- [`config/reverb.php`](config/reverb.php) — WebSocket config
- [`config/services.php`](config/services.php) — Third-party services
- [`.env.example`](.env.example) — Template environment variables

---

## Conventions Penting

1. **Tidak ada emoji** di seluruh proyek (mobile & website). Gunakan Ionicons (mobile) atau inline SVG (website).
2. **Favicon**: `public/favicon.png`, **Logo**: `public/logo-rm.png`
3. **API versioning**: Selalu gunakan prefix `/v1/`
4. **Auth**: Sanctum Bearer Token untuk mobile, session untuk web
5. **Naming**: snake_case untuk database & API, camelCase untuk TypeScript/JavaScript
6. **Mobile icons**: Ionicons dari `react-native-vector-icons/Ionicons`
7. **Web icons**: Inline SVG (Heroicons pattern) atau Tailwind CSS
