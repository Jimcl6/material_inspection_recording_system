<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('annealing_checks', function (Blueprint $table) {
            // Add new text columns for user names
            $table->string('pic_name', 255)->after('pic_id');
            $table->string('checked_by_name', 255)->nullable()->after('checked_by_id');
            $table->string('verified_by_name', 255)->nullable()->after('verified_by_id');
        });

        // Migrate existing data from ID columns to name columns
        \DB::statement('
            UPDATE annealing_checks ac 
            LEFT JOIN users u1 ON ac.pic_id = u1.id 
            LEFT JOIN users u2 ON ac.checked_by_id = u2.id 
            LEFT JOIN users u3 ON ac.verified_by_id = u3.id 
            SET ac.pic_name = COALESCE(u1.name, ac.pic_id),
                ac.checked_by_name = COALESCE(u2.name, ac.checked_by_id),
                ac.verified_by_name = COALESCE(u3.name, ac.verified_by_id)
        ');

        // Drop old ID columns
        Schema::table('annealing_checks', function (Blueprint $table) {
            $table->dropColumn(['pic_id', 'checked_by_id', 'verified_by_id']);
        });

        // Rename new columns to original names
        Schema::table('annealing_checks', function (Blueprint $table) {
            $table->renameColumn('pic_name', 'pic_id');
            $table->renameColumn('checked_by_name', 'checked_by_id');
            $table->renameColumn('verified_by_name', 'verified_by_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('annealing_checks', function (Blueprint $table) {
            // Add back the original ID columns
            $table->unsignedBigInteger('pic_id')->nullable()->after('machine_setting');
            $table->unsignedBigInteger('checked_by_id')->nullable()->after('pic_id');
            $table->unsignedBigInteger('verified_by_id')->nullable()->after('checked_by_id');
        });

        // Try to convert names back to IDs (this may not work perfectly)
        \DB::statement('
            UPDATE annealing_checks ac 
            LEFT JOIN users u1 ON ac.pic_id = u1.name 
            LEFT JOIN users u2 ON ac.checked_by_id = u2.name 
            LEFT JOIN users u3 ON ac.verified_by_id = u3.name 
            SET ac.pic_id_new = u1.id,
                ac.checked_by_id_new = u2.id,
                ac.verified_by_id_new = u3.id
        ');

        // Drop the name columns
        Schema::table('annealing_checks', function (Blueprint $table) {
            $table->dropColumn(['pic_id', 'checked_by_id', 'verified_by_id']);
        });
    }
};
