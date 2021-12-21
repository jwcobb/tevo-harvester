<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeStorageEngineToInnodb extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE categories ENGINE=InnoDB;');
        DB::statement('ALTER TABLE configurations ENGINE=InnoDB;');
        DB::statement('ALTER TABLE events ENGINE=InnoDB;');
        DB::statement('ALTER TABLE harvests ENGINE=InnoDB;');
        DB::statement('ALTER TABLE performances ENGINE=InnoDB;');
        DB::statement('ALTER TABLE performers ENGINE=InnoDB;');
        DB::statement('ALTER TABLE venues ENGINE=InnoDB;');

        if (env('CREATE_BROKERAGES_OFFICES', false)) {
            DB::statement('ALTER TABLE brokerages ENGINE=InnoDB;');
            DB::statement('ALTER TABLE office_email_addresses ENGINE=InnoDB;');
            DB::statement('ALTER TABLE office_hours ENGINE=InnoDB;');
            DB::statement('ALTER TABLE offices ENGINE=InnoDB;');
        }
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE categories ENGINE=MyISAM;');
        DB::statement('ALTER TABLE configurations ENGINE=MyISAM;');
        DB::statement('ALTER TABLE events ENGINE=MyISAM;');
        DB::statement('ALTER TABLE harvests ENGINE=MyISAM;');
        DB::statement('ALTER TABLE performances ENGINE=MyISAM;');
        DB::statement('ALTER TABLE performers ENGINE=MyISAM;');
        DB::statement('ALTER TABLE venues ENGINE=MyISAM;');

        if (env('CREATE_BROKERAGES_OFFICES', false)) {
            DB::statement('ALTER TABLE brokerages ENGINE=MyISAM;');
            DB::statement('ALTER TABLE office_email_addresses ENGINE=MyISAM;');
            DB::statement('ALTER TABLE office_hours ENGINE=MyISAM;');
            DB::statement('ALTER TABLE offices ENGINE=MyISAM;');
        }
    }
}
