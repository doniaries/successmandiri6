<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OptimizeTransaksiDoIndexes extends Migration
{
    public function up()
    {
        Schema::table('transaksi_do', function (Blueprint $table) {
            // Index untuk filtering dan sorting cepat
            $table->index(['tanggal', 'cara_bayar']);
            $table->index(['penjual_id', 'tanggal']);
            $table->index(['supir_id', 'tanggal']);
            $table->index(['kendaraan_id', 'tanggal']);
            
            // Composite index untuk query kompleks
            $table->index(['tanggal', 'penjual_id', 'cara_bayar']);
        });
    }

    public function down()
    {
        Schema::table('transaksi_do', function (Blueprint $table) {
            $table->dropIndex(['tanggal', 'cara_bayar']);
            $table->dropIndex(['penjual_id', 'tanggal']);
            $table->dropIndex(['supir_id', 'tanggal']);
            $table->dropIndex(['kendaraan_id', 'tanggal']);
            $table->dropIndex(['tanggal', 'penjual_id', 'cara_bayar']);
        });
    }
}
