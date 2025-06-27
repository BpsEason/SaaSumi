<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant; // If guests are tied to central tenant

class Guest extends Model
{
    use HasFactory;
    use BelongsToTenant; // Use this if guests belong to the central tenant and share tables

    protected $fillable = [
        'tenant_id', // Add this if using BelongsToTenant
        'name',
        'email',
        'phone',
        'country',
        'stay_count',
        'last_stay_date',
        'status', // e.g., 'active', 'inactive'
    ];

    protected $casts = [
        'last_stay_date' => 'date',
    ];

    // Example relationship if guests have bookings
    // public function bookings()
    // {
    //     return $this->hasMany(Booking::class);
    // }
}
