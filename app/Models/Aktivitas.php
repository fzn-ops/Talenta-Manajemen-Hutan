<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Aktivitas extends Model
{
    //
    use HasFactory;
    protected $table = 'aktivitas';

    protected $fillable = [
        'judul',
        'deskripsi',
        'kategori',
        'partisipan',
        'jenis',
        'status_aktivitas',
        'deadline',
        'created_at',
        'updated_at',
    ];

    public function gambarAktivitas()
    {
        return $this->hasMany(GambarAktivitas::class, 'id_aktivitas', 'id');
    }
    
    public function pendaftaranAktivitas()
    {
        return $this->hasMany(PendaftaranAktivitas::class, 'id_aktivitas', 'id');
    }

}
