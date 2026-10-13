<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Berita extends Model
{
    //
    use HasFactory;
    protected $table='berita';
    protected $fillable=[
        'judul',
        'deskripsi',
        'foto',
        'created_at',
        'updated_at',
    ];

    protected $cast =[
        'created_at'=>'date:Y-m-d',
        'updated_at'=>'date:Y-m-d',
    ];
}
