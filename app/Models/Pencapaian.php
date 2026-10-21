<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pencapaian extends Model
{
    use HasFactory;

    protected $table = 'pencapaian';

    protected $fillable = [
        'id_user',
        'id_materi_roadmap',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function materiRoadmap()
    {
        return $this->belongsTo(MateriRoadmap::class, 'id_materi_roadmap', 'id');
    }

    // Accessor agar kompatibel jika ada pemanggilan tanggal_selesai
    public function getTanggalSelesaiAttribute()
    {
        return $this->created_at;
    }
}
