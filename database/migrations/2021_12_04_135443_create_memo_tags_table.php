<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemoTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('memo_tags', function (Blueprint $table) {
            $table->unsignedBigInteger('memo_id');
            $table->unsignedBigInteger('tag_id');

            // 外部キー制約
            $table->foreign('memo_id')->references('memo')->on('id');
            $table->foreign('tag_id')->references('tag')->on('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('memo_tags');
    }
}
