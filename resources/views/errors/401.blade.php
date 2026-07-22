@include('errors.layout', [
    'code' => '401',
    'title' => 'Perlu Login',
    'message' => $exception->getMessage() ?: 'Anda harus login terlebih dahulu untuk membuka halaman ini.',
    'hint' => 'Sesi belum aktif atau sudah berakhir. Silakan login ulang dengan akun staff atau mitra.',
    'tone' => 'amber',
    'icon' => 'auth',
])
