<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('login_attempts', function (Blueprint $table) {
            if (! Schema::hasColumn('login_attempts', 'attempts')) {
                $table->integer('attempts')->default(0)->after('email');
            }
            if (! Schema::hasColumn('login_attempts', 'locked_until')) {
                $table->timestamp('locked_until')->nullable()->after('attempts');
            }
            if (! Schema::hasColumn('login_attempts', 'last_attempt')) {
                $table->timestamp('last_attempt')->useCurrent()->after('locked_until');
            }
        });
    }

    public function down(): void
    {
        Schema::table('login_attempts', function (Blueprint $table) {
            $cols = collect(['attempts', 'locked_until', 'last_attempt'])
                ->filter(fn ($c) => Schema::hasColumn('login_attempts', $c))
                ->all();

            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
};
