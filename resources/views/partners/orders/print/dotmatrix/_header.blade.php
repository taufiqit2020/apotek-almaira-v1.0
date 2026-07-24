{{-- Kop surat LX-310 — teks tebal, tanpa logo/gambar --}}
@php
    if ($isPT) {
        $kopName = 'PT. NUR MADANI FARMA';
        $kopTagline = 'Distributor & Mitra Pengadaan Alat Kesehatan & Farmasi';
    } else {
        $kopName = 'APOTEK ALMAIRA';
        $kopTagline = 'Pelayanan Kesehatan & Kefarmasian Terpercaya';
    }
    $addrLine = \App\Support\DotMatrixText::compactAddress($address);
@endphp
<div class="header">
    <h1>{{ $kopName }}</h1>
    <p class="sub">{{ $kopTagline }}</p>
    <p class="addr">{{ $addrLine }}</p>
    <p class="phone">Telp/WA: {{ $phone }}</p>
</div>
