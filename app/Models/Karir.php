<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Karir extends Model
{
    use HasFactory;

    protected $table = 'karir';

    protected $fillable = [
        'posisi',
        'nama_instansi',
        'deskripsi',
        'kualifikasi',
        'deadline',
        'tautan',
        'foto',
    ];

    protected $appends = ['instansi', 'logo'];

    public function getInstansiAttribute()
    {
        return $this->attributes['nama_instansi'] ?? null;
    }

    public function setInstansiAttribute($value)
    {
        $this->attributes['nama_instansi'] = $value;
    }

    public function getLogoAttribute()
    {
        return $this->attributes['foto'] ?? null;
    }

    public function setLogoAttribute($value)
    {
        $this->attributes['foto'] = $value;
    }
}
