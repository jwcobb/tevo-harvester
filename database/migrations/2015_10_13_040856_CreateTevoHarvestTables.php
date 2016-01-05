<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use TevoHarvester\Tevo\Brokerage;
use TevoHarvester\Tevo\Category;
use TevoHarvester\Tevo\Configuration;
use TevoHarvester\Tevo\Event;
use TevoHarvester\Tevo\Office;
use TevoHarvester\Tevo\Performer;
use TevoHarvester\Tevo\Venue;

class CreateTevoHarvestTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('brokerages', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->smallInteger('id', false, true);
            $table->string('name', 191);
//            $table->index('name');
            $table->string('abbreviation', 191);
            $table->boolean('natb_member')->unsigned()->default(0);
//            $table->boolean('evopay')->unsigned()->default(0);
            $table->string('logo', 191)->nullable()->default(null);

            $table->string('url', 191);
            $table->timestamp('tevo_created_at')->nullable()->default(null);
            $table->timestamp('tevo_updated_at')->nullable()->default(null);
            $table->timestamp('tevo_deleted_at')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });
        DB::statement('ALTER TABLE brokerages ADD FULLTEXT search(name, abbreviation)');


        Schema::create('offices', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->smallInteger('id', false, true);
            $table->smallInteger('brokerage_id', false, true)->index();
            $table->foreign('brokerage_id')->references('id')
                ->on('brokerages')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->string('name', 191);
            $table->boolean('main')->unsigned()->default(0);

            $table->string('street_address', 191)->nullable()->default(null);
            $table->string('extended_address', 191)->nullable()->default(null);
            $table->string('locality', 191)->nullable()->default(null)->index()->index();
            $table->string('region', 191)->nullable()->default(null)->index();
            $table->string('postal_code', 10)->nullable()->default(null);
            $table->string('country_code', 5)->nullable()->default(null)->index();
            $table->decimal('latitude', 17, 14)->nullable()->default(null);
            $table->decimal('longitude', 17, 14)->nullable()->default(null);

            $table->string('phone', 40)->nullable()->default(null);
            $table->string('fax', 40)->nullable()->default(null);

            $table->boolean('po_box')->unsigned()->default(0);
            $table->string('time_zone', 191)->nullable()->default(null);
            $table->boolean('pos')->unsigned()->default(0);
            $table->boolean('evopay')->unsigned()->default(0);
            $table->decimal('evopay_discount', 4, 2)->unsigned()->default(0.00);

            $table->string('url', 191);
            $table->timestamp('tevo_created_at')->nullable()->default(null);
            $table->timestamp('tevo_updated_at')->nullable()->default(null);
            $table->timestamp('tevo_deleted_at')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('office_email_addresses', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->mediumInteger('id', true, true);
            $table->smallInteger('office_id', false, true)->index();
            $table->foreign('office_id')->references('id')
                ->on('offices')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->string('email_address', 191);
            $table->unique(['office_id', 'email_address']);

            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('office_hours', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->mediumInteger('id', true, true);
            $table->smallInteger('office_id', false, true)->index();
            $table->foreign('office_id')->references('id')
                ->on('offices')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->tinyInteger('day_of_week');
            $table->boolean('closed')->unsigned()->default(0);
            $table->time('open');
            $table->time('close');

            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('categories', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->smallInteger('id', false, true);
            $table->smallInteger('parent_id', false, true)->nullable()->default(null)->index();
//            $table->foreign('parent_id')->references('id')
//                ->on('categories')
//                ->onDelete('restrict')
//                ->onUpdate('restrict');

            $table->string('name', 191)->index();
            $table->string('slug', 191)->index();

            $table->string('url', 191);
            $table->string('slug_url', 191);
            $table->timestamp('tevo_created_at')->nullable()->default(null);
            $table->timestamp('tevo_updated_at')->nullable()->default(null);
            $table->timestamp('tevo_deleted_at')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('venues', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->mediumInteger('id', false, true);

            $table->string('name', 191)->index();
            $table->string('slug', 191);
            $table->decimal('popularity_score', 7, 6)->unsigned()->default(0.000000)->index();

            $table->string('street_address', 191)->nullable()->default(null);
            $table->string('extended_address', 191)->nullable()->default(null);
            $table->string('locality', 191)->nullable()->default(null)->index()->index();
            $table->string('region', 191)->nullable()->default(null)->index();
            $table->string('postal_code', 10)->nullable()->default(null);
            $table->string('country_code', 5)->nullable()->default(null)->index();
            $table->decimal('latitude', 17, 14)->nullable()->default(null);
            $table->decimal('longitude', 17, 14)->nullable()->default(null);

            $table->text('keywords')->nullable()->default(null);
            $table->timestamp('upcoming_event_first')->nullable()->default(null)->index();
            $table->timestamp('upcoming_event_last')->nullable()->default(null)->index();

            $table->string('url', 191);
            $table->string('slug_url', 191);
            $table->timestamp('tevo_created_at')->nullable()->default(null);
            $table->timestamp('tevo_updated_at')->nullable()->default(null);
            $table->timestamp('tevo_deleted_at')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });
        DB::statement('ALTER TABLE venues ADD FULLTEXT search(name, keywords)');


        Schema::create('configurations', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->mediumInteger('id', false, true);
            $table->mediumInteger('venue_id', false, true)->index();
            $table->foreign('venue_id')->references('id')
                ->on('venues')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->string('name', 191)->index();
            $table->boolean('primary')->unsigned()->default(0);
            $table->boolean('general_admission')->unsigned()->default(0);
            $table->integer('capacity')->unsigned()->nullable()->default(null);
            $table->string('seating_chart_url_medium', 191)->nullable()->default(null);
            $table->string('seating_chart_url_large', 191)->nullable()->default(null);

            $table->string('url', 191);
            $table->timestamp('tevo_created_at')->nullable()->default(null);
            $table->timestamp('tevo_updated_at')->nullable()->default(null);
            $table->timestamp('tevo_deleted_at')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('performers', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->mediumInteger('id', false, true);

            $table->string('name', 191)->index();
            $table->string('slug', 191);
            $table->smallInteger('category_id', false, true)->nullable()->default(null)->index();
            $table->foreign('category_id')->references('id')->on('categories');

            $table->decimal('popularity_score', 7, 6)->unsigned()->default(0.000000)->index();
            $table->mediumInteger('venue_id', false, true)->nullable()->default(null)->index();
            $table->foreign('venue_id')->references('id')->on('tevoVenues');

            $table->text('keywords')->nullable()->default(null);
            $table->timestamp('upcoming_event_first')->nullable()->default(null)->index();
            $table->timestamp('upcoming_event_last')->nullable()->default(null)->index();

            $table->string('url', 191);
            $table->string('slug_url', 191);
            $table->timestamp('tevo_created_at')->nullable()->default(null);
            $table->timestamp('tevo_updated_at')->nullable()->default(null);
            $table->timestamp('tevo_deleted_at')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });
        DB::statement('ALTER TABLE performers ADD FULLTEXT search(name, keywords)');


        Schema::create('events', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->integer('id', false, true);

            $table->string('name', 191)->index();
            $table->timestamp('occurs_at')->index();
            $table->mediumInteger('venue_id', false, true)->index();
            $table->foreign('venue_id')->references('id')->on('venues');
            $table->mediumInteger('configuration_id', false, true)->index();
            $table->foreign('configuration_id')->references('id')->on('configurations');
            $table->smallInteger('category_id', false, true)->nullable()->default(null)->index();
            $table->foreign('category_id')->references('id')->on('categories');
            $table->decimal('popularity_score', 12, 6)->unsigned()->default(0.000000)->index();
            $table->decimal('short_term_popularity_score', 7, 6)->unsigned()->default(0.000000)->index();
            $table->decimal('long_term_popularity_score', 7, 6)->unsigned()->default(0.000000)->index();

            $table->integer('products_count')->unsigned();
            $table->integer('products_eticket_count')->unsigned();
            $table->integer('available_count')->unsigned();
            $table->enum('state', ['shown', 'postponed', 'canceled'])->default('shown')->index();
            $table->text('notes')->nullable()->default(null);
            $table->integer('stubhub_id')->unsigned()->nullable()->default(null)->index();

            $table->string('url');
            $table->timestamp('tevo_created_at')->nullable()->default(null);
            $table->timestamp('tevo_updated_at')->nullable()->default(null);
            $table->timestamp('tevo_deleted_at')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });
        DB::statement('ALTER TABLE events ADD FULLTEXT search(name)');


        Schema::create('performances', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->integer('id', true, true);
            $table->integer('event_id')->unsigned();
            $table->mediumInteger('performer_id', false, true)->index();
            $table->foreign('performer_id')->references('id')
                ->on('performers')
                ->onDelete('cascade')
                ->onUpdate('cascade');
//            $table->primary(['event_id', 'performer_id']);
            $table->unique(['event_id', 'performer_id']);
            $table->boolean('primary')->unsigned()->default(0)->index();

            $table->string('event_name', 191)->index();
            $table->timestamp('occurs_at')->index();

            $table->mediumInteger('venue_id', false, true)->index();
            $table->foreign('venue_id')->references('id')
                ->on('venues')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->timestamp('tevo_created_at')->nullable()->default(null);
            $table->timestamp('tevo_updated_at')->nullable()->default(null);
            $table->timestamp('tevo_deleted_at')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('harvests', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->tinyInteger('id', true, true);
            $table->string('resource', 30);
            $table->enum('action', ['active', 'deleted', 'popularity'])->default('active');
            $table->unique(['resource', 'action']);
            $table->string('library_method', 50);
            $table->string('model_class', 191);
            $table->string('scheduler_frequency_method', 100)->nullable()->default(null);
            $table->string('ping_before_url', 191)->nullable()->default(null);
            $table->string('then_ping_url', 191)->nullable()->default(null);
            $table->timestamp('last_run_at')->nullable()->default(null);

            $table->timestamps();
        });
        DB::table('harvests')->insert([
            [
                'resource'                   => 'brokerages',
                'action'                     => 'active',
                'library_method'             => 'listBrokerages',
                'model_class'                => Brokerage::class,
                'scheduler_frequency_method' => 'daily',
                'ping_before_url'            => null,
                'then_ping_url'              => null,
                'last_run_at'                => null,
                'created_at'                 => DB::raw('CURRENT_TIMESTAMP'),
                'updated_at'                 => DB::raw('CURRENT_TIMESTAMP'),
            ],
            [
                'resource'                   => 'categories',
                'action'                     => 'active',
                'library_method'             => 'listCategories',
                'model_class'                => Category::class,
                'scheduler_frequency_method' => 'daily',
                'ping_before_url'            => null,
                'then_ping_url'              => null,
                'last_run_at'                => null,
                'created_at'                 => DB::raw('CURRENT_TIMESTAMP'),
                'updated_at'                 => DB::raw('CURRENT_TIMESTAMP'),
            ],
            [
                'resource'                   => 'categories',
                'action'                     => 'deleted',
                'library_method'             => 'listCategories',
                'model_class'                => Category::class,
                'scheduler_frequency_method' => 'daily',
                'ping_before_url'            => null,
                'then_ping_url'              => null,
                'last_run_at'                => null,
                'created_at'                 => DB::raw('CURRENT_TIMESTAMP'),
                'updated_at'                 => DB::raw('CURRENT_TIMESTAMP'),
            ],
            [
                'resource'                   => 'configurations',
                'action'                     => 'active',
                'library_method'             => 'listConfigurations',
                'model_class'                => Configuration::class,
                'scheduler_frequency_method' => 'everyThirtyMinutes',
                'ping_before_url'            => null,
                'then_ping_url'              => null,
                'last_run_at'                => null,
                'created_at'                 => DB::raw('CURRENT_TIMESTAMP'),
                'updated_at'                 => DB::raw('CURRENT_TIMESTAMP'),
            ],
            [
                'resource'                   => 'events',
                'action'                     => 'active',
                'library_method'             => 'listEvents',
                'model_class'                => Event::class,
                'scheduler_frequency_method' => 'everyThirtyMinutes',
                'ping_before_url'            => null,
                'then_ping_url'              => null,
                'last_run_at'                => null,
                'created_at'                 => DB::raw('CURRENT_TIMESTAMP'),
                'updated_at'                 => DB::raw('CURRENT_TIMESTAMP'),
            ],
            [
                'resource'                   => 'events',
                'action'                     => 'deleted',
                'library_method'             => 'listEvents',
                'model_class'                => Event::class,
                'scheduler_frequency_method' => 'everyThirtyMinutes',
                'ping_before_url'            => null,
                'then_ping_url'              => null,
                'last_run_at'                => null,
                'created_at'                 => DB::raw('CURRENT_TIMESTAMP'),
                'updated_at'                 => DB::raw('CURRENT_TIMESTAMP'),
            ],
            [
                'resource'                   => 'offices',
                'action'                     => 'active',
                'library_method'             => 'listOffices',
                'model_class'                => Office::class,
                'scheduler_frequency_method' => 'daily',
                'ping_before_url'            => null,
                'then_ping_url'              => null,
                'last_run_at'                => null,
                'created_at'                 => DB::raw('CURRENT_TIMESTAMP'),
                'updated_at'                 => DB::raw('CURRENT_TIMESTAMP'),
            ],
            [
                'resource'                   => 'performers',
                'action'                     => 'active',
                'library_method'             => 'listPerformers',
                'model_class'                => Performer::class,
                'scheduler_frequency_method' => 'everyThirtyMinutes',
                'ping_before_url'            => null,
                'then_ping_url'              => null,
                'last_run_at'                => null,
                'created_at'                 => DB::raw('CURRENT_TIMESTAMP'),
                'updated_at'                 => DB::raw('CURRENT_TIMESTAMP'),
            ],
            [
                'resource'                   => 'performers',
                'action'                     => 'deleted',
                'library_method'             => 'listPerformers',
                'model_class'                => Performer::class,
                'scheduler_frequency_method' => 'everyThirtyMinutes',
                'ping_before_url'            => null,
                'then_ping_url'              => null,
                'last_run_at'                => null,
                'created_at'                 => DB::raw('CURRENT_TIMESTAMP'),
                'updated_at'                 => DB::raw('CURRENT_TIMESTAMP'),
            ],
            [
                'resource'                   => 'performers',
                'action'                     => 'popularity',
                'library_method'             => 'listPerformers',
                'model_class'                => Performer::class,
                'scheduler_frequency_method' => 'daily',
                'ping_before_url'            => null,
                'then_ping_url'              => null,
                'last_run_at'                => null,
                'created_at'                 => DB::raw('CURRENT_TIMESTAMP'),
                'updated_at'                 => DB::raw('CURRENT_TIMESTAMP'),
            ],
            [
                'resource'                   => 'venues',
                'action'                     => 'active',
                'library_method'             => 'listVenues',
                'model_class'                => Venue::class,
                'scheduler_frequency_method' => 'everyThirtyMinutes',
                'ping_before_url'            => null,
                'then_ping_url'              => null,
                'last_run_at'                => null,
                'created_at'                 => DB::raw('CURRENT_TIMESTAMP'),
                'updated_at'                 => DB::raw('CURRENT_TIMESTAMP'),
            ],
            [
                'resource'                   => 'venues',
                'action'                     => 'deleted',
                'library_method'             => 'listVenues',
                'model_class'                => Venue::class,
                'scheduler_frequency_method' => 'everyThirtyMinutes',
                'ping_before_url'            => null,
                'then_ping_url'              => null,
                'last_run_at'                => null,
                'created_at'                 => DB::raw('CURRENT_TIMESTAMP'),
                'updated_at'                 => DB::raw('CURRENT_TIMESTAMP'),
            ],
        ]);

    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /**
         * The order here could be important because
         * “if you have foreign key constraints such as RESTRICT that
         * ensure referential integrity with other tables, you’ll want
         * to drop those keys prior to dropping or truncating a table.”
         *
         * @link http://stackoverflow.com/a/887623/99071
         *
         * Currently restraints should not cause that issue,
         * but ordered nicely just in case.
         */
        Schema::drop('office_email_addresses');
        Schema::drop('office_hours');
        Schema::drop('offices');
//        Schema::drop('tevo_users');
        Schema::drop('brokerages');

        Schema::drop('performances');
        Schema::drop('events');
        Schema::drop('configurations');
        Schema::drop('venues');
        Schema::drop('performers');
        Schema::drop('categories');

        Schema::drop('harvests');

    }
}
