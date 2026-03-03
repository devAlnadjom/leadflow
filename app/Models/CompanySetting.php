<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanySetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'quote_prefix',
        'invoice_prefix',
        'default_tax_rate',
        'currency',
        'terms_and_conditions',
    ];

    /**
     * Get the company for these settings.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
