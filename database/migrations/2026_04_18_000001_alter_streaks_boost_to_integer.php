<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('streaks', function (Blueprint $table) {
            $table->integer('boost')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('streaks', function (Blueprint $table) {
            $table->boolean('boost')->default(false)->change();
        });
    }
};
