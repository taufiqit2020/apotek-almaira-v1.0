@include('errors.layout', [
    'code' => '503',
    'title' => 'Layanan Tidak Tersedia',
    'message' => $exception->getMessage() ?: 'Sistem sedang dalam pemeliharaan atau sementara tidak dapat diakses.',
    'hint' => 'Silakan coba lagi nanti. Jika sedang ada maintenance, tunggu hingga layanan kembali normal.',
    'tone' => 'slate',
    'icon' => 'server',
])
