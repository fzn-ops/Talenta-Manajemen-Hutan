<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GambarPengajuanAktivitas extends Model
{
    //
    use HasFactory;
    protected $table='gambar_pengajuan_aktivitas';
    protected $fillable = [
        'id_pengangajuan_aktivitas',
        'gambar',
        'utama',
    ];

    public function pengajuanAktivitas()
    {
        return $this->belongsTo(PengajuanAktivitas::class, 'id_pengangajuan_aktivitas', 'id');
    }

    public function scopeUtama($query)
    {
        return $query->where('utama', true);
    }

    public function getPathGambarAttribute()
    {
        return asset('storage/' . $this->gambar);
    }
}
