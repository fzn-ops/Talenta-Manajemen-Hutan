<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Override;

#[Fillable(['role','nim','nama','angkatan','talent_mapping','email','no_handphone','foto','password',])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $appends = ['name', 'phone', 'profile_picture'];

    public function getNameAttribute()
    {
        return $this->attributes['nama'] ?? null;
    }

    public function getPhoneAttribute()
    {
        return $this->attributes['no_handphone'] ?? null;
    }

    public function getProfilePictureAttribute()
    {
        $foto = $this->attributes['foto'] ?? null;
        if (!$foto) {
            return null;
        }
        if (str_starts_with($foto, 'http://') || str_starts_with($foto, 'https://')) {
            return $foto;
        }
        $clean = ltrim($foto, '/');
        if (str_starts_with($clean, 'storage/')) {
            return '/' . $clean;
        }
        return '/storage/' . $clean;
    }

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

    public function hasilAktivitas()
    {
        return $this->hasMany(HasilAktivitas::class, 'id_user', 'id');
    }

    public function hasVerifiedEmail()
    {
        if ($this->role==="admin"){
            return true;
        };

        return ! is_null($this->email_verified_at);
    }
}
