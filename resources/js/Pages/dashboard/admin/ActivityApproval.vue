<script setup>
import { ref, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';

import AdminLayout from '@/Layouts/dashboard/AdminLayout.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import SearchBarTable from '@/Components/dashboard/SearchBarTable.vue';
import TablePagination from '@/Components/dashboard/TablePagination.vue';
import RejectModal from '@/Components/dashboard/RejectModal.vue';

import AcceptButton from '@/Components/dashboard/admin/AcceptButton.vue';
import RejectButton from '@/Components/dashboard/admin/RejectButton.vue';

// 10 Data Dummy
const kegiatan = ref([
  { id: 1, judul: 'Lorem Ipsum dolor sit amet consectetur', deskripsi: 'Deskripsi kegiatan 1...', kategori: 'Profesional, Bisnis, Birokrat, Akademisi', deadline: '10 Juli 2029', gambar: '123.jpg', status: 'Menunggu' },
  { id: 2, judul: 'Pelatihan AI dan Machine Learning', deskripsi: 'Deskripsi kegiatan 2...', kategori: 'Profesional, Akademisi', deadline: '12 Agustus 2029', gambar: '456.jpg', status: 'Disetujui' },
  { id: 3, judul: 'Seminar Nasional Teknologi Hijau', deskripsi: 'Deskripsi kegiatan 3...', kategori: 'Bisnis, Birokrat', deadline: '15 September 2029', gambar: '789.jpg', status: 'Ditolak' },
  { id: 4, judul: 'Workshop Kewirausahaan Digital', deskripsi: 'Deskripsi kegiatan 4...', kategori: 'Bisnis, Profesional', deadline: '20 Oktober 2029', gambar: '101.jpg', status: 'Menunggu' },
  { id: 5, judul: 'Sosialisasi Kebijakan Kampus Merdeka', deskripsi: 'Deskripsi kegiatan 5...', kategori: 'Birokrat, Akademisi', deadline: '01 November 2029', gambar: '112.jpg', status: 'Ditolak' },
  { id: 6, judul: 'Kompetisi Hackathon Mahasiswa', deskripsi: 'Deskripsi kegiatan 6...', kategori: 'Profesional, Akademisi', deadline: '10 Desember 2029', gambar: '131.jpg', status: 'Disetujui' },
  { id: 7, judul: 'Pelatihan Sertifikasi Mikrotik', deskripsi: 'Deskripsi kegiatan 7...', kategori: 'Profesional', deadline: '15 Januari 2030', gambar: '141.jpg', status: 'Menunggu' },
  { id: 8, judul: 'Forum Diskusi Industri Kreatif', deskripsi: 'Deskripsi kegiatan 8...', kategori: 'Bisnis, Profesional', deadline: '20 Februari 2030', gambar: '151.jpg', status: 'Disetujui' },
  { id: 9, judul: 'Pengabdian Masyarakat Desa Binaan', deskripsi: 'Deskripsi kegiatan 9...', kategori: 'Akademisi, Birokrat', deadline: '05 Maret 2030', gambar: '161.jpg', status: 'Menunggu' },
  { id: 10, judul: 'Webinar Persiapan Karir Global', deskripsi: 'Deskripsi kegiatan 10...', kategori: 'Profesional, Akademisi', deadline: '12 April 2030', gambar: '171.jpg', status: 'Ditolak' },
]);

const searchQuery = ref('');
const showFilter = ref(false);

const sortColumn = ref('id');
const sortDirection = ref('asc');

const showDetailModal = ref(false);
const showRejectModal = ref(false);
const selectedItem = ref(null);
const showImageModal = ref(false);
const selectedImage = ref('');

const toast = ref({ show: false, message: '', type: 'success' });

const showToast = (message, type = 'success') => {
  toast.value.show = false; 
  setTimeout(() => {
    toast.value = { show: true, message, type };
    setTimeout(() => { toast.value.show = false; }, 3500);
  }, 50);
};

// ==============================
// FUNGSI SORTING & FILTERING
// ==============================
const handleSort = (column) => {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortColumn.value = column;
    sortDirection.value = 'asc';
  }
};

const processedKegiatan = computed(() => {
  let data = [...kegiatan.value];

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    data = data.filter(item => 
      item.judul.toLowerCase().includes(query) ||
      item.kategori.toLowerCase().includes(query) ||
      item.status.toLowerCase().includes(query)
    );
  }

  if (sortColumn.value) {
    data.sort((a, b) => {
      let valA = a[sortColumn.value];
      let valB = b[sortColumn.value];

      if (typeof valA === 'string') valA = valA.toLowerCase();
      if (typeof valB === 'string') valB = valB.toLowerCase();

      if (valA < valB) return sortDirection.value === 'asc' ? -1 : 1;
      if (valA > valB) return sortDirection.value === 'asc' ? 1 : -1;
      return 0;
    });
  }

  return data;
});

