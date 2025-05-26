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
     /**
     * Get the subject teacher associated with the subject class.
     */
    public function subjectTeacher()
    {
        return $this->belongsTo(SubjectTeacher::class, 'subjectteacherid', 'id');
    }

    /**
     * Get the school class associated with the subject class.
     */
    public function schoolClass()
    {
        return $this->belongsTo(Schoolclass::class, 'schoolclassid', 'id');
    }

    /**
     * Get the subject associated with the subject class.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subjectid', 'id');
    }

}
