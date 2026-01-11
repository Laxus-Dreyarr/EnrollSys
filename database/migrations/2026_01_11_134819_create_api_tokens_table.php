<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiTokensTable extends Migration
{
    public function up()
    {
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('token', 64); // SHA256 produces 64-character hex strings
            $table->timestamp('expires_at');
            $table->timestamps();
            
            // Add foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Add index for faster token lookups
            $table->index('token');
            $table->index('expires_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('api_tokens');
    }
}
