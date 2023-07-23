<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOperatorSmsHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('operator_sms_histories', function (Blueprint $table) {
            $table->id();
            $table->string('date')->nullable();
            $table->string('service_id')->nullable();
            $table->string('bu')->nullable();
            $table->string('type')->nullable();
            $table->string('service_name')->nullable();
            $table->integer('total_sub_base')->nullable();
            $table->integer('activation')->nullable();
            $table->integer('renewal_count')->nullable();
            $table->integer('deactivation')->nullable();
            $table->integer('ppu_success_count')->nullable();
            $table->integer('total_success_count')->nullable();

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
        Schema::dropIfExists('operator_sms_history');
    }
}
