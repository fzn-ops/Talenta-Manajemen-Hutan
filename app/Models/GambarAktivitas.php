<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GambarAktivitas extends Model
{
    //
    use HasFactory;
    protected $table='gambar_aktivitas';
    protected $fillable = [
        'id_aktivitas',
        'gambar',
        'utama',
    ];

    public function aktivitas()
    {
        return $this->belongsTo(Aktivitas::class, 'id_aktivitas', 'id');
    }
}
