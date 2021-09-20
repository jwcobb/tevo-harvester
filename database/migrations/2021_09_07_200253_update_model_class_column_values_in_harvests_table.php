<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateModelClassColumnValuesInHarvestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::update("UPDATE `harvests` SET `model_class` = 'App\\\Models\\\Tevo\\\Brokerage' WHERE `resource` = 'brokerages'");
        DB::update("UPDATE `harvests` SET `model_class` = 'App\\\Models\\\Tevo\\\Category' WHERE `resource` = 'categories'");
        DB::update("UPDATE `harvests` SET `model_class` = 'App\\\Models\\\Tevo\\\Configuration' WHERE `resource` = 'configurations'");
        DB::update("UPDATE `harvests` SET `model_class` = 'App\\\\Models\\\\Tevo\\\\Event' WHERE `resource` = 'events'");
        DB::update("UPDATE `harvests` SET `model_class` = 'App\\\Models\\\Tevo\\\Office' WHERE `resource` = 'offices'");
        DB::update("UPDATE `harvests` SET `model_class` = 'App\\\Models\\\Tevo\\\Performer' WHERE `resource` = 'performers'");
        DB::update("UPDATE `harvests` SET `model_class` = 'App\\\Models\\\Tevo\\\Venue' WHERE `resource` = 'venues'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('harvests', function (Blueprint $table) {
            //
        });
    }
}
