<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HasilAktivitas extends Model
{
    use HasFactory;

    protected $table = 'hasil_aktivitas';

    protected $fillable = [
        'id_user',
        'id_materi_roadmap',
        'id_pendaftaran_aktivitas',
        'deskripsi',
        'media',
        'catatan_penolakan',
        'status',
    ];

    protected $appends = [
        'nama_mahasiswa',
        'nim',
        'nama_kegiatan',
        'jenis_roadmap',
        'gambar',
        'alasan_tolak',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function materiRoadmap()
    {
        return $this->belongsTo(MateriRoadmap::class, 'id_materi_roadmap', 'id');
    }

    public function pendaftaranAktivitas()
    {
        return $this->belongsTo(PendaftaranAktivitas::class, 'id_pendaftaran_aktivitas', 'id');
    }

    // Accessors for frontend compatibility
    public function getNamaMahasiswaAttribute()
    {
        return $this->user?->nama ?? '-';
    }

    public function getNimAttribute()
    {
        return $this->user?->nim ?? '-';
    }

    public function getNamaKegiatanAttribute()
    {
        return $this->pendaftaranAktivitas?->activity?->judul 
            ?? $this->materiRoadmap?->judul 
            ?? '-';
    }

    public function getJenisRoadmapAttribute()
    {
        return $this->materiRoadmap?->bulanRoadmap?->roadmap?->kategori 
            ?? $this->materiRoadmap?->bulanRoadmap?->roadmap?->nama_roadmap 
            ?? $this->user?->talent_mapping 
            ?? 'Professional';
    }

    public function getGambarAttribute()
    {
        return $this->media;
    }

    public function getAlasanTolakAttribute()
    {
        return $this->catatan_penolakan;
    }
}
