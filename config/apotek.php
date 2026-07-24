<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Apotek Almaira — Konfigurasi Aplikasi
    |--------------------------------------------------------------------------
    */

    'name'              => env('APOTEK_NAME', 'Apotek Almaira'),
    'phone'             => env('APOTEK_PHONE', '0851-6665-7070'),
    'address'           => env('APOTEK_ADDRESS', 'Jl Nuri No.14 RT/RW 001/005, Kel. Komet, Kec. Banjarbaru Utara, Kalsel 70714'),
    'owner_company'     => env('APOTEK_OWNER', 'PT Nur Madani Farma'),

    // QRIS
    'qris_nmid'         => env('APOTEK_QRIS_NMID', 'ID1026522359276'),
    'qris_terminal'     => 'A01',

    // PPN
    'ppn_default'       => env('PPN_DEFAULT', 11),

    // Invoice
    'invoice_prefix'    => env('INVOICE_PREFIX', 'APK'),

    // Security
    'login_max_attempts'     => env('LOGIN_MAX_ATTEMPTS', 5),
    'login_lockout_minutes'  => env('LOGIN_LOCKOUT_MINUTES', 15),
    'session_timeout_minutes'=> env('SESSION_TIMEOUT_MINUTES', 0),

    // HET Markup options (%)
    'het_options' => [5, 10, 15, 20, 25, 30],

    // Discount tiers
    'discount_tiers' => [
        ['label' => '1–10%', 'min' => 1, 'max' => 10, 'values' => [
            1.5, 2.5, 3.5, 4.5, 5.5, 6.5, 7.5, 8.5, 9.5, 10.5
        ]],
        ['label' => '11–20%', 'min' => 11, 'max' => 20, 'values' => [
            11.5, 12.5, 13.5, 14.5, 15.5, 16.5, 17.5, 18.5, 19.5, 20.5
        ]],
        ['label' => '21–30%', 'min' => 21, 'max' => 30, 'values' => [
            21.5, 22.5, 23.5, 24.5, 25.5, 26.5, 27.5, 28.5, 29.5, 30.5
        ]],
    ],
];
