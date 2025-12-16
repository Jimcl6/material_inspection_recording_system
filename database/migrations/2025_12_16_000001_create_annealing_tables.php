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
    public function up(): void
    {
        // Main annealing checks table
        Schema::create('annealing_checks', function (Blueprint $table) {
            $table->id();
            
            // Basic information
            $table->string('item_code', 50);
            $table->date('receiving_date');
            $table->string('supplier_lot_number', 100);
            $table->integer('quantity');
            $table->date('annealing_date');
            $table->string('machine_number', 50);
            $table->string('machine_setting', 100)->nullable();
            
            // User references - will be foreign keys after users table is set up
            $table->unsignedBigInteger('pic_id')->comment('Person In Charge');
            $table->unsignedBigInteger('checked_by_id')->nullable();
            $table->unsignedBigInteger('verified_by_id')->nullable();
            
            // Foreign key constraints will be added in a separate migration
            // after the users table is created
            
            // Audit trail
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            
            // Additional fields
            $table->text('remarks')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('item_code');
            $table->index('supplier_lot_number');
            $table->index('annealing_date');
        });

        // Temperature readings table
        Schema::create('temperature_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('annealing_check_id')->constrained()->onDelete('cascade');
            
            // Reading details
            $table->time('reading_time');
            $table->decimal('temperature', 8, 2);
            
            // Audit trail
            $table->unsignedBigInteger('recorded_by');
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('reading_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('temperature_readings');
        Schema::dropIfExists('annealing_checks');
    }
};
