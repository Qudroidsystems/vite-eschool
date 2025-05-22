<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectRegistrationStatus extends Model
{
    use HasFactory;
    protected $table = "subjectRegistrationStatus";
    //protected $primaryKey = "studentid";

    protected $fillable = [
        'studentid',
        'studentclassid',
        'subjectid',
        'termid',
        'sessionid',
        'Status',

    ];
}
