<?php

namespace App\Listeners;


use App\Events\ImportOfferAccepted;
use App\Events\ImportOfferRejected;
use App\Models\InternalNotification;
use App\Models\User;

class SendAdminImportOfferResponseNotification
{
    public function handle(ImportOfferAccepted|ImportOfferRejected $event): void
    {
        $importRequest = $event->importRequest;
        $user = $importRequest->user;
        $isAccepted = $event instanceof ImportOfferAccepted;

        // جلب جميع المدراء والموظفين
        $recipients = User::role(['admin', 'employee'], 'sanctum')->get();

        $title = [
            'ar' => $isAccepted ? 'قبول عرض سعر استيراد' : 'رفض عرض سعر استيراد',
            'en' => $isAccepted ? 'Import Offer Accepted' : 'Import Offer Rejected',
        ];

        $message = [
            'ar' => $isAccepted
                ? "قام العميل ({$user?->name}) بـ قبول عرض السعر لطلب الاستيراد رقم (#{$importRequest->id})."
                : "قام العميل ({$user?->name}) بـ رفض عرض السعر لطلب الاستيراد رقم (#{$importRequest->id}).",
            'en' => $isAccepted
                ? "Customer ({$user?->name}) accepted the price offer for import request (#{$importRequest->id})."
                : "Customer ({$user?->name}) rejected the price offer for import request (#{$importRequest->id}).",
        ];

        foreach ($recipients as $recipient) {
            InternalNotification::create([
                'user_id' => $recipient->id,
                'title'   => $title,
                'message' => $message,
                'type'    => $isAccepted ? 'import_offer_accepted' : 'import_offer_rejected',
                'is_read' => false,
                'sent_at' => now(),
            ]);
        }
    }
}
