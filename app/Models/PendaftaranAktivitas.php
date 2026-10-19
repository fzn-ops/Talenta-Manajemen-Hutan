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
        'id_aktivitas',
        'nama_tim',
        'bukti_pendaftaran',
        'catatan_penolakan',
        'status',
    ];

    public function activity(){
        return $this->belongsTo(Aktivitas::class,'id_aktivitas', 'id');
    }

    public function peserta()
    {
        return $this->belongsToMany(
            User::class, 
            'pendaftaran_peserta', 
            'id_pendaftaran', 
            'id_user'
        );
    }

    public function hasilAktivitas()
    {
        return $this->hasMany(HasilAktivitas::class, 'id_pendaftaran_aktivitas', 'id');
    }
}
