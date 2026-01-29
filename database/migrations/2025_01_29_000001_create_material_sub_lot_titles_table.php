<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('material_sub_lot_titles', function (Blueprint $table) {
            $table->id();
            $table->string('material_type');
            $table->string('title');
            $table->unsignedInteger('sort_order');
            $table->timestamps();

            $table->index(['material_type', 'sort_order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('material_sub_lot_titles');
    }
};
