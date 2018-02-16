<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFreeSpotsClaimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('free_spots_claims', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('free_spot_id');
            $table->string('claimer_user');
            $table->date('date_claimed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('free_spots_claims');
    }
}
