# MontirGo — REST API Documentation

> **Base URL:** `https://montirgo.test/api`  
> **Version:** v1  
> **Auth:** Laravel Sanctum (Bearer Token)  
> **Content-Type:** `application/json`

---

## Daftar Isi

- [Autentikasi](#autentikasi)
- [Pelanggan (Customer)](#pelanggan-customer)
- [Mitra/Bengkel (Partner)](#mitrabengkel-partner)
- [Mekanik (Mechanic)](#mekanik-mechanic)
- [Gejala / Diagnosis Wizard](#gejala--diagnosis-wizard)
- [Chat & Komunikasi](#chat--komunikasi)
- [Dompet Digital (Wallet)](#dompet-digital-wallet)
- [Notifikasi](#notifikasi)
- [Asuransi (Insurance)](#asuransi-insurance)
- [Iklan (Advertisement Tracking)](#iklan-advertisement-tracking)
- [Payment Gateway](#payment-gateway)
- [SOS / Darurat](#sos--darurat)
- [Admin Partner Management](#admin-partner-management)
- [Response Format](#response-format)

---

## Autentikasi

### `POST /v1/auth/register`

Registrasi akun baru (customer atau partner).

**Request Body:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `name` | string | ✅ | Nama lengkap |
| `email` | string | ✅ | Email unik |
| `password` | string | ✅ | Password (min 8 char, harus dikonfirmasi) |
| `password_confirmation` | string | ✅ | Konfirmasi password |
| `phone` | string | ❌ | Nomor telepon |
| `role` | string | ❌ | `customer` (default) atau `partner` |
| `captcha_token` | string | ⚠️ | Token reCAPTCHA v3. Wajib jika `RECAPTCHA_SECRET_KEY` dikonfigurasi. Kosongkan/di-skip jika tidak ada konfigurasi reCAPTCHA. |

**Response (201):**

```json
{
    "message": "Registrasi berhasil",
    "user": {
        "id": 1,
        "name": "Budi",
        "email": "budi@email.com",
        "role": "customer",
        "phone": "08123456789"
    },
    "token": "1|abc123def456..."
}
```

---

### `POST /v1/auth/login`

Login ke akun.

**Request Body:**

| Field | Type | Required |
|:---|:---|:---|
| `email` | string | ✅ |
| `password` | string | ✅ |
| `captcha_token` | string | ⚠️ | Token reCAPTCHA v3. Wajib jika `RECAPTCHA_SECRET_KEY` dikonfigurasi. |

**Response (200):**

```json
{
    "message": "Login berhasil",
    "user": { "id": 1, "name": "Budi", "role": "customer" },
    "token": "1|abc123def456..."
}
```

**Error Response (401):**

```json
{ "message": "Email atau password salah" }
```

---

### `POST /v1/auth/logout`

Logout — revoke token saat ini.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{ "message": "Logout berhasil" }
```

---

### `GET /v1/auth/profile`

Dapatkan profil user yang sedang login.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "user": {
        "id": 1,
        "name": "Budi",
        "email": "budi@email.com",
        "role": "customer",
        "phone": "08123456789",
        "location_lat": -6.2088,
        "location_lng": 106.8456,
        "avatar": null,
        "is_active": true
    }
}
```

---

### `PATCH /v1/auth/profile`

Update profil user.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

| Field | Type | Required |
|:---|:---|:---|
| `name` | string | ❌ |
| `phone` | string | ❌ |
| `location_lat` | numeric | ❌ |
| `location_lng` | numeric | ❌ |

**Response (200):**

```json
{
    "message": "Profil berhasil diperbarui",
    "user": { "id": 1, "name": "Budi Updated", ... }
}
```

---

### `DELETE /v1/auth/account`

Hapus akun user yang sedang login (soft delete). Semua token akan di-revoke.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{ "message": "Akun berhasil dihapus" }
```

---

### `POST /v1/auth/location`

Update lokasi partner (untuk tracking real-time).

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

| Field | Type | Required |
|:---|:---|:---|
| `location_lat` | numeric | ✅ |
| `location_lng` | numeric | ✅ |

**Response (200):**

```json
{ "message": "Lokasi berhasil diperbarui" }
```

---

## Pelanggan (Customer)

### `GET /v1/orders`

Daftar order milik user yang sedang login.

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `status` | string | ❌ | Filter: `pending`, `dispatching`, `accepted`, `on_the_way`, `arrived`, `in_progress`, `completed`, `cancelled` |
| `page` | int | ❌ | Halaman pagination |

**Response (200):**

```json
{
    "data": [
        {
            "id": 1,
            "code": "ORD-ABC123",
            "service_type": "Servis Motor",
            "status": "accepted",
            "status_label": "Diterima",
            "status_color": "blue",
            "callout_fee": 30000,
            "service_fee": 0,
            "total_amount": 30000,
            "payment_method": "qris",
            "partner": { "id": 1, "workshop_name": "Bengkel Jaya" },
            "vehicle": { "brand": "Honda", "model": "Beat" },
            "created_at": "2026-07-16T10:00:00.000000Z"
        }
    ],
    "links": { ... },
    "meta": { "current_page": 1, "last_page": 5, "per_page": 15 }
}
```

---

### `POST /v1/orders`

Buat order baru (customer).

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `service_type` | string | ✅ | Jenis layanan |
| `vehicle_id` | int | ❌ | ID kendaraan |
| `problem_description` | string | ❌ | Deskripsi keluhan |
| `location_lat` | numeric | ✅ | Latitude lokasi |
| `location_lng` | numeric | ✅ | Longitude lokasi |
| `location_address` | string | ❌ | Alamat lokasi |
| `payment_method` | string | ✅ | `qris`, `ewallet`, atau `bank_transfer` |

**Response (201):**

```json
{
    "message": "Order berhasil dibuat",
    "order": {
        "id": 1,
        "code": "ORD-ABC123",
        "status": "dispatching",
        "callout_fee": 30000,
        ...
    }
}
```

---

### `GET /v1/orders/{order}`

Detail order.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "data": {
        "id": 1,
        "code": "ORD-ABC123",
        "service_type": "Servis Motor",
        "problem_description": "Motor mogok",
        "status": "in_progress",
        "status_label": "Sedang Dikerjakan",
        "status_color": "orange",
        "location_lat": -6.2088,
        "location_lng": 106.8456,
        "location_address": "Jl. Merdeka No. 1",
        "callout_fee": 30000,
        "service_fee": 150000,
        "total_amount": 180000,
        "platform_commission": 15000,
        "partner_earning": 135000,
        "payment_method": "qris",
        "payment_status": "pending",
        "is_sos": false,
        "started_at": "2026-07-16T10:30:00.000000Z",
        "partner": { "id": 1, "workshop_name": "Bengkel Jaya" },
        "vehicle": { "brand": "Honda", "model": "Beat", "license_plate": "AB 1234 CD" },
        "payment": { "status": "pending", "amount": 175000 },
        "review": null,
        "created_at": "2026-07-16T10:00:00.000000Z"
    }
}
```

---

### `PATCH /v1/orders/{order}/cancel`

Batalkan order (hanya status `pending` atau `dispatching`).

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

| Field | Type | Required |
|:---|:---|:---|
| `cancel_reason` | string | ❌ |

**Response (200):**

```json
{
    "message": "Order berhasil dibatalkan",
    "order": { "id": 1, "status": "cancelled", ... }
}
```

---

## Kendaraan (Vehicle)

### `GET /v1/vehicles`

Daftar kendaraan milik user yang sedang login.

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `page` | int | ❌ | Halaman pagination |

**Response (200):**

```json
{
    "data": [
        {
            "id": 1,
            "brand": "Honda",
            "model": "Beat",
            "year": 2023,
            "license_plate": "AB 1234 CD",
            "vehicle_type": "motorcycle",
            "is_default": true,
            "created_at": "2026-07-16T10:00:00.000000Z"
        }
    ]
}
```

---

### `POST /v1/vehicles`

Tambah kendaraan baru.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `brand` | string | ✅ | Merek kendaraan |
| `model` | string | ✅ | Model kendaraan |
| `year` | int | ✅ | Tahun pembuatan |
| `license_plate` | string | ✅ | Nomor plat |
| `vehicle_type` | string | ✅ | `motorcycle`, `car`, `suv`, `truck`, atau `other` |

**Response (201):**

```json
{
    "data": {
        "id": 1,
        "brand": "Honda",
        "model": "Beat",
        "year": 2023,
        "license_plate": "AB 1234 CD",
        "vehicle_type": "motorcycle",
        "is_default": false
    }
}
```

---

### `GET /v1/vehicles/{vehicle}`

Detail kendaraan.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "data": {
        "id": 1,
        "brand": "Honda",
        "model": "Beat",
        "year": 2023,
        "license_plate": "AB 1234 CD",
        "vehicle_type": "motorcycle",
        "is_default": true
    }
}
```

---

### `PUT /v1/vehicles/{vehicle}`

Update kendaraan.

**Headers:** `Authorization: Bearer {token}`

**Request Body:** Sama seperti `POST /v1/vehicles` (semua field opsional).

**Response (200):**

```json
{
    "message": "Kendaraan berhasil diperbarui",
    "data": { ... }
}
```

---

### `DELETE /v1/vehicles/{vehicle}`

Hapus kendaraan.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{ "message": "Kendaraan berhasil dihapus" }
```

---

### `PATCH /v1/vehicles/{vehicle}/default`

Atur kendaraan sebagai default.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{ "message": "Kendaraan berhasil diatur sebagai default" }
```

---

## Mitra/Bengkel (Partner)

### `GET /v1/partners/nearby`

Cari partner terdekat berdasarkan lokasi (public, tidak perlu auth).

**Query Parameters:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `lat` | numeric | ✅ | Latitude |
| `lng` | numeric | ✅ | Longitude |
| `radius` | numeric | ❌ | Radius pencarian dalam km (1-50, default: 30) |

**Response (200):**

```json
{
    "data": [
        {
            "id": 1,
            "workshop_name": "Bengkel Jaya",
            "workshop_address": "Jl. Raya No. 1",
            "workshop_lat": -6.21,
            "workshop_lng": 106.85,
            "description": "Bengkel motor dan mobil",
            "operating_hours": "08:00 - 17:00",
            "avg_rating": 4.5,
            "total_reviews": 28,
            "distance_meters": 1200
        }
    ]
}
```

---

### `GET /v1/partner/profile`

Dapatkan profil partner yang sedang login.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "data": {
        "id": 1,
        "user_id": 2,
        "workshop_name": "Bengkel Jaya",
        "workshop_address": "Jl. Raya No. 1",
        "workshop_lat": -6.21,
        "workshop_lng": 106.85,
        "description": "Bengkel motor dan mobil",
        "operating_hours": "08:00 - 17:00",
        "status": "approved",
        "is_online": true,
        "is_available": true,
        "avg_rating": 4.5,
        "total_reviews": 28,
        "total_orders_completed": 150
    }
}
```

---

### `PATCH /v1/partner/profile`

Update profil partner.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

| Field | Type | Required |
|:---|:---|:---|
| `workshop_name` | string | ❌ |
| `workshop_address` | string | ❌ |
| `workshop_lat` | numeric | ❌ |
| `workshop_lng` | numeric | ❌ |
| `description` | string | ❌ |
| `operating_hours` | string | ❌ |

**Response (200):**

```json
{
    "message": "Profil partner berhasil diperbarui",
    "partner": { "id": 1, "workshop_name": "Bengkel Jaya Baru", ... }
}
```

---

### `POST /v1/partner/toggle-online`

Toggle status online/offline partner.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "message": "Status: Online",
    "is_online": true,
    "is_available": false
}
```

---

### `POST /v1/partner/toggle-availability`

Toggle status ketersediaan partner.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "message": "Tersedia",
    "is_available": true
}
```

---

### `POST /v1/partner/location`

Update lokasi partner untuk real-time tracking.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `lat` | numeric | ✅ | Latitude (-90 s/d 90) |
| `lng` | numeric | ✅ | Longitude (-180 s/d 180) |

**Response (200):**

```json
{
    "message": "Lokasi berhasil diperbarui",
    "location": { "lat": -6.2088, "lng": 106.8456 }
}
```

---

### `GET /v1/partner/orders`

Daftar order untuk partner.

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `status` | string | ❌ | Filter status order |
| `page` | int | ❌ | Halaman pagination |

**Response (200):**

```json
{
    "data": [
        {
            "id": 1,
            "code": "ORD-ABC123",
            "service_type": "Servis Motor",
            "status": "dispatching",
            "callout_fee": 30000,
            "user": { "id": 3, "name": "Budi" },
            "vehicle": { "brand": "Honda", "model": "Beat" },
            "created_at": "2026-07-16T10:00:00.000000Z"
        }
    ]
}
```

---

### `PATCH /v1/partner/orders/{order}/accept`

Partner menerima order (status harus `dispatching`).

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "message": "Order berhasil diterima",
    "order": { "id": 1, "status": "accepted", ... }
}
```

---

### `PATCH /v1/partner/orders/{order}/reject`

Partner menolak order (status harus `dispatching`).

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{ "message": "Order ditolak" }
```

---

### `PATCH /v1/partner/orders/{order}/status`

Partner memperbarui status order.

**Headers:** `Authorization: Bearer {token}`

**Transisi status yang diizinkan:**

| Status Saat Ini | Status Baru yang Diizinkan |
|:---|:---|
| `accepted` | `on_the_way` |
| `on_the_way` | `arrived` |
| `arrived` | `in_progress` |
| `in_progress` | `completed` |

**Request Body:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `status` | string | ✅ | Status baru |
| `service_fee` | numeric | Wajib jika `completed` | Biaya servis (Rp) |

**Response (200):**

```json
{
    "message": "Status berhasil diperbarui",
    "order": { "id": 1, "status": "completed", "service_fee": 150000, ... }
}
```

---

### `GET /v1/partner/stats`

Dapatkan statistik partner (total order, pendapatan, rating, dll).

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "data": {
        "total_orders": 150,
        "completed_orders": 140,
        "total_earnings": 15000000,
        "avg_rating": 4.5,
        "total_reviews": 28,
        "completion_rate": 93.3
    }
}
```

---

### `GET /v1/partner/orders/{order}/track`

Dapatkan info tracking untuk order tertentu (jarak + ETA).

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "order_code": "ORD-ABC123",
    "order_status": "on_the_way",
    "customer_location": {
        "lat": -6.2088,
        "lng": 106.8456,
        "address": "Jl. Merdeka No. 1"
    },
    "partner_location": {
        "lat": -6.21,
        "lng": 106.85
    },
    "distance_km": 1.2,
    "distance_formatted": "1.2 km",
    "eta": "± 5 menit"
}
```

---

## Layanan Partner (Partner Services)

### `GET /v1/partner/services`

Daftar layanan yang ditawarkan partner.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "data": [
        {
            "id": 1,
            "service_name": "Servis Motor Ringan",
            "description": "Servis ringan motor matic/manual",
            "price": 50000,
            "is_active": true,
            "created_at": "2026-07-16T10:00:00.000000Z"
        }
    ]
}
```

---

### `POST /v1/partner/services`

Tambah layanan baru.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `service_name` | string | ✅ | Nama layanan |
| `description` | string | ❌ | Deskripsi layanan |
| `price` | numeric | ✅ | Harga layanan (Rp) |

**Response (201):**

```json
{
    "message": "Layanan berhasil ditambahkan",
    "data": { "id": 1, "service_name": "Servis Motor Ringan", ... }
}
```

---

### `PATCH /v1/partner/services/{service}`

Update layanan partner.

**Headers:** `Authorization: Bearer {token}`

**Request Body:** Sama seperti `POST /v1/partner/services` (semua field opsional).

**Response (200):**

```json
{
    "message": "Layanan berhasil diperbarui",
    "data": { ... }
}
```

---

### `DELETE /v1/partner/services/{service}`

Hapus layanan partner.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{ "message": "Layanan berhasil dihapus" }
```

---

### `PATCH /v1/partner/services/{service}/toggle`

Aktifkan/nonaktifkan layanan partner.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "message": "Status layanan berhasil diubah",
    "data": { "id": 1, "is_active": false, ... }
}
```

---

## Suku Cadang (Spareparts)

### `GET /v1/partner/spareparts`

Daftar suku cadang milik partner.

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `category` | string | ❌ | Filter: `engine`, `electrical`, `body`, `accessory`, `oil`, `tire`, `battery`, `other` |
| `is_active` | bool | ❌ | Filter status aktif |
| `page` | int | ❌ | Halaman pagination |

**Response (200):**

```json
{
    "data": [
        {
            "id": 1,
            "name": "Oli Mesin AHM MPX 1",
            "category": "oil",
            "stock": 10,
            "price": 45000,
            "is_active": true,
            "created_at": "2026-07-16T10:00:00.000000Z"
        }
    ]
}
```

---

### `POST /v1/partner/spareparts`

Tambah suku cadang baru.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `name` | string | ✅ | Nama suku cadang |
| `category` | string | ✅ | `engine`, `electrical`, `body`, `accessory`, `oil`, `tire`, `battery`, `other` |
| `stock` | int | ✅ | Stok tersedia |
| `price` | numeric | ✅ | Harga per unit (Rp) |

**Response (201):**

```json
{
    "message": "Suku cadang berhasil ditambahkan",
    "data": { "id": 1, "name": "Oli Mesin AHM MPX 1", ... }
}
```

---

### `GET /v1/partner/spareparts/{sparepart}`

Detail suku cadang.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "data": { "id": 1, "name": "Oli Mesin AHM MPX 1", "category": "oil", ... }
}
```

---

### `PATCH /v1/partner/spareparts/{sparepart}`

Update suku cadang.

**Headers:** `Authorization: Bearer {token}`

**Request Body:** Sama seperti `POST /v1/partner/spareparts` (semua field opsional).

**Response (200):**

```json
{
    "message": "Suku cadang berhasil diperbarui",
    "data": { ... }
}
```

---

### `DELETE /v1/partner/spareparts/{sparepart}`

Hapus suku cadang.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{ "message": "Suku cadang berhasil dihapus" }
```

---

### `PATCH /v1/partner/spareparts/{sparepart}/toggle`

Aktifkan/nonaktifkan suku cadang.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "message": "Status suku cadang berhasil diubah",
    "data": { "id": 1, "is_active": false, ... }
}
```

---

## Mekanik (Mechanic)

Manajemen mekanik untuk bengkel (multi-mekanik per bengkel).

### `GET /v1/partner/mechanics`

Daftar semua mekanik milik partner yang sedang login.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Andi Mekanik",
            "photo": "https://...",
            "phone": "081234567890",
            "expertise": "both",
            "description": "Ahli mesin motor dan mobil",
            "is_active": true
        }
    ]
}
```

### `POST /v1/partner/mechanics`

Tambah mekanik baru.

**Headers:** `Authorization: Bearer {token}`

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `name` | string | ✅ | Nama mekanik |
| `photo` | string | ❌ | URL foto mekanik |
| `phone` | string | ❌ | Nomor telepon |
| `expertise` | string | ❌ | `motorcycle`, `car`, atau `both` (default: `both`) |
| `description` | string | ❌ | Deskripsi keahlian |

**Response (201):** `{ "message": "Mekanik berhasil ditambahkan", "data": { ... } }`

### `PATCH /v1/partner/mechanics/{mechanic}`

Update data mekanik.

**Response (200):** `{ "message": "Mekanik berhasil diperbarui", "data": { ... } }`

### `DELETE /v1/partner/mechanics/{mechanic}`

Hapus mekanik.

**Response (200):** `{ "message": "Mekanik berhasil dihapus" }`

---

## Gejala / Diagnosis Wizard

Daftar gejala untuk wizard diagnosis berdasarkan kategori kendaraan.

### `GET /v1/symptoms`

Daftar gejala yang tersedia. Bisa difilter berdasarkan `vehicle_category`.

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `vehicle_category` | string | ❌ | `motorcycle` atau `car` |

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "vehicle_category": "motorcycle",
            "label": "Mesin tidak menyala",
            "description": "Mesin completely mati, starter tidak berfungsi",
            "icon": "power-outline",
            "category": "engine",
            "sort_order": 1
        }
    ]
}
```

---

## Partner Status (Granular)

Update status bengkel secara granular.

### `PATCH /v1/partner/status`

Update status partner (granular).

**Headers:** `Authorization: Bearer {token}`

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `partner_status` | string | ✅ | `online`, `resting`, atau `closed` |

**Response (200):**
```json
{
    "message": "Status partner berhasil diperbarui",
    "partner_status": "online",
    "is_online": true,
    "is_available": true
}
```

### `GET /v1/partner/profile-completion`

Cek kelengkapan profil partner.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
    "partner_completion": 85,
    "user_completion": 90,
    "is_approved": true,
    "status": "approved"
}
```

### `GET /v1/auth/profile-completion`

Cek kelengkapan profil user yang sedang login.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
    "user_completion": 75,
    "is_profile_complete": false
}
```

---

## Chat & Komunikasi

### `GET /v1/orders/{order}/chat`

Buka atau dapatkan chat room untuk order.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "data": {
        "id": 1,
        "order_id": 1,
        "is_active": true,
        "messages": [
            {
                "id": 1,
                "sender_id": 2,
                "sender_name": "Pak Joko",
                "message": "Saya sedang dalam perjalanan",
                "attachment_url": null,
                "attachment_type": "none",
                "is_read": true,
                "created_at": "2026-07-16T10:30:00.000000Z"
            }
        ]
    }
}
```

---

### `POST /v1/orders/{order}/chat/send`

Kirim pesan chat.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `message` | string | ✅* | Teks pesan (max 1000 char) |
| `attachment_url` | string | ❌ | URL lampiran |
| `attachment_type` | string | ❌ | `image`, `file`, `location`, atau `none` |

> *`message` wajib jika `attachment_url` tidak diisi.

**Response (201):**

```json
{
    "data": {
        "id": 2,
        "message": "Baik, saya hampir tiba",
        "sender_id": 1,
        "created_at": "2026-07-16T10:35:00.000000Z"
    }
}
```

---

### `GET /v1/orders/{order}/chat/poll`

Polling untuk pesan baru (long polling).

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `last_id` | int | ❌ | ID pesan terakhir yang dimiliki (default: 0) |

**Response (200):**

```json
{
    "data": [
        {
            "id": 5,
            "sender_id": 2,
            "sender_name": "Pak Joko",
            "message": "Sudah sampai",
            "attachment_url": null,
            "created_at": "2026-07-16T10:40:00.000000Z"
        }
    ],
    "unread_count": 2
}
```

---

## Dompet Digital (Wallet)

### `GET /v1/wallet`

Dapatkan saldo wallet.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "data": {
        "id": 1,
        "balance": 2500000,
        "total_earned": 5000000,
        "total_withdrawn": 2500000,
        "formatted_balance": "Rp 2.500.000"
    }
}
```

---

### `GET /v1/wallet/transactions`

Riwayat transaksi wallet.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "data": [
        {
            "id": 1,
            "type": "earning",
            "amount": 135000,
            "description": "Pendapatan order ORD-ABC123",
            "order_id": 1,
            "created_at": "2026-07-16T12:00:00.000000Z"
        }
    ]
}
```

---

### `POST /v1/wallet/withdraw`

Ajukan penarikan dana.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `amount` | numeric | ✅ | Jumlah penarikan (min 10.000, max 50.000.000) |
| `bank_name` | string | ✅ | Nama bank |
| `bank_account_number` | string | ✅ | Nomor rekening |
| `bank_account_name` | string | ✅ | Nama pemegang rekening |

**Response (201):**

```json
{
    "message": "Pengajuan penarikan berhasil",
    "withdraw": {
        "id": 1,
        "amount": 500000,
        "bank_name": "BCA",
        "bank_account_number": "1234567890",
        "status": "pending",
        "created_at": "2026-07-16T12:00:00.000000Z"
    }
}
```

---

### `GET /v1/wallet/withdraw/history`

Riwayat pengajuan penarikan.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "data": [
        {
            "id": 1,
            "amount": 500000,
            "bank_name": "BCA",
            "bank_account_number": "1234567890",
            "status": "approved",
            "created_at": "2026-07-16T12:00:00.000000Z"
        }
    ]
}
```

---

## Ulasan (Reviews)

### `GET /v1/reviews`

Daftar ulasan milik user yang sedang login (customer).

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "data": [
        {
            "id": 1,
            "order_id": 1,
            "rating": 5,
            "comment": "Servis sangat memuaskan!",
            "partner_reply": "Terima kasih atas reviewnya!",
            "created_at": "2026-07-16T12:00:00.000000Z"
        }
    ]
}
```

---

### `POST /v1/reviews`

Buat ulasan untuk order yang sudah selesai.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `order_id` | int | ✅ | ID order yang sudah selesai |
| `rating` | int | ✅ | Rating 1-5 |
| `comment` | string | ❌ | Komentar |

**Response (201):**

```json
{
    "message": "Review berhasil dibuat",
    "data": { "id": 1, "rating": 5, ... }
}
```

---

### `GET /v1/reviews/stats`

Statistik ulasan (global).

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "data": {
        "total_reviews": 150,
        "avg_rating": 4.5,
        "rating_distribution": { "5": 80, "4": 40, "3": 20, "2": 7, "1": 3 }
    }
}
```

---

### `GET /v1/partners/{partner}/reviews`

Daftar ulasan untuk partner tertentu (public, tidak perlu auth).

**Query Parameters:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `limit` | int | ❌ | Jumlah data (default: 20) |

**Response (200):**

```json
{
    "data": [
        {
            "id": 1,
            "user_name": "Budi",
            "rating": 5,
            "comment": "Servis sangat memuaskan!",
            "partner_reply": "Terima kasih!",
            "created_at": "2026-07-16T12:00:00.000000Z"
        }
    ]
}
```

---

### `GET /v1/partner/reviews`

Daftar ulasan untuk partner yang sedang login.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "data": [
        {
            "id": 1,
            "user_name": "Budi",
            "rating": 5,
            "comment": "Servis sangat memuaskan!",
            "partner_reply": null,
            "created_at": "2026-07-16T12:00:00.000000Z"
        }
    ]
}
```

---

### `POST /v1/reviews/{review}/reply`

Balas ulasan customer (partner only).

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `reply` | string | ✅ | Balasan partner |

**Response (200):**

```json
{
    "message": "Balasan berhasil dikirim",
    "data": { "id": 1, "partner_reply": "Terima kasih atas reviewnya!", ... }
}
```

---

## Notifikasi

### `GET /v1/notifications`

Dapatkan daftar notifikasi (max 50 terbaru).

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "unread_count": 5,
    "data": [
        {
            "id": 1,
            "type": "order_status",
            "title": "Order Diterima",
            "message": "Order ORD-ABC123 telah diterima oleh Bengkel Jaya",
            "is_read": false,
            "created_at": "2026-07-16T10:05:00.000000Z"
        }
    ]
}
```

