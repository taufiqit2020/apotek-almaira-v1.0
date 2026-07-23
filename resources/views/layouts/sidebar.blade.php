{{-- ═══════════════════════════════════════════════════════
     SIDEBAR — Apotek Almaira
     Menu dikelompokkan sesuai fungsi & role
     ══════════════════════════════════════════════════════ --}}
<aside
    class="sidebar no-print transition-all duration-300"
    :class="{ 'collapsed': collapsed, 'is-mobile-open': !collapsed }"
>
    {{-- Brand — satu blok; mode ciut diatur CSS --}}
    <div class="sidebar-brand">
        <button type="button"
                @click="if (collapsed) toggle()"
                class="sidebar-brand-logo-btn sidebar-show-collapsed"
                title="Perluas menu">
            <img src="{{ asset('assets/images/logodashboard.jpeg') }}" alt="Logo" class="sidebar-brand-img">
        </button>
        <div class="sidebar-brand-logo sidebar-hide-collapsed">
            <img src="{{ asset('assets/images/logodashboard.jpeg') }}" alt="Logo" class="sidebar-brand-img">
        </div>
        <div class="sidebar-brand-text sidebar-hide-collapsed">
            <h1 class="sidebar-brand-name">
                <span class="text-emerald-400">Apotek</span>
                <span class="text-slate-100 font-semibold">Almaira</span>
            </h1>
            <p class="sidebar-brand-sub">PT Nur Madani Farma</p>
        </div>
        <button type="button" @click="toggle()" class="sidebar-collapse-btn sidebar-hide-collapsed" title="Ciutkan menu">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
        </button>
    </div>

    {{-- User --}}
    <div class="sidebar-user-wrap" :class="{ 'is-collapsed': collapsed }">
        <div class="sidebar-user" :class="{ 'is-collapsed': collapsed }">
            <div class="sidebar-user-avatar">
                @if(auth()->user()->avatarUrl())
                    <img src="{{ auth()->user()->avatarUrl() }}" alt="Avatar">
                @else
                    <span>{{ auth()->user()->initials() }}</span>
                @endif
            </div>
            <div class="sidebar-user-meta sidebar-hide-collapsed">
                <p class="sidebar-user-name" title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</p>
                <p class="sidebar-user-role">{{ auth()->user()->role?->name ?? 'User' }}</p>
            </div>
            <div class="sidebar-user-status sidebar-hide-collapsed">
                <span class="sidebar-online">
                    <span class="sidebar-online-dot"></span>
                    Online
                </span>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="sidebar-nav" id="sidebar-nav">

        {{-- ══ 1. DASHBOARD ══ --}}
        <div class="sidebar-section">
            <a href="{{ route('dashboard') }}" wire:navigate data-nav="dashboard" class="sidebar-nav-item" title="Dashboard">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </span>
                <span class="sidebar-nav-label sidebar-hide-collapsed">Dashboard</span>
            </a>
        </div>

        {{-- ══ 2. KASIR & PENJUALAN ══ --}}
        @if(auth()->user()->isSuperAdmin() || auth()->user()->isKasir())
        <div class="sidebar-section">
            <div class="sidebar-section-label sidebar-hide-collapsed">
                <span>Kasir & Penjualan</span>
            </div>
            <a href="{{ route('pos.index') }}" wire:navigate data-nav="pos" class="sidebar-nav-item" title="Kasir / POS">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </span>
                <span class="sidebar-nav-label sidebar-hide-collapsed">Kasir / POS</span>
            </a>

            <a href="{{ route('prescriptions.index') }}" wire:navigate data-nav="prescriptions" class="sidebar-nav-item" title="Resep Dokter">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
                <span class="sidebar-nav-label sidebar-hide-collapsed">Resep Dokter</span>
            </a>

            <a href="{{ route('sales.index') }}" wire:navigate data-nav="sales" class="sidebar-nav-item" title="Riwayat Penjualan">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </span>
                <span class="sidebar-nav-label sidebar-hide-collapsed">Riwayat Penjualan</span>
            </a>

            @php
                $invoiceStats = \App\Services\InvoiceReceivableService::stats();
                $overdueCount = $invoiceStats['total_overdue'];
                $unpaidCount  = $invoiceStats['total_unpaid'];
            @endphp
            <a href="{{ route('invoices.index') }}" wire:navigate data-nav="invoices" class="sidebar-nav-item" title="Tagihan Invoice">
                <span class="nav-icon relative">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    @if($overdueCount > 0)
                    <span class="sidebar-item-dot is-danger">{{ $overdueCount > 9 ? '9+' : $overdueCount }}</span>
                    @elseif($unpaidCount > 0)
                    <span class="sidebar-item-dot is-warn">{{ $unpaidCount > 9 ? '9+' : $unpaidCount }}</span>
                    @endif
                </span>
                <span class="sidebar-nav-label sidebar-hide-collapsed sidebar-nav-label--with-badge">
                    Tagihan Invoice
                    @if($overdueCount > 0)
                    <span class="sidebar-chip is-danger">{{ $overdueCount }} OD</span>
                    @elseif($unpaidCount > 0)
                    <span class="sidebar-chip is-warn">{{ $unpaidCount }}</span>
                    @endif
                </span>
            </a>

            <a href="{{ route('customers.index') }}" wire:navigate data-nav="customers" class="sidebar-nav-item" title="Pelanggan (CRM)">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </span>
                <span class="sidebar-nav-label sidebar-hide-collapsed">Pelanggan (CRM)</span>
            </a>
        </div>
        @endif

        {{-- ══ 3. INVENTORI & STOK ══ --}}
        @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdminKeuangan() || auth()->user()->isKasir())
        <div class="sidebar-section">
            <div class="sidebar-section-label sidebar-hide-collapsed">
                <span>Inventori & Stok</span>
            </div>
            <a href="{{ route('products.index') }}" wire:navigate data-nav="products" class="sidebar-nav-item" title="Master Produk">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
                <span class="sidebar-nav-label sidebar-hide-collapsed">Master Produk</span>
            </a>

            <a href="{{ route('categories.index') }}" wire:navigate data-nav="categories" class="sidebar-nav-item" title="Kategori Produk">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </span>
                <span class="sidebar-nav-label sidebar-hide-collapsed">Kategori Produk</span>
            </a>

            {{-- Pengadaan: admin saja — bukan tugas harian kasir --}}
            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdminKeuangan())
            <a href="{{ route('purchases.index') }}" wire:navigate data-nav="purchases" class="sidebar-nav-item" title="Barang Masuk">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 16l4-4m0 0l4 4m-4-4v12M21 16l-4-4m0 0l-4 4m4-4v12"/></svg>
                </span>
                <span class="sidebar-nav-label sidebar-hide-collapsed">Barang Masuk</span>
            </a>
            @endif

            <a href="{{ route('stock-outs.index') }}" wire:navigate data-nav="stock-outs" class="sidebar-nav-item" title="Barang Keluar">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </span>
                <span class="sidebar-nav-label sidebar-hide-collapsed">Barang Keluar</span>
            </a>

            <a href="{{ route('stock-opnames.index') }}" wire:navigate data-nav="stock-opnames" class="sidebar-nav-item" title="Stok Opname">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </span>
                <span class="sidebar-nav-label sidebar-hide-collapsed">Stok Opname</span>
            </a>
        </div>
        @endif

        {{-- ══ 4. MASTER DATA (Admin) ══ --}}
        @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdminKeuangan())
        <div class="sidebar-section">
            <div class="sidebar-section-label sidebar-hide-collapsed">
                <span>Master Data</span>
            </div>
            <a href="{{ route('suppliers.index') }}" wire:navigate data-nav="suppliers" class="sidebar-nav-item" title="Supplier">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </span>
                <span class="sidebar-nav-label sidebar-hide-collapsed">Supplier</span>
            </a>

            <a href="{{ route('customers.index') }}" wire:navigate data-nav="customers" class="sidebar-nav-item" title="Pelanggan (CRM)">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </span>
                <span class="sidebar-nav-label sidebar-hide-collapsed">Pelanggan (CRM)</span>
            </a>

            <a href="{{ route('employees.index') }}" wire:navigate data-nav="employees" class="sidebar-nav-item" title="Master Karyawan">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </span>
                <span class="sidebar-nav-label sidebar-hide-collapsed">Master Karyawan</span>
            </a>

            <a href="{{ route('job-positions.index') }}" wire:navigate data-nav="job-positions" class="sidebar-nav-item" title="Master Jabatan">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </span>
                <span class="sidebar-nav-label sidebar-hide-collapsed">Master Jabatan</span>
            </a>
        </div>
        @endif

        {{-- ══ 5. MITRA B2B & E-CATALOG ══ --}}
        @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdminKeuangan())
        <div class="sidebar-section">
            <div class="sidebar-section-label sidebar-hide-collapsed">
                <span>Mitra B2B</span>
            </div>
            <a href="{{ route('catalog.index') }}" target="_blank" class="sidebar-nav-item" title="E-Catalog (Publik)">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                </span>
                <span class="sidebar-nav-label sidebar-hide-collapsed sidebar-nav-label--with-badge">
                    E-Catalog Publik
                    <svg class="sidebar-external-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </span>
            </a>

            <livewire:shared.partner-nav-badge />

            <a href="{{ route('partner-orders.index') }}" wire:navigate data-nav="partner-orders" class="sidebar-nav-item" title="PO Mitra">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </span>
                <span class="sidebar-nav-label sidebar-hide-collapsed">PO Mitra</span>
            </a>
        </div>
        @endif

        {{-- ══ 6. KEUANGAN ══ --}}
        @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdminKeuangan())
        <div class="sidebar-section">
            <div class="sidebar-section-label sidebar-hide-collapsed">
                <span>Keuangan</span>
            </div>
            <a href="{{ route('credits.index') }}" wire:navigate data-nav="credits" class="sidebar-nav-item" title="Kredit & Piutang">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <span class="sidebar-nav-label sidebar-hide-collapsed">Kredit & Piutang</span>
            </a>

            <a href="{{ route('salaries.index') }}" wire:navigate data-nav="salaries" class="sidebar-nav-item" title="Gaji Karyawan">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
                <span class="sidebar-nav-label sidebar-hide-collapsed">Gaji Karyawan</span>
            </a>

            <a href="{{ route('reports.index') }}" wire:navigate data-nav="reports" class="sidebar-nav-item" title="Laporan">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
                <span class="sidebar-nav-label sidebar-hide-collapsed">Semua Laporan</span>
            </a>
        </div>
        @endif

        {{-- ══ 7. PENGATURAN ══ --}}
        @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdminKeuangan())
        <div class="sidebar-section">
            <div class="sidebar-section-label sidebar-hide-collapsed">
                <span>Pengaturan</span>
            </div>
            <a href="{{ route('settings.index') }}" wire:navigate data-nav="settings" class="sidebar-nav-item" title="Pengaturan Aplikasi">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </span>
                <span class="sidebar-nav-label sidebar-hide-collapsed">PPN, Diskon & Info Apotek</span>
            </a>
        </div>
        @endif

        {{-- ══ 8. SISTEM & IT ══ --}}
        @if(auth()->user()->isSuperAdmin())
        <div class="sidebar-section">
            <div class="sidebar-section-label sidebar-hide-collapsed">
                <span>Sistem & IT</span>
            </div>
            <a href="{{ route('users.index') }}" wire:navigate data-nav="users" class="sidebar-nav-item" title="Manajemen User">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </span>
                <span class="sidebar-nav-label sidebar-hide-collapsed">Manajemen User</span>
            </a>

            <a href="{{ route('reports.index') }}?type=log_aktivitas" wire:navigate data-nav="log-aktivitas" class="sidebar-nav-item" title="Log Aktivitas">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <span class="sidebar-nav-label sidebar-hide-collapsed">Log Aktivitas</span>
            </a>

            <a href="{{ route('backup.index') }}" wire:navigate data-nav="backup" class="sidebar-nav-item" title="Database Backup">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                </span>
                <span class="sidebar-nav-label sidebar-hide-collapsed">Database Backup</span>
            </a>
        </div>
        @endif
    </nav>

    {{-- Logout --}}
    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-nav-item sidebar-logout" title="Logout">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </span>
                <span class="sidebar-nav-label sidebar-hide-collapsed">Logout</span>
            </button>
        </form>
    </div>
</aside>
