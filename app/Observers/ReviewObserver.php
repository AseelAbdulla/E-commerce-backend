<?php

namespace App\Observers;

use App\Models\Review;
use App\Models\InternalNotification;
use App\Models\User;

class ReviewObserver
{
    public function created(Review $review): void
    {
        $recipients = User::role(['admin', 'employee'], 'sanctum')->get();

        // 1. معالجة اسم الكاتب
        $rawAuthor = $review->visitor_name ?? $review->user?->name ?? 'زائر';
        $authorAr = is_array($rawAuthor) ? ($rawAuthor['ar'] ?? reset($rawAuthor)) : $rawAuthor;
        $authorEn = is_array($rawAuthor) ? ($rawAuthor['en'] ?? reset($rawAuthor)) : $rawAuthor;

        // 2. معالجة اسم المنتج (أحد أسباب الخطأ الشائعة)
        $rawProduct = $review->product?->name ?? 'منتج';
        
        if (is_array($rawProduct)) {
            $productAr = $rawProduct['ar'] ?? reset($rawProduct);
            $productEn = $rawProduct['en'] ?? reset($rawProduct);
        } else {
            // التحقق في حال استخدام Spatie Translatable Directly
            $productAr = is_object($review->product) && method_exists($review->product, 'getTranslation')
                ? $review->product->getTranslation('name', 'ar')
                : $rawProduct;

            $productEn = is_object($review->product) && method_exists($review->product, 'getTranslation')
                ? $review->product->getTranslation('name', 'en')
                : $rawProduct;
        }

        // 3. إنشاء الإشعار بأمان
        foreach ($recipients as $recipient) {
            InternalNotification::create([
                'user_id' => $recipient->id,
                'type' => 'review',
                'title' => [
                    'ar' => 'تقييم جديد منتظر للمراجعة',
                    'en' => 'New Pending Review'
                ],
                'message' => [
                    'ar' => "أضاف {$authorAr} تقييماً ({$review->rating}/5) على: {$productAr}",
                    'en' => "{$authorEn} left a ({$review->rating}/5) review on: {$productEn}"
                ],
                'is_read' => false,
                'sent_at' => now(),
            ]);
        }
    }
}