// ==============================
// FUNGSI MODAL & AKSI
// ==============================
const openDetail = (item) => {
  selectedItem.value = item;
  showDetailModal.value = true;
};

const openReject = (item) => {
  if(item) selectedItem.value = item; 
  showRejectModal.value = true;
};

const openImage = (gambar) => {
  selectedImage.value = gambar;
  showImageModal.value = true;
};

const handleApprove = () => {
  showDetailModal.value = false;
  showToast('Kegiatan berhasil disetujui!', 'success');
};

const handleReject = (reason) => {
  showRejectModal.value = false;
  showDetailModal.value = false; 
  showToast(`Kegiatan ditolak. Alasan: ${reason || 'Tidak ada'}`, 'error');
};
</script>

<template>
  <Head title="Daftar Persetujuan Kegiatan"/>

  <AdminLayout>
    <div class="p-8 font-sans bg-[#fcfcfc] min-h-screen relative overflow-hidden">
      
      <!-- TOAST NOTIFICATION (Fade-Slide Opacity 0 ke 100) -->
      <Transition 
        enter-active-class="transition-all transform duration-500 ease-out" 
        enter-from-class="translate-x-12 opacity-0" 
        enter-to-class="translate-x-0 opacity-100" 
        leave-active-class="transition-all transform duration-300 ease-in" 
        leave-from-class="translate-x-0 opacity-100" 
        leave-to-class="translate-x-12 opacity-0"
      >
        <div v-if="toast.show" class="fixed top-8 right-8 z-[100]">
          <ToastNotification 
            :show="true"
            :message="toast.message" 
            :type="toast.type" 
            @close="toast.show = false" 
          />
        </div>
      </Transition>

      <div class="mb-6">
        <h1 class="text-3xl font-extrabold text-[#233547]">Daftar Persetujuan Kegiatan</h1>
        <p class="text-sm text-gray-500 mt-1">Cek kegiatan dan setujui yah!</p>
      </div>

      <div class="mb-4 flex items-center gap-3 w-full relative">
        <div class="flex-1 w-full flex">
          <SearchBarTable class="w-full flex-1" placeholder="Cari Aktivitas disini..." v-model="searchQuery"/>
        </div>

        <div class="relative">
          <button @click="showFilter = !showFilter" class="flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-gray-400 bg-white text-gray-700 transition hover:bg-gray-50">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" /></svg>
          </button>
          <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-2">
            <div v-if="showFilter" class="absolute right-0 top-12 z-40 w-48 rounded-xl border border-gray-200 bg-white p-2 shadow-lg">
              <p class="px-3 py-1.5 text-xs font-bold text-gray-400 uppercase">Filter Status</p>
              <button @click="searchQuery = ''; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 font-medium">Semua Data</button>
              <button @click="searchQuery = 'Menunggu'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-yellow-600 hover:bg-yellow-50 font-medium">Menunggu</button>
              <button @click="searchQuery = 'Disetujui'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-green-600 hover:bg-green-50 font-medium">Disetujui</button>
              <button @click="searchQuery = 'Ditolak'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50 font-medium">Ditolak</button>
            </div>
          </Transition>
        </div>
      </div>

      <div class="overflow-hidden rounded-t-xl border border-gray-200 bg-white shadow-sm mb-6">
        <div class="overflow-x-auto">
          <table class="w-full text-center text-sm whitespace-nowrap">
            <!-- HEADER DENGAN ANIMASI PANAH SVG SMOOTH ROTATE -->
            <thead class="bg-[#48796f] text-white select-none">
              <tr>
                <th @click="handleSort('id')" class="px-4 py-4 font-semibold w-16 cursor-pointer hover:bg-[#3d675e] transition-colors group">
                  <div class="flex items-center justify-center gap-1.5">
                    No 
                    <svg class="h-3.5 w-3.5 transition-transform duration-300 ease-in-out" :class="[sortColumn === 'id' ? 'opacity-100' : 'opacity-30 group-hover:opacity-50', sortColumn === 'id' && sortDirection === 'asc' ? 'rotate-180' : 'rotate-0']" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                  </div>
                </th>
                <th @click="handleSort('judul')" class="px-4 py-4 font-semibold cursor-pointer hover:bg-[#3d675e] transition-colors group">
                  <div class="flex items-center justify-center gap-1.5">
                    Judul 
                    <svg class="h-3.5 w-3.5 transition-transform duration-300 ease-in-out" :class="[sortColumn === 'judul' ? 'opacity-100' : 'opacity-30 group-hover:opacity-50', sortColumn === 'judul' && sortDirection === 'asc' ? 'rotate-180' : 'rotate-0']" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                  </div>
                </th>
                <th @click="handleSort('kategori')" class="px-4 py-4 font-semibold cursor-pointer hover:bg-[#3d675e] transition-colors group">
                  <div class="flex items-center justify-center gap-1.5">
                    Kategori 
                    <svg class="h-3.5 w-3.5 transition-transform duration-300 ease-in-out" :class="[sortColumn === 'kategori' ? 'opacity-100' : 'opacity-30 group-hover:opacity-50', sortColumn === 'kategori' && sortDirection === 'asc' ? 'rotate-180' : 'rotate-0']" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                  </div>
                </th>
                <th @click="handleSort('deadline')" class="px-4 py-4 font-semibold cursor-pointer hover:bg-[#3d675e] transition-colors group">
                  <div class="flex items-center justify-center gap-1.5">
                    Deadline 
                    <svg class="h-3.5 w-3.5 transition-transform duration-300 ease-in-out" :class="[sortColumn === 'deadline' ? 'opacity-100' : 'opacity-30 group-hover:opacity-50', sortColumn === 'deadline' && sortDirection === 'asc' ? 'rotate-180' : 'rotate-0']" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                  </div>
                </th>
                <th class="px-4 py-4 font-semibold">Gambar</th>
                <th @click="handleSort('status')" class="px-4 py-4 font-semibold cursor-pointer hover:bg-[#3d675e] transition-colors group">
                  <div class="flex items-center justify-center gap-1.5">
                    Status 
                    <svg class="h-3.5 w-3.5 transition-transform duration-300 ease-in-out" :class="[sortColumn === 'status' ? 'opacity-100' : 'opacity-30 group-hover:opacity-50', sortColumn === 'status' && sortDirection === 'asc' ? 'rotate-180' : 'rotate-0']" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                  </div>
                </th>
                <th class="px-4 py-4 font-semibold w-32">Aksi</th>
              </tr>
            </thead>
            
            <tbody class="text-gray-600">
              <tr v-for="(item, index) in processedKegiatan" :key="item.id" class="border-b border-gray-200 hover:bg-gray-50/70 transition-colors">
                <td class="px-4 py-4">{{ item.id }}</td>
                <td class="px-4 py-4 font-medium max-w-[200px] truncate text-[#233547]">{{ item.judul }}</td>
                <td class="px-4 py-4 max-w-[220px] truncate">{{ item.kategori }}</td>
                <td class="px-4 py-4">{{ item.deadline }}</td>
                <td class="px-4 py-4">
                  <button @click="openImage(item.gambar)" class="text-[#3b82f6] hover:text-blue-700 hover:underline transition-colors font-medium">
                    {{ item.gambar }}
                  </button>
                </td>
                <td class="px-4 py-4">
                  <div class="mx-auto flex w-fit items-center justify-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium border"
                       :class="{
                         'bg-yellow-50 text-yellow-600 border-yellow-200': item.status === 'Menunggu',
                         'bg-green-50 text-green-600 border-green-200': item.status === 'Disetujui',
                         'bg-red-50 text-red-600 border-red-200': item.status === 'Ditolak'
                       }">
                    <svg v-if="item.status === 'Menunggu'" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" /></svg>
                    <svg v-if="item.status === 'Disetujui'" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <svg v-if="item.status === 'Ditolak'" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    {{ item.status }}
                  </div>
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center justify-center gap-2">
                    <AcceptButton @click="handleApprove(item)" />
                    <RejectButton @click="openReject(item)" />
                    <button @click="openDetail(item)" title="Lihat Detail" class="flex h-7 w-7 items-center justify-center rounded-md bg-[#60a5fa] text-white hover:bg-[#3b82f6] transition hover:scale-105 shadow-sm">
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="processedKegiatan.length === 0">
                <td colspan="7" class="px-4 py-8 text-center text-gray-500 font-medium">Data tidak ditemukan.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <TablePagination :links="[]" :currentPage="1" :rowsPerPage="10"/>
    </div>

    <!-- MODAL GAMBAR (LIGHTBOX) -->
    <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showImageModal" class="fixed inset-0 z-[70] flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm" @click="showImageModal = false">
        <div class="relative max-w-4xl rounded-2xl bg-white p-2 shadow-2xl" @click.stop>
          <button @click="showImageModal = false" class="absolute -top-4 -right-4 flex h-10 w-10 items-center justify-center rounded-full bg-red-500 text-white shadow-lg hover:bg-red-600 border-2 border-white transition-transform hover:scale-110 z-10">✕</button>
          <img :src="`https://picsum.photos/seed/${selectedImage}/800/600`" alt="Preview Gambar" class="w-full max-h-[80vh] object-contain rounded-xl" />
        </div>
      </div>
    </Transition>

    <!-- MODAL DETAIL (PERSETUJUAN AKTIVITAS) -->
    <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm md:p-8">
        <div class="absolute inset-0" @click="showDetailModal = false"></div>
        <Transition enter-active-class="transition duration-300 ease-out delay-75" enter-from-class="opacity-0 translate-y-4 scale-95" enter-to-class="opacity-100 translate-y-0 scale-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0 scale-100" leave-to-class="opacity-0 translate-y-4 scale-95">
          <div v-if="showDetailModal" class="relative w-full max-w-6xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-8 shadow-2xl">
            <h2 class="mb-8 text-2xl font-bold text-[#1a2b4c]">Persetujuan Aktivitas</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
              <div class="flex flex-col gap-5">
                <div>
                  <label class="mb-1.5 block text-sm font-bold text-[#1a2b4c]">Judul Aktivitas<span class="text-red-500">*</span></label>
                  <input type="text" readonly :value="selectedItem?.judul" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-500 outline-none" />
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-bold text-[#1a2b4c]">Deskripsi<span class="text-red-500">*</span></label>
                  <textarea readonly rows="6" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-500 outline-none leading-relaxed">{{ selectedItem?.deskripsi }}</textarea>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-bold text-[#1a2b4c]">Deadline<span class="text-red-500">*</span></label>
                  <input type="text" readonly :value="selectedItem?.deadline" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-500 outline-none" />
                </div>
                <div>
                  <label class="mb-3 block text-sm font-bold text-[#1a2b4c]">Kategori<span class="text-red-500">*</span></label>
                  <div class="flex flex-wrap gap-5 text-xs font-semibold text-gray-500">
                    <label class="flex items-center gap-1.5 cursor-not-allowed"><input type="checkbox" :checked="selectedItem?.kategori.includes('Profesional')" disabled class="rounded border-gray-300 text-[#1a2b4c]" /> Professional</label>
                    <label class="flex items-center gap-1.5 cursor-not-allowed"><input type="checkbox" :checked="selectedItem?.kategori.includes('Bisnis')" disabled class="rounded border-gray-300 text-[#1a2b4c]" /> Bisnis</label>
                    <label class="flex items-center gap-1.5 cursor-not-allowed"><input type="checkbox" :checked="selectedItem?.kategori.includes('Akademisi')" disabled class="rounded border-gray-300 text-[#1a2b4c]" /> Akademisi</label>
                    <label class="flex items-center gap-1.5 cursor-not-allowed"><input type="checkbox" :checked="selectedItem?.kategori.includes('Birokrat')" disabled class="rounded border-gray-300 text-[#1a2b4c]" /> Birokrat</label>
                  </div>
                </div>
              </div>
              <div class="flex flex-col gap-6">
                <div>
                  <label class="mb-2 block text-sm font-bold text-[#1a2b4c]">Gambar<span class="text-red-500">*</span></label>
                  <div class="flex flex-wrap gap-4 rounded-xl border-2 border-dashed border-gray-300 p-5 items-center justify-center">
                    <div v-for="i in 3" :key="i" @click="openImage(selectedItem?.gambar || `dummy-${i}.jpg`)" class="flex h-32 w-32 flex-shrink-0 items-center justify-center rounded-lg bg-gray-300 cursor-pointer hover:scale-105 hover:bg-gray-400 transition-all shadow-sm">
                      <svg class="h-10 w-10 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                    </div>
                  </div>
                </div>
                <div>
                  <label class="mb-1 block text-sm font-bold text-[#1a2b4c]">Catatan Penolakan Sebelumnya</label>
                  <p class="mb-2 text-[10px] text-gray-400">Berikut adalah catatan penolakan sebelumnya (Catatan kosong apabila submisi belum pernah ditolak)</p>
                  <div class="w-full rounded-lg border border-red-300 bg-[#fecaca] p-4 text-sm font-medium text-red-600 min-h-[120px]">
                    {{ selectedItem?.status === 'Ditolak' ? 'Data tidak lengkap atau format gambar kurang jelas.' : '' }}
                  </div>
                </div>
              </div>
            </div>
            <div class="mt-10 flex justify-end gap-4">
              <button @click="showDetailModal = false" class="rounded-lg border border-gray-300 bg-white px-8 py-2.5 text-sm font-bold text-gray-500 transition hover:bg-gray-50">Kembali</button>
              <button @click="handleApprove" class="rounded-lg bg-[#51d28c] px-8 py-2.5 text-sm font-bold text-white transition hover:bg-[#3fb874]">Setuju</button>
              <button @click="openReject(null)" class="rounded-lg bg-[#ef4444] px-8 py-2.5 text-sm font-bold text-white transition hover:bg-red-600">Tolak</button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>

    <RejectModal :show="showRejectModal" @close="showRejectModal = false" @submit="handleReject"/>

  </AdminLayout>
</template>