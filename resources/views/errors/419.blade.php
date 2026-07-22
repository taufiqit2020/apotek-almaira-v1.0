@include('errors.layout', [
    'code' => '419',
    'title' => 'Sesi Kedaluwarsa',
    'message' => $exception->getMessage() ?: 'Halaman atau formulir sudah kedaluwarsa. Muat ulang lalu coba lagi.',
    'hint' => 'Biasanya terjadi jika halaman terbuka terlalu lama. Tekan kembali, refresh, lalu kirim ulang formulir.',
    'tone' => 'amber',
    'icon' => 'clock',
])
