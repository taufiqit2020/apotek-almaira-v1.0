{{-- ===== LOG AKTIVITAS TEMPLATE - Beautiful Modern Design ===== --}}

@php
    // Color-coded badge mapping for modules
    $moduleBadgeMap = [
        'AUTH'           => ['bg' => '#dbeafe', 'color' => '#1e40af', 'icon' => '🔐'],
        'LAPORAN'        => ['bg' => '#f0fdf4', 'color' => '#166534', 'icon' => '📊'],
        'GAJI KARYAWAN'  => ['bg' => '#fef9c3', 'color' => '#854d0e', 'icon' => '💰'],
        'PENJUALAN'      => ['bg' => '#ffe4e6', 'color' => '#9f1239', 'icon' => '🛒'],
        'PEMBELIAN'      => ['bg' => '#fce7f3', 'color' => '#831843', 'icon' => '📦'],
        'PRODUK'         => ['bg' => '#f0f9ff', 'color' => '#075985', 'icon' => '💊'],
        'STOK'           => ['bg' => '#fff7ed', 'color' => '#9a3412', 'icon' => '📋'],
        'PENGGUNA'       => ['bg' => '#f5f3ff', 'color' => '#5b21b6', 'icon' => '👤'],
        'KATEGORI'       => ['bg' => '#ecfdf5', 'color' => '#065f46', 'icon' => '🏷️'],
        'SUPPLIER'       => ['bg' => '#fef3c7', 'color' => '#92400e', 'icon' => '🏭'],
        'KASIR'          => ['bg' => '#ede9fe', 'color' => '#4c1d95', 'icon' => '💳'],
        'QRIS'           => ['bg' => '#f0fdf4', 'color' => '#14532d', 'icon' => '📱'],
    ];

    // Action type color mapping
    $actionColorMap = [
        'LOGIN'           => ['color' => '#059669', 'bg' => '#d1fae5'],
        'LOGOUT'          => ['color' => '#dc2626', 'bg' => '#fee2e2'],
        'PRINT_REPORT'    => ['color' => '#0284c7', 'bg' => '#e0f2fe'],
        'EXPORT'          => ['color' => '#7c3aed', 'bg' => '#ede9fe'],
        'CREATE'          => ['color' => '#065f46', 'bg' => '#d1fae5'],
        'UPDATE'          => ['color' => '#92400e', 'bg' => '#fef3c7'],
        'DELETE'          => ['color' => '#991b1b', 'bg' => '#fee2e2'],
        'VIEW'            => ['color' => '#1e40af', 'bg' => '#dbeafe'],
    ];

    // Count per module
    $moduleStats = $data->groupBy(function($log) {
        return $log->module ?? 'AUTH';
    })->map->count()->sortByDesc(fn($v) => $v);

    // Count per action
    $actionStats = $data->groupBy('action')->map->count()->sortByDesc(fn($v) => $v);

    // Unique users
    $uniqueUsers = $data->pluck('user_id')->filter()->unique()->count();
@endphp

{{-- ======== STATISTICS PANEL ======== --}}
@if($data->count() > 0)
<div style="display: flex; gap: 10px; margin: 0 0 16px 0;">
    {{-- Total Log --}}
    <div style="flex: 1; background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; border-radius: 8px; padding: 10px 14px;">
        <div style="font-size: 8px; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.85; margin-bottom: 4px;">📋 Total Log</div>
        <div style="font-size: 20px; font-weight: 800; line-height: 1;">{{ number_format($data->count()) }}</div>
        <div style="font-size: 8px; opacity: 0.75; margin-top: 3px;">kejadian tercatat</div>
    </div>
    {{-- Unique Users --}}
    <div style="flex: 1; background: linear-gradient(135deg, #065f46, #10b981); color: white; border-radius: 8px; padding: 10px 14px;">
        <div style="font-size: 8px; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.85; margin-bottom: 4px;">👤 Pengguna Aktif</div>
        <div style="font-size: 20px; font-weight: 800; line-height: 1;">{{ $uniqueUsers }}</div>
        <div style="font-size: 8px; opacity: 0.75; margin-top: 3px;">user terlibat</div>
    </div>
    {{-- Login Events --}}
    <div style="flex: 1; background: linear-gradient(135deg, #5b21b6, #8b5cf6); color: white; border-radius: 8px; padding: 10px 14px;">
        <div style="font-size: 8px; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.85; margin-bottom: 4px;">🔐 Event Login</div>
        <div style="font-size: 20px; font-weight: 800; line-height: 1;">{{ $data->where('action', 'LOGIN')->count() }}</div>
        <div style="font-size: 8px; opacity: 0.75; margin-top: 3px;">kali masuk sistem</div>
    </div>
    {{-- Print/Export Events --}}
    <div style="flex: 1; background: linear-gradient(135deg, #92400e, #f59e0b); color: white; border-radius: 8px; padding: 10px 14px;">
        <div style="font-size: 8px; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.85; margin-bottom: 4px;">📊 Cetak / Ekspor</div>
        <div style="font-size: 20px; font-weight: 800; line-height: 1;">{{ $data->where('action', 'PRINT_REPORT')->count() }}</div>
        <div style="font-size: 8px; opacity: 0.75; margin-top: 3px;">laporan dicetak</div>
    </div>
    {{-- Top Module --}}
    <div style="flex: 1.5; background: linear-gradient(135deg, #0f172a, #334155); color: white; border-radius: 8px; padding: 10px 14px;">
        <div style="font-size: 8px; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.85; margin-bottom: 4px;">🏆 Modul Terbanyak</div>
        <div style="font-size: 13px; font-weight: 800; line-height: 1.2;">{{ $moduleStats->keys()->first() ?? '-' }}</div>
        <div style="font-size: 8px; opacity: 0.75; margin-top: 3px;">{{ $moduleStats->first() ?? 0 }} aktivitas</div>
    </div>
