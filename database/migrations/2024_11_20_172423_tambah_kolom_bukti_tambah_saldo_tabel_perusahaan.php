<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('laporan_keuangan', function (Blueprint $table) {
            $table->string('bukti_tambah_saldo')->nullable()->after('keterangan');
        });
    }

    public function down()
    {
        Schema::table('laporan_keuangan', function (Blueprint $table) {
            $table->dropColumn('bukti_tambah_saldo');
        });
    }
};
