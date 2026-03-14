<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WidgetTemplate extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'category',
        'icon',
        'description',
        'layout_mode',
        'submit_button_label',
        'fields',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'fields'    => 'array',
            'is_system' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope: return system templates + templates belonging to the given company.
     */
    public function scopeAvailableFor($query, ?int $companyId): mixed
    {
        return $query->where(function ($q) use ($companyId) {
            $q->where('is_system', true);

            if ($companyId) {
                $q->orWhere('company_id', $companyId);
            }
        });
    }
}
