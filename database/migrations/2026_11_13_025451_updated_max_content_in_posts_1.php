<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Potong content yang melebihi 500 karakter
        DB::table('posts')
            ->whereRaw('CHAR_LENGTH(content) > 500')
            ->update([
                'content' => DB::raw('LEFT(content, 500)')
            ]);

        // Ubah tipe data menjadi VARCHAR(255)
        Schema::table('posts', function (Blueprint $table) {
            $table->string('content', 500)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->text('content')->nullable()->change();
        });
    }
};
