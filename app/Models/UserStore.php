<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserStore extends Pivot
{
    protected $table = 'user_stores';

    protected $fillable = [
        'user_id',
        'store_id',
        'role',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
