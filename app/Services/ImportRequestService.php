<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\InternalNotification;
use App\Models\Order;
use App\Events\ImportRequestCreated;
use App\Models\ImportRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Events\ImportRequestReviewed;

class ImportRequestService
{
    public function createRequest(User $user, array $data): ImportRequest
    {
        return DB::transaction(function () use ($user, $data) {
            $importRequest = $user->importRequests()->create([
                'currency'        => $data['currency'] ?? 'USD',
                'phone'           => $data['phone'],
                'address_details' => $data['address_details'],
                'notes'           => $data['notes'] ?? null,
                'status'          => 'pending',
            ]);

            foreach ($data['items'] as $item) {
                $importRequest->items()->create([
                    'product_id' => $item['product_id'],
                    'unit_id'    => $item['unit_id'],
                    'quantity'   => $item['quantity'],
                ]);
            }

            ImportRequestCreated::dispatch($importRequest);

            return $importRequest->load(['items.product.images', 'items.unit', 'user']);
        });
    }

    public function reviewRequest(ImportRequest $importRequest, array $data): ImportRequest
    {
        return DB::transaction(function () use ($importRequest, $data) {
            $action = $data['action'] ?? 'offer_sent';

            if ($action === 'reject') {
                $importRequest->update([
                    'status'           => 'rejected',
                    'rejection_reason' => $data['rejection_reason'] ?? null,
                    'admin_notes'      => $data['admin_notes'] ?? null,
                ]);
            } else {
                $calculatedItemsTotal = 0;

                // تحديث أسعار العناصر وحساب المجموع إجبارياً
                if (isset($data['items']) && is_array($data['items'])) {
                    foreach ($data['items'] as $itemData) {
                        $item = $importRequest->items()->where('id', $itemData['id'])->first();

                        if ($item) {
                            $unitPrice = floatval($itemData['offered_unit_price'] ?? $itemData['unit_price'] ?? 0);
                            $quantity = intval($item->quantity);
                            $subtotal = floatval($itemData['offered_subtotal'] ?? ($unitPrice * $quantity));

                            $item->update([
                                'offered_unit_price' => $unitPrice,
                                'offered_subtotal'   => $subtotal,
                            ]);

                            $calculatedItemsTotal += $subtotal;
                        }
                    }
                }

                $shippingFee = floatval($data['offered_shipping_fee'] ?? $importRequest->offered_shipping_fee ?? 0);
                $itemsTotal = floatval($data['offered_items_total'] ?? $calculatedItemsTotal);
                $grandTotal = floatval($data['offered_grand_total'] ?? ($itemsTotal + $shippingFee));

                $importRequest->update([
                    'status'               => 'offer_sent',
                    'shipping_method'      => $data['shipping_method'] ?? $importRequest->shipping_method,
                    'offered_shipping_fee' => $shippingFee,
                    'offered_items_total'  => $itemsTotal,
                    'offered_grand_total'  => $grandTotal,
                    'offer_expires_at'     => $data['offer_expires_at'] ?? $importRequest->offer_expires_at,
                    'admin_notes'          => $data['admin_notes'] ?? $importRequest->admin_notes,
                ]);
            }

            $freshRequest = $importRequest->fresh(['items.product.images', 'items.unit', 'user']);

            ImportRequestReviewed::dispatch($freshRequest, $action);

            return $freshRequest;
        });
    }

    public function convertOfferToOrder(ImportRequest $importRequest, User $user): Order
    {
        return DB::transaction(function () use ($importRequest, $user) {
            $importRequest->loadMissing(['items.product', 'items.unit']);

            // 1. إعادة إعادة احتساب إجمالي العناصر لضمان عدم وجود أصفار عند التحويل
            $itemsTotal = 0;
            foreach ($importRequest->items as $item) {
                $subtotal = floatval($item->offered_subtotal ?? ($item->offered_unit_price * $item->quantity));
                $itemsTotal += $subtotal;
            }

            $shippingFee = floatval($importRequest->offered_shipping_fee ?? 0);
            $grandTotal = floatval($importRequest->offered_grand_total > 0 ? $importRequest->offered_grand_total : ($itemsTotal + $shippingFee));

            // 2. تحديث حالة طلب الاستيراد مع ضمان تسجيل الإجماليات
            $importRequest->update([
                'status'              => 'offer_accepted',
                'offered_items_total' => $itemsTotal,
                'offered_grand_total' => $grandTotal,
            ]);

            // 3. إنشاء الطلب بالقيم المحسوبة
            $order = Order::create([
                'user_id'           => $user->id,
                'import_request_id' => $importRequest->id,
                'order_type'        => 'import',
                'status'            => OrderStatus::PREPARING->value,
                'customer_name'     => $user->name,
                'customer_phone'    => $importRequest->phone,
                'customer_email'    => $user->email,
                
                'shipping_country'  => 'International',
                'shipping_city'     => 'Import',
                'shipping_region'   => 'Import',
                'shipping_street'   => $importRequest->address_details,
                'shipping_building' => 'N/A',
                
                'notes'             => $importRequest->notes,
                
                'subtotal'          => $itemsTotal,
                'shipping_fee'      => $shippingFee,
                'tax'               => 0,
                'discount'          => 0,
                'total_price'       => $grandTotal,
                
                'payment_method'    => PaymentMethod::CASH_ON_DELIVERY->value ?? 'cod',
                'payment_status'    => PaymentStatus::PENDING->value,
            ]);

            // 4. إدراج التفاصيل مع تعبئة أسعار الوحدات الصريحة
            foreach ($importRequest->items as $item) {
                $productNameSnapshot = match (true) {
                    is_array($item->product?->name) => $item->product->name,
                    is_string($item->product?->name) => ['ar' => $item->product->name, 'en' => $item->product->name],
                    default => ['ar' => 'منتج استيراد', 'en' => 'Import Product'],
                };

                $unitNameSnapshot = match (true) {
                    is_array($item->unit?->name) => $item->unit->name,
                    is_string($item->unit?->name) => ['ar' => $item->unit->name, 'en' => $item->unit->name],
                    default => ['ar' => 'وحدة القياس', 'en' => 'Unit'],
                };

                $unitPrice = floatval($item->offered_unit_price ?? 0);
                $totalPrice = floatval($item->offered_subtotal ?? ($unitPrice * $item->quantity));

                $order->details()->create([
                    'product_id'            => $item->product_id,
                    'unit_id'               => $item->unit_id,
                    'product_name_snapshot' => $productNameSnapshot,
                    'unit_name_snapshot'    => $unitNameSnapshot,
                    'quantity'              => $item->quantity,
                    'unit_price'            => $unitPrice,
                    'total_price'           => $totalPrice,
                ]);
            }

            InternalNotification::create([
                'user_id' => $user->id,
                'title' => [
                    'ar' => 'تم نقل الطلب إلى الطلبات النشطة',
                    'en' => 'Order Transferred to Active Orders'
                ],
                'message' => [
                    'ar' => "تم قبول عرض السعر بنجاح ونقل الطلب رقم (#{$order->id}) إلى قائمة طلباتك وجاري التجهيز.",
                    'en' => "Offer accepted. Order (#{$order->id}) has been created and is being processed."
                ],
                'type' => 'import_order_converted',
                'is_read' => false,
                'sent_at' => now(),
            ]);

            return $order;
        });
    }
}