---

### `POST /v1/notifications/read-all`

Tandai semua notifikasi sebagai sudah dibaca.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{ "success": true }
```

---

### `POST /v1/fcm-token`

Simpan FCM token untuk push notification.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

| Field | Type | Required |
|:---|:---|:---|
| `fcm_token` | string | ✅ |

**Response (200):**

```json
{ "success": true }
```

---

## Asuransi (Insurance)

### `GET /v1/insurance/partners`

Daftar insurance partner yang aktif (public, tidak perlu auth).

**Response (200):**

```json
{
    "data": [
        {
            "id": 1,
            "name": "Asuransi MobilKu",
            "code": "MOBILKU"
        }
    ]
}
```

---

### `POST /v1/orders/{order}/insurance-claim`

Buat klaim asuransi untuk order.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `insurance_partner_id` | int | ✅ | ID insurance partner |
| `claimed_amount` | numeric | ✅ | Jumlah klaim |
| `notes` | string | ❌ | Catatan tambahan |

**Response (201):**

```json
{
    "success": true,
    "message": "Klaim asuransi berhasil dibuat.",
    "data": {
        "id": 1,
        "claim_number": "CLM-ABC123XYZ",
        "status": "pending",
        "claimed_amount": 500000,
        "insurance_partner_id": 1,
        "order_id": 1
    }
}
```

---

### `GET /v1/insurance-claims/{claim}/status`

Cek status klaim asuransi.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "data": {
        "id": 1,
        "claim_number": "CLM-ABC123XYZ",
        "status": "approved",
        "claimed_amount": 500000,
        "approved_amount": 450000,
        "processed_at": "2026-07-16T14:00:00.000000Z",
        "insurance_partner": {
            "id": 1,
            "name": "Asuransi MobilKu",
            "code": "MOBILKU"
        }
    }
}
```

