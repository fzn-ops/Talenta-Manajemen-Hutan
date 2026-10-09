<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Karir extends Model
{
    //
    use HasFactory;
    protected $table='karir';
    protected $fillable=[
        'posisi',
        'nama_instansi',
        'deskripsi',
        'kualifikasi',
        'deadline',
        'tautan',
        'foto',
        'created_at',
        'updated_at',
    ];
}
