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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            // message the column is for the message body.
            $table->text('message');
            // "chat_id" is the foreigner key for the chats table and the user_id for the users’ table
            $table->unsignedBigInteger('chat_id');
            $table->unsignedBigInteger('user_id')->nullable();
            // the message can be text, image, URL, etc so we add the "type" column.
            $table->string('type')->default('text');

            // "data" is for any metadata for the message to be stored as JSON in the database.
            // here we will save “seen by” and the message’s status
            $table->text('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_messages');
    }
};
