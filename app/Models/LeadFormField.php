<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadFormField extends Model
{
    /** @use HasFactory<\Database\Factories\LeadFormFieldFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'lead_form_id',
        'label',
        'field_key',
        'type',
        'is_required',
        'placeholder',
        'options',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'options' => 'array',
        ];
    }

    /**
     * Get the parent lead form.
     */
    public function leadForm(): BelongsTo
    {
        return $this->belongsTo(LeadForm::class);
    }
}
