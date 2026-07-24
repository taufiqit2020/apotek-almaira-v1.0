<?php

namespace App\Support;

/**
 * Helper teks monospasi untuk cetak Epson LX-310 (grid karakter tetap).
 */
final class DotMatrixText
{
    /** Lebar grid karakter untuk kertas LX-310 ~25 cm (font 15pt). */
    public const WIDTH = 72;

    public static function pad(string $text, int $width, string $align = 'left'): string
    {
        $text = preg_replace('/\s+/u', ' ', trim($text)) ?? '';
        $len = mb_strlen($text, 'UTF-8');

        if ($len > $width) {
            $text = mb_substr($text, 0, max(1, $width - 1), 'UTF-8').'.';
            $len = mb_strlen($text, 'UTF-8');
        }

        $pad = $width - $len;
        if ($pad <= 0) {
            return $text;
        }

        return match ($align) {
            'right' => str_repeat(' ', $pad).$text,
            'center' => str_repeat(' ', intdiv($pad, 2)).$text.str_repeat(' ', $pad - intdiv($pad, 2)),
            default => $text.str_repeat(' ', $pad),
        };
    }

    public static function line(int $width = self::WIDTH, string $char = '-'): string
    {
        return str_repeat($char, $width);
    }

    /**
     * Pecah teks panjang jadi beberapa baris (tanpa dipotong), lalu pad tiap baris.
     *
     * @return list<string>
     */
    public static function wrap(string $text, int $width = self::WIDTH, string $align = 'left'): array
    {
        $text = preg_replace('/\s+/u', ' ', trim($text)) ?? '';
        if ($text === '') {
            return [self::pad('', $width, $align)];
        }

        $words = preg_split('/\s+/u', $text) ?: [];
        $chunks = [];
        $current = '';

        foreach ($words as $word) {
            $trial = $current === '' ? $word : $current.' '.$word;
            if (mb_strlen($trial, 'UTF-8') <= $width) {
                $current = $trial;
                continue;
            }
            if ($current !== '') {
                $chunks[] = $current;
            }
            while (mb_strlen($word, 'UTF-8') > $width) {
                $chunks[] = mb_substr($word, 0, $width, 'UTF-8');
                $word = mb_substr($word, $width, null, 'UTF-8');
            }
            $current = $word;
        }

        if ($current !== '') {
            $chunks[] = $current;
        }

        return array_map(
            static fn (string $chunk): string => self::pad($chunk, $width, $align),
            $chunks ?: ['']
        );
    }

    /**
     * Dua kolom sejajar (label singkat). Nama panjang dibungkus, tidak dipotong.
     *
     * @return list<string>
     */
    public static function twin(string $left, string $right, int $totalWidth = self::WIDTH): array
    {
        $half = (int) floor($totalWidth / 2);
        $rightW = $totalWidth - $half;
        $leftRows = self::wrap($left, $half, 'center');
        $rightRows = self::wrap($right, $rightW, 'center');
        $n = max(count($leftRows), count($rightRows));
        $lines = [];

        for ($i = 0; $i < $n; $i++) {
            $lines[] = ($leftRows[$i] ?? self::pad('', $half, 'left'))
                .($rightRows[$i] ?? self::pad('', $rightW, 'left'));
        }

        return $lines;
    }

    /**
     * Font (pt) agar teks tetap 1 baris dalam lebar kolom karakter.
     * Nama lebih panjang → font otomatis mengecil (min $minPt).
     */
    public static function fitFontPt(
        string $text,
        int $colChars = 32,
        float $basePt = 15.0,
        float $minPt = 8.0
    ): float {
        $len = max(1, mb_strlen(trim($text), 'UTF-8'));
        if ($len <= $colChars) {
            return $basePt;
        }

        return max($minPt, round($basePt * $colChars / $len, 1));
    }

