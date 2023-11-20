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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('author_id');
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->text('content')->nullable();
            $table->string('location')->nullable();
            $table->integer('like_count')->default(0);
            $table->timestamps();

            $table->foreign('author_id')->references('id')->on('users'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
///////////////////////////////////////////////////

    // public function up()
    // {
    //     Schema::create('posts', function (Blueprint $table) {
    //         $table->id('post_id');
    //         $table->unsignedBigInteger('sender_id');
    //         $table->unsignedBigInteger('author_id');
    //         $table->text('content');
    //         $table->string('location')->nullable();
    //         $table->integer('like_count')->default(0);
    //         $table->timestamps();
    //     });

    //     Schema::create('post_images', function (Blueprint $table) {
    //         $table->id('image_id');
    //         $table->unsignedBigInteger('post_id');
    //         $table->string('image_url');
    //         $table->timestamps();

    //         $table->foreign('post_id')->references('post_id')->on('posts')->onDelete('cascade');
    //     });
    // }

    // public function down()
    // {
    //     Schema::dropIfExists('post_images');
    //     Schema::dropIfExists('posts');
    // }
};
