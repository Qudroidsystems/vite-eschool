<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassTeacher extends Model
{
    use HasFactory;
    protected $table = "classteacher";

    protected $fillable = [
        'staffid',
        'schoolclassid',
        'termid',
        'sessionid',
    ];
}
