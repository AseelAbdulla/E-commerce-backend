<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    protected $fillable = [
        'product_id',
        'unit_name',
        'price',
        'has_discount',
        'discount_price',
        'stock',
    ];

    protected $casts = [
        'unit_name' => 'array',
        'has_discount' => 'boolean',
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // السعر الفعّال (بعد الخصم لو موجود، وإلا السعر الأساسي)
    public function getActivePriceAttribute()
    {
        return ($this->has_discount && $this->discount_price !== null)
            ? $this->discount_price
            : $this->price;
    }
}
