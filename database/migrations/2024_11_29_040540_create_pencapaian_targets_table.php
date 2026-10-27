<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePencapaianTargetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('pencapaian_target')) {
            Schema::create('pencapaian_target', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('kelas_id');
                $table->unsignedBigInteger('murid_id');
                $table->unsignedBigInteger('kurikulum_target_detail_id');
                $table->integer('target')->nullable();
                $table->tinyInteger('bulan');
                $table->bigInteger('tahun');
                $table->timestamps();

                $table->foreign('kelas_id')->references('id')->on('m_kelas')->onDelete('cascade');
                $table->foreign('murid_id')->references('id')->on('murid')->onDelete('cascade');
                $table->foreign('kurikulum_target_detail_id')->references('id')->on('kurikulum_target_detail')->onDelete('cascade');
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
        Schema::dropIfExists('pencapaian_target');
    }
}
