<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PendaftaranAktivitas extends Model
{
    //
    use HasFactory;

    protected $table='Pendaftaran_aktivitas';
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

    protected $casts = [
        'deadline' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}
