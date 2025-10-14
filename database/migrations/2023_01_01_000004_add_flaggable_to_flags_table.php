<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('flags', function (Blueprint $table) {
            $table->string('flaggable_type')->after('flagger_id');
            $table->unsignedBigInteger('flaggable_id')->after('flaggable_type');
            
            $table->index(['flaggable_type', 'flaggable_id']);
        });
    }

    public function down()
    {
        Schema::table('flags', function (Blueprint $table) {
            $table->dropIndex(['flaggable_type', 'flaggable_id']);
            $table->dropColumn(['flaggable_type', 'flaggable_id']);
        });
    }
};