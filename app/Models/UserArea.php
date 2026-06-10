<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserArea extends Model
{
    protected $table = 'user_area';

    protected $fillable = [
        'user_id',
        'area_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}
