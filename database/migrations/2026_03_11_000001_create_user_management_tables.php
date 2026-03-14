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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('code');
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->index('code');
            $table->index('department_id');
        });

        Schema::create('user_qr_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('qr_data', 500)->unique(); // JSON string or encrypted data
            $table->string('employee_id', 50)->unique(); // Employee ID from QR
            $table->enum('employment_status', ['regular', 'contractual', 'probationary']);
            $table->date('hire_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_scanned_at')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('employee_id');
            $table->index('employment_status');
        });

        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->string('module', 50); // Module name (e.g., 'annealing', 'material')
            $table->string('action', 50); // Action (e.g., 'create', 'read', 'update', 'delete')
            $table->timestamps();
            
            $table->index(['module', 'action']);
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');
            $table->timestamps();
            
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('user_permissions')->onDelete('cascade');
            $table->unique(['role_id', 'permission_id']);
        });

        // Update users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id', 50)->unique()->nullable()->after('email');
            $table->unsignedBigInteger('department_id')->nullable()->after('role_id');
            $table->unsignedBigInteger('position_id')->nullable()->after('department_id');
            $table->string('contact_number', 20)->nullable()->after('position_id');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('contact_number');
            $table->timestamp('last_login_at')->nullable()->after('updated_at');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('set null');
            $table->index('employee_id');
            $table->index('status');
        });

        // Create user login history table
        Schema::create('user_login_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('ip_address', 45);
            $table->string('user_agent', 500)->nullable();
            $table->enum('login_type', ['password', 'qr_code', 'sso'])->default('password');
            $table->timestamp('login_at');
            $table->timestamp('logout_at')->nullable();
            $table->boolean('is_successful')->default(true);
            $table->text('failure_reason')->nullable();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'login_at']);
            $table->index('login_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_login_history');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('user_qr_codes');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('departments');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['position_id']);
            $table->dropColumn([
                'employee_id',
                'department_id',
                'position_id',
                'contact_number',
                'status',
                'last_login_at',
                'last_login_ip'
            ]);
        });
    }
};
