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
        'tax1_name',
        'tax1_rate',
        'tax2_name',
        'tax2_rate',
        'currency',
        'terms_and_conditions',
        'legal_mentions',
        'facebook_url',
        'instagram_url',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];

    protected $casts = [
        'legal_mentions' => 'array',
    ];

    /**
     * Get the company for these settings.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
