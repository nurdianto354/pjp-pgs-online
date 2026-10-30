<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        if (Schema::hasTable('kelompok_m_jamaah') && Schema::hasColumn('kelompok_m_jamaah', 'kelompok_id')) {
            Schema::table('kelompok_m_jamaah', function (Blueprint $table) {
                $table->dropForeign(['kelompok_id']);
                $table->dropColumn('kelompok_id');
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
        if (Schema::hasTable('kelompok_m_jamaah') && !Schema::hasColumn('kelompok_m_jamaah', 'kelompok_id')) {
            Schema::table('kelompok_m_jamaah', function (Blueprint $table) {
                $table->unsignedBigInteger('kelompok_id')->nullable()->after('id')->index();
                $table->foreign('kelompok_id')->references('id')->on('kelompok_m_kelompok')->onDelete('set null');
            });
        }
    }
};

