<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom');
            $table->string('prenom');
            $table->string('login')->unique();
            $table->string('telephone')->unique();
            $table->enum('status', ['Actif', 'Inactif']);
            $table->string('cni')->unique();
            $table->string('code');
            $table->enum('sexe', ['Homme', 'Femme']);
            $table->enum('role', ['Admin', 'Client']);
            $table->integer('is_verified')->default(0);
            $table->date('date_naissance');
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
