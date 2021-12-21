<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTevoHarvestTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('CREATE_BROKERAGES_OFFICES', false)) {
            Schema::create('brokerages', function (Blueprint $table) {
                $table->engine = 'MyISAM';

                $table->id();
                $table->string('name', 100);
                $table->string('abbreviation', 100);
                $table->boolean('natb_member')->unsigned()->default(0);
                $table->text('logo')->nullable()->default(null);

                $table->string('url', 150);
                $table->timestamp('tevo_created_at')->nullable()->default(null);
                $table->timestamp('tevo_updated_at')->nullable()->default(null);
                $table->timestamp('tevo_deleted_at')->nullable()->default(null);
                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement('ALTER TABLE brokerages ADD FULLTEXT search(name, abbreviation)');


            Schema::create('offices', function (Blueprint $table) {
                $table->engine = 'MyISAM';

                $table->id();
                $table->bigInteger('brokerage_id', false, true)->index();
                $table->foreign('brokerage_id')->references('id')->on('brokerages');

                $table->string('name', 50);
                $table->boolean('main')->unsigned()->default(0);

                $table->string('street_address', 100)->nullable()->default(null);
                $table->string('extended_address', 100)->nullable()->default(null);
                $table->string('locality', 50)->nullable()->default(null);
                $table->string('region', 50)->nullable()->default(null)->index();
                $table->string('postal_code', 20)->nullable()->default(null);
                $table->string('country_code', 2)->nullable()->default(null)->index();
                $table->decimal('latitude', 17, 14)->nullable()->default(null);
                $table->decimal('longitude', 17, 14)->nullable()->default(null);

                $table->string('phone', 20)->nullable()->default(null);
                $table->string('fax', 20)->nullable()->default(null);

                $table->boolean('po_box')->unsigned()->default(0);
                $table->string('time_zone', 30)->nullable()->default(null);
                $table->boolean('pos')->unsigned()->default(0);
                $table->boolean('evopay')->unsigned()->default(0);
                $table->decimal('evopay_discount', 4, 2)->unsigned()->default(0.00);

                $table->string('url');
                $table->timestamp('fedex_pickup_dropoff_time')->nullable()->default(null);
                $table->timestamp('tevo_created_at')->nullable()->default(null);
                $table->timestamp('tevo_updated_at')->nullable()->default(null);
                $table->timestamp('tevo_deleted_at')->nullable()->default(null);
                $table->timestamps();
                $table->softDeletes();
            });


            Schema::create('office_email_addresses', function (Blueprint $table) {
                $table->engine = 'MyISAM';

                $table->id();
                $table->bigInteger('office_id', false, true)->index();
                $table->foreign('office_id')->references('id')->on('offices');

                $table->string('email_address');

                $table->timestamps();
                $table->softDeletes();
            });


            Schema::create('office_hours', function (Blueprint $table) {
                $table->engine = 'MyISAM';

                $table->id();
                $table->bigInteger('office_id', false, true)->index();
                $table->foreign('office_id')->references('id')->on('offices');

                $table->tinyInteger('day_of_week');
                $table->boolean('closed')->unsigned()->default(0);
                $table->time('open');
                $table->time('close');

                $table->timestamps();
                $table->softDeletes();
            });
        }


        Schema::create('categories', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->id();
            $table->bigInteger('parent_id', false, true)->nullable()->default(null)->index();
            $table->foreign('parent_id')->references('id')->on('categories');

            $table->string('name', 50)->index();
            $table->string('slug', 100)->unique();

            $table->string('url');
            $table->string('slug_url');
            $table->timestamp('tevo_created_at')->nullable()->default(null);
            $table->timestamp('tevo_updated_at')->nullable()->default(null);
            $table->timestamp('tevo_deleted_at')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('venues', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->id();

            $table->string('name', 200)->index();
            $table->string('slug', 250)->unique();
            $table->decimal('popularity_score', 14, 6)->default(0)->index();

            $table->string('street_address', 100)->nullable()->default(null);
            $table->string('extended_address', 100)->nullable()->default(null);
            $table->string('locality', 50)->nullable()->default(null);
            $table->string('region', 50)->nullable()->default(null)->index();
            $table->string('postal_code', 20)->nullable()->default(null);
            $table->string('country_code', 2)->nullable()->default(null)->index();
            $table->decimal('latitude', 17, 14)->nullable()->default(null);
            $table->decimal('longitude', 17, 14)->nullable()->default(null);

            $table->text('keywords')->nullable()->default(null);
            $table->dateTime('upcoming_event_first')->nullable()->default(null)->index();
            $table->dateTime('upcoming_event_last')->nullable()->default(null)->index();

            $table->string('url');
            $table->string('slug_url');
            $table->timestamp('tevo_created_at')->nullable()->default(null);
            $table->timestamp('tevo_updated_at')->nullable()->default(null);
            $table->timestamp('tevo_deleted_at')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });
        DB::statement('ALTER TABLE venues ADD FULLTEXT search(name, keywords)');


        Schema::create('configurations', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->id();
            $table->bigInteger('venue_id', false, true)->index();
            $table->foreign('venue_id')->references('id')->on('venues');

            $table->string('name', 100)->index();
            $table->boolean('primary')->unsigned()->default(0);
            $table->boolean('general_admission')->unsigned()->default(0);
            $table->integer('capacity')->unsigned()->nullable()->default(null);
            $table->string('seating_chart_url_medium')->nullable()->default(null);
            $table->string('seating_chart_url_large')->nullable()->default(null);

            $table->string('url');
            $table->timestamp('tevo_created_at')->nullable()->default(null);
            $table->timestamp('tevo_updated_at')->nullable()->default(null);
            $table->timestamp('tevo_deleted_at')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('performers', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->id();
            $table->string('name', 100)->index();
            $table->string('slug', 150)->unique();
            $table->bigInteger('category_id', false, true)->nullable()->default(null)->index();
            $table->foreign('category_id')->references('id')->on('categories');

            $table->decimal('popularity_score', 14, 6)->default(0)->index();
            $table->bigInteger('venue_id', false, true)->nullable()->default(null)->index();
            $table->foreign('venue_id')->references('id')->on('venues');

            $table->text('keywords')->nullable()->default(null);
            $table->dateTime('upcoming_event_first')->nullable()->default(null)->index();
            $table->dateTime('upcoming_event_last')->nullable()->default(null)->index();

            $table->string('url');
            $table->string('slug_url');
            $table->timestamp('tevo_created_at')->nullable()->default(null);
            $table->timestamp('tevo_updated_at')->nullable()->default(null);
            $table->timestamp('tevo_deleted_at')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });
        DB::statement('ALTER TABLE performers ADD FULLTEXT search(name, keywords)');


        Schema::create('events', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->id();
            $table->string('name', 200)->index();
            $table->dateTime('occurs_at')->index()->default('1970-01-01 00:00:01');
            $table->timestampTz('occurs_at_local')->index()->default('1970-01-01 00:00:01');
            $table->integer('venue_id', false, true)->index();
            $table->foreign('venue_id')->references('id')->on('venues');
            $table->integer('configuration_id', false, true)->index();
            $table->foreign('configuration_id')->references('id')->on('configurations');
            $table->integer('category_id', false, true)->nullable()->default(null)->index();
            $table->foreign('category_id')->references('id')->on('categories');
            $table->decimal('popularity_score', 14, 6)->default(0)->index();
            $table->decimal('short_term_popularity_score', 7, 6)->default(0)->index();
            $table->decimal('long_term_popularity_score', 7, 6)->default(0)->index();

            $table->integer('products_count')->unsigned();
            $table->integer('products_eticket_count')->unsigned();
            $table->integer('available_count')->unsigned();
            $table->string('state', 20)->default('shown')->index();
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

            $table->id();
            $table->bigInteger('event_id', false, true)->unsigned()->index();
            $table->bigInteger('performer_id', false, true)->index();
            $table->foreign('performer_id')->references('id')->on('performers');
            $table->unique(['event_id', 'performer_id']);
            $table->boolean('primary')->unsigned()->default(0)->index();

            $table->string('event_name', 200)->index();
            $table->dateTime('occurs_at')->index();

            $table->integer('venue_id', false, true)->index();
            $table->foreign('venue_id')->references('id')->on('venues');

            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('harvests', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->id();
            $table->string('resource', 30);
            $table->enum('action', ['active', 'deleted', 'popularity'])->default('active');
            $table->unique(['resource', 'action']);
            $table->string('library_method', 50);
            $table->string('model_class');
            $table->string('scheduler_frequency_method', 100)->nullable()->default(null);
            $table->string('ping_before_url')->nullable()->default(null);
            $table->string('then_ping_url')->nullable()->default(null);
            $table->timestamp('last_run_at')->nullable()->default(null);

            $table->timestamps();
        });
        if (env('CREATE_BROKERAGES_OFFICES', false)) {
            DB::table('harvests')->insert([
                [
                    'resource'                   => 'brokerages',
                    'action'                     => 'active',
                    'library_method'             => 'listBrokerages',
                    'model_class'                => \App\Models\Tevo\Brokerage::class,
                    'scheduler_frequency_method' => 'twiceDaily',
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
                    'model_class'                => \App\Models\Tevo\Office::class,
                    'scheduler_frequency_method' => 'twiceDaily',
                    'ping_before_url'            => null,
                    'then_ping_url'              => null,
                    'last_run_at'                => null,
                    'created_at'                 => DB::raw('CURRENT_TIMESTAMP'),
                    'updated_at'                 => DB::raw('CURRENT_TIMESTAMP'),
                ],
            ]);
        }
        DB::table('harvests')->insert([
            [
                'resource'                   => 'categories',
                'action'                     => 'active',
                'library_method'             => 'listCategories',
                'model_class'                => \App\Models\Tevo\Category::class,
                'scheduler_frequency_method' => 'everyThirtyMinutes',
                'ping_before_url'            => null,
                'then_ping_url'              => null,
                'last_run_at'                => null,
                'created_at'                 => DB::raw('CURRENT_TIMESTAMP'),
                'updated_at'                 => DB::raw('CURRENT_TIMESTAMP'),
            ],
            [
                'resource'                   => 'categories',
                'action'                     => 'deleted',
                'library_method'             => 'listCategoriesDeleted',
                'model_class'                => \App\Models\Tevo\Category::class,
                'scheduler_frequency_method' => 'everyThirtyMinutes',
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
                'model_class'                => \App\Models\Tevo\Configuration::class,
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
                'model_class'                => \App\Models\Tevo\Event::class,
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
                'library_method'             => 'listEventsDeleted',
                'model_class'                => \App\Models\Tevo\Event::class,
                'scheduler_frequency_method' => 'everyThirtyMinutes',
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
                'model_class'                => \App\Models\Tevo\Performer::class,
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
                'library_method'             => 'listPerformersDeleted',
                'model_class'                => \App\Models\Tevo\Performer::class,
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
                'model_class'                => \App\Models\Tevo\Performer::class,
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
                'model_class'                => \App\Models\Tevo\Venue::class,
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
                'library_method'             => 'listVenuesDeleted',
                'model_class'                => \App\Models\Tevo\Venue::class,
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
         * Currently, restraints should not cause that issue,
         * but ordered nicely just in case.
         */
        Schema::dropIfExists('office_email_addresses');
        Schema::dropIfExists('office_hours');
        Schema::dropIfExists('offices');
        Schema::dropIfExists('brokerages');
        Schema::dropIfExists('performances');
        Schema::dropIfExists('events');
        Schema::dropIfExists('configurations');
        Schema::dropIfExists('venues');
        Schema::dropIfExists('performers');
        Schema::dropIfExists('categories');

        Schema::dropIfExists('harvests');
    }
}
