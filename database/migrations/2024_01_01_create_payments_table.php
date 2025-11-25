<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('payment_intent_id')->unique();
            $table->string('client_key');
            $table->string('transaction_id')->nullable();
            $table->decimal('amount', 8, 2);
            $table->string('currency')->default('PHP');
            $table->string('payment_method')->nullable();
            $table->string('status'); // pending, succeeded, failed
            $table->timestamp('paid_at')->nullable();
            $table->text('payment_details')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->index(['student_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
}