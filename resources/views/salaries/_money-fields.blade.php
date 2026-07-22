{{-- Komponen keuangan + gaji bersih. Mengandalkan Alpine state: basic, overtime, allowance, bpjs_kes, bpjs_ket, deduction --}}
<div class="space-y-4">
    <div class="bg-emerald-50/30 p-3 rounded-xl border border-emerald-100/50 space-y-3">
        <h4 class="text-xs font-bold text-emerald-800 uppercase tracking-wider">I. PENDAPATAN (EARNINGS)</h4>
        <div>
            <label class="form-label text-xs font-semibold mb-1 block">Gaji Pokok <span class="text-rose-500">*</span></label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-semibold z-10">Rp</span>
                <input type="text" inputmode="numeric" autocomplete="off"
                       class="form-input pl-9 font-semibold tracking-wide"
                       x-bind:value="formatId(basic)"
                       @input="basic = parseId($event.target.value)"
                       @blur="$event.target.value = formatId(basic)"
                       placeholder="0">
                <input type="hidden" name="basic_salary" x-bind:value="basic">
            </div>
        </div>
        <div>
            <label class="form-label text-xs font-semibold mb-1 block">Lembur</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-semibold z-10">Rp</span>
                <input type="text" inputmode="numeric" autocomplete="off"
                       class="form-input pl-9 font-semibold tracking-wide"
                       x-bind:value="formatId(overtime)"
                       @input="overtime = parseId($event.target.value)"
                       @blur="$event.target.value = formatId(overtime)"
                       placeholder="0">
                <input type="hidden" name="overtime" x-bind:value="overtime">
            </div>
        </div>
        <div>
            <label class="form-label text-xs font-semibold mb-1 block">Tunjangan / Bonus</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-semibold z-10">Rp</span>
                <input type="text" inputmode="numeric" autocomplete="off"
                       class="form-input pl-9 font-semibold tracking-wide"
                       x-bind:value="formatId(allowance)"
                       @input="allowance = parseId($event.target.value)"
                       @blur="$event.target.value = formatId(allowance)"
                       placeholder="0">
                <input type="hidden" name="allowance" x-bind:value="allowance">
            </div>
        </div>
    </div>

    <div class="bg-rose-50/30 p-3 rounded-xl border border-rose-100/50 space-y-3">
        <h4 class="text-xs font-bold text-rose-800 uppercase tracking-wider">II. POTONGAN (DEDUCTIONS)</h4>
        <div>
            <label class="form-label text-xs font-semibold mb-1 block">BPJS Kesehatan</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-semibold z-10">Rp</span>
                <input type="text" inputmode="numeric" autocomplete="off"
                       class="form-input pl-9 font-semibold tracking-wide text-rose-600"
                       x-bind:value="formatId(bpjs_kes)"
                       @input="bpjs_kes = parseId($event.target.value)"
                       @blur="$event.target.value = formatId(bpjs_kes)"
                       placeholder="0">
                <input type="hidden" name="bpjs_kesehatan" x-bind:value="bpjs_kes">
            </div>
        </div>
        <div>
            <label class="form-label text-xs font-semibold mb-1 block">BPJS Ketenagakerjaan</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-semibold z-10">Rp</span>
                <input type="text" inputmode="numeric" autocomplete="off"
                       class="form-input pl-9 font-semibold tracking-wide text-rose-600"
                       x-bind:value="formatId(bpjs_ket)"
                       @input="bpjs_ket = parseId($event.target.value)"
                       @blur="$event.target.value = formatId(bpjs_ket)"
                       placeholder="0">
                <input type="hidden" name="bpjs_ketenagakerjaan" x-bind:value="bpjs_ket">
            </div>
        </div>
        <div>
            <label class="form-label text-xs font-semibold mb-1 block">Potongan Lainnya</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-semibold z-10">Rp</span>
                <input type="text" inputmode="numeric" autocomplete="off"
                       class="form-input pl-9 font-semibold tracking-wide text-rose-600"
                       x-bind:value="formatId(deduction)"
                       @input="deduction = parseId($event.target.value)"
                       @blur="$event.target.value = formatId(deduction)"
                       placeholder="0">
                <input type="hidden" name="deduction" x-bind:value="deduction">
            </div>
        </div>
    </div>
</div>
