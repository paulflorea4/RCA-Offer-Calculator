<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('businessName')->nullable();
            $table->string('cnp/cui');
            $table->date('drivingLicense')->nullable();
            $table->string('idType')->nullable();
            $table->string('idNumber')->nullable();
            $table->string('email')->nullable();
            $table->string('mobileNumber')->nullable();
            $table->string('county');
            $table->string('city');
            $table->string('street');
            $table->string('houseNumber')->nullable();
            $table->string('building')->nullable();
            $table->string('staircase')->nullable();
            $table->string('floor')->nullable();
            $table->string('apartment')->nullable();
            $table->string('postcode')->nullable();
            $table->string('licensePlate');
            $table->string('vin');
            $table->string('driverFirstName')->nullable();
            $table->string('driverLastName')->nullable();
            $table->string('driverCNP')->nullable();
            $table->string('driverIdNumber')->nullable();
            $table->string('driverMobilePhone')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
