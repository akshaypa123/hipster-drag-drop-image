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
    Schema::create('uploads', function (Blueprint $table) {
        $table->id();
        $table->string('upload_id')->unique(); // unique ID for chunk session
        $table->string('filename');
        $table->string('status')->default('pending'); // pending, completed, failed
        $table->string('checksum')->nullable();
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
        Schema::dropIfExists('uploads');
    }
};
