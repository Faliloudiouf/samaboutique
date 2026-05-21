<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $t) {
            $t->string('emoji', 8)->default('📦')->after('nom');
            $t->string('couleur_fond', 12)->default('#F5EFE6')->after('emoji');
            $t->string('couleur_accent', 12)->default('#C84B31')->after('couleur_fond');
        });

        Schema::table('products', function (Blueprint $t) {
            $t->string('emoji', 8)->nullable()->after('nom');
        });

        Schema::table('sales', function (Blueprint $t) {
            $t->decimal('remise', 12, 2)->default(0)->after('montant_total');
            $t->date('echeance')->nullable()->after('statut');
        });
        // ENUM extension via raw SQL (compat MySQL/MariaDB)
        DB::statement("ALTER TABLE sales MODIFY statut ENUM('payee','partielle','credit','annulee') DEFAULT 'payee'");

        Schema::table('customers', function (Blueprint $t) {
            $t->string('etiquette', 30)->nullable()->after('notes'); // ex: "cliente VIP", "épicier voisin"
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $t) {
            $t->dropColumn(['emoji', 'couleur_fond', 'couleur_accent']);
        });
        Schema::table('products', fn($t) => $t->dropColumn('emoji'));
        Schema::table('sales', fn($t) => $t->dropColumn(['remise', 'echeance']));
        Schema::table('customers', fn($t) => $t->dropColumn('etiquette'));
    }
};
