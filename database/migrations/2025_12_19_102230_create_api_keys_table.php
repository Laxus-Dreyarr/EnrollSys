<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique(); // Hashed API key
            $table->string('client_name');
            $table->string('email');
            $table->foreignId('created_by')->nullable()->constrained('admin');
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('api_access_logs', function (Blueprint $table) {
            $table->id();
            $table->string('api_key', 32); // Partial hash
            $table->string('ip_address', 45);
            $table->string('endpoint');
            $table->text('user_agent')->nullable();
            $table->json('request_params')->nullable();
            $table->integer('response_code')->nullable();
            $table->float('response_time')->nullable();
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
        Schema::dropIfExists('api_keys');
    }
}
