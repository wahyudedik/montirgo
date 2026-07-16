# MontirGo — REST API Documentation

> **Base URL:** `https://montirgo.test/api`  
> **Version:** v1  
> **Auth:** Laravel Sanctum (Bearer Token)  
> **Content-Type:** `application/json`

---

## 📑 Daftar Isi

- [Autentikasi](#autentikasi)
- [Pelanggan (Customer)](#pelanggan-customer)
- [Mitra/Bengkel (Partner)](#mitrabengkel-partner)
- [Chat & Komunikasi](#chat--komunikasi)
- [Dompet Digital (Wallet)](#dompet-digital-wallet)
- [Notifikasi](#notifikasi)
- [Asuransi (Insurance)](#asuransi-insurance)
- [Iklan (Advertisement Tracking)](#iklan-advertisement-tracking)
- [Payment Gateway](#payment-gateway)
- [SOS / Darurat](#sos--darurat)
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
            "callout_fee": 25000,
            "service_fee": 0,
            "total_amount": 25000,
            "payment_method": "cash",
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
| `payment_method` | string | ✅ | `cash`, `wallet`, `qris`, atau `card` |

**Response (201):**

```json
{
    "message": "Order berhasil dibuat",
    "order": {
        "id": 1,
        "code": "ORD-ABC123",
        "status": "dispatching",
        "callout_fee": 25000,
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
        "callout_fee": 25000,
        "service_fee": 150000,
        "total_amount": 175000,
        "platform_commission": 15000,
        "partner_earning": 135000,
        "payment_method": "cash",
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
            "callout_fee": 25000,
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

## Payment Gateway

### `POST /v1/payment/webhook`

Webhook callback dari payment gateway (Midtrans/Xendit). Dipanggil oleh sistem payment provider.

**Request Body:**

| Field | Type | Required | Description |
|:---|:---|:---|:---|
| `order_code` | string | ✅ | Kode order |
| `status` | string | ✅ | `paid`, `failed`, atau `refunded` |
| `transaction_id` | string | ❌ | ID transaksi dari provider |
| `reference_number` | string | ❌ | Nomor referensi |
| `amount` | numeric | ✅ | Jumlah pembayaran |

**Response (200):**

```json
{
    "message": "Payment status updated",
    "order_code": "ORD-ABC123",
    "status": "paid"
}
```

---

### `GET /v1/payment/status/{orderCode}`

Cek status pembayaran untuk order tertentu.

**Response (200):**

```json
{
    "order_code": "ORD-ABC123",
    "payment_status": "paid",
    "amount": 175000,
    "method": "qris",
    "paid_at": "2026-07-16T12:00:00.000000Z",
    "transaction_id": "TXN-MIDTRANS-123"
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
    "message": "🚨 SOS Ban Kembung berhasil dikirim! Sedang mencari mekanik terdekat...",
    "order": {
        "id": 1,
        "code": "ORD-SOS123",
        "service_type": "SOS Darurat - Ban Kembung",
        "status": "pending",
        "is_sos": true,
        "sos_type": "flat_tire",
        "callout_fee": 0,
        "total_amount": 0,
        ...
    },
    "sos_categories": {
        "flat_tire": { "label": "Ban Kembung", "icon": "🛞" },
        "dead_battery": { "label": "Aki Mati", "icon": "🔋" },
        "out_of_fuel": { "label": "Kehabisan Bensin", "icon": "⛽" },
        "locked_keys": { "label": "Kunci Tertinggal", "icon": "🔑" },
        "overheat": { "label": "Mesin Overheat", "icon": "🌡️" }
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

> **Last Updated:** 16 Juli 2026  
> **Total Endpoints:** 30+  
> **Auth Provider:** Laravel Sanctum
