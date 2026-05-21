<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('contact')->nullable();
            $table->string('telephone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('adresse')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->enum('statut', ['en_cours', 'recue', 'annulee'])->default('en_cours');
            $table->decimal('montant_total', 12, 2)->default(0);
            $table->date('date_commande');
            $table->date('date_reception')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantite');
            $table->decimal('prix_unitaire', 12, 2);
            $table->decimal('sous_total', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('suppliers');
    }
};