</div>
@endif

{{-- ======== MAIN DATA TABLE ======== --}}
<table class="report-table" style="font-size: 9px;">
    <thead>
        <tr>
            <th style="width: 6%; text-align: center;">#</th>
            <th style="width: 10%;">Waktu Kejadian</th>
            <th style="width: 13%;">Pengguna</th>
            <th style="width: 9%; text-align: center;">Modul</th>
            <th style="width: 11%; text-align: center;">Aksi / Tindakan</th>
            <th style="width: 37%;">Detail Aktivitas</th>
            <th style="width: 7%; text-align: center;">IP Address</th>
            <th style="width: 7%;">Perangkat</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $index => $log)
        @php
            $moduleName = strtoupper($log->module ?? 'AUTH');
            $badgeStyle = $moduleBadgeMap[$moduleName] ?? ['bg' => '#f1f5f9', 'color' => '#475569', 'icon' => '📌'];
            $action = strtoupper($log->action ?? '');

            // Determine action color
            $actionStyle = null;
            foreach ($actionColorMap as $key => $style) {
                if (str_contains($action, $key)) {
                    $actionStyle = $style;
                    break;
                }
            }
            $actionStyle = $actionStyle ?? ['color' => '#475569', 'bg' => '#f1f5f9'];

            // Parse user agent to a short device string
            $ua = $log->user_agent ?? '';
            $device = 'Desktop';
            if (str_contains($ua, 'Android')) $device = '📱 Android';
            elseif (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) $device = '📱 iOS';
            elseif (str_contains($ua, 'Windows')) $device = '💻 Windows';
            elseif (str_contains($ua, 'Mac')) $device = '🍎 Mac';
            elseif (str_contains($ua, 'Linux')) $device = '🐧 Linux';

            $browser = 'Browser';
            if (str_contains($ua, 'Chrome') && !str_contains($ua, 'Edge')) $browser = 'Chrome';
            elseif (str_contains($ua, 'Firefox')) $browser = 'Firefox';
            elseif (str_contains($ua, 'Edge')) $browser = 'Edge';
            elseif (str_contains($ua, 'Safari') && !str_contains($ua, 'Chrome')) $browser = 'Safari';
        @endphp
        <tr style="{{ $index % 2 === 0 ? 'background-color: #ffffff;' : 'background-color: #f8fafc;' }}">
            {{-- No --}}
            <td style="text-align: center; font-weight: 600; color: #94a3b8; font-size: 8px;">
                {{ $index + 1 }}
            </td>
            {{-- Waktu --}}
            <td>
                <div style="font-weight: 700; color: #1e293b; font-size: 9px;">
                    {{ $log->created_at->format('d/m/Y') }}
                </div>
                <div style="color: #64748b; font-size: 8px; font-family: monospace;">
                    {{ $log->created_at->format('H:i:s') }} WITA
                </div>
            </td>
            {{-- Pengguna --}}
            <td>
                <div style="font-weight: 700; color: #1e293b; font-size: 9px;">
                    {{ $log->user?->name ?? 'System' }}
                </div>
                @if($log->user?->username)
                <div style="color: #94a3b8; font-size: 7.5px;">@{{ $log->user->username }}</div>
                @endif
            </td>
            {{-- Modul Badge --}}
            <td style="text-align: center;">
                <span style="
                    display: inline-block;
                    background-color: {{ $badgeStyle['bg'] }};
                    color: {{ $badgeStyle['color'] }};
                    padding: 3px 6px;
                    border-radius: 4px;
                    font-size: 7.5px;
                    font-weight: 800;
                    text-transform: uppercase;
                    letter-spacing: 0.3px;
                    white-space: nowrap;
                ">
                    {{ $badgeStyle['icon'] }} {{ $moduleName }}
                </span>
            </td>
            {{-- Aksi --}}
            <td style="text-align: center;">
                <span style="
                    display: inline-block;
                    background-color: {{ $actionStyle['bg'] }};
                    color: {{ $actionStyle['color'] }};
                    padding: 3px 6px;
                    border-radius: 4px;
                    font-size: 7.5px;
                    font-weight: 700;
                    letter-spacing: 0.3px;
                    white-space: nowrap;
                ">
                    {{ $log->action }}
                </span>
            </td>
            {{-- Detail --}}
            <td style="color: #334155; font-size: 8.5px; line-height: 1.45;">
                {{ $log->description }}
            </td>
            {{-- IP Address --}}
            <td style="text-align: center; font-family: monospace; font-size: 8px; color: #475569; font-weight: 600;">
                {{ $log->ip_address ?? '-' }}
            </td>
            {{-- Device --}}
            <td style="font-size: 7.5px; color: #64748b;">
                <div>{{ $device }}</div>
                <div style="color: #94a3b8;">{{ $browser }}</div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" style="text-align: center; padding: 30px; color: #94a3b8; font-style: italic;">
                📋 Tidak ada log aktivitas dalam rentang waktu ini
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

