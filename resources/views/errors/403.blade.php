@include('errors.layout', [
    'code' => '403',
    'title' => 'Akses Ditolak',
    'message' => $exception->getMessage() ?: 'Anda tidak memiliki akses ke halaman ini.',
    'hint' => 'Halaman ini hanya untuk role tertentu. Pastikan Anda login dengan akun yang berwenang, atau kembali ke menu yang diizinkan.',
    'tone' => 'rose',
    'icon' => 'lock',
])
