<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadRecord extends Model
{
    /** @use HasFactory<\Database\Factories\LeadRecordFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'lead_form_id',
        'name',
        'email',
        'phone',
        'payload',
        'source',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    /**
     * Get the widget form for this lead record.
     */
    public function leadForm(): BelongsTo
    {
        return $this->belongsTo(LeadForm::class);
    }
}
