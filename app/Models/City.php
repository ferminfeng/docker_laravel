<?php

namespace App\Models;

class City extends BaseModel
{
    protected $table = 'city';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'name'
    ];

}
