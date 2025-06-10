<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolInformation extends Model
{
    use HasFactory;

    protected $table = 'school_information';
    
    protected $fillable = [
        'school_name',
        'school_address',
        'school_phone',
        'school_email',
        'school_logo',
        'school_motto',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the active school information
     */
    public static function getActiveSchool()
    {
        return self::where('is_active', true)->first();
    }

    /**
     * Get the school logo URL
     */
    public function getLogoUrlAttribute()
    {
        return $this->school_logo ? asset('storage/' . $this->school_logo) : null;
    }
}
?>