<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            // Check and add columns if they don't exist
            if (!Schema::hasColumn('activities', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('activities', 'type')) {
                $table->string('type', 50)->after('user_id');
            }
            
            if (!Schema::hasColumn('activities', 'module')) {
                $table->string('module', 50)->nullable()->after('type');
            }
            
            if (!Schema::hasColumn('activities', 'subject_type')) {
                $table->string('subject_type')->nullable()->after('module');
            }
            
            if (!Schema::hasColumn('activities', 'subject_id')) {
                $table->unsignedBigInteger('subject_id')->nullable()->after('subject_type');
            }
            
            if (!Schema::hasColumn('activities', 'description')) {
                $table->text('description')->nullable()->after('subject_id');
            }
            
            if (!Schema::hasColumn('activities', 'properties')) {
                $table->json('properties')->nullable()->after('description');
            }
            
            if (!Schema::hasColumn('activities', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('properties');
            }
            
            if (!Schema::hasColumn('activities', 'user_agent')) {
                $table->string('user_agent', 500)->nullable()->after('ip_address');
            }
        });

        // Add indexes for efficient querying (check if they exist first)
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $indexes = collect($sm->listTableIndexes('activities'))->keys()->toArray();

        Schema::table('activities', function (Blueprint $table) use ($indexes) {
            if (!in_array('activities_user_id_index', $indexes)) {
                $table->index('user_id', 'activities_user_id_index');
            }
            if (!in_array('activities_type_index', $indexes)) {
                $table->index('type', 'activities_type_index');
            }
            if (!in_array('activities_module_index', $indexes)) {
                $table->index('module', 'activities_module_index');
            }
            if (!in_array('activities_subject_index', $indexes)) {
                $table->index(['subject_type', 'subject_id'], 'activities_subject_index');
            }
            if (!in_array('activities_created_at_index', $indexes)) {
                $table->index('created_at', 'activities_created_at_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropIndex('activities_user_id_index');
            $table->dropIndex('activities_type_index');
            $table->dropIndex('activities_module_index');
            $table->dropIndex('activities_subject_index');
            $table->dropIndex('activities_created_at_index');
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id',
                'type',
                'module',
                'subject_type',
                'subject_id',
                'description',
                'properties',
                'ip_address',
                'user_agent',
            ]);
        });
    }
};
