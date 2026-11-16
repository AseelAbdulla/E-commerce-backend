<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'name' => [
                'ar' => $this->name['ar'] ?? null,
                'en' => $this->name['en'] ?? null,
            ],

            'description' => [
                'ar' => $this->description['ar'] ?? '',
                'en' => $this->description['en'] ?? '',
            ],

            'slug' => $this->slug,

            'sku' => $this->sku,

            'price' => $this->base_price,

            'discount_price' => $this->discount_price,

            'has_discount' => $this->has_discount,

            'stock' => $this->stock,

            'low_stock_threshold' => $this->low_stock_threshold,

            'status' => $this->status,

            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),

            // الصور
            'images' => ProductImageResource::collection(
                $this->whenLoaded('images')
            ),

            // وحدات المنتج المستخدمة في نموذج التعديل
            'units' => ProductUnitResource::collection(
                $this->whenLoaded('units')
            ),

            // الصورة الرئيسية فقط للبطاقة
            'main_image' => $this->whenLoaded('images', function () {

                $mainImage = $this->images
                    ->where('is_main', true)
                    ->sortBy('sort_order')
                    ->first();

                if (!$mainImage) {
                    $mainImage = $this->images
                        ->sortBy('sort_order')
                        ->first();
                }

                return $mainImage
                    ? asset('storage/' . $mainImage->image_path)
                    : null;
            }),

            // يظهر فقط بمنتجات جاية من endpoint الأكثر مبيعاً
            'sales_count' => $this->when(
                isset($this->sales_count),
                fn () => (int) $this->sales_count
            ),

            // يظهر فقط بمنتجات جاية من endpoint الأكثر مبيعاً
            'badge' => $this->when(
                isset($this->badge),
                fn () => $this->badge
            ),

            // متوسط التقييم وعدد التقييمات (يظهر فقط لو تم تحميلهم)
            'rating_average' => $this->when(
                $this->approved_reviews_avg_rating !== null,
                fn () => round((float) $this->approved_reviews_avg_rating, 1)
            ),

            'reviews_count' => $this->when(
                isset($this->approved_reviews_count),
                fn () => (int) $this->approved_reviews_count
            ),

            'created_at' => $this->created_at,
        ];
    }
}
