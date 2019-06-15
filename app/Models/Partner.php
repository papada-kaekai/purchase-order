<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\SoftDeletes;
use Moloquent;

class Partner extends Moloquent
{
    use SoftDeletes;

    protected $connection = 'mongodb';

    protected $collection = 'partners';

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    protected $fillable = [
        'code',
        'name',
        'address',
        'tel',
        'tax',
        'active',
        'mobile',
        'fax',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

}