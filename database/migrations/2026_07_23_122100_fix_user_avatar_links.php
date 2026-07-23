<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $taufiqId = DB::table('users')->where('username', 'taufiq')->value('id');
        $taufiqopId = DB::table('users')->where('username', 'taufiqop')->value('id');
        $headPhoto = 'uploads/employees/emp_1784350637_6a5b07adc206c.jpeg';

        // Batalkan salah taut: foto Kepala Operasional sempat menempel ke STAFF OPERASIONAL.
        if ($taufiqopId) {
            $avatar = DB::table('users')->where('id', $taufiqopId)->value('avatar');
            if ($avatar === $headPhoto) {
                DB::table('users')->where('id', $taufiqopId)->update([
                    'avatar' => null,
                    'updated_at' => now(),
                ]);
            }
            DB::table('employees')
                ->where('user_id', $taufiqopId)
                ->where('photo', $headPhoto)
                ->update([
                    'user_id' => $taufiqId,
                    'updated_at' => now(),
                ]);
        }

        if ($taufiqId) {
            DB::table('employees')
                ->where('name', 'like', '%Taufiqurrahman%')
                ->update([
                    'user_id' => $taufiqId,
                    'updated_at' => now(),
                ]);
        }

        // Tautkan karyawan → user secara ketat (hanya mapping eksplisit).
        $links = [
            'taufiq' => '%Taufiqurrahman%',
            'siti' => '%Siti Kamariah%',
            'alya' => '%Alya%Iqlima%',
        ];

        foreach ($links as $username => $employeeLike) {
            $userId = DB::table('users')->where('username', $username)->value('id');
            if (! $userId) {
                continue;
            }

            $employee = DB::table('employees')
                ->whereNull('deleted_at')
                ->where('name', 'like', $employeeLike)
                ->first();

            if (! $employee) {
                continue;
            }

            DB::table('employees')->where('id', $employee->id)->update([
                'user_id' => $userId,
                'updated_at' => now(),
            ]);

            $userAvatar = DB::table('users')->where('id', $userId)->value('avatar');
            $hasAvatar = $userAvatar && is_file(public_path($userAvatar));
            $hasEmployeePhoto = $employee->photo && is_file(public_path($employee->photo));
            if (! $hasAvatar && $hasEmployeePhoto) {
                DB::table('users')->where('id', $userId)->update([
                    'avatar' => $employee->photo,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        //
    }
};
