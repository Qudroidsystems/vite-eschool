<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Studentclass extends Model
{
    use HasFactory;
    protected $table = "studentclass";
    protected $primaryKey= "studentId";

    protected $fillable = [
        'studentId',
        'schoolclassid',
        'termid',
        'sessionid',

    ];

    public function armRelation()
    {
        return $this->belongsTo(Schoolarm::class, 'arm');
    }

}
