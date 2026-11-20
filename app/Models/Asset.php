<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['name', 'brand', 'image_url', 'is_under_warranty', 'purchase_date'];

    protected function casts(): array
    {
        return [
            'is_under_warranty' => 'boolean',
            'purchase_date' => 'date',
        ];
    }

    public function employeeAssets(): HasMany
    {
        return $this->hasMany(EmployeeAsset::class);
    }
}