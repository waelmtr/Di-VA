<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    protected $connection = 'mysql';
    public function up()
    {
        Schema::connection($this->connection)->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('photo')->nullable();
            $table->string('password');
            $table->string('bio')->default('Hi There')->chang();
            $table->bigInteger('gender');
            $table->date('birthday');
            $table->string('code')->nullable();
            $table->Boolean('is_promtion')->default(0)->chang();
            $table->integer('number_of_posts')->default(0)->chang();
            $table->bigInteger('pay')->default(0);
            $table->string('FCM');
            $table->rememberToken();
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
        Schema::connection($this->connection)->dropIfExists('users');
    }
}