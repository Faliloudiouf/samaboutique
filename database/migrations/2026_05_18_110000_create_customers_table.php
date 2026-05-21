<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('telephone', 30)->nullable();
            $table->string('adresse')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('nom');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });

        Schema::create('customer_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->decimal('montant', 12, 2);
            $table->enum('mode_paiement', ['especes', 'wave', 'orange_money', 'carte'])->default('especes');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_payments');
        Schema::table('sales', fn($t) => $t->dropConstrainedForeignId('customer_id'));
        Schema::dropIfExists('customers');
    }
};
