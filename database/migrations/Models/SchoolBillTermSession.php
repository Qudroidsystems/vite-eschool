<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolBillTermSession extends Model
{
    use HasFactory;
    protected $table = "school_bill_class_term_session";

    protected $fillable = [
        'bill_id',
        'class_id',
        'termid_id',
        'session_id',
        'createdBy'
    ];
}
