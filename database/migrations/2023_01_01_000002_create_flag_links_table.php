<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('flag_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flag_type_id')->constrained('flag_types');
            $table->foreignId('flag_target_id')->constrained('flag_targets');
            $table->unique(['flag_type_id', 'flag_target_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('flag_links');
    }
};