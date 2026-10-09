<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PendaftaranRoadmap extends Model
{
    //
    use HasFactory;
    protected $table='pendaftaran_roadmap';
    protected $fillable=[
        'id_user',
        'id_roadmap',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
    public function roadmap()
    {
        return $this->belongsTo(Roadmap::class, 'id_roadmap', 'id');
    }
}
