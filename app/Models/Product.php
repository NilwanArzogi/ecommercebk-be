<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model {
    protected $fillable = [
        'name', 'description', 'price', 'stock',
        'image', 'category', 'is_active', 'sold_count'
    ];

    public function orderItems() {
        return $this->hasMany(OrderItem::class);
    }
}