    /**
     * @param  array<int, array{0: string, 1: int, 2?: string}>  $cols
     */
    public static function row(array $cols): string
    {
        $out = '';
        foreach ($cols as $col) {
            $text = (string) ($col[0] ?? '');
            $width = (int) ($col[1] ?? 0);
            $align = (string) ($col[2] ?? 'left');
            $out .= self::pad($text, $width, $align);
        }

        return rtrim($out);
    }

    /** Label tetap lebar + nilai (titik dua sejajar di kolom yang sama). */
    public static function field(string $label, string $value, int $labelWidth = 10, int $totalWidth = self::WIDTH): string
    {
        $label = rtrim($label);
        // Label rata kiri dalam lebar tetap → semua ':' pada kolom yang sama
        $prefix = self::pad($label, $labelWidth, 'left').': ';
        $valueWidth = max(1, $totalWidth - mb_strlen($prefix, 'UTF-8'));
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? '';

        if (mb_strlen($value, 'UTF-8') > $valueWidth) {
            $value = mb_substr($value, 0, max(1, $valueWidth - 1), 'UTF-8').'.';
        }

        return $prefix.$value;
    }

    /**
     * Dua field sejajar dalam 1 baris (kiri | kanan), titik dua tetap rapi di tiap kolom.
     * $leftWidth opsional untuk kolom kiri lebih lebar (nilai panjang).
     */
    public static function fieldPair(
        string $labelA,
        string $valueA,
        string $labelB,
        string $valueB,
        int $labelWidth = 9,
        int $totalWidth = self::WIDTH,
        ?int $leftWidth = null
    ): string {
        $half = $leftWidth ?? (int) floor($totalWidth / 2);
        $half = max(18, min($half, $totalWidth - 18));
        $rightW = $totalWidth - $half;
        $left = self::pad(self::field($labelA, $valueA, $labelWidth, $half), $half, 'left');
        $right = self::field($labelB, $valueB, $labelWidth, $rightW);

        return $left.$right;
    }

    /**
     * Field pair yang membungkus nilai kiri jika terlalu panjang (tidak dipotong kasar).
     *
     * @return list<string>
     */
    public static function fieldPairWrap(
        string $labelA,
        string $valueA,
        string $labelB,
        string $valueB,
        int $labelWidth = 9,
        int $totalWidth = self::WIDTH,
        ?int $leftWidth = null
    ): array {
        $half = $leftWidth ?? (int) floor($totalWidth / 2);
        $half = max(18, min($half, $totalWidth - 18));
        $rightW = $totalWidth - $half;

        $leftRows = self::fieldWrap($labelA, $valueA, $labelWidth, $half);
        $right = self::pad(self::field($labelB, $valueB, $labelWidth, $rightW), $rightW, 'left');
        $blankRight = self::pad('', $rightW, 'left');

        $out = [];
        foreach ($leftRows as $i => $row) {
            $out[] = self::pad($row, $half, 'left').($i === 0 ? $right : $blankRight);
        }

        return $out ?: [self::pad('', $half, 'left').$right];
    }

    /** Garis pemisah monospasi. */
    public static function rule(int $width = self::WIDTH, string $char = '-'): string
    {
        return self::line($width, $char);
    }

    /**
     * Baris kop dokumen LX-310 (nama, tagline, alamat, kontak, judul).
     *
     * @return list<string>
     */
    public static function kopLines(
        string $companyName,
        string $tagline,
        string $address,
        string $phone,
        string $website,
        string $instagram,
        string $docTitle,
        int $width = self::WIDTH
    ): array {
        $addrLine = trim(preg_replace('/\s+/u', ' ', str_replace(["\r\n", "\n", "\r"], ' ', $address)) ?? '');
        $phoneDisp = preg_replace('/-/', ' ', $phone) ?: $phone;
        $webDisp = preg_replace('#^https?://#i', '', $website) ?: $website;
        $igDisp = trim($instagram);
        if ($igDisp !== '' && ! str_starts_with($igDisp, '@')) {
            $igDisp = '@'.$igDisp;
        }
        $contact = 'Telp/WA : '.$phoneDisp.' Website : '.$webDisp.' instagram : '.$igDisp;

        $lines = [];
        $lines[] = self::pad($companyName, $width, 'center');
        foreach (self::wrap($tagline, $width, 'center') as $row) {
            $lines[] = $row;
        }
        foreach (self::wrap($addrLine, $width, 'center') as $row) {
            $lines[] = $row;
        }
        foreach (self::wrap($contact, $width, 'center') as $row) {
            $lines[] = $row;
        }
        $lines[] = '';
        $lines[] = self::pad($docTitle, $width, 'center');
        $lines[] = '';

        return $lines;
    }

