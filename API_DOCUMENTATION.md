# 🚀 Dokumentasi REST API Sistem Akuntansi Enterprise (v1.0)

Selamat datang di Dokumentasi Resmi REST API **Sistem Akuntansi Enterprise**. API ini dilengkapi dengan sistem keamanan **Laravel Sanctum Bearer Token** dan **Tenant Guard** untuk menjamin data keuangan tiap perusahaan 100% terisolasi dan aman.

---

## 📌 1. Gambaran Umum & Quick Links

- **Base URL (Localhost / XAMPP):** `http://localhost/akutansi/public/api/v1` atau `http://127.0.0.1:8000/api/v1`
- **Base URL (Production Hosting):** `https://domain-anda.com/api/v1`
- **Interactive Web API Docs & Sandbox:** `/docs/api` (atau klik menu **API Docs** di sidebar)
- **Postman Collection:** [`public/akuntansi_api_postman_collection.json`](file:///public/akuntansi_api_postman_collection.json)
- **OpenAPI 3.0 Schema:** [`public/openapi.json`](file:///public/openapi.json)

---

## 🛡️ 2. Alur Autentikasi & Keamanan Tenant (Sanctum)

### Langkah 1: Dapatkan Bearer Token & Company ID
Panggil endpoint `POST /api/v1/auth/login`:
```bash
curl -X POST "http://127.0.0.1:8000/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@dapurgemoy.com",
    "password": "password123"
  }'
```

**Response Sukses:**
```json
{
  "status": "success",
  "token": "1|eyJhbGciOi...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "Admin Utama",
    "email": "admin@dapurgemoy.com"
  },
  "active_company": {
    "id": 1,
    "name": "Dapur Gemoy",
    "city": "Bekasi Kota"
  },
  "accessible_companies": [
    { "id": 1, "name": "Dapur Gemoy", "role": "admin" }
  ]
}
```

### Langkah 2: Gunakan Bearer Token pada Request Selanjutnya
Sertakan header `Authorization: Bearer <token>` pada setiap pemanggilan API:
```http
Authorization: Bearer 1|eyJhbGciOi...
Accept: application/json
```

### 🔒 Bagaimana Keamanan Tenant Guard Bekerja?
1. **Otomatis Tanpa Kirim ID:** Jika user hanya mengelola 1 perusahaan, sistem otomatis memproses data ke `user->default_company_id`. Klien **tidak perlu mengirim `X-Company-Id`**.
2. **Multi-Cabang (Kirim `X-Company-Id: 2`):** Jika akun memiliki banyak cabang dan ingin mengarahkan ke cabang ID 2, sertakan header `X-Company-Id: 2`.
3. **Proteksi Akses Ilegal (403 Forbidden):** Jika hacker mencoba mengganti ID ke perusahaan milik orang lain yang bukan miliknya, server akan langsung memblokir dengan status **`403 Forbidden`** (*"Akses Ditolak: Anda tidak memiliki wewenang pada entitas Perusahaan ini"*).

---

## 📚 3. Ringkasan Endpoint

### 🔐 Autentikasi & Token
| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `POST` | `/auth/login` | Login email & password ➡️ Dapatkan Bearer Token & Company ID aktif. |
| `POST` | `/auth/register` | Mendaftarkan akun pemilik baru + provisioning otomatis perusahaan. |
| `GET` | `/auth/me` | Cek profil pengguna dan daftar seluruh Company ID yang bisa diakses. |
| `POST` | `/auth/logout` | Mencabut (revoke) Bearer Token aktif. |

### 📦 Master Data
| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `GET` | `/accounts` | Daftar Chart of Accounts (COA) aktif. |
| `POST` | `/accounts/initial-balances` | Update Saldo Awal Akun COA. |
| `GET` | `/contacts` | Master Kontak (Customer, Vendor, Karyawan). |
| `POST` | `/contacts` | Tambah Kontak Baru. |
| `GET` | `/departments` | Master Departemen / Divisi. |
| `GET` | `/projects` | Master Proyek / Cost Center. |
| `GET` | `/payment-methods` | Master Metode Pembayaran. |
| `GET` | `/master-bundle` | **All-in-One Master Data** untuk Caching Mobile App / POS. |

### 💳 Transaksi & Jurnal
| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `GET` | `/transactions` | Riwayat Transaksi (Filterable & Paginated). |
| `POST` | `/transactions` | **Catat Transaksi Cepat (1:1) atau Majemuk Multi-Line** (Auto-Resolve Kontak & Dept). |
| `GET` | `/transactions/{id}` | Detail Transaksi & Jurnal Akuntansi. |

### 📊 Laporan Keuangan
| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `GET/POST` | `/dashboard/summary` | Dashboard Executive Summary (Pendapatan, Beban, Laba Bersih, Kas). |
| `GET/POST` | `/reports/profit-loss` | Laporan Laba Rugi (P&L). |
| `GET/POST` | `/reports/balance-sheet` | Laporan Neraca Keuangan. |
| `GET/POST` | `/reports/trial-balance` | Laporan Neraca Saldo. |
| `GET/POST` | `/reports/general-ledger` | Laporan Buku Besar. |
| `GET/POST` | `/reports/cash-flow` | Laporan Arus Kas. |
| `GET/POST` | `/reports/journal` | Laporan Jurnal Umum (Audit Trail). |

### 🤖 AI Journaling
| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `POST` | `/ai/parse` | Gemini AI Text to Draft Journal Parser. |

---

## 💻 4. Contoh Kode Pemanggilan API (dengan Token)

### cURL
```bash
curl -X POST "http://127.0.0.1:8000/api/v1/transactions" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|xyz..." \
  -d '{
    "date": "2026-09-24",
    "type": "income",
    "debit_account_code": "1111",
    "credit_account_code": "4101",
    "amount": 1500000,
    "notes": "Penjualan Katering POS #01"
  }'
```

### JavaScript / Fetch
```javascript
const response = await fetch('http://127.0.0.1:8000/api/v1/transactions', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': 'Bearer 1|xyz...'
  },
  body: JSON.stringify({
    date: '2026-09-24',
    type: 'income',
    debit_account_code: '1111',
    credit_account_code: '4101',
    amount: 1500000,
    notes: 'Penjualan Katering POS #01'
  })
});
const data = await response.json();
console.log(data);
```
