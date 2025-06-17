<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentBillPaymentRecord extends Model
{
    use HasFactory;

    protected $table = 'student_bill_payment_record';

    protected $fillable = [
        'student_bill_payment_id',
        'total_bill',
        'amount_paid',
        'last_payment',
        'amount_owed',
        'complete_payment',
        'class_id',
        'termid_id',
        'session_id',
        'generated_by',
        'payment_date',
        'received_by',
    ];

    /**
     * Define the relationship with StudentBillPayment.
     */
    public function studentBillPayment()
    {
        return $this->belongsTo(StudentBillPayment::class, 'student_bill_payment_id', 'id');
    }

    
}