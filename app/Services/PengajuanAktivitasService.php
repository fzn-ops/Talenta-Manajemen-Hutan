<?php

namespace App\Services;

use App\Models\PengajuanAktivitas;
use App\Models\GambarPengajuanAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PengajuanAktivitasService
{
    // ==========================================
    // 1. READ: Ambil Data Spesifik Milik User Login
    // ==========================================
public function getPaginatedData(Request $request)
    {
        $query = PengajuanAktivitas::with(['user', 'gambarPengajuanAktivitas'])
                    ->where('id_user', $request->user()->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('judul', 'like', "%{$search}%");
        }

        if ($request->filled('status') && $request->status !== 'Semua') {
            $status = strtolower($request->status);
            if ($status === 'disetujui' || $status === 'diterima') {
                $query->whereIn('status', ['disetujui', 'diterima']);
            } else {
                $query->where('status', $status);
            }
        }

        $sortBy = $request->get('sort', $request->get('sort_by', 'updated_at')); 
        $sortDirection = strtolower($request->get('direction', $request->get('sort_direction', 'desc'))) === 'asc' ? 'asc' : 'desc';

        $allowedColumns = ['id', 'deadline', 'created_at', 'updated_at', 'judul', 'status'];
        
        if (in_array($sortBy, $allowedColumns)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('updated_at', 'desc'); 
        }

        $perPage = $request->get('per_page', 10);
        return $query->paginate($perPage)->withQueryString();
    }

    // ==========================================
    // 2. CREATE: Simpan Pengajuan Baru
    // ==========================================
    public function createPengajuan(Request $request)
    {
        $pengajuan = PengajuanAktivitas::create([
            'id_user'    => $request->user()->id,
            'judul'      => $request->judul,
            'deskripsi'  => $request->deskripsi,
            'kategori'   => $request->kategori,
            'partisipan' => $request->partisipan,
            'deadline'   => $request->deadline,
            'status'     => 'menunggu',
        ]);

        if ($request->hasFile('gambar')) {
            foreach ($request->file('gambar') as $file) {
                $path = $file->store('pengajuan_gambar', 'public');
                GambarPengajuanAktivitas::create([
                    'id_pengajuan_aktivitas' => $pengajuan->id,
                    'gambar' => $path,
                ]);
            }
        }

        return $pengajuan;
    }

    // ==========================================
    // 3. UPDATE: Edit Pengajuan yang Udah Ada
    // ==========================================
    public function updatePengajuan(Request $request, $id)
    {
        // Cari pengajuan, pastikan cuma punya user ini yang bisa diedit
        $pengajuan = PengajuanAktivitas::where('id', $id)
                        ->where('id_user', $request->user()->id)
                        ->firstOrFail();

        $pengajuan->update([
            'judul'      => $request->judul,
            'deskripsi'  => $request->deskripsi,
            'kategori'   => $request->kategori,
            'deadline'   => $request->deadline,
            'status'     => 'menunggu',
        ]);
        $pengajuan->touch();

        // Kalau ada file baru yang diupload
        if ($request->hasFile('gambar_baru')) {
            foreach ($request->file('gambar_baru') as $file) {
                $path = $file->store('pengajuan_gambar', 'public');
                GambarPengajuanAktivitas::create([
                    'id_pengajuan_aktivitas' => $pengajuan->id,
                    'gambar' => $path,
                ]);
            }
        }

        if ($request->has('gambar_dihapus') && is_array($request->gambar_dihapus)) {
        // Ambil data gambar dari database berdasarkan ID yang dikirim
        $gambarYgDihapus = GambarPengajuanAktivitas::whereIn('id', $request->gambar_dihapus)->get();

        foreach ($gambarYgDihapus as $gbr) {
            // 1. Hapus file fisiknya dari storage
            if (Storage::disk('public')->exists($gbr->gambar)) {
                Storage::disk('public')->delete($gbr->gambar);
            }
        }
    
        // 2. Hapus baris datanya dari database
        GambarPengajuanAktivitas::whereIn('id', $request->gambar_dihapus)->delete();
    }

        return $pengajuan;
    }

    // ==========================================
    // 4. DELETE: Hapus Pengajuan & File Fotonya
    // ==========================================
public function deletePengajuan($id, $userId)
    {
        $pengajuan = PengajuanAktivitas::where('id', $id)
                        ->where('id_user', $userId)
                        ->firstOrFail();

        foreach ($pengajuan->gambarPengajuanAktivitas as $gambar) {
            if (!empty($gambar->gambar) && !str_starts_with($gambar->gambar, 'http')) {
                if (Storage::disk('public')->exists($gambar->gambar)) {
                    Storage::disk('public')->delete($gambar->gambar);
                }
            }
        }

        $pengajuan->gambarPengajuanAktivitas()->delete();

        return $pengajuan->delete();
    }
}