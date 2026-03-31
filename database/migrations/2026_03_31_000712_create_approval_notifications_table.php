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
        Schema::create('approval_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('annealing_check_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->comment('User to notify (admin/inspector)');
            $table->string('type')->default('new_submission')->comment('new_submission, update');
            $table->enum('status', ['pending', 'read', 'acted'])->default('pending');
            $table->text('message')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_notifications');
    }
};
