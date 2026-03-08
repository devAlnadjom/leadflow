<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToCompany;

class ClientNote extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'client_id',
        'user_id',
        'content',
        'type',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
