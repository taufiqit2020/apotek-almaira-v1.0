<?php
$filePath = 'C:/apotek-almairav1.0/apotek-almaira/public/assets/images/qris-almaira.jpeg';
$url = 'http://api.qrserver.com/v1/read-qr-code/';

if (!file_exists($filePath)) {
    die("File not found: $filePath\n");
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'file' => new CURLFile($filePath)
]);

$response = curl_exec($ch);
if (curl_errno($ch)) {
    die('Error: ' . curl_error($ch) . "\n");
}
curl_close($ch);

$data = json_decode($response, true);
if (isset($data[0]['symbol'][0]['data'])) {
    echo "Decoded QRIS Data:\n";
    echo $data[0]['symbol'][0]['data'] . "\n";
} else {
    echo "Failed to decode QR code. API Response:\n";
    print_r($response);
    echo "\n";
}
