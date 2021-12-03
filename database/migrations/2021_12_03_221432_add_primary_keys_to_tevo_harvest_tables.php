<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrimaryKeysToTevoHarvestTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('brokerages', function (Blueprint $table) {
            $table->primary('id');
        });
        Schema::table('offices', function (Blueprint $table) {
            $table->primary('id');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->primary('id');
        });
        Schema::table('venues', function (Blueprint $table) {
            $table->primary('id');
        });
        Schema::table('configurations', function (Blueprint $table) {
            $table->primary('id');
        });
        Schema::table('performers', function (Blueprint $table) {
            $table->primary('id');
        });
        Schema::table('events', function (Blueprint $table) {
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('brokerages', function (Blueprint $table) {
            $table->dropPrimary('brokerages_id_primary');
        });
        Schema::table('offices', function (Blueprint $table) {
            $table->dropPrimary('offices_id_primary');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropPrimary('categories_id_primary');
        });
        Schema::table('venues', function (Blueprint $table) {
            $table->dropPrimary('venues_id_primary');
        });
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropPrimary('configurations_id_primary');
        });
        Schema::table('performers', function (Blueprint $table) {
            $table->dropPrimary('performers_id_primary');
        });
        Schema::table('events', function (Blueprint $table) {
            $table->dropPrimary('events_id_primary');
        });
    }
}
