@include('errors.layout', [
    'code' => '429',
    'title' => 'Terlalu Banyak Permintaan',
    'message' => $exception->getMessage() ?: 'Anda mengirim terlalu banyak permintaan dalam waktu singkat.',
    'hint' => 'Tunggu beberapa saat sebelum mencoba lagi. Batasan ini melindungi sistem dari penyalahgunaan.',
    'tone' => 'amber',
    'icon' => 'ban',
])
