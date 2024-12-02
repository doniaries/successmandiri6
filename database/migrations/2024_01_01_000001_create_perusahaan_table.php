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
            $table->string('name')->unique();
            $table->decimal('saldo', 15, 0)->default(0);
            $table->decimal('saldo_kas', 15, 0)->default(0)->comment('Saldo kas fisik/tunai');  // [NEW]
            $table->decimal('saldo_bank', 15, 0)->default(0)->comment('Saldo di rekening bank'); // [NEW]
            $table->string('alamat')->nullable();
            $table->string('telepon')->nullable();
            $table->string('email')->nullable();
            $table->string('pimpinan')->nullable()->comment('Pimpinan Perusahaan');
            $table->string('npwp', 30)->nullable();
            $table->string('logo')->nullable()->comment('Logo Perusahaan');
            $table->boolean('is_active')->default(true)->comment('Status aktif perusahaan');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('telepon');
            $table->index('email');
            $table->index('npwp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perusahaans');
    }
};
