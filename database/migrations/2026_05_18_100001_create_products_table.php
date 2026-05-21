<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->decimal('prix_achat', 12, 2)->default(0);
            $table->decimal('prix_vente', 12, 2);
            $table->integer('stock')->default(0);
            $table->integer('seuil_alerte')->default(5);
            $table->string('image')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
