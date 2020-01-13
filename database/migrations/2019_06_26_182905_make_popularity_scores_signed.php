<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MakePopularityScoresSigned extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /** doctrine/dbal can't handle ALTERing the `events` table because the `state`
         * column is an ENUM even though we aren’t touching that column.
         * Therefore just use raw SQL instead
         *
            Schema::table('events', function (Blueprint $table) {
                $table->decimal('popularity_score', 12, 6)->change();
                $table->decimal('short_term_popularity_score', 7, 6)->change();
                $table->decimal('long_term_popularity_score', 7, 6)->change();
            });

            Schema::table('performers', function (Blueprint $table) {
                $table->decimal('popularity_score', 7, 6)->change();
            });

            Schema::table('venues', function (Blueprint $table) {
                $table->decimal('popularity_score', 7, 6)->change();
            });
         */
        DB::statement('ALTER TABLE `events` CHANGE COLUMN `popularity_score` `popularity_score` DECIMAL(12, 6) NOT NULL DEFAULT 0.000000  COMMENT \'\' AFTER `category_id`;');
        DB::statement('ALTER TABLE `events` CHANGE COLUMN `short_term_popularity_score` `short_term_popularity_score` DECIMAL(7, 6) NOT NULL DEFAULT 0.000000  COMMENT \'\' AFTER `popularity_score`;');
        DB::statement('ALTER TABLE `events` CHANGE COLUMN `long_term_popularity_score` `long_term_popularity_score` DECIMAL(7, 6) NOT NULL DEFAULT 0.000000  COMMENT \'\' AFTER `short_term_popularity_score`;');

        DB::statement('ALTER TABLE `performers` CHANGE COLUMN `popularity_score` `popularity_score` DECIMAL(7, 6) NOT NULL DEFAULT 0.000000  COMMENT \'\' AFTER `category_id`;');

        DB::statement('ALTER TABLE `venues` CHANGE COLUMN `popularity_score` `popularity_score` DECIMAL(7, 6) NOT NULL DEFAULT 0.000000  COMMENT \'\' AFTER `slug`;');
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // As the band Boston said, Don’t Look Back
    }
}
