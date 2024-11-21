<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perusahaans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->index();
            $table->decimal('saldo', 15, 0)->default(0);
            $table->string('alamat')->nullable();
            $table->string('telepon')->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->string('pimpinan')->nullable()->comment('Pimpinan Perusahaan');
            $table->string('npwp', 30)->nullable()->index();
            $table->string('logo')->nullable()->comment('Logo Perusahaan');
            $table->boolean('is_active')->default(true)->comment('Status aktif perusahaan');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perusahaans');
    }
};
