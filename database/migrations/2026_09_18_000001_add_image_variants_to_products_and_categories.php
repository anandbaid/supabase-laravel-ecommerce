<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('image_small')->nullable()->after('image');
            $table->string('image_medium')->nullable()->after('image_small');
            $table->string('image_large')->nullable()->after('image_medium');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('image_small')->nullable()->after('image');
            $table->string('image_medium')->nullable()->after('image_small');
            $table->string('image_large')->nullable()->after('image_medium');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['image_small', 'image_medium', 'image_large']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['image_small', 'image_medium', 'image_large']);
        });
    }
};
