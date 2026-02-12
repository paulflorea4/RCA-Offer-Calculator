<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $table = 'quotes';

    protected $fillable = [
        'firstName',
        'lastName',
        'businessName',
        'cnp/cui',
        'drivingLicense',
        'idType',
        'idNumber',
        'email',
        'mobileNumber',
        'county',
        'city',
        'street',
        'houseNumber',
        'building',
        'staircase',
        'floor',
        'apartment',
        'postcode',
        'licensePlate',
        'vin',
        'driverFirstName',
        'driverLastName',
        'driverCNP',
        'driverIdNumber',
        'driverMobilePhone',
    ];

    // Casts (optional, you can cast dates, JSON, etc.)
    protected $casts = [
        'drivingLicense' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
