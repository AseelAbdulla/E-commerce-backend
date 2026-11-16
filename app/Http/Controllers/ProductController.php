<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\OrderDetail;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;

use App\Http\Resources\ProductResource;

class ProductController extends Controller
{

    // عرض جميع المنتجات
    public function index()
    {
        $products = Product::with([
            'category',
            'images',
            'units',
        ])->get();

        return ProductResource::collection($products);
    }

    /**
     * المنتجات الأكثر مبيعاً (مع fallback ذكي)
     *
     * الأولوية: 1) الأكثر مبيعاً فعلياً (أي حالة مخزون)
     *           2) عليها خصم ومتوفرة
     *           3) قارب مخزونها ينفذ
     *           4) الأحدث إضافة ومتوفرة
     *           5) نفذ مخزونها بالكامل (لملء الباقي فقط)
     *
     * المخزون هنا يُحسب من مجموع stock بجدول product_units، وليس
     * من عمود stock بجدول products مباشرة.
     */
    public function bestSellers(Request $request)
    {
        $limit = (int) $request->query('limit', 10);

        $completedStatuses = [
            OrderStatus::DELIVERED->value,
        ];

        /*
        |--------------------------------------------------------------------------
        | حساب مجموع المخزون لكل منتج من وحداته (product_units)
        |--------------------------------------------------------------------------
        */
        $stockByProduct = DB::table('product_units')
            ->select('product_id', DB::raw('SUM(stock) as total_stock'))
            ->groupBy('product_id')
            ->pluck('total_stock', 'product_id');

        $allProductIds = Product::pluck('low_stock_threshold', 'id');

        $getTotalStock = fn ($productId) => (int) ($stockByProduct[$productId] ?? 0);

        $inStockIds = $allProductIds->keys()
            ->filter(fn ($id) => $getTotalStock($id) > 0)
            ->values();

        $outOfStockAllIds = $allProductIds->keys()
            ->filter(fn ($id) => $getTotalStock($id) <= 0)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | المرحلة 1: الأكثر مبيعاً فعلياً (بغض النظر عن حالة المخزون)
        |--------------------------------------------------------------------------
        */
        $salesByProduct = OrderDetail::query()
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->whereNotNull('product_id')
            ->whereHas('order', function ($query) use ($completedStatuses) {
                $query->whereIn('status', $completedStatuses);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->pluck('total_sold', 'product_id');

        $selectedIds = $salesByProduct->keys()->all();
        $remaining = $limit - count($selectedIds);

        /*
        |--------------------------------------------------------------------------
        | المرحلة 2: عليها خصم ومتوفرة بالمخزون (من وحداتها)
        |--------------------------------------------------------------------------
        */
        if ($remaining > 0) {
            $discountedIds = Product::query()
                ->whereIn('id', $inStockIds)
                ->where('has_discount', true)
                ->whereNotNull('discount_price')
                ->whereNotIn('id', $selectedIds)
                ->latest('id')
                ->limit($remaining)
                ->pluck('id')
                ->all();

            $selectedIds = array_merge($selectedIds, $discountedIds);
            $remaining = $limit - count($selectedIds);
        }

        /*
        |--------------------------------------------------------------------------
        | المرحلة 3: قارب مخزونها ينفذ (مجموع وحداتها <= العتبة، وأكبر من 0)
        |--------------------------------------------------------------------------
        */
        if ($remaining > 0) {
            $lowStockIds = $inStockIds
                ->filter(function ($id) use ($allProductIds, $getTotalStock, $selectedIds) {
                    if (in_array($id, $selectedIds)) {
                        return false;
                    }
                    $threshold = $allProductIds[$id];
                    return $threshold !== null && $getTotalStock($id) <= $threshold;
                })
                ->sortBy(fn ($id) => $getTotalStock($id))
                ->take($remaining)
                ->values()
                ->all();

            $selectedIds = array_merge($selectedIds, $lowStockIds);
            $remaining = $limit - count($selectedIds);
        }

        /*
        |--------------------------------------------------------------------------
        | المرحلة 4: الأحدث إضافة ومتوفرة
        |--------------------------------------------------------------------------
        */
        if ($remaining > 0) {
            $newestIds = Product::query()
                ->whereIn('id', $inStockIds)
                ->whereNotIn('id', $selectedIds)
                ->latest('id')
                ->limit($remaining)
                ->pluck('id')
                ->all();

            $selectedIds = array_merge($selectedIds, $newestIds);
            $remaining = $limit - count($selectedIds);
        }

        /*
        |--------------------------------------------------------------------------
        | المرحلة 5: نفذ مخزونها بالكامل (لملء الباقي فقط، أقل أولوية)
        |--------------------------------------------------------------------------
        */
        if ($remaining > 0) {
            $outOfStockIds = Product::query()
                ->whereIn('id', $outOfStockAllIds)
                ->whereNotIn('id', $selectedIds)
                ->latest('id')
                ->limit($remaining)
                ->pluck('id')
                ->all();

            $selectedIds = array_merge($selectedIds, $outOfStockIds);
        }

        if (empty($selectedIds)) {
            return ProductResource::collection(collect());
        }

        /*
        |--------------------------------------------------------------------------
        | جلب المنتجات كاملة مع الحفاظ على ترتيب الأولوية
        |--------------------------------------------------------------------------
        */
        $orderedIds = array_values($selectedIds);

        $products = Product::with(['category', 'images', 'units'])
            ->whereIn('id', $orderedIds)
            ->get()
            ->sortBy(fn ($product) => array_search($product->id, $orderedIds))
            ->values();

        // إرفاق sales_count وbadge لكل منتج
        $products->each(function ($product) use ($salesByProduct, $getTotalStock) {
            $salesCount = (int) ($salesByProduct[$product->id] ?? 0);
            $totalStock = $getTotalStock($product->id);

            $product->sales_count = $salesCount;
            $product->badge = $this->determineBadge($product, $salesCount, $totalStock);
        });

        return ProductResource::collection($products);
    }

    /**
     * تحديد الشارة (badge) الأنسب للمنتج بناءً على حالته الفعلية
     * الأولوية: نفذ > خصم > قارب على النفاذ > الأكثر مبيعاً > بدون شارة
     */
    private function determineBadge(Product $product, int $salesCount, int $totalStock): ?string
    {
        if ($totalStock <= 0) {
            return 'out_of_stock';
        }

        if ($product->has_discount && $product->discount_price) {
            return 'discount';
        }

        if ($product->low_stock_threshold !== null && $totalStock <= $product->low_stock_threshold) {
            return 'low_stock';
        }

        if ($salesCount > 0) {
            return 'best_seller';
        }

        return null;
    }

    // حفظ المنتج
    public function store(StoreProductRequest $request)
    {

        $data = $request->validated();
 
        $product = Product::create([

            'category_id' => $data['category_id'],

            'name' => [

                'ar' => $data['name_ar'],

                'en' => $data['name_en'],

            ],

            'description' => [
                'ar' => $data['description_ar'] ?? '',
                'en' => $data['description_en'] ?? '',
            ],
  
            'sku' => $data['sku'],

            'base_price' => $data['base_price'],

            'has_discount' =>
                $data['has_discount'] ?? false,

            'discount_price' =>
                $data['discount_price'] ?? null,

            'stock' => $data['stock'],

            'low_stock_threshold' =>
                $data['low_stock_threshold'] ?? 5,

            'status' =>
                $data['status'] ?? 'active',

        ]);
 
        return new ProductResource(
            $product->load('category')
        );

    }
  
    // عرض منتج واحد
    public function show(Product $product)
    {

        $product->load([
            'category',
            'images',
            'units'
        ]);

        // حساب متوسط التقييم وعدد التقييمات المعتمدة
        $product->loadAvg('approvedReviews', 'rating');
        $product->loadCount('approvedReviews');

        return new ProductResource($product);

    }
  
    // تحديث المنتج
    public function update(
        UpdateProductRequest $request,
        Product $product
    )
    {

        $data = $request->validated();
 
        $product->update([

            'category_id' =>
                $data['category_id'],

            'name' => [

                'ar' => $data['name_ar'],

                'en' => $data['name_en'],

            ],

            'description' => [
                'ar' => $data['description_ar'] ?? '',
                'en' => $data['description_en'] ?? '',
            ],

           
 
            'sku' =>
                $data['sku']
                ?? $product->sku,
 
            'base_price' =>
                $data['base_price'],
 
            'has_discount' =>
                $data['has_discount'] ?? false,
 
            'discount_price' =>
                $data['discount_price'] ?? null,
 
            'stock' =>
                $data['stock'],
 
            'low_stock_threshold' =>
                $data['low_stock_threshold'] ?? 5,
 
            'status' =>
                $data['status'] ?? 'active',

        ]);
 
        return new ProductResource(
            $product->load('category')
        );

    }
  
    // حذف المنتج
    public function destroy(Product $product)
    {

        $product->delete();

        return response()->json([

            'message' =>
                'Product deleted successfully'

        ]);

    }

}