    /**
     * Baris ringkasan uang (Subtotal/Diskon/PPN/TOTAL) — titik dua, Rp, dan angka sejajar.
     * Lebar blok nilai tetap, lalu rata kanan agar sejajar kolom SUBTOTAL tabel.
     * Tidak memakai pad() pada seluruh blok agar spasi alignment tidak ter-collapse.
     */
    public static function moneySummaryLine(
        string $label,
        string $amountDigits,
        int $labelWidth = 8,
        int $amountWidth = 12,
        int $gapAfterColon = 8,
        int $totalWidth = self::WIDTH
    ): string {
        $label = preg_replace('/\s+/u', ' ', trim($label)) ?? '';
        $amountDigits = preg_replace('/\s+/u', ' ', trim($amountDigits)) ?? '0';

        $gap = max(1, $gapAfterColon);
        $prefix = self::pad($label, $labelWidth, 'left').':'.str_repeat(' ', $gap);
        $value = 'Rp '.self::pad($amountDigits, $amountWidth, 'right');
        $block = $prefix.$value;

        $len = mb_strlen($block, 'UTF-8');
        if ($len > $totalWidth) {
            return mb_substr($block, 0, $totalWidth, 'UTF-8');
        }

        return str_repeat(' ', $totalWidth - $len).$block;
    }

    /**
     * Baris detail item menjorok, dibungkus rapi.
     *
     * @return list<string>
     */
    public static function detailLines(string $text, int $indent = 4, int $totalWidth = self::WIDTH): array
    {
        $indentStr = str_repeat(' ', max(0, $indent));
        $innerW = max(8, $totalWidth - $indent);
        $rows = self::wrap($text, $innerW, 'left');
        $out = [];
        foreach ($rows as $row) {
            $out[] = $indentStr.rtrim($row);
        }

        return $out ?: [$indentStr];
    }

    /**
     * Field dengan nilai panjang (alamat) — baris lanjutan menjorok rapi.
     *
     * @return list<string>
     */
    public static function fieldWrap(string $label, string $value, int $labelWidth = 10, int $totalWidth = self::WIDTH): array
    {
        $label = rtrim($label);
        $prefix = self::pad($label, $labelWidth, 'left').': ';
        $indent = str_repeat(' ', mb_strlen($prefix, 'UTF-8'));
        $valueWidth = max(1, $totalWidth - mb_strlen($prefix, 'UTF-8'));

        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? '';
        if ($value === '') {
            return [$prefix];
        }

        $words = preg_split('/\s+/u', $value) ?: [];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $trial = $current === '' ? $word : $current.' '.$word;
            if (mb_strlen($trial, 'UTF-8') <= $valueWidth) {
                $current = $trial;
                continue;
            }
            if ($current !== '') {
                $lines[] = ($lines === [] ? $prefix : $indent).$current;
            }
            // kata lebih panjang dari lebar: potong
            while (mb_strlen($word, 'UTF-8') > $valueWidth) {
                $lines[] = ($lines === [] && $current === '' ? $prefix : $indent)
                    .mb_substr($word, 0, $valueWidth, 'UTF-8');
                $word = mb_substr($word, $valueWidth, null, 'UTF-8');
            }
            $current = $word;
        }

        if ($current !== '') {
            $lines[] = ($lines === [] ? $prefix : $indent).$current;
        }

        return $lines ?: [$prefix];
    }
}
