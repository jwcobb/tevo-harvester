<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixHarvestsForDeleted extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("UPDATE harvests SET library_method = 'listCategoriesDeleted', last_run_at = '2010-01-01' WHERE resource = 'categories' AND action = 'deleted';");
        DB::statement("UPDATE harvests SET library_method = 'listEventsDeleted', last_run_at = '2010-01-01' WHERE resource = 'events' AND action = 'deleted';");
        DB::statement("UPDATE harvests SET library_method = 'listPerformersDeleted', last_run_at = '2010-01-01' WHERE resource = 'performers' AND action = 'deleted';");
        DB::statement("UPDATE harvests SET library_method = 'listVenuesDeleted', last_run_at = '2010-01-01' WHERE resource = 'venues' AND action = 'deleted';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("UPDATE harvests SET library_method = 'listCategories', last_run_at = '2010-01-01' WHERE resource = 'categories' AND action = 'deleted';");
        DB::statement("UPDATE harvests SET library_method = 'listEvents', last_run_at = '2010-01-01' WHERE resource = 'events' AND action = 'deleted';");
        DB::statement("UPDATE harvests SET library_method = 'listPerformers', last_run_at = '2010-01-01' WHERE resource = 'performers' AND action = 'deleted';");
        DB::statement("UPDATE harvests SET library_method = 'listVenues', last_run_at = '2010-01-01' WHERE resource = 'venues' AND action = 'deleted';");
    }
}
