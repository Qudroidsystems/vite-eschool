<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schoolclass extends Model
{
    use HasFactory;
    protected $table = "schoolclass";

    protected $fillable = [
        'schoolclass',
        'arm',
        'classcategoryid',
    ];
}
