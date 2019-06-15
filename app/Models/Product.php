<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\SoftDeletes;
use Moloquent;

class Product extends Moloquent
{
    use SoftDeletes;

    protected $connection = 'mongodb';

    protected $collection = 'products';

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    protected $fillable = [
        'code',
        'name',
        'price',
        'unit',
        'active',
        'category',
        'image',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

}