<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MahasiswaService
{
    /**
     * Ambil semua data mahasiswa tanpa pagination.
     */
    public function getAllMahasiswa()
    {
        return User::where('role', 'mahasiswa')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Ambil data mahasiswa dengan filter, search, sorting, dan pagination.
     */
    public function getPaginationMahasiswa(array $filters = [], $perPage = 10)
    {
        $query = User::where('role', 'mahasiswa');

        // 1. Sorting
        if (!empty($filters['sort']) && !empty($filters['direction'])) {
            $sortColumn = $filters['sort'];
            
            // Map alias kolom frontend ke nama kolom database
            $columnMap = [
                'name'           => 'nama',
                'nama'           => 'nama',
                'nim'            => 'nim',
                'angkatan'       => 'angkatan',
                'talent_mapping' => 'talent_mapping',
                'email'          => 'email',
                'phone'          => 'no_handphone',
                'no_handphone'   => 'no_handphone',
                'id'             => 'id',
                'created_at'     => 'created_at',
            ];

            $actualColumn = $columnMap[$sortColumn] ?? 'id';
            $direction = strtolower($filters['direction']) === 'desc' ? 'desc' : 'asc';
            $query->orderBy($actualColumn, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // 2. Pencarian (Search)
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('no_handphone', 'like', "%{$search}%")
                  ->orWhere('talent_mapping', 'like', "%{$search}%")
                  ->orWhere('angkatan', 'like', "%{$search}%");
            });
        }

        // 3. Filter Angkatan
        if (!empty($filters['angkatan'])) {
            $angkatanList = is_array($filters['angkatan']) 
                ? $filters['angkatan'] 
                : explode(',', $filters['angkatan']);
            
            $angkatanList = array_filter(array_map('trim', $angkatanList));
            if (!empty($angkatanList)) {
                $query->whereIn('angkatan', $angkatanList);
            }
        }

        // 4. Filter Talent Mapping
        if (!empty($filters['talent_mapping'])) {
            $talentList = is_array($filters['talent_mapping']) 
                ? $filters['talent_mapping'] 
                : explode(',', $filters['talent_mapping']);

            $talentList = array_filter(array_map('trim', $talentList));
            if (!empty($talentList)) {
                $query->whereIn('talent_mapping', $talentList);
            }
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Dapatkan daftar angkatan yang ada di sistem secara unik.
     */
    public function getAvailableAngkatans()
    {
        return User::where('role', 'mahasiswa')
            ->whereNotNull('angkatan')
            ->distinct()
            ->orderBy('angkatan', 'asc')
            ->pluck('angkatan')
            ->map(fn($item) => (string) $item)
            ->values()
            ->toArray();
    }

    /**
     * Dapatkan jumlah mahasiswa berdasarkan angkatan dan talent mapping untuk badge filter.
     */
    public function getFilterCounts()
    {
        $angkatanCounts = User::where('role', 'mahasiswa')
            ->whereNotNull('angkatan')
            ->groupBy('angkatan')
            ->selectRaw('CAST(angkatan AS CHAR) as angkatan_str, count(*) as total')
            ->pluck('total', 'angkatan_str')
            ->toArray();

        $talentCounts = User::where('role', 'mahasiswa')
            ->whereNotNull('talent_mapping')
            ->groupBy('talent_mapping')
            ->selectRaw('talent_mapping, count(*) as total')
            ->pluck('total', 'talent_mapping')
            ->toArray();

        return [
            'angkatan'       => $angkatanCounts,
            'talent_mapping' => $talentCounts,
        ];
    }

    /**
     * Buat data mahasiswa baru.
     */
    public function createMahasiswa(array $data)
    {
        return DB::transaction(function () use ($data) {
            $nim = trim($data['nim']);
            $nama = trim($data['name'] ?? $data['nama'] ?? '');
            $angkatan = !empty($data['angkatan']) ? (int) $data['angkatan'] : null;
            $talentMapping = !empty($data['talent_mapping']) && $data['talent_mapping'] !== '-' ? trim($data['talent_mapping']) : null;
            $email = !empty($data['email']) && $data['email'] !== '-' ? trim($data['email']) : null;
            
            $rawPhone = $data['phone'] ?? $data['no_handphone'] ?? null;
            $phone = (!empty($rawPhone) && $rawPhone !== '-') ? trim($rawPhone) : null;

            // Password default adalah NIM jika tidak diisi secara spesifik
            $rawPassword = !empty($data['password']) ? $data['password'] : $nim;

            return User::create([
                'role'           => 'mahasiswa',
                'nim'            => $nim,
                'nama'           => $nama,
                'angkatan'       => $angkatan,
                'talent_mapping' => $talentMapping,
                'email'          => $email,
                'no_handphone'   => $phone,
                'password'       => Hash::make($rawPassword),
            ]);
        });
    }

    /**
     * Perbarui data mahasiswa.
     */
    public function updateMahasiswa($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $user = User::where('role', 'mahasiswa')->findOrFail($id);

            $rawPhone = $data['phone'] ?? $data['no_handphone'] ?? null;
            $phone = (!empty($rawPhone) && $rawPhone !== '-') ? trim($rawPhone) : null;

            $updateData = [
                'nim'            => trim($data['nim']),
                'nama'           => trim($data['name'] ?? $data['nama'] ?? $user->nama),
                'angkatan'       => !empty($data['angkatan']) ? (int) $data['angkatan'] : null,
                'talent_mapping' => !empty($data['talent_mapping']) && $data['talent_mapping'] !== '-' ? trim($data['talent_mapping']) : null,
                'email'          => !empty($data['email']) && $data['email'] !== '-' ? trim($data['email']) : null,
                'no_handphone'   => $phone,
            ];

            // Update password hanya jika diisi
            if (!empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            $user->update($updateData);

            return $user;
        });
    }

    /**
     * Hapus data mahasiswa (single).
     */
    public function deleteMahasiswa($id)
    {
        return DB::transaction(function () use ($id) {
            $user = User::where('role', 'mahasiswa')->findOrFail($id);
            return $user->delete();
        });
    }

    /**
     * Hapus banyak mahasiswa berdasarkan daftar angkatan yang dipilih.
     */
    public function bulkDeleteByAngkatan(array $angkatans)
    {
        return DB::transaction(function () use ($angkatans) {
            $angkatanList = array_map('intval', $angkatans);
            return User::where('role', 'mahasiswa')
                ->whereIn('angkatan', $angkatanList)
                ->delete();
        });
    }

    /**
     * Impor banyak data mahasiswa sekaligus (batch import).
     */
    public function importMahasiswa(array $students)
    {
        return DB::transaction(function () use ($students) {
            $validTalents = ['Profesional', 'Bisnis', 'Birokrat', 'Akademisi'];
            $count = 0;
            foreach ($students as $item) {
                $nim = trim($item['nim'] ?? '');
                $nama = trim($item['name'] ?? $item['nama'] ?? '');

                $angkatan = !empty($item['angkatan']) && $item['angkatan'] !== '-' 
                    ? (int) preg_replace('/\D/', '', $item['angkatan']) 
                    : null;
                $rawTalent = !empty($item['talent_mapping']) && $item['talent_mapping'] !== '-' 
                    ? trim($item['talent_mapping']) 
                    : null;

                $talentMapping = null;
                if ($rawTalent) {
                    foreach ($validTalents as $vt) {
                        if (strcasecmp($vt, $rawTalent) === 0) {
                            $talentMapping = $vt;
                            break;
                        }
                    }
                }

                // NIM, Nama, Angkatan, dan Talent Mapping valid wajib diisi
                if (!$nim || !$nama || !$angkatan || !$talentMapping) {
                    continue;
                }
                $email = !empty($item['email']) && $item['email'] !== '-' 
                    ? trim($item['email']) 
                    : null;
                $rawPhone = $item['phone'] ?? $item['no_handphone'] ?? null;
                $phone = (!empty($rawPhone) && $rawPhone !== '-') 
                    ? trim($rawPhone) 
                    : null;
                $rawPassword = !empty($item['password']) && $item['password'] !== '-' 
                    ? $item['password'] 
                    : $nim;

                User::updateOrCreate(
                    ['nim' => $nim],
                    [
                        'role'           => 'mahasiswa',
                        'nama'           => $nama,
                        'angkatan'       => $angkatan,
                        'talent_mapping' => $talentMapping,
                        'email'          => $email,
                        'no_handphone'   => $phone,
                        'password'       => Hash::make($rawPassword),
                    ]
                );

                $count++;
            }

            return $count;
        });
    }
}
