<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MateriRoadmap extends Model
{
    //
    use HasFactory;
    protected $table='materi_roadmap';
    protected $fillable=[
        'id_bulan_roadmap',
        'judul',
        'isi',
        'tipe',
        'topik',
        'maksimal_tugas',
    ];

    protected $casts = [
        'kartu_konten'=>'array',
        'maksimal_tugas'=>'integer',
    ];  

    public function bulanRoadmap(){
        return $this->belongsTo(BulanRoadmap::class, 'id_bulan_roadmap', 'id');
    }

    public function pencapaian(){
        return $this->hasMany(Pencapaian::class, 'id_materi_roadmap', 'id');
    }
    
    public function hasilAktivitas(){
        return $this->hasMany(HasilAktivitas::class, 'id_materi_roadmap', 'id');
    }
}
