<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['role','nim','nama','angkatan','talent_mapping','email','no_handphone','password',])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function pendaftaranAktivitas()
    {
        return $this->hasMany(PendaftaranAktivitas::class, 'id_user', 'id');
    }

    public function pengajuanAktivitas()
    {
        return $this->hasMany(PengajuanAktivitas::class, 'id_user', 'id');
    }

    public function pendaftaranRoadmap()
    {
        return $this->hasMany(PendaftaranRoadmap::class, 'id_user', 'id');
    }

    public function pencapaian()
    {
        return $this->hasMany(Pencapaian::class, 'id_user', 'id');
    }

    
}
