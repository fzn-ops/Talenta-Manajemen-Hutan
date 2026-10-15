<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Services\MahasiswaService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MahasiswaController extends Controller
{
    protected $mahasiswaService;

    public function __construct(MahasiswaService $mahasiswaService)
    {
        $this->mahasiswaService = $mahasiswaService;
    }

    /**
     * READ: Menampilkan daftar mahasiswa dengan filter, pencarian, sorting, dan pagination.
     */
    public function index(Request $request)
    {
        $filters = [
            'search'         => $request->input('search'),
            'angkatan'       => $request->input('angkatan'),
            'talent_mapping' => $request->input('talent_mapping'),
            'sort'           => $request->input('sort', 'id'),
            'direction'      => $request->input('direction', 'asc'),
            'per_page'       => (int) $request->input('per_page', 10),
            'page'           => (int) $request->input('page', 1),
        ];

        $mahasiswaList = $this->mahasiswaService->getPaginationMahasiswa($filters, $filters['per_page']);
        $availableAngkatans = $this->mahasiswaService->getAvailableAngkatans();
        $filterCounts = $this->mahasiswaService->getFilterCounts();
        $existingStudents = \App\Models\User::where('role', 'mahasiswa')->select('id', 'nim')->get();

        return Inertia::render('dashboard/admin/Mahasiswa', [
            'mahasiswaData'      => $mahasiswaList,
            'filters'            => $filters,
            'availableAngkatans' => $availableAngkatans,
            'angkatanCounts'     => $filterCounts['angkatan'],
            'talentCounts'       => $filterCounts['talent_mapping'],
            'existingStudents'   => $existingStudents,
            'existingNims'       => $existingStudents->pluck('nim'),
        ]);
    }

    /**
     * CREATE: Simpan data mahasiswa baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nim'            => 'required|string|max:50|unique:users,nim',
            'name'           => 'required|string|max:255',
            'angkatan'       => 'required|integer',
            'talent_mapping' => 'required|string|max:100',
            'email'          => 'nullable|email|max:255|unique:users,email',
            'phone'          => 'nullable|string|max:30',
            'no_handphone'   => 'nullable|string|max:30',
            'password'       => 'nullable|string|min:6',
        ], [
            'nim.required'            => 'NIM wajib diisi.',
            'nim.unique'              => 'NIM sudah terdaftar di sistem.',
            'name.required'           => 'Nama mahasiswa wajib diisi.',
            'angkatan.required'       => 'Angkatan wajib diisi.',
            'angkatan.integer'        => 'Angkatan harus berupa angka tahun/urutan.',
            'talent_mapping.required' => 'Talent mapping wajib dipilih.',
            'email.unique'            => 'Email sudah digunakan akun lain.',
            'password.min'            => 'Password minimal 6 karakter.',
        ]);

        $this->mahasiswaService->createMahasiswa($validated);

        return redirect()->back()->with('success', "Akun mahasiswa \"{$validated['name']}\" berhasil ditambahkan.");
    }

    /**
     * UPDATE: Perbarui data mahasiswa yang sudah ada.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nim'            => 'required|string|max:50|unique:users,nim,' . $id,
            'name'           => 'required|string|max:255',
            'angkatan'       => 'required|integer',
            'talent_mapping' => 'required|string|max:100',
            'email'          => 'nullable|email|max:255|unique:users,email,' . $id,
            'phone'          => 'nullable|string|max:30',
            'no_handphone'   => 'nullable|string|max:30',
            'password'       => 'nullable|string|min:6',
        ], [
            'nim.required'            => 'NIM wajib diisi.',
            'nim.unique'              => 'NIM sudah terdaftar di sistem.',
            'name.required'           => 'Nama mahasiswa wajib diisi.',
            'angkatan.required'       => 'Angkatan wajib diisi.',
            'angkatan.integer'        => 'Angkatan harus berupa angka tahun/urutan.',
            'talent_mapping.required' => 'Talent mapping wajib dipilih.',
            'email.unique'            => 'Email sudah digunakan akun lain.',
            'password.min'            => 'Password minimal 6 karakter.',
        ]);

        $this->mahasiswaService->updateMahasiswa($id, $validated);

        return redirect()->back()->with('success', "Data mahasiswa \"{$validated['name']}\" berhasil diperbarui.");
    }

    /**
     * DELETE: Hapus data mahasiswa (single).
     */
    public function destroy($id)
    {
        $this->mahasiswaService->deleteMahasiswa($id);

        return redirect()->back()->with('success', 'Data mahasiswa berhasil dihapus.');
    }

    /**
     * BULK DELETE: Hapus data mahasiswa secara massal berdasarkan angkatan.
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'angkatans'   => 'required|array|min:1',
            'angkatans.*' => 'required',
        ]);

        $deletedCount = $this->mahasiswaService->bulkDeleteByAngkatan($validated['angkatans']);
        $angkatanStr = implode(', ', $validated['angkatans']);

        return redirect()->back()->with('success', "{$deletedCount} data mahasiswa angkatan {$angkatanStr} berhasil dihapus.");
    }

    /**
     * IMPORT: Impor banyak data mahasiswa sekaligus dari preview Excel.
     */
    public function import(Request $request)
    {
        $validated = $request->validate([
            'students'   => 'required|array|min:1',
            'students.*' => 'required|array',
        ]);

        $importedCount = $this->mahasiswaService->importMahasiswa($validated['students']);

        return redirect()->back()->with('success', "{$importedCount} data mahasiswa berhasil diimpor ke sistem.");
    }
}
