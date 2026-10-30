<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKelompokIdToKelompokMLokasiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('kelompok_m_lokasi')) {
            Schema::table('kelompok_m_lokasi', function (Blueprint $table) {
                if (!Schema::hasColumn('kelompok_m_lokasi', 'kelompok_id')) {
                    $table->unsignedBigInteger('kelompok_id')->nullable()->after('id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kelompok_m_lokasi', function (Blueprint $table) {
            if (Schema::hasColumn('kelompok_m_lokasi', 'kelompok_id')) {
                $table->dropColumn('kelompok_id');
            }
        });
    }
}

