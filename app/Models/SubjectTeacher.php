<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectTeacher extends Model
{
    use HasFactory;
    protected $table = "subjectteacher";
    //protected $primaryKey = "userid";

    protected $fillable = [
        'userid',
        'staffid',
        'subjectid',
        'termid',
        'sessionid'

    ];
    
    public function schoolsession() {
        return $this->belongsTo(SchoolSession::class, 'sessionid');
    }

 
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subjectid');
    }

    public function schoolterm()
    {
        return $this->belongsTo(Schoolterm::class, 'termid');
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'staffid');
    }
}
