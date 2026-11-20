<?php

namespace App\Http\Resources;

use App\Support\ApiDate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\EmployeeAsset (with `asset` loaded) */
class AssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->asset->id,
            'name' => $this->asset->name,
            'brand' => $this->asset->brand,
            'imageUrl' => $this->asset->image_url,
            'isUnderWarranty' => $this->asset->is_under_warranty,
            'purchaseDate' => ApiDate::isoDate($this->asset->purchase_date),
            'receivedDate' => ApiDate::iso($this->assigned_at),
        ];
    }
}
