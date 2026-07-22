@include('errors.layout', [
    'code' => '500',
    'title' => 'Kesalahan Server',
    'message' => $exception->getMessage() ?: 'Terjadi gangguan pada server. Tim teknis akan menanganinya.',
    'hint' => 'Coba muat ulang beberapa saat lagi. Jika berulang, laporkan ke Super Admin / IT beserta waktu kejadian.',
    'tone' => 'rose',
    'icon' => 'server',
])
