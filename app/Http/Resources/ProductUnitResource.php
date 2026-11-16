<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductUnitResource extends JsonResource
{

    public function toArray(Request $request): array
    {

        return [

            'id' => $this->id,

            'unit_name' => [

                'ar' => $this->unit_name['ar'] ?? null,

                'en' => $this->unit_name['en'] ?? null,

            ],

            'price' => $this->price,

            'has_discount' => $this->has_discount,

            'discount_price' => $this->discount_price,

            // السعر الفعّال (بعد الخصم لو موجود) - جاهز للاستخدام المباشر بالفرونت
            'active_price' => $this->has_discount && $this->discount_price !== null
                ? $this->discount_price
                : $this->price,

            'stock' => $this->stock,

            'product' => $this->whenLoaded(
                'product',
                function(){

                    return [

                        'id' => $this->product->id,

                        'name' => $this->product->name,

                    ];

                }
            ),

            'created_at' => $this->created_at,

        ];

    }

}
