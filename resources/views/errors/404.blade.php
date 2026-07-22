@include('errors.layout', [
    'code' => '404',
    'title' => 'Halaman Tidak Ditemukan',
    'message' => $exception->getMessage() ?: 'Halaman yang Anda cari tidak tersedia atau sudah dipindahkan.',
    'hint' => 'Periksa kembali alamat URL, atau gunakan menu navigasi untuk menuju halaman yang benar.',
    'tone' => 'slate',
    'icon' => 'search',
])