---

## Iklan (Advertisement Tracking)

### `POST /v1/ads/{advertisement}/impression`

Catat impression iklan (public, tidak perlu auth).

**Response (200):**

```json
{ "success": true }
```

---

### `POST /v1/ads/{advertisement}/click`

Catat click iklan (public, tidak perlu auth).

**Response (200):**

```json
{
    "success": true,
    "target_url": "https://example.com/product"
}
```

---

## Payment Gateway (Midtrans)

### `POST /v1/orders/{order}/pay`

Buat payment token (Snap Token) untuk order baru. Dipanggil oleh customer mobile app setelah membuat order untuk mendapatkan Snap Token yang digunakan membuka halaman pembayaran Midtrans.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "message": "Payment token berhasil dibuat",
    "payment": {
        "id": 1,
        "amount": 30000,
        "status": "pending"
    },
    "snap_token": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "redirect_url": "https://app.sandbox.midtrans.com/snap/v2/Redirect/..."
}
```

**Keterangan:**
- `amount` = Callout Fee (Rp30.000)
- `snap_token` = Token untuk Midtrans Snap SDK di mobile app
- `redirect_url` = URL pembayaran Midtrans (alternatif jika tidak pakai Snap SDK)

---

### `POST /v1/partner/orders/{order}/service-fee-pay`

Buat payment token untuk service fee (biaya servis). Dipanggil oleh partner mobile app saat order selesai dan partner sudah input biaya servis.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `service_fee` | numeric | Ya | Biaya servis/sparepart |

**Response (200):**

```json
{
    "message": "Service fee payment token berhasil dibuat",
    "payment": {
        "id": 2,
        "amount": 175000,
        "status": "pending"
    },
    "snap_token": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "redirect_url": "https://app.sandbox.midtrans.com/snap/v2/Redirect/...",
    "fee_breakdown": {
        "callout_fee": 30000,
        "service_fee": 145000,
        "total_amount": 175000,
        "commission_percent": 5,
        "platform_commission": 7250,
        "partner_earning": 137750
    }
}
```

---

### `GET /v1/ads`

Daftar iklan aktif (mobile, public).

**Query Parameters:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `type` | string | ❌ | Filter: `banner`, `popup`, `interstitial` |

**Response (200):**

```json
{
    "data": [
        {
            "id": 1,
            "title": "Promo Ganti Oli",
            "description": "Diskon 20% untuk ganti oli",
            "image_url": "https://...",
            "target_url": "https://example.com",
            "type": "banner",
            "impressions": 150,
            "clicks": 25
        }
    ]
}
```

---

### `GET /v1/ads/{advertisement}`

Detail iklan (public).

**Response (200):**

```json
{
    "data": {
        "id": 1,
        "title": "Promo Ganti Oli",
        "description": "Diskon 20% untuk ganti oli",
        "image_url": "https://...",
        "target_url": "https://example.com",
        "type": "banner"
    }
}
```

---

### `POST /v1/payment/webhook`

Webhook callback dari Midtrans. Dipanggil oleh Midtrans saat status transaksi berubah. Verifikasi signature dilakukan otomatis.

**Request Body (Midtrans Notification):**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `order_id` | string | Ya | Kode order MontirGo |
| `transaction_status` | string | Ya | Status Midtrans: `capture`, `settlement`, `pending`, `deny`, `expire`, `cancel`, `refund` |
| `fraud_status` | string | Tidak | Status fraud: `accept`, `challenge`, `deny` |
| `status_code` | string | Ya | Kode status HTTP |
| `gross_amount` | string | Ya | Total pembayaran |
| `signature_key` | string | Ya | Signature untuk verifikasi |
| `transaction_id` | string | Tidak | ID transaksi Midtrans |
| `payment_type` | string | Tidak | Tipe pembayaran |

**Response (200):**

```json
{
    "message": "Payment status updated",
    "order_code": "MTG-ABC123",
    "status": "paid",
    "midtrans_status": "settlement"
}
```

**Mapping Status Midtrans ke Internal:**
- `capture` / `settlement` → `paid`
- `pending` → `pending`
- `deny` / `expire` / `cancel` → `failed`
- `refund` / `partial_refund` → `refunded`
- `fraud_status: deny` → `failed` (selalu)

---

### `GET /v1/payment/status/{orderCode}`

Cek status pembayaran untuk order tertentu.

**Response (200):**

```json
{
    "order_code": "MTG-ABC123",
    "payment_status": "paid",
    "amount": 175000,
    "callout_fee": 30000,
    "service_fee": 145000,
    "method": "qris",
    "paid_at": "2026-07-17T12:00:00.000000Z",
    "transaction_id": "TXN-MIDTRANS-123"
}
```

---

## Admin Dashboard

> Semua endpoint admin memerlukan role `admin`.

### `GET /v1/admin/dashboard/stats`

Statistik dashboard admin.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "data": {
        "total_users": 500,
        "total_partners": 45,
        "total_orders": 1200,
        "revenue_this_month": 15000000,
        "revenue_last_month": 12000000,
        "avg_rating": 4.5,
        "completion_rate": 93.3
    }
}
```

