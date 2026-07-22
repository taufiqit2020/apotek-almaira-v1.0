{{--
    Partial: Blok Tanda Tangan Laporan Terpadu (Apotek / PT)
    Tanda tangan tunggal sesuai entitas yang dipilih:
    1. PT Nur Madani Farma: Direktur Hj. Nor Maulida, S.H.
    2. Apotek Almaira: Apoteker Penanggung Jawab Apt. Wulan Ageng Sujatmiko, S.Farm., M.M.
--}}

<div class="signature-section" style="margin-top: 40px; padding-top: 16px; border-top: 1px solid #e5e7eb; page-break-inside: avoid;">
    <table style="width: 100%; border-collapse: collapse; font-size: 10px; border: none;">
        <tr>
            <td style="width: 60%; border: none; padding: 0;"></td>
            <td style="width: 40%; border: none; padding: 0; text-align: center; vertical-align: top;">
                <p style="margin: 0 0 5px 0; color: #1a2433;">Banjarbaru, {{ now()->locale('id')->isoFormat('D MMMM Y') }}</p>
                @if(($entity ?? 'apotek') === 'pt')
                    <p style="margin: 0 0 60px 0; font-weight: bold; color: #1a2433; text-transform: uppercase;">Direktur PT Nur Madani Farma</p>
                    <div style="border-bottom: 1px solid #374151; margin: 0 auto 4px auto; width: 80%;"></div>
                    <p style="margin: 0; font-weight: bold; color: #1a2433;">Hj. Nor Maulida, S.H.</p>
                @else
                    <p style="margin: 0 0 60px 0; font-weight: bold; color: #1a2433; text-transform: uppercase;">Apoteker Penanggung Jawab</p>
                    <div style="border-bottom: 1px solid #374151; margin: 0 auto 4px auto; width: 80%;"></div>
                    <p style="margin: 0; font-weight: bold; color: #1a2433;">Apt. Wulan Ageng Sujatmiko, S.Farm., M.M.</p>
                    <p style="margin: 2px 0 0 0; color: #6b7280; font-size: 9px;">No. SIP: NR63722606010965</p>
                @endif
            </td>
        </tr>
    </table>
</div>
