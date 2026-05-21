<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('photo')->nullable()->after('password');
            $t->string('telephone', 30)->nullable()->after('email');
            $t->timestamp('suspended_at')->nullable()->after('actif');
        });

        Schema::table('categories', function (Blueprint $t) {
            $t->string('image')->nullable()->after('emoji');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['photo', 'telephone', 'suspended_at']);
        });
        Schema::table('categories', fn($t) => $t->dropColumn('image'));
    }
};
