<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model {
    protected $fillable = [
        'customer_name', 'customer_phone', 'customer_address',
        'total_price', 'status', 'payment_method', 'payment_proof', 'notes'
    ];

    public function items() {
        return $this->hasMany(OrderItem::class);
    }
}