<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roadmap extends Model
{
    //
    use HasFactory;
    protected $table = 'Roadmap';

    protected $fillable = [
        'judul',
        'gambar',
        'kategori',
        'partisipan',
    ];

    public function bulanRoadmap(){
        return $this->hasMany(BulanRoadmap::class, 'id_roadmap', 'id');
    }
    
    public function pendaftaranRoadmap(){
        return $this->hasMany(PendaftaranRoadmap::class, 'id_roadmap', 'id');
    }
}
