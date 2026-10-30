<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
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

            // Ubah tipe kolom alamat menjadi TEXT NULL jika masih varchar
            try {
                DB::statement('ALTER TABLE kelompok_m_lokasi MODIFY alamat TEXT NULL');
            } catch (\Throwable $th) {
                // Abaikan jika database engine tidak mengizinkan ALTER MODIFY
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('kelompok_m_lokasi')) {
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
                if (Schema::hasColumn('kelompok_m_lokasi', 'kelompok_id')) {
                    $table->dropColumn('kelompok_id');
                }
            });
        }
    }
};

