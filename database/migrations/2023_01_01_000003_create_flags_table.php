<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flag_link_id')->constrained('flag_links');
            $table->string('flagger_type');
            $table->unsignedBigInteger('flagger_id');
            $table->timestamps();

            $table->unique(['flag_link_id', 'flagger_type', 'flagger_id']);
            $table->index(['flagger_type', 'flagger_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('flags');
    }
};