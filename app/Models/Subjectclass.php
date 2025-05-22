<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subjectclass extends Model
{
    use HasFactory;
    protected $table = "subjectclass";

    protected $fillable = [
        'schoolclassid',
        'subjectid',
        'subjectteacherid',

    ];

}
