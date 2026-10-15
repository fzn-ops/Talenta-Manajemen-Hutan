<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roadmap extends Model
{
    //
    use HasFactory;
    protected $table = 'roadmap';

    protected $fillable = [
        'nama_roadmap',
        'gambar',
        'kategori',
        'partisipan',
    ];

    public function bulanRoadmap()
    {
        return $this->hasMany(BulanRoadmap::class, 'id_roadmap', 'id')->orderBy('bulan_ke', 'asc');
    }
    
    public function pendaftaranRoadmap()
    {
        return $this->hasMany(PendaftaranRoadmap::class, 'id_roadmap', 'id');
    }
}