---

### `GET /v1/admin/dashboard/revenue-chart`

Data grafik pendapatan 12 bulan terakhir.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "data": [
        { "month": "Jan 2026", "revenue": 8000000 },
        { "month": "Feb 2026", "revenue": 9500000 },
        ...
    ]
}
```

---

### `GET /v1/admin/dashboard/order-status`

Distribusi status order.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**

```json
{
    "data": [
        { "status": "completed", "count": 800, "label": "Selesai" },
        { "status": "cancelled", "count": 50, "label": "Dibatalkan" },
        ...
    ]
}
```

---

### `GET /v1/admin/dashboard/top-partners`

Top partner berdasarkan jumlah order selesai.

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `limit` | int | ❌ | Jumlah partner (default: 5) |

**Response (200):**

```json
{
    "data": [
        {
            "id": 1,
            "workshop_name": "Bengkel Jaya",
            "total_completed": 50,
            "avg_rating": 4.8,
            "total_earnings": 5000000
        }
    ]
}
```

### `PATCH /v1/partner/profile` (dengan file upload)

Update profil partner termasuk upload dokumen (multipart/form-data).

**Headers:** `Authorization: Bearer {token}`, `Content-Type: multipart/form-data`

**Request Body (form-data):**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `workshop_name` | string | ❌ | Nama bengkel |
| `workshop_address` | string | ❌ | Alamat bengkel |
| `workshop_lat` | numeric | ❌ | Latitude |
| `workshop_lng` | numeric | ❌ | Longitude |
| `workshop_category` | string | ❌ | `motorcycle`, `car`, `both` |
| `service_radius` | integer | ❌ | Radius layanan (km, 1-100) |
| `owner_name` | string | ❌ | Nama pemilik |
| `owner_phone` | string | ❌ | Telepon pemilik |
| `description` | string | ❌ | Deskripsi bengkel |
| `operating_hours` | string | ❌ | Jam operasional |
| `ktp_number` | string | ❌ | Nomor KTP |
| `ktp_photo` | file | ❌ | Foto KTP (jpg/png/webp, max 5MB) |
| `selfie_with_ktp` | file | ❌ | Selfie dengan KTP |
| `workshop_photo` | file | ❌ | Foto bengkel |
| `front_workshop_photo` | file | ❌ | Foto depan bengkel |
| `inside_workshop_photo` | file | ❌ | Foto dalam bengkel |
| `business_license` | file | ❌ | Foto izin usaha |
| `bank_name` | string | ❌ | Nama bank |
| `bank_account_number` | string | ❌ | Nomor rekening |
| `bank_account_name` | string | ❌ | Nama rekening |
| `npwp` | string | ❌ | Nomor NPWP |
| `nib` | string | ❌ | Nomor NIB |

**Response (200):**
```json
{
    "message": "Profil partner berhasil diperbarui",
    "partner": { ... }
}
```

---

## Admin Partner Management

> Semua endpoint admin memerlukan role `admin`.

### `GET /v1/admin/partners`

Daftar semua partner (admin).

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**

| Param | Type | Required | Description |
|:---|:---|:---|:---|
| `search` | string | ❌ | Cari nama bengkel/owner |
| `status` | string | ❌ | Filter: `draft`, `pending`, `approved`, `suspended`, `rejected` |
| `workshop_category` | string | ❌ | Filter: `motorcycle`, `car`, `both` |
| `per_page` | int | ❌ | Items per page (default 15) |

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "workshop_name": "Bengkel Jaya",
            "workshop_category": "motorcycle",
            "status": "pending",
            ...
        }
    ],
    "links": { ... },
    "meta": { ... }
}
```

