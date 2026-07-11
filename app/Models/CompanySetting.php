<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_name',
        'logo_path',
        'address',
        'email',
        'phone',
        'tax_id',
    ];

    public static function getOrNew(): self
    {
        return self::first() ?? new self([
            'company_name' => 'My Business',
        ]);
    }
}
