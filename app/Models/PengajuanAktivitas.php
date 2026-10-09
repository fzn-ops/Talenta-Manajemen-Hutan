<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PengajuanAktivitas extends Model
{
    //
    use HasFactory;
    protected $table='pengajuan_aktivitas';
    protected $fillable=[
        'id_user',
        'judul',
        'deskripsi',
        'kategori',
        'status',
        'deadline',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function gambarPengajuanAktivitas()
    {
        return $this->hasMany(GambarPengajuanAktivitas::class, 'id_pengajuan_aktivitas', 'id');
    }
}
