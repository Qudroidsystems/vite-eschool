<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Studentpersonalityprofile extends Model
{
    use HasFactory;

    protected $table = 'studentpersonalityprofiles';

    protected $primaryKey = 'studentid';

    protected $fillable = [
        'studentid',
        'staffid',
        'schoolclassid',
        'punctuality',
        'neatness',
        'leadership',
        'attitude',
        'reading',
        'honesty',
        'cooperation',
        'selfcontrol',
        'politeness',
        'physicalhealth',
        'stability',
        'gamesandsports',
        'principalscomment',
        'classteacherscomment',
        'termid',
        'sessionid',
        'attendance',

    ];
}