### `GET /v1/admin/partners/{partner}`

Detail partner (admin).

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
    "data": {
        "id": 1,
        "workshop_name": "Bengkel Jaya",
        "workshop_category": "motorcycle",
        "service_radius": 30,
        "status": "pending",
        "partner_status": "offline",
        "profile_completion": 78,
        ...
    }
}
```

### `PATCH /v1/admin/partners/{partner}/approve`

Approve partner + kirim notifikasi ke partner.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
    "message": "Partner Bengkel Jaya approved successfully."
}
```

### `PATCH /v1/admin/partners/{partner}/reject`

Reject partner + kirim notifikasi dengan alasan.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `rejection_reason` | string | ✅ | Alasan penolakan (max 500 karakter) |

**Response (200):**
```json
{
    "message": "Partner Bengkel Jaya rejected."
}
```

### `PATCH /v1/admin/partners/{partner}/suspend`

Suspend partner + kirim notifikasi.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
    "message": "Partner Bengkel Jaya suspended."
}
```

---

## SOS / Darurat

### `POST /v1/sos`

Buat order SOS darurat (customer). Flow disederhanakan tanpa service type manual.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `sos_type` | string | ✅ | `flat_tire`, `dead_battery`, `out_of_fuel`, `locked_keys`, `overheat` |
| `vehicle_id` | int | ❌ | ID kendaraan |
| `location_lat` | numeric | ✅ | Latitude |
| `location_lng` | numeric | ✅ | Longitude |
| `location_address` | string | ❌ | Alamat |

**Response (201):**

```json
{
    "message": "SOS Ban Kembung berhasil dikirim! Sedang mencari mekanik terdekat...",
    "order": {
        "id": 1,
        "code": "ORD-SOS123",
        "service_type": "SOS Darurat - Ban Kembung",
        "status": "pending",
        "is_sos": true,
        "sos_type": "flat_tire",
        "callout_fee": 30000,
        "total_amount": 0,
        ...
    },
    "sos_categories": {
        "flat_tire": { "label": "Ban Kembung", "icon_name": "ellipse-outline" },
        "dead_battery": { "label": "Aki Mati", "icon_name": "battery-dead-outline" },
        "out_of_fuel": { "label": "Kehabisan Bensin", "icon_name": "water-outline" },
        "locked_keys": { "label": "Kunci Tertinggal", "icon_name": "key-outline" },
        "overheat": { "label": "Mesin Overheat", "icon_name": "thermometer-outline" }
    }
}
```

---

## Response Format

### Sukses

```json
{
    "message": "Deskripsi sukses",
    "data": { ... }
}
```

### Error

```json
{
    "message": "Deskripsi error"
}
```

### Validation Error (422)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

### HTTP Status Codes

| Code | Deskripsi |
|:---|:---|
| `200` | Sukses |
| `201` | Created (berhasil membuat resource baru) |
| `401` | Unauthorized (token tidak valid/tidak ada) |
| `403` | Forbidden (akses ditolak) |
| `404` | Not Found (resource tidak ditemukan) |
| `422` | Unprocessable Entity (validasi gagal) |
| `500` | Internal Server Error |

---

## Autentikasi

Semua endpoint yang dilindungi memerlukan header:

```
Authorization: Bearer {your_token}
Accept: application/json
Content-Type: application/json
```

Token didapatkan dari response `register` atau `login`. Token sebelumnya akan di-revoke otomatis saat login baru.

---

## Rate Limiting

API menerapkan rate limiting per IP. Jika melebihi batas, response `429 Too Many Requests` akan dikembalikan.

---

## Webhook

### `POST /v1/insurance/webhook/claim-update`

Webhook callback dari insurance partner untuk update status klaim. Dikirim dengan API key, rate limited.

**Request Body:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `claim_number` | string | ✅ | Nomor klaim |
| `status` | string | ✅ | Status baru: `approved`, `rejected`, `processing` |
| `approved_amount` | numeric | ❌ | Jumlah yang disetujui (jika approved) |

**Response (200):**

```json
{ "message": "Claim status updated" }
```

---

> **Last Updated:** 18 Juli 2026
> **Total Endpoints:** 45+
> **Auth Provider:** Laravel Sanctum
