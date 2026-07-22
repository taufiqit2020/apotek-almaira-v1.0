@php
    $isEdit = isset($employee);
    $action = $isEdit ? route('employees.update', $employee) : route('employees.store');
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-5 pb-28">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Foto & status --}}
        <section class="card overflow-hidden bg-white border border-gray-100 rounded-2xl shadow-sm h-fit">
            <div class="px-5 py-3.5 border-b border-gray-100 bg-slate-50/80">
                <h3 class="text-sm font-bold text-gray-800">Foto Karyawan</h3>
                <p class="text-[11px] text-gray-400">Opsional · JPG/PNG · max 2MB</p>
            </div>
            <div class="p-5 space-y-4">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-36 h-36 sm:w-40 sm:h-40 rounded-2xl border border-slate-100 bg-gradient-to-br from-slate-50 to-emerald-50/50 shadow-sm overflow-hidden flex items-center justify-center p-2">
                        @if($isEdit && $employee->photo_url)
                        <img src="{{ $employee->photo_url }}" alt="{{ $employee->name }}"
                             class="max-w-full max-h-full w-auto h-auto object-contain object-center rounded-xl">
                        @else
                        <div class="w-full h-full rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center text-3xl font-black">
                            {{ $isEdit ? $employee->initials : 'K' }}
                        </div>
                        @endif
                    </div>
                    <p class="text-[11px] text-slate-400 text-center leading-relaxed">Foto ditampilkan utuh (tidak terpotong). Disarankan rasio 1:1.</p>
                    <input type="file" name="photo" accept="image/*" class="form-input text-xs rounded-xl w-full">
                    @error('photo')<p class="form-error">{{ $message }}</p>@enderror
                    @if($isEdit && $employee->photo)
                    <label class="flex items-center gap-2 text-xs text-slate-500 cursor-pointer">
                        <input type="checkbox" name="remove_photo" value="1" class="rounded border-slate-300">
                        Hapus foto saat ini
                    </label>
                    @endif
                </div>

                @if($isEdit)
                <div>
                    <label class="form-label font-bold">Status</label>
                    <select name="is_active" class="form-input rounded-xl">
                        <option value="1" {{ old('is_active', $employee->is_active) ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ !old('is_active', $employee->is_active) ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                @endif
            </div>
        </section>

        {{-- Identitas --}}
        <section class="lg:col-span-2 card overflow-hidden bg-white border border-gray-100 rounded-2xl shadow-sm">
            <div class="px-5 py-3.5 border-b border-gray-100 bg-slate-50/80">
                <h3 class="text-sm font-bold text-gray-800">Identitas Karyawan</h3>
                <p class="text-[11px] text-gray-400">Data utama untuk slip gaji dan arsip HR</p>
            </div>
            <div class="p-5 sm:p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label font-bold">Kode Karyawan</label>
                    <input type="text" name="code" value="{{ old('code', $employee->code ?? $nextCode) }}" class="form-input rounded-xl font-mono" placeholder="KRY-0001">
                    @error('code')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label font-bold">Lingkup Entitas <span class="text-red-500">*</span></label>
                    <select name="entity_scope" class="form-input rounded-xl" required>
                        @foreach($entityScopes as $key => $label)
                        <option value="{{ $key }}" {{ old('entity_scope', $employee->entity_scope ?? 'both') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Karyawan bisa digaji di satu atau kedua entitas.</p>
                </div>

                <div class="md:col-span-2">
                    <label class="form-label font-bold">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $employee->name ?? '') }}" class="form-input rounded-xl {{ $errors->has('name') ? 'error' : '' }}" required placeholder="Contoh: Ahmad Fauzi">
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <label class="form-label font-bold mb-0">Jabatan</label>
                        <a wire:navigate href="{{ route('job-positions.index') }}" class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700">
                            + Kelola Jabatan
                        </a>
                    </div>
                    <select name="job_position_id" class="form-input rounded-xl {{ $errors->has('job_position_id') ? 'error' : '' }}">
                        <option value="">— Pilih jabatan —</option>
                        @foreach($jobPositions as $job)
                        <option value="{{ $job->id }}" {{ (string) old('job_position_id', $employee->job_position_id ?? '') === (string) $job->id ? 'selected' : '' }}>
                            {{ $job->name }}{{ $job->is_active ? '' : ' (nonaktif)' }}
                        </option>
                        @endforeach
                    </select>
                    @error('job_position_id')<p class="form-error">{{ $message }}</p>@enderror
                    @if($jobPositions->isEmpty())
                    <p class="text-[11px] text-amber-600 mt-1">Belum ada jabatan. <a wire:navigate href="{{ route('job-positions.index') }}" class="font-bold underline">Buat di Master Jabatan</a>.</p>
                    @else
                    <p class="text-[11px] text-slate-400 mt-1">Pilihan diambil otomatis dari Master Jabatan.</p>
                    @endif
                </div>
                <div>
                    <label class="form-label font-bold">Jenis Kelamin</label>
                    <select name="gender" class="form-input rounded-xl">
                        <option value="">— Pilih —</option>
                        <option value="laki-laki" {{ old('gender', $employee->gender ?? '') === 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="perempuan" {{ old('gender', $employee->gender ?? '') === 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <div>
                    <label class="form-label font-bold">Tanggal Masuk</label>
                    <input type="date" name="join_date" value="{{ old('join_date', isset($employee) && $employee->join_date ? $employee->join_date->format('Y-m-d') : '') }}" class="form-input rounded-xl">
                </div>
                <div>
                    <label class="form-label font-bold">Tanggal Lahir</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date', isset($employee) && $employee->birth_date ? $employee->birth_date->format('Y-m-d') : '') }}" class="form-input rounded-xl">
                </div>

                <div>
                    <label class="form-label font-bold">NIK</label>
                    <input type="text" name="nik" value="{{ old('nik', $employee->nik ?? '') }}" class="form-input rounded-xl font-mono" placeholder="16 digit NIK">
                </div>
                <div>
                    <label class="form-label font-bold">Telepon / WA</label>
                    <input type="text" name="phone" value="{{ old('phone', $employee->phone ?? '') }}" class="form-input rounded-xl" placeholder="08xx-xxxx-xxxx">
                </div>

                <div class="md:col-span-2">
                    <label class="form-label font-bold">Email</label>
                    <input type="email" name="email" value="{{ old('email', $employee->email ?? '') }}" class="form-input rounded-xl" placeholder="email@contoh.com">
                </div>

                <div class="md:col-span-2">
                    <label class="form-label font-bold">Alamat</label>
                    <textarea name="address" rows="2" class="form-input rounded-xl" placeholder="Alamat domisili karyawan">{{ old('address', $employee->address ?? '') }}</textarea>
                </div>
            </div>
        </section>

        {{-- Bank & tautan akun --}}
        <section class="lg:col-span-3 card overflow-hidden bg-white border border-gray-100 rounded-2xl shadow-sm">
            <div class="px-5 py-3.5 border-b border-gray-100 bg-slate-50/80">
                <h3 class="text-sm font-bold text-gray-800">Rekening & Tautan Sistem</h3>
                <p class="text-[11px] text-gray-400">Opsional — untuk transfer gaji dan tautan akun login</p>
            </div>
            <div class="p-5 sm:p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label font-bold">Bank</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $employee->bank_name ?? '') }}" class="form-input rounded-xl" placeholder="BNI / BCA / Mandiri">
                </div>
                <div>
                    <label class="form-label font-bold">No. Rekening</label>
                    <input type="text" name="bank_account" value="{{ old('bank_account', $employee->bank_account ?? '') }}" class="form-input rounded-xl font-mono" placeholder="Nomor rekening">
                </div>
                <div>
                    <label class="form-label font-bold">Atas Nama</label>
                    <input type="text" name="bank_holder" value="{{ old('bank_holder', $employee->bank_holder ?? '') }}" class="form-input rounded-xl" placeholder="Nama pemilik rekening">
                </div>
                <div class="md:col-span-2">
                    <label class="form-label font-bold">Tautkan Akun Login (opsional)</label>
                    <select name="user_id" class="form-input rounded-xl">
                        <option value="">— Tidak ditautkan —</option>
                        @foreach($users as $u)
                        @php $taken = $linkedUserIds->contains($u->id); @endphp
                        <option value="{{ $u->id }}"
                            {{ (string) old('user_id', $employee->user_id ?? '') === (string) $u->id ? 'selected' : '' }}
                            @disabled($taken)>
                            {{ $u->name }} ({{ $u->role->name ?? '-' }}){{ $taken ? ' — sudah ditautkan' : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="form-label font-bold">Catatan</label>
                    <textarea name="notes" rows="2" class="form-input rounded-xl" placeholder="Catatan internal...">{{ old('notes', $employee->notes ?? '') }}</textarea>
                </div>
            </div>
        </section>
    </div>

    <div class="fixed bottom-[4.75rem] left-0 right-0 z-30 px-4 pointer-events-none lg:pl-64">
        <div class="max-w-6xl mx-auto pointer-events-auto">
            <div class="rounded-2xl bg-white/95 backdrop-blur border border-gray-100 shadow-lg px-4 py-3 flex flex-col sm:flex-row gap-2 sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500">Data ini dipakai untuk membuat slip gaji per entitas.</p>
                <div class="flex gap-2">
                    <a wire:navigate href="{{ route('employees.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Karyawan' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
