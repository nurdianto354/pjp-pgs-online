<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoordinatesToKelompokMLokasiTable extends Migration
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
                if (!Schema::hasColumn('kelompok_m_lokasi', 'latitude')) {
                    $table->decimal('latitude', 10, 8)->nullable()->after('alamat');
                }
                if (!Schema::hasColumn('kelompok_m_lokasi', 'longitude')) {
                    $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
                }
                if (!Schema::hasColumn('kelompok_m_lokasi', 'radius')) {
                    $table->integer('radius')->default(100)->comment('Radius absensi dalam meter')->after('longitude');
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
            if (Schema::hasColumn('kelompok_m_lokasi', 'radius')) {
                $table->dropColumn('radius');
            }
            if (Schema::hasColumn('kelompok_m_lokasi', 'longitude')) {
                $table->dropColumn('longitude');
            }
            if (Schema::hasColumn('kelompok_m_lokasi', 'latitude')) {
                $table->dropColumn('latitude');
            }
        });
    }
}

