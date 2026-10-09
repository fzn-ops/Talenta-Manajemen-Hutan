<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BulanRoadmap extends Model
{
    //
    use HasFactory;
    protected $table='bulan_roadmap';
    protected $fillable=[
        'id_roadmap',
        'bulan_Ke',
    ];

    public function roadmap()
    {
        return $this->belongsTo(Roadmap::class, 'id_roadmap', 'id');
    }
}
