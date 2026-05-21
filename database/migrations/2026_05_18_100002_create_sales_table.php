<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('client_nom')->nullable();
            $table->string('client_tel')->nullable();
            $table->decimal('montant_total', 12, 2);
            $table->decimal('montant_paye', 12, 2)->default(0);
            $table->enum('mode_paiement', ['especes', 'wave', 'orange_money', 'carte'])->default('especes');
            $table->enum('statut', ['payee', 'partielle', 'credit'])->default('payee');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
