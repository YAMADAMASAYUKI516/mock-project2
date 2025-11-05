<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');

            $table->date('requested_date');
            $table->date('target_date');

            $table->time('start_time');
            $table->time('end_time');
            $table->time('break1_start')->nullable();
            $table->time('break1_end')->nullable();
            $table->time('break2_start')->nullable();
            $table->time('break2_end')->nullable();
            $table->text('note');

            $table->enum('status', ['pending', 'approved'])->default('approved');
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
        Schema::dropIfExists('requests');
    }
}
