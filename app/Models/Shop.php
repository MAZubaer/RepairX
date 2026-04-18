<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $primaryKey = 'shop_id';

    protected $fillable = [
        'user_id',
        'stripe_customer_id',
        'stripe_subscription_id',
        'subscription_status',
        'subscription_plan',
        'expiry_date',
        'current_period_end',
        'description',
        'motto',
        'services_provided',
        'rating',
    ];

    protected $casts = [
        'expiry_date' => 'datetime',
        'current_period_end' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(ShopImage::class, 'shop_id', 'shop_id');
    }

    public function logoImage()
    {
        return $this->hasOne(ShopImage::class, 'shop_id', 'shop_id')->where('type', 'logo');
    }

    public function galleryImages()
    {
        return $this->hasMany(ShopImage::class, 'shop_id', 'shop_id')->where('type', 'gallery');
    }
}
