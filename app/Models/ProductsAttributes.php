<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsAttributes extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'name',
        'type',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
