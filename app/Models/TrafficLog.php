<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrafficLog extends Model
{
    use HasFactory;

    // Izinkan kolom ini diisi secara massal
    protected $fillable = [
        'location_name',
        'vehicle_count',
    ];
}
