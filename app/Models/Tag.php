<?php

namespace App\Models;

use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['company_id', 'name', 'color'];

    protected static function booted(): void
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function ($tag) {
            if (auth()->check() && empty($tag->company_id)) {
                $tag->company_id = auth()->user()->company_id;
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function leads(): MorphToMany
    {
        return $this->morphedByMany(LeadRecord::class, 'taggable');
    }

    public function clients(): MorphToMany
    {
        return $this->morphedByMany(Client::class, 'taggable');
    }
}
