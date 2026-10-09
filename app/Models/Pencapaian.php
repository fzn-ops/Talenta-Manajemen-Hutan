<?php

namespace App\Models;

use GuzzleHttp\ClientTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pencapaian extends Model
{
    //
    use HasFactory;

    protected $table='pencapaian';
    protected $fillable=[
        'id_user',
        'id_materi_roadmap',
        'tanggal_selesai',
    ];

    protected $casts = [
        'tanggal_selesai' => 'datetime',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function materiRoadmap(){
        return $this->belongsTo(MateriRoadmap::class, 'id_materi_roadmap', 'id');
    }
}
