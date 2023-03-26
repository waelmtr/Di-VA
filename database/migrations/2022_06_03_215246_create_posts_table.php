<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    protected $connection = 'mongodb';

    public function up()
    {
        Schema::connection($this->connection)->create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('content')->nullable();
            $table->string('photo');
            $table->bigInteger('user_id');
            $table->array('upvotes_user_id')->defaultValue(0);
            $table->array('downvotes_user_id')->defaultValue(0);
            $table->array('interest_id');
            $table->array('comments')->nullable()->chang();
            $table->Boolean('is_prometed')->default(false);
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
        Schema::connection($this->connection)->dropIfExists('posts');
    }
}
