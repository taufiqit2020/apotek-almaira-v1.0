<?php

use App\Models\Employee;
use App\Models\Role;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->after('user_id')->constrained('employees')->nullOnDelete();
            $table->index(['employee_id', 'entity', 'period_year', 'period_month']);
        });

        // Seed karyawan dari user staff (bukan mitra), lalu tautkan gaji lama.
        $staffUsers = User::with('role')
            ->whereHas('role', fn ($q) => $q->whereIn('slug', [
                Role::SUPER_ADMIN,
                Role::ADMIN_KEUANGAN,
                Role::KASIR,
            ]))
            ->orderBy('id')
            ->get();

        $seq = 1;
        foreach ($staffUsers as $user) {
            $code = 'KRY-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
            while (Employee::withTrashed()->where('code', $code)->exists()) {
                $seq++;
                $code = 'KRY-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
            }

            $employee = Employee::create([
                'code' => $code,
                'name' => $user->name,
                'position' => $user->role?->name,
                'entity_scope' => 'both',
                'email' => $user->email,
                'user_id' => $user->id,
                'is_active' => (bool) $user->is_active,
                'notes' => 'Diimpor otomatis dari akun user sistem.',
            ]);

            Salary::withTrashed()
                ->where('user_id', $user->id)
                ->whereNull('employee_id')
                ->update(['employee_id' => $employee->id]);

            $seq++;
        }

        // Gaji yang user-nya bukan staff: buat karyawan minimal dari nama user.
        $orphanUserIds = Salary::withTrashed()
            ->whereNull('employee_id')
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        foreach ($orphanUserIds as $userId) {
            $user = User::withTrashed()->find($userId);
            if (! $user) {
                continue;
            }

            $code = 'KRY-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
            while (Employee::withTrashed()->where('code', $code)->exists()) {
                $seq++;
                $code = 'KRY-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
            }

            $employee = Employee::create([
                'code' => $code,
                'name' => $user->name ?: ('Karyawan #'.$user->id),
                'position' => $user->role?->name,
                'entity_scope' => 'both',
                'email' => $user->email,
                'user_id' => $user->id,
                'is_active' => (bool) $user->is_active,
                'notes' => 'Diimpor otomatis dari data gaji lama.',
            ]);

            Salary::withTrashed()
                ->where('user_id', $user->id)
                ->whereNull('employee_id')
                ->update(['employee_id' => $employee->id]);

            $seq++;
        }
    }

    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employee_id');
        });
    }
};
