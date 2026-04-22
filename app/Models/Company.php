<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Company extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($company) {
            if (!$company->slug) {
                $company->slug = Str::slug($company->name) . '-' . Str::random(5);
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'phone',
        'email',
        'website',
        'industry',
        'address',
        'served_areas',
        'logo_path',
        'primary_color',
        'secondary_color',
        'is_active',
        'slug',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'served_areas' => 'array',
        ];
    }

    /**
     * Get all users that belong to this company.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all quotes for this company.
     */
    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    /**
     * Get all invoices for this company.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get this company's settings.
     */
    public function settings(): HasOne
    {
        return $this->hasOne(CompanySetting::class);
    }

    /**
     * Get all lead forms for this company.
     */
    public function leadForms(): HasMany
    {
        return $this->hasMany(LeadForm::class);
    }

    /**
     * Get all clients for this company.
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Get all services for this company.
     */
    public function services(): HasMany
    {
        return $this->hasMany(CompanyService::class);
    }

    /**
     * Get all galleries for this company.
     */
    public function galleries(): HasMany
    {
        return $this->hasMany(CompanyGallery::class);
    }
}
