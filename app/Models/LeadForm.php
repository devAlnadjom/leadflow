<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadForm extends Model
{
    /** @use HasFactory<\Database\Factories\LeadFormFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'embed_key',
        'is_active',
        'submit_button_label',
        'layout_mode',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the company that owns this lead form.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the fields configured for this lead form.
     */
    public function fields(): HasMany
    {
        return $this->hasMany(LeadFormField::class)->orderBy('sort_order');
    }

    /**
     * Get all submitted records for this lead form.
     */
    public function records(): HasMany
    {
        return $this->hasMany(LeadRecord::class)->latest();
    }
}
