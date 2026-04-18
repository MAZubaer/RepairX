<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRecord extends Model
{
    use HasFactory;

    protected $primaryKey = 'service_id';

    protected $fillable = [
        'customer_id',
        'shop_id',
        'phone_number',
        'phone_model',
        'phone_imei_number',
        'customer_problem',
        'shop_problem',
        'status',
        'repair_cost',
        'rating',
    ];

    protected $casts = [
        'in_progress_emailed_at' => 'datetime',
        'completed_emailed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'shop_id');
    }
}
