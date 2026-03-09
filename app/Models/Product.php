<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'category_id',
        'name',
        'description',
        'status',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function views()
    {
        return $this->hasMany(ProductView::class);
    }

    public function likes()
    {
        return $this->hasMany(ProductLike::class);
    }

    public function orderItems()
    {
        return $this->hasManyThrough(OrderItem::class, ProductVariant::class, 'product_id', 'product_variant_id');
    }

    public function completedOrderItems()
    {
        return $this->hasManyThrough(OrderItem::class, ProductVariant::class, 'product_id', 'product_variant_id')
            ->whereHas('order', fn($q) => $q->where('status', 'completed'));
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
