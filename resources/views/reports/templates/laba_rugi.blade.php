<div style="margin-top: 20px; max-width: 650px; margin-left: auto; margin-right: auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; font-family: 'Inter', system-ui, sans-serif; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
    <div style="background: linear-gradient(135deg, #0f172a, #1e293b); padding: 12px 18px; font-weight: 700; font-size: 13px; border-bottom: 1px solid #e2e8f0; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.5px;">
        Rincian Perhitungan Laba Rugi Bersih
    </div>
    <div style="padding: 20px; background-color: #ffffff;">
        <table style="width: 100%; border-collapse: collapse; font-size: 11.5px; color: #334155;">
            
            {{-- PENDAPATAN --}}
            <tr style="border-bottom: 1.5px solid #cbd5e1;">
                <td style="padding: 10px 0; font-weight: 800; font-size: 12px; color: #0f172a; text-transform: uppercase; border: none !important;">1. Pendapatan Penjualan</td>
                <td style="padding: 10px 0; text-align: right; border: none !important;"></td>
            </tr>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 8px 0; padding-left: 20px; border: none !important; color: #475569;">Subtotal Penjualan (Sebelum Diskon & PPN)</td>
                <td style="padding: 8px 0; text-align: right; border: none !important; font-weight: 600;">Rp {{ number_format($data->total_subtotal, 0, ',', '.') }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 8px 0; padding-left: 20px; border: none !important; color: #ef4444;">Potongan Diskon Transaksi (-)</td>
                <td style="padding: 8px 0; text-align: right; border: none !important; color: #ef4444; font-weight: 600;">Rp {{ number_format($data->total_discount, 0, ',', '.') }}</td>
            </tr>
            <tr style="border-bottom: 2px double #e2e8f0; background-color: #f8fafc;">
                <td style="padding: 9px 12px; font-weight: 700; border: none !important; color: #0f172a;">Total Pendapatan Bersih (A)</td>
                <td style="padding: 9px 12px; text-align: right; font-weight: 700; color: #10b981; border: none !important;">Rp {{ number_format($data->total_subtotal - $data->total_discount, 0, ',', '.') }}</td>
            </tr>

            {{-- BEBAN POKOK --}}
            <tr style="border-bottom: 1.5px solid #cbd5e1;">
                <td style="padding: 18px 0 10px 0; font-weight: 800; font-size: 12px; color: #0f172a; text-transform: uppercase; border: none !important;">2. Harga Pokok Penjualan</td>
                <td style="padding: 18px 0 10px 0; text-align: right; border: none !important;"></td>
            </tr>
            <tr style="border-bottom: 2px double #e2e8f0; background-color: #f8fafc;">
                <td style="padding: 9px 12px; font-weight: 700; border: none !important; color: #ef4444;">Harga Pokok Pembelian / HPP (-) (B)</td>
                <td style="padding: 9px 12px; text-align: right; font-weight: 700; color: #ef4444; border: none !important;">Rp {{ number_format($data->total_hpp, 0, ',', '.') }}</td>
            </tr>

            {{-- LABA KOTOR --}}
            <tr style="background-color: #f1f5f9; font-weight: 700; font-size: 12px; border: 1.5px solid #e2e8f0;">
                <td style="padding: 10px 12px; border: none !important; color: #0f172a;">LABA KOTOR PENJUALAN (A - B)</td>
                <td style="padding: 10px 12px; text-align: right; border: none !important; color: #0369a1; font-size: 12.5px;">Rp {{ number_format($data->gross_profit, 0, ',', '.') }}</td>
            </tr>

            {{-- BEBAN OPERASIONAL --}}
            <tr style="border-bottom: 1.5px solid #cbd5e1;">
                <td style="padding: 18px 0 10px 0; font-weight: 800; font-size: 12px; color: #0f172a; text-transform: uppercase; border: none !important;">3. Beban Operasional & Kerugian</td>
                <td style="padding: 18px 0 10px 0; text-align: right; border: none !important;"></td>
            </tr>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 8px 0; padding-left: 20px; border: none !important; color: #475569;">Beban Gaji Karyawan (C)</td>
                <td style="padding: 8px 0; text-align: right; border: none !important; color: #ef4444; font-weight: 600;">Rp {{ number_format($data->total_salaries, 0, ',', '.') }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 8px 0; padding-left: 20px; border: none !important; color: #475569;">Beban Kerugian Barang Rusak / Expired (D)</td>
                <td style="padding: 8px 0; text-align: right; border: none !important; color: #ef4444; font-weight: 600;">Rp {{ number_format($data->total_stock_out_loss, 0, ',', '.') }}</td>
            </tr>
            <tr style="border-bottom: 2px double #e2e8f0; background-color: #f8fafc;">
                <td style="padding: 9px 12px; font-weight: 700; border: none !important; color: #475569;">Total Beban Operasional (-) (E = C + D)</td>
                <td style="padding: 9px 12px; text-align: right; font-weight: 700; color: #ef4444; border: none !important;">Rp {{ number_format($data->total_operational_costs, 0, ',', '.') }}</td>
            </tr>

            {{-- LABA BERSIH --}}
            <tr style="background-color: #0f172a; font-weight: 700; font-size: 13px; color: #ffffff;">
                <td style="padding: 12px; border: none !important;">LABA BERSIH APOTEK (Laba Kotor - E)</td>
                <td style="padding: 12px; text-align: right; border: none !important; color: #38bdf8; font-size: 14px;">Rp {{ number_format($data->net_profit, 0, ',', '.') }}</td>
            </tr>
        </table>
        
        <div style="margin-top: 20px; border-top: 1px dashed #cbd5e1; padding-top: 12px; font-size: 8.5px; color: #64748b; line-height: 1.5;">
            <strong>Catatan & Keterangan Laporan:</strong><br>
            - **Beban Gaji Karyawan** dihitung berdasarkan penarikan data slip gaji pegawai yang dibayarkan dalam rentang tanggal periode laporan.<br>
            - **Kerugian Barang Rusak/Expired** didapatkan dari penarikan data transaksi barang keluar (*Stock Out*) dengan alasan rusak/kedaluwarsa dikali dengan HPP (Harga Beli) produk.<br>
            - **Informasi PPN**: Total PPN terkumpul periode ini sebesar <strong>Rp {{ number_format($data->total_ppn, 0, ',', '.') }}</strong> (tidak dimasukkan ke perhitungan laba rugi karena PPN merupakan titipan pajak negara).
        </div>
    </div>
</div>

@include('reports.partials.signature')
