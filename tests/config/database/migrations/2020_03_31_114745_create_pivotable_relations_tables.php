<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePivotableRelationsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('text')->nullable();
            $table->morphs('commentable');
            $table->timestamps();
        });

        Schema::create('recommendables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('text')->nullable();
            $table->morphs('recommendable');
            $table->bigInteger('recommend_id');
            $table->timestamps();
        });

        Schema::create('billables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('text')->nullable();
            $table->morphs('billable');
            $table->bigInteger('bill_id');
            $table->timestamps();
        });

        Schema::create('articles_user', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('article_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('notes');
            $table->nullableTimestamps();
        });

        Schema::create('recommends', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->nullable();
            $table->timestamps();
        });

        Schema::create('bills', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->nullable();
            $table->timestamps();
        });

        Schema::create('stars', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('starable_type');
            $table->bigInteger('starable_id');
            $table->string('title')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('recommendables');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('recommends');
        Schema::dropIfExists('stars');
        Schema::dropIfExists('billables');
        Schema::dropIfExists('bills');
        Schema::dropIfExists('articles_users');
    }
}