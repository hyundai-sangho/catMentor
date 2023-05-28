<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnswersTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('answers', function (Blueprint $table) {
      $table->id();
      $table->string('unique_id', 50);
      $table->string('user_uniqueid', 50);
      $table->string('content', 2000);
      $table->string('accept', 10);
      $table->string('questions_uniqueid', 30)->nullable();
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
    Schema::dropIfExists('answers');
  }
}
