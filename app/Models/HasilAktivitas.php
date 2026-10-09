<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HasilAktivitas extends Model
{
    //
    use HasFactory;
    protected $table='hasil_aktivitas';
    protected $fillable=[
        'id_materi_roadmap',
        'id_pendaftaran_aktivitas',
        'deskripsi',
        'media',
        'catatan_penolakan',
        'status',
        'created_at',
        'updated_at',
    ];

    public function materiRoadmap()
    {
        return $this->belongsTo(MateriRoadmap::class, 'id_materi_roadmap', 'id');
    }

    public function pendaftaranAktivitas()
    {
        return $this->belongsTo(PendaftaranAktivitas::class, 'id_pendaftaran_aktivitas', 'id');
    }
}
