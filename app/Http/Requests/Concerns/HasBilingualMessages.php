<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Support\Facades\App;

trait HasBilingualMessages
{
    /**
     * يحول مصفوفة رسائل ثنائية اللغة إلى رسائل بلغة واحدة
     * حسب اللغة الحالية للتطبيق (يحددها middleware SetLocaleFromHeader)
     *
     * الاستخدام داخل messages():
     * return $this->bilingual([
     *     'country.required' => ['ar' => '...', 'en' => '...'],
     * ]);
     */
    protected function bilingual(array $messages): array
    {
        $locale = App::getLocale();

        return array_map(
            fn ($translations) => $translations[$locale] ?? $translations['ar'],
            $messages
        );
    }
}
