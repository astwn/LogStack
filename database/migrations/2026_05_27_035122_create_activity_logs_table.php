<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('username')->nullable();
            $table->string('app', 50); // laravel, sogo, nextcloud, odoo
            $table->string('action', 50); // login, logout, open_app, send_email, dll
            $table->string('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            // Index untuk query cepat
            $table->index(['username', 'created_at']);
            $table->index(['app', 'action']);
            $table->index('created_at');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
