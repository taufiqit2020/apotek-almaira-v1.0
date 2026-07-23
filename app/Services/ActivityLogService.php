<?php
namespace App\Services;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;
class ActivityLogService {
    public static function log(string $action, string $module = '', string $description = '', ?int $userId = null, $oldData = null, $newData = null): void {
        try {
            if (! Schema::hasTable('activity_logs')) {
                return;
            }

            ActivityLog::create([
                'user_id' => $userId ?? Auth::id(),
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'ip_address' => Request::ip(),
                'user_agent' => substr(Request::userAgent() ?? '', 0, 500),
                'old_data' => $oldData ? json_encode($oldData) : null,
                'new_data' => $newData ? json_encode($newData) : null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('ActivityLog error: ' . $e->getMessage());
        }
    }
    public static function login(string $userName, ?string $ip = null): void { self::log('LOGIN', 'Auth', "User {$userName} berhasil login. IP: " . ($ip ?? Request::ip())); }
    public static function logout(string $userName): void { self::log('LOGOUT', 'Auth', "User {$userName} logout"); }
    public static function created(string $module, string $detail, $newData = null): void { self::log('CREATE', $module, "Membuat data: {$detail}", null, null, $newData); }
    public static function updated(string $module, string $detail, $oldData = null, $newData = null): void { self::log('UPDATE', $module, "Memperbarui data: {$detail}", null, $oldData, $newData); }
    public static function deleted(string $module, string $detail, $oldData = null): void { self::log('DELETE', $module, "Menghapus data: {$detail}", null, $oldData, null); }
    public static function transaction(string $invoiceNo, float $total): void { self::log('TRANSACTION', 'POS', "Transaksi {$invoiceNo} — Total: Rp " . number_format($total, 0, ',', '.')); }
}
