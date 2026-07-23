<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $links = [
            'taufiq' => ['taufiqurrahman'],
            'siti' => ['siti kamariah'],
            'alya' => ['alya iqlima', 'alyaiqlima'],
        ];

        $employees = DB::table('employees')->whereNull('deleted_at')->get(['id', 'name', 'photo', 'user_id']);
        $users = DB::table('users')->whereNull('deleted_at')->get(['id', 'username', 'name', 'avatar']);

        foreach ($users as $user) {
            $username = strtolower((string) $user->username);
            $needles = $links[$username] ?? [strtolower((string) $user->name)];

            $matched = $employees->first(function ($employee) use ($needles, $user) {
                if ((int) $employee->user_id === (int) $user->id) {
                    return true;
                }
                $empName = strtolower((string) $employee->name);
                foreach ($needles as $needle) {
                    if ($needle !== '' && str_contains($empName, $needle)) {
                        return true;
                    }
                }

                return false;
            });

            if (! $matched) {
                continue;
            }

            if ((int) ($matched->user_id ?? 0) !== (int) $user->id) {
                DB::table('employees')->where('id', $matched->id)->update([
                    'user_id' => $user->id,
                    'updated_at' => now(),
                ]);
            }

            $hasAvatar = $user->avatar && is_file(public_path($user->avatar));
            $hasEmployeePhoto = $matched->photo && is_file(public_path($matched->photo));
            if (! $hasAvatar && $hasEmployeePhoto) {
                DB::table('users')->where('id', $user->id)->update([
                    'avatar' => $matched->photo,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Non-destructive: keep linked photos/user_id as-is.
    }
};
