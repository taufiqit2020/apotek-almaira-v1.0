<?php
function crc16($str) {
    $crc = 0xFFFF;
    for ($c = 0; $c < strlen($str); $c++) {
        $crc ^= ord($str[$c]) << 8;
        for ($i = 0; $i < 8; $i++) {
            if ($crc & 0x8000) {
                $crc = ($crc << 1) ^ 0x1021;
            } else {
                $crc = $crc << 1;
            }
        }
    }
    return sprintf('%04X', $crc & 0xFFFF);
}

$originalWithoutCrc = '00020101021126590013ID.CO.BNI.WWW011893600009150464484202096095449950303UMI51440014ID.CO.QRIS.WWW0215ID10265223592760303UMI5204591253033605802ID5914APOTEK ALMAIRA6010BANJARBARU61057071462070703A016304';
$computedCrc = crc16($originalWithoutCrc);
echo "Computed CRC: $computedCrc\n";
echo "Original CRC: 90BA\n";
if ($computedCrc === '90BA') {
    echo "SUCCESS: Checksums match!\n";
} else {
    echo "FAIL: Checksums do not match.\n";
}
