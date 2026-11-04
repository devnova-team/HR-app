<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = ['name', 'brand', 'image_url', 'is_under_warranty', 'purchase_date'];

    protected function casts(): array
    {
        return [
            'is_under_warranty' => 'boolean',
            'purchase_date' => 'date',
        ];
    }
}
