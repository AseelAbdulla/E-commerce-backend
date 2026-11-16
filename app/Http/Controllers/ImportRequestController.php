<?php

namespace App\Http\Controllers;

use App\Events\ImportOfferAccepted;
use App\Events\ImportOfferRejected;
use App\Http\Requests\ReviewImportRequest;
use App\Http\Requests\StoreImportRequest;
use App\Http\Resources\ImportRequestResource;
use App\Models\ImportRequest;
use App\Services\ImportRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImportRequestController extends Controller
{
    public function __construct(
        protected ImportRequestService $importRequestService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('admin') || $user->can('view-orders')) {
            $requests = ImportRequest::with(['user', 'items.product', 'items.unit', 'items.product.images'])
                ->latest()
                ->get();
        } else {
            $requests = $user->importRequests()
                ->with(['items.product', 'items.unit', 'items.product.images'])
                ->latest()
                ->get();
        }

        return ImportRequestResource::collection($requests);
    }

    public function store(StoreImportRequest $request): JsonResponse
    {
        // ملاحظة: إشعار الأدمن يتم إطلاقه تلقائياً عبر ImportRequestService أو Event مخصص عند إنشاء الطلب
        $importRequest = $this->importRequestService->createRequest(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'status'  => true,
            'message' => 'تم تقديم طلب الاستيراد بنجاح وهو قيد المراجعة.',
            'data'    => new ImportRequestResource($importRequest),
        ], 201);
    }

    public function show(ImportRequest $importRequest): ImportRequestResource
    {
        $importRequest->load(['items.product', 'items.unit', 'user']);
        return new ImportRequestResource($importRequest);
    }

    public function review(ReviewImportRequest $request, ImportRequest $importRequest): JsonResponse
    {
        // ملاحظة: إشعار العميل يتم إطلاقه عبر ImportRequestReviewed Event داخل reviewRequest في Service
        $updatedRequest = $this->importRequestService->reviewRequest(
            $importRequest,
            $request->validated()
        );

        $isRejected = $request->action === 'reject';

        return response()->json([
            'status'  => true,
            'message' => $isRejected ? 'تم رفض الطلب.' : 'تم إرسال عرض السعر للعميل بنجاح.',
            'data'    => new ImportRequestResource($updatedRequest),
        ]);
    }

    // قبول عرض السعر من قبل العميل
    public function acceptOffer(Request $request, $id): JsonResponse
    {
        $importRequest = $request->user()->importRequests()
            ->with(['items'])
            ->findOrFail($id);

        if ($importRequest->status !== 'offer_sent') {
            return response()->json(['message' => 'لا يمكن اتخاذ إجراء على هذا الطلب حالياً.'], 422);
        }

        // استدعاء الخدمة لتحويل الطلب وإنشاء السجل المرتبط
        $order = $this->importRequestService->convertOfferToOrder($importRequest, $request->user());

        return response()->json([
            'status'  => true,
            'message' => 'تم قبول عرض السعر بنجاح ونقل الطلب إلى قائمة الطلبات النشطة.',
            'data'    => [
                'import_request_id' => $importRequest->id,
                'order_id'          => $order->id,
                'status'            => $order->status,
            ]
        ]);
    }

    public function rejectOffer(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $importRequest = $user->importRequests()->findOrFail($id);

        if ($importRequest->status !== 'offer_sent') {
            return response()->json(['message' => 'لا يمكن اتخاذ إجراء على هذا الطلب حالياً.'], 422);
        }

        $reason = $request->input('reason', 'تم رفض العرض من قبل العميل');

        $importRequest->update([
            'status'           => 'offer_rejected',
            'rejection_reason' => $reason
        ]);

        // 🚀 إطلاق حدث رفض العرض لإشعار الأدمن
        event(new ImportOfferRejected($importRequest));

        return response()->json([
            'status'  => true,
            'message' => 'تم رفض عرض السعر.',
            'data'    => new ImportRequestResource($importRequest)
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $importRequest = $request->user()->importRequests()->findOrFail($id);

        if ($importRequest->status !== 'pending') {
            return response()->json([
                'message' => 'لا يمكن إلغاء الطلب بعد البدء في معالجته أو مراجعته.'
            ], 422);
        }

        $importRequest->update([
            'status' => 'cancelled'
        ]);

        return response()->json([
            'message' => 'تم إلغاء طلب الاستيراد بنجاح.'
        ]);
    }

    public function update(StoreImportRequest $request, $id)
    {
        $importRequest = $request->user()->importRequests()->findOrFail($id);

        if ($importRequest->status !== 'pending') {
            return response()->json([
                'message' => 'لا يمكن تعديل الطلب بعد البدء في مراجعته أو تجهيزه.'
            ], 422);
        }

        $importRequest->update($request->only([
            'currency',
            'phone',
            'address_details',
            'notes'
        ]));

        if ($request->has('items')) {
            $importRequest->items()->delete();

            foreach ($request->items as $item) {
                $importRequest->items()->create([
                    'product_id' => $item['product_id'],
                    'unit_id'    => $item['unit_id'],
                    'quantity'   => $item['quantity'],
                ]);
            }
        }

        return response()->json([
            'message' => 'تم تعديل طلب الاستيراد بنجاح.',
            'data'    => new ImportRequestResource($importRequest->load(['items.product.images', 'items.unit']))
        ]);
    }
}
