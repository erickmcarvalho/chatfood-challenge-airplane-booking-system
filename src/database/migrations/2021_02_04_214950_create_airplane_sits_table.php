<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAirplaneSitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('airplane_sits', function (Blueprint $table) {
            $table->id();
            $table->foreignId("airplane_id")->constrained();
            $table->string("name", 20);
            $table->enum("seat_side", [0, 1]);
            $table->tinyInteger("row");
            $table->tinyInteger("column");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('airplane_sits');
    }
}
