<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendaftaranPeserta extends Model
{
    //
    protected $table="pendaftaran_peserta";

    protected $fillable = [
        'id_user',
        'id_pendaftaran'
    ];

    public function pendaftaran()
    {
        return $this->belongsTo(PendaftaranAktivitas::class, 'id_pendaftaran', 'id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}
