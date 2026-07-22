<?php

namespace App\Support;

/**
 * Helper teks monospasi untuk cetak Epson LX-310 (grid karakter tetap).
 */
final class DotMatrixText
{
    /** Lebar grid lebih sempit agar font 15pt muat di kertas 25 cm. */
    public const WIDTH = 64;

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
