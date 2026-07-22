# PRD — Apotek Almaira v1.0 (Full Stack)

**Perusahaan:** PT Nur Madani Farma  
**Unit Operasional:** Apotek Almaira Banjarbaru  
**Versi dokumen:** 1.0 — Juli 2026  
**Stack:** Laravel 13 · Livewire 3 · Alpine.js · Tailwind CSS · Vite · MySQL/SQLite

---

## 1. Ringkasan Eksekutif

Aplikasi manajemen apotek terintegrasi yang mencakup operasional internal (kasir, inventori, keuangan) dan kanal digital B2B (e-catalog mitra). Sistem dirancang untuk PT Nur Madani Farma dengan branding resmi, alur kredit/invoice tempo, dan portal mitra mandiri.

---

## 2. Tujuan Produk

| # | Tujuan |
|---|--------|
| 1 | Digitalisasi penjualan retail (POS) dan back-office apotek |
| 2 | E-catalog publik + portal mitra B2B (RS, Klinik, Apotek, UMKM, Instansi) |
| 3 | Manajemen piutang/kredit invoice tempo (POS + PO mitra) |
| 4 | Laporan keuangan & operasional terpadu dengan branding PT |
| 5 | Keamanan role-based access & audit log |

---

## 3. Persona & Role

| Role | Akses utama |
|------|-------------|
| **Super Admin / IT** | Semua modul + user + backup |
| **Admin Keuangan** | Master data, mitra, PO, kredit, laporan, gaji |
| **Kasir** | POS, penjualan, resep, inventori baca/tulis terbatas |
| **Mitra (B2B)** | E-catalog, keranjang, PO, bukti transfer — **tanpa** panel staff |

---

## 4. Modul & Fitur

### 4.1 Autentikasi & Portal

- **Login staff** — UI glassmorphism gelap, logo Apotek Almaira
- **Daftar mitra** — UI konsisten login (layout `auth-portal`), logo **PT Nur Madani Farma**
- **Login mitra** — portal terpisah, approval admin wajib
- Rate limit, lockout brute-force, session timeout

### 4.2 E-Catalog (Publik)

- Browse produk tanpa login (harga eceran)
- Mitra login: harga sesuai mode (eceran/grosir/auto)
- Keranjang session → checkout PO
- Logo PT + Apotek di banner

### 4.3 Mitra B2B

| Tahap | Status |
|-------|--------|
| Pendaftaran mandiri | ✅ |
| Approval admin | ✅ |
| Keranjang + PO | ✅ |
| Bayar: Transfer / COD / Invoice tempo | ✅ |
| Potong stok saat fulfill | ✅ |
| Notifikasi admin (WA/email simulasi) | ✅ |

**Default komersial per tipe mitra:**

- RS/Klinik/Apotek → grosir + invoice tempo
- UMKM → eceran, tanpa invoice
- Instansi → eceran + invoice tempo

### 4.4 POS & Penjualan Retail

- Tunai, QRIS, Transfer, **Invoice tempo 30 hari** (CRM pelanggan)
- PPN, diskon bertingkat, poin CRM
- Cetak struk thermal & invoice

### 4.5 Kredit & Invoice (Piutang)

**Alur bisnis:**

```
Transaksi Invoice Tempo (belum lunas) → Kredit / Piutang
         ↓ pelunasan admin
Invoice Lunas → Laporan Invoice Lunas (otomatis)
```

**Sumber data:**

| Sumber | Belum lunas | Sudah lunas |
|--------|-------------|-------------|
| POS (Sale) | Manajemen Invoice + Kredit | Laporan Invoice Lunas |
| PO Mitra (PartnerOrder) | Kredit & Piutang | Laporan Invoice Lunas |

**Halaman:** `/credits` — tab Kredit Belum Lunas | Invoice Lunas  
**Laporan:** `kredit_piutang`, `invoice_lunas` di Portal Laporan

### 4.6 Rekening ATM Perusahaan

Disimpan di **Pengaturan → Info Apotek:**

- Bank (BCA/BRI/dll)
- No. Rekening / ATM
- Atas Nama: PT Nur Madani Farma

Ditampilkan di checkout mitra (transfer) & detail PO.

### 4.7 Inventori & Pembelian

- Master produk, kategori, supplier
- Barang masuk, keluar, opname
- Import Excel template 18 kolom

### 4.8 Laporan

Penjualan, inventori, keuangan (termasuk kredit & invoice lunas), gaji, log aktivitas — export HTML/PDF/Excel.

---

## 5. Arsitektur Teknis

```
┌─────────────────────────────────────────────────────────┐
│  Browser (Staff SPA / Mitra Portal / E-Catalog Public)  │
└────────────────────────┬────────────────────────────────┘
                         │ HTTPS
┌────────────────────────▼────────────────────────────────┐
│  Laravel 13 — Routes · Middleware · Controllers         │
│  Livewire 3 (tables, catalog grid)                      │
│  Services: Cart, Pricing, Notification, ActivityLog     │
└────────────────────────┬────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────┐
│  MySQL / SQLite — users, products, sales, partners,       │
│  partner_orders, settings, activity_logs                  │
└─────────────────────────────────────────────────────────┘
```

### Model utama mitra

- `partners` — data mitra + komersial
- `partner_orders` + `partner_order_items` — PO e-catalog
- Settlement: `settled_at`, `settled_by`, `settlement_method`

### Middleware

- `auth`, `session.timeout`, `role:...`

---

## 6. UI/UX & Branding

| Asset | Path | Penggunaan |
|-------|------|------------|
| Logo PT NMF | `public/assets/images/logo-ptnmf.png` | Portal mitra, e-catalog, laporan PT |
| Watermark PT | `public/assets/images/watermark-ptnmf.png` | PDF/laporan entity=pt |
| Logo Apotek | `public/assets/images/logo-apotek.png` | E-catalog, struk |

**Halaman daftar mitra:** layout `auth-portal` — dark gradient, glass card, logo PT, form multi-section.

---

## 7. API & Routes (ringkas)

| Grup | Prefix | Contoh |
|------|--------|--------|
| Publik | `/catalog` | index, show |
| Mitra guest | `/mitra/daftar`, `/mitra/login` | register, login |
| Mitra auth | `/mitra/cart`, `/mitra/orders` | cart, checkout, PO |
| Staff | `/`, `/pos`, `/sales` | dashboard, POS |
| Admin | `/partners`, `/partner-orders`, `/credits` | mitra, PO, kredit |
| Laporan | `/reports` | generate |

---

## 8. Keamanan

- Password bcrypt, role middleware
- CSRF pada semua form
- Throttle login mitra & staff
- Activity log untuk aksi sensitif
- Mitra tidak akses sidebar staff (redirect ke portal)

---

## 9. Roadmap (Fase berikutnya)

| Prioritas | Fitur |
|-----------|-------|
| P1 | Notifikasi WA/email produksi (webhook) |
| P1 | Cetak invoice formal PO mitra (PDF) |
| P2 | Integrasi payment gateway |
| P2 | Dashboard analitik mitra |
| P3 | Multi-cabang |

---

## 10. Kriteria Penerimaan (Acceptance)

- [x] Daftar mitra tampilan profesional sama gaya login
- [x] Logo PT NMF sesuai file resmi user
- [x] PO invoice tempo masuk kredit; lunas masuk laporan invoice
- [x] Rekening ATM PT di pengaturan & checkout
- [x] PRD full stack terdokumentasi

---

*Dokumen ini menjadi acuan pengembangan lanjutan Apotek Almaira v1.0.*
