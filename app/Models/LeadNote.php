<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\BelongsToCompany;

class LeadNote extends Model
{
    use BelongsToCompany;

    protected $fillable = ['lead_record_id', 'user_id', 'content', 'company_id', 'type'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leadRecord(): BelongsTo
    {
        return $this->belongsTo(LeadRecord::class);
    }
}
