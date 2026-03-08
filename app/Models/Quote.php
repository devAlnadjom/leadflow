<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Quote extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'public_uid',
        'company_id',
        'client_id',
        'lead_record_id',
        'quote_number',
        'status',
        'subtotal',
        'tax1_amount',
        'tax2_amount',
        'total',
        'expire_at',
        'notes',
        'terms',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax1_amount' => 'decimal:2',
        'tax2_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'expire_at' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quote) {
            if (empty($quote->public_uid)) {
                $quote->public_uid = (string) Str::uuid();
            }
        });
    }

    public function leadRecord(): BelongsTo
    {
        return $this->belongsTo(LeadRecord::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
