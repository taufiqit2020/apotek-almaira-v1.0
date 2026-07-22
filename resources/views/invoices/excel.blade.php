<table>
    <!-- HEADER -->
    <tr>
        <td colspan="6" align="center"><b>APOTEK ALMAIRA</b></td>
    </tr>
    <tr>
        <td colspan="6" align="center"><b>PT NUR MADANI FARMA</b></td>
    </tr>
    <tr>
        <td colspan="6" align="center">Jl. Nuri No.14 RT/RW 001/005, Kel. Komet</td>
    </tr>
    <tr>
        <td colspan="6" align="center">Banjarbaru Utara, Kalsel 70714</td>
    </tr>
    <tr>
        <td colspan="6" align="center">Telp/WA: 0851-6665-7070</td>
    </tr>
    <tr>
        <td colspan="6" align="center"></td>
    </tr>
    <tr>
        <td colspan="6" align="center"><b>INVOICE PENJUALAN</b></td>
    </tr>
    <tr>
        <td colspan="6" align="center"></td>
    </tr>

    <!-- INFO -->
    <tr>
        <td><b>PENJUALAN INVOICE</b></td>
        <td>: {{ $sale->invoice_no }}</td>
        <td></td>
        <td><b>Kepada</b></td>
        <td colspan="2">: {{ $sale->customer_name }}</td>
    </tr>
    <tr>
        <td><b>Tanggal</b></td>
        <td>: {{ \Carbon\Carbon::parse($sale->sold_at)->format('d-m-Y H:i') }}</td>
        <td></td>
        <td><b>Jatuh Tempo</b></td>
        <td colspan="2">: {{ \Carbon\Carbon::parse($sale->due_date)->format('d-m-Y') }}</td>
    </tr>
    <tr>
        <td><b>Kasir</b></td>
        <td>: {{ $sale->user->name }}</td>
        <td></td>
        <td><b>Status</b></td>
        <td colspan="2">: {{ $sale->payment_status === 'paid' ? 'LUNAS (' . \Carbon\Carbon::parse($sale->settled_at)->format('d/m/Y') . ')' : 'BELUM LUNAS' }}</td>
    </tr>
    <tr>
        <td colspan="6"></td>
    </tr>

    <!-- ITEMS HEADER -->
    <tr>
        <th align="center" style="border: 1px solid #000000;"><b>No.</b></th>
        <th align="center" style="border: 1px solid #000000;"><b>Nama Barang</b></th>
        <th align="center" style="border: 1px solid #000000;"><b>Qty</b></th>
        <th align="right" style="border: 1px solid #000000;"><b>Harga</b></th>
        <th align="right" style="border: 1px solid #000000;"><b>Disc</b></th>
        <th align="right" style="border: 1px solid #000000;"><b>Subtotal</b></th>
    </tr>

    <!-- ITEMS DATA -->
    @foreach($sale->items as $index => $item)
    <tr>
        <td align="center" style="border: 1px solid #000000;">{{ $index + 1 }}</td>
        <td style="border: 1px solid #000000;">{{ $item->product_name }}</td>
        <td align="center" style="border: 1px solid #000000;">{{ $item->quantity }} {{ $item->unit_name }}</td>
        <td align="right" style="border: 1px solid #000000;">{{ (float) $item->unit_price }}</td>
        <td align="right" style="border: 1px solid #000000;">{{ $item->discount_percent > 0 ? $item->discount_percent.'%' : '-' }}</td>
        <td align="right" style="border: 1px solid #000000;">{{ (float) $item->subtotal }}</td>
    </tr>
    @endforeach

    <!-- SUMMARY -->
    <tr>
        <td colspan="4"></td>
        <td align="right">Subtotal :</td>
        <td align="right">{{ (float) $sale->subtotal }}</td>
    </tr>
    @if($sale->discount_amount > 0)
    <tr>
        <td colspan="4"></td>
        <td align="right">Diskon :</td>
        <td align="right">{{ (float) $sale->discount_amount }}</td>
    </tr>
    @endif
    <tr>
        <td colspan="4"></td>
        <td align="right">PPN ({{ $sale->ppn_percent + 0 }}%) :</td>
        <td align="right">{{ (float) $sale->ppn_amount }}</td>
    </tr>
    <tr>
        <td colspan="4"></td>
        <td align="right"><b>TOTAL TAGIHAN :</b></td>
        <td align="right"><b>{{ (float) $sale->total }}</b></td>
    </tr>
    <tr>
        <td colspan="6"></td>
    </tr>

    <!-- SIGNATURES -->
    <tr>
        <td colspan="2" align="center">Penerima / Pembeli,</td>
        <td colspan="2"></td>
        <td colspan="2" align="center">DIREKTUR,</td>
    </tr>
    <tr>
        <td colspan="6" style="height: 50px;"></td>
    </tr>
    <tr>
        <td colspan="2" align="center"><b>( {{ $sale->customer_name }} )</b></td>
        <td colspan="2"></td>
        <td colspan="2" align="center"><b>( Hj. Nor Maulida, S.H. )</b></td>
    </tr>
    <tr>
        <td colspan="6"></td>
    </tr>
    <tr>
        <td colspan="6" align="center">Terima kasih atas kepercayaan Anda.</td>
    </tr>
    <tr>
        <td colspan="6" align="center">Dicetak pada: {{ now()->format('d-m-Y H:i:s') }}</td>
    </tr>
</table>
