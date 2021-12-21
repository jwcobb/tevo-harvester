<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Some Performers were found to have been deleted and then re-added with the same slugs.
 * Use a clever solution to allow these to not break anything due to the uniqueness of the slug.
 *
 * Example: One Funny Mother exists with IDs 46429 & 70197 and the slug one-funny-mother
 * ID 46429 was tevo_deleted_at 2019-08-01 18:25:50
 *
 * This removes the unique constraint on the slug column and creates a virtual column slug_unique
 * that will concat the slug with the tevo_deleted_at column and apply a unique index constraint to slug_unique.
 * 
 * ID       SLUG                TEVO_DELETED_AT         SLUG_UNIQUE
 * 46429    one-funny-mother    2019-08-01 18:25:50     one-funny-mother#2019-08-01 18:25:50
 * 70197    one-funny-mother    NULL                    one-funny-mother#-
 */
class AllowNonUniqueSlugs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_slug_unique');
            $table->index(['slug']);
        });
        DB::statement('ALTER TABLE categories ADD COLUMN slug_unique varchar (150) GENERATED ALWAYS AS (CONCAT (slug, \'#\', IF (tevo_deleted_at IS NULL, \'-\', tevo_deleted_at))) VIRTUAL;');
        DB::statement('CREATE UNIQUE INDEX categories_slug_unique ON categories (slug_unique);');

        Schema::table('performers', function (Blueprint $table) {
            $table->dropUnique('performers_slug_unique');
            $table->index(['slug']);
        });
        DB::statement('ALTER TABLE performers ADD COLUMN slug_unique varchar (200) GENERATED ALWAYS AS (CONCAT (slug, \'#\', IF (tevo_deleted_at IS NULL, \'-\', tevo_deleted_at))) VIRTUAL;');
        DB::statement('CREATE UNIQUE INDEX performers_slug_unique ON performers (slug_unique);');

        Schema::table('venues', function (Blueprint $table) {
            $table->dropUnique('venues_slug_unique');
            $table->index(['slug']);
        });
        DB::statement('ALTER TABLE venues ADD COLUMN slug_unique varchar (255) GENERATED ALWAYS AS (CONCAT (slug, \'#\', IF (tevo_deleted_at IS NULL, \'-\', tevo_deleted_at))) VIRTUAL;');
        DB::statement('CREATE UNIQUE INDEX venues_slug_unique ON venues (slug_unique);');
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_slug_unique');
            $table->dropColumn(['slug_unique']);
            $table->unique(['slug']);
        });

        Schema::table('performers', function (Blueprint $table) {
            $table->dropUnique('performers_slug_unique');
            $table->dropColumn(['slug_unique']);
            $table->unique(['slug']);
        });

        Schema::table('venues', function (Blueprint $table) {
            $table->dropUnique('venues_slug_unique');
            $table->dropColumn(['slug_unique']);
            $table->unique(['slug']);
        });
    }
}
