<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PengajuanAktivitas extends Model
{
    //
    use HasFactory;
    protected $table='pengajuan_aktivitas';
    protected $fillable = [
        'id_user',
        'judul',
        'deskripsi',
        'kategori',
        'status',
        'catatan_penolakan',
        'deadline',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'deadline' => 'date:Y-m-d',
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