{{-- ======== MODULE BREAKDOWN SUMMARY ======== --}}
@if($data->count() > 0)
<div style="margin-top: 18px; display: flex; gap: 14px; align-items: flex-start;">

    {{-- Ringkasan per Modul --}}
    <div style="flex: 1; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="background: #1e293b; color: white; padding: 7px 12px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
            📊 Distribusi Aktivitas per Modul
        </div>
        <div style="padding: 6px 10px;">
            @foreach($moduleStats as $module => $count)
            @php
                $moduleKey = strtoupper($module);
                $mBadge = $moduleBadgeMap[$moduleKey] ?? ['bg' => '#f1f5f9', 'color' => '#475569', 'icon' => '📌'];
                $percent = round(($count / $data->count()) * 100);
            @endphp
            <div style="display: flex; align-items: center; margin-bottom: 5px; gap: 6px;">
                <span style="background: {{ $mBadge['bg'] }}; color: {{ $mBadge['color'] }}; padding: 2px 5px; border-radius: 3px; font-size: 7.5px; font-weight: 700; min-width: 80px; text-align: center;">
                    {{ $mBadge['icon'] }} {{ $moduleKey }}
                </span>
                <div style="flex: 1; background: #f1f5f9; border-radius: 10px; height: 8px; overflow: hidden;">
                    <div style="width: {{ $percent }}%; background: {{ $mBadge['color'] }}; height: 100%; border-radius: 10px;"></div>
                </div>
                <span style="font-size: 8px; font-weight: 700; color: #334155; min-width: 55px; text-align: right;">{{ $count }} ({{ $percent }}%)</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Summary Box --}}
    <div style="min-width: 220px;">
        <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 10px;">
            <div style="background: #0f172a; color: white; padding: 7px 12px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                📈 Ringkasan Aksi Terbanyak
            </div>
            <div style="padding: 6px 10px;">
                @foreach($actionStats->take(5) as $action => $count)
                @php
                    $aStyle = null;
                    foreach ($actionColorMap as $key => $style) {
                        if (str_contains(strtoupper($action), $key)) { $aStyle = $style; break; }
                    }
                    $aStyle = $aStyle ?? ['color' => '#475569', 'bg' => '#f1f5f9'];
                @endphp
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 3px 0; border-bottom: 1px solid #f8fafc; font-size: 8.5px;">
                    <span style="background: {{ $aStyle['bg'] }}; color: {{ $aStyle['color'] }}; padding: 2px 5px; border-radius: 3px; font-weight: 700; font-size: 7.5px;">{{ $action }}</span>
                    <span style="font-weight: 700; color: #1e293b;">{{ $count }}x</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Grand Total Box --}}
        <div style="background: linear-gradient(135deg, #1e293b, #334155); color: white; border-radius: 8px; padding: 10px 14px; text-align: center;">
            <div style="font-size: 8px; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.75; margin-bottom: 4px;">Total Keseluruhan</div>
            <div style="font-size: 22px; font-weight: 800;">{{ number_format($data->count()) }}</div>
            <div style="font-size: 8px; opacity: 0.75; margin-top: 2px;">Log Aktivitas Sistem</div>
        </div>
    </div>

</div>
@endif

@include('reports.partials.signature')
