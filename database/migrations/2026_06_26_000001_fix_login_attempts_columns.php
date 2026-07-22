<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        // Get existing columns in login_attempts table
        $columns = DB::select("PRAGMA table_info(login_attempts)");
        $existingColumns = array_map(fn($c) => $c->name, $columns);

        Schema::table('login_attempts', function (Blueprint $table) use ($existingColumns) {
            if (!in_array('attempts', $existingColumns)) {
                $table->integer('attempts')->default(0)->after('email');
            }
            if (!in_array('locked_until', $existingColumns)) {
                $table->timestamp('locked_until')->nullable()->after('attempts');
            }
            if (!in_array('last_attempt', $existingColumns)) {
                $table->timestamp('last_attempt')->useCurrent()->after('locked_until');
            }
        });
    }

    public function down(): void {
        Schema::table('login_attempts', function (Blueprint $table) {
            $table->dropColumn(['attempts', 'locked_until', 'last_attempt']);
        });
    }
};
