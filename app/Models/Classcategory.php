<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classcategory extends Model
{
    use HasFactory;
    protected $table = "classcategories";
    //protected $primaryKey = "studentid";

    protected $fillable = [
       'category',
       'ca1score',
       'ca2score',
       'ca3score',
       'examscore',
    ];
}
