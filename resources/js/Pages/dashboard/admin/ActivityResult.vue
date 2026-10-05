<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head } from '@inertiajs/vue3';

import AdminLayout from '@/Layouts/dashboard/AdminLayout.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import SearchBarTable from '@/Components/dashboard/SearchBarTable.vue';
import TablePagination from '@/Components/dashboard/TablePagination.vue';
import RejectModal from '@/Components/dashboard/RejectModal.vue';
import ModalFormHasilAktivitas from '@/Components/dashboard/admin/ModalFormHasilAktivitas.vue';

import AcceptButton from '@/Components/dashboard/admin/AcceptButton.vue';
import RejectButton from '@/Components/dashboard/admin/RejectButton.vue';
import PreviewButtonTable from '@/Components/dashboard/PreviewButtonTable.vue';

// Data Dummy Sesuai Gambar Hasil Aktivitas
const kegiatan = ref([
  { id: 1, nama_mahasiswa: 'Fauzan Fuadiansyah', nim: 'J0403231085', jenis_roadmap: 'Professional', nama_kegiatan: 'Hackaton V2.0 Tahun 2026', deskripsi: 'Lorem ipsum dolor sit amet, duis officia reprehenderit esse duis. Dolore cupidatat aliqua veniam eu culpa reprehenderit in excepteur exercitation adipiscing. Aliquip esse laborum cupidatat duis in excepteur enim dolore sed incididunt proident aliquip. Aute consectetur nulla proident ea sunt qui in culpa est.\n\nDuis consequat proident culpa est tempor nulla in. Sunt anim ad occaecat pariatur exercitation reprehenderit proident duis tempor proident cillum nisi. Aliqua nulla laboris esse dolor ea esse anim. Pariatur excepteur enim in labore esse eu proident sint exercitation labore laborum. Dolore laboris veniam qui officia velit irure sint commodo excepteur. Excepteur incididunt excepteur aute laborum ullamco eiusmod consectetur ut voluptate sunt officia laboris.', gambar: '123.jpg', status: 'Menunggu', alasan_tolak: '' },
  { id: 2, nama_mahasiswa: 'Fauzan Fuadiansyah', nim: 'J0403231085', jenis_roadmap: 'Professional', nama_kegiatan: 'Hackaton V2.0 Tahun 2026', deskripsi: 'Deskripsi hasil kegiatan...', gambar: '123.jpg', status: 'Menunggu', alasan_tolak: '' },
  { id: 3, nama_mahasiswa: 'Fauzan Fuadiansyah', nim: 'J0403231085', jenis_roadmap: 'Professional', nama_kegiatan: 'Hackaton V2.0 Tahun 2026', deskripsi: 'Deskripsi hasil kegiatan...', gambar: '123.jpg', status: 'Menunggu', alasan_tolak: '' },
  { id: 4, nama_mahasiswa: 'Fauzan Fuadiansyah', nim: 'J0403231085', jenis_roadmap: 'Professional', nama_kegiatan: 'Hackaton V2.0 Tahun 2026', deskripsi: 'Deskripsi hasil kegiatan...', gambar: '123.jpg', status: 'Menunggu', alasan_tolak: '' },
  { id: 5, nama_mahasiswa: 'Fauzan Fuadiansyah', nim: 'J0403231085', jenis_roadmap: 'Professional', nama_kegiatan: 'Hackaton V2.0 Tahun 2026', deskripsi: 'Deskripsi hasil kegiatan...', gambar: '123.jpg', status: 'Disetujui', alasan_tolak: '' },
  { id: 6, nama_mahasiswa: 'Fauzan Fuadiansyah', nim: 'J0403231085', jenis_roadmap: 'Professional', nama_kegiatan: 'Hackaton V2.0 Tahun 2026', deskripsi: 'Deskripsi hasil kegiatan...', gambar: '123.jpg', status: 'Menunggu', alasan_tolak: '' },
  { id: 7, nama_mahasiswa: 'Fauzan Fuadiansyah', nim: 'J0403231085', jenis_roadmap: 'Professional', nama_kegiatan: 'Hackaton V2.0 Tahun 2026', deskripsi: 'Deskripsi hasil kegiatan...', gambar: '123.jpg', status: 'Ditolak', alasan_tolak: 'Kurang Mantap' },
  { id: 8, nama_mahasiswa: 'Fauzan Fuadiansyah', nim: 'J0403231085', jenis_roadmap: 'Professional', nama_kegiatan: 'Hackaton V2.0 Tahun 2026', deskripsi: 'Deskripsi hasil kegiatan...', gambar: '123.jpg', status: 'Menunggu', alasan_tolak: '' },
  { id: 9, nama_mahasiswa: 'Fauzan Fuadiansyah', nim: 'J0403231085', jenis_roadmap: 'Professional', nama_kegiatan: 'Hackaton V2.0 Tahun 2026', deskripsi: 'Deskripsi hasil kegiatan...', gambar: '123.jpg', status: 'Menunggu', alasan_tolak: '' },
  { id: 10, nama_mahasiswa: 'Fauzan Fuadiansyah', nim: 'J0403231085', jenis_roadmap: 'Professional', nama_kegiatan: 'Hackaton V2.0 Tahun 2026', deskripsi: 'Deskripsi hasil kegiatan...', gambar: '123.jpg', status: 'Menunggu', alasan_tolak: '' },
]);

const searchQuery = ref('');
const filterStatus = ref('');
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

  if (filterStatus.value) {
    data = data.filter(item => item.status === filterStatus.value);
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    data = data.filter(item => 
      item.nama_mahasiswa.toLowerCase().includes(query) ||
      item.nim.toLowerCase().includes(query) ||
      item.jenis_roadmap.toLowerCase().includes(query) ||
      item.nama_kegiatan.toLowerCase().includes(query)
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

const currentPage = ref(1);
const rowsPerPage = ref(10);

onMounted(() => {
  if (window.innerWidth < 768) {
    rowsPerPage.value = 5;
  }
});

const paginatedKegiatan = computed(() => {
  const start = (currentPage.value - 1) * rowsPerPage.value;
  return processedKegiatan.value.slice(start, start + rowsPerPage.value);
});

const totalPages = computed(() => Math.ceil(processedKegiatan.value.length / rowsPerPage.value) || 1);

watch([searchQuery, filterStatus, rowsPerPage], () => {
  currentPage.value = 1;
});

// ==============================
// FUNGSI MODAL & AKSI
// ==============================
const openDetail = (item) => {
  selectedItem.value = item;
  showDetailModal.value = true;
};

const openReject = (item) => {
  if (item) selectedItem.value = item; 
  showRejectModal.value = true;
};

const openImage = (gambar) => {
  if (gambar && typeof gambar === 'object' && gambar.url) {
    selectedImage.value = gambar.url;
  } else if (gambar && typeof gambar === 'string' && (gambar.startsWith('http') || gambar.startsWith('/'))) {
    selectedImage.value = gambar;
  } else {
    selectedImage.value = `https://picsum.photos/seed/${gambar || 'dummy'}/800/600`;
  }
  showImageModal.value = true;
};

const handleApprove = (item) => {
  const target = item || selectedItem.value;
  if (target) {
    const idx = kegiatan.value.findIndex(k => k.id === target.id);
    if (idx !== -1) {
      kegiatan.value[idx].status = 'Disetujui';
    }
  }
  showDetailModal.value = false;
  showToast('Hasil aktivitas berhasil disetujui!', 'success');
};

const handleReject = (reason) => {
  if (selectedItem.value) {
    const idx = kegiatan.value.findIndex(k => k.id === selectedItem.value.id);
    if (idx !== -1) {
      kegiatan.value[idx].status = 'Ditolak';
      kegiatan.value[idx].alasan_tolak = reason;
    }
  }
  showRejectModal.value = false;
  showDetailModal.value = false; 
  showToast(`Hasil aktivitas ditolak. Alasan: ${reason || 'Tidak ada'}`, 'error');
};
</script>

<template>
  <Head title="Hasil Aktivitas Mahasiswa"/>

  <AdminLayout>
    <section class="mx-auto w-full max-w-[1520px] px-4 py-6 font-poppins sm:px-6 sm:py-8 lg:px-8">
      <div class="space-y-6">
        
        <!-- TOAST NOTIFICATION -->
        <Transition enter-active-class="transition-all transform duration-500 ease-out" enter-from-class="translate-x-12 opacity-0" enter-to-class="translate-x-0 opacity-100" leave-active-class="transition-all transform duration-300 ease-in" leave-from-class="translate-x-0 opacity-100" leave-to-class="translate-x-12 opacity-0">
          <div v-if="toast.show" class="fixed top-8 right-8 z-[100]">
            <ToastNotification :message="toast.message" :show="true" :type="toast.type" @close="toast.show = false"/>
          </div>
        </Transition>

        <div class="space-y-1.5">
          <h1 class="mt-1 text-[34px] font-bold leading-[1.02] tracking-[-0.03em] text-[#17334F] sm:text-[42px] lg:text-[48px]">Hasil Aktivitas Mahasiswa</h1>
          <p class="mt-1.5 font-inter text-[14px] font-medium leading-tight text-[#4d6786] sm:text-[16px]">Lihat seberapa banyak mahasiswa yang mengikuti aktivitas Talenta!</p>
        </div>

        <!-- TOP BAR -->
        <div class="flex flex-row items-center gap-2.5 sm:gap-3 w-full">
          <SearchBarTable class="w-full flex-1 min-w-0" placeholder="Cari Hasil Aktivitas disini..." v-model="searchQuery"/>

          <!-- Filter Dropdown Container -->
          <div class="flex items-center justify-end gap-2 sm:gap-3 shrink-0">
            <div class="relative">
              <button @click="showFilter = !showFilter" class="relative flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-[10px] border-2 bg-transparent text-[#183669] transition-colors hover:border-[#8ea9cb] focus:outline-none select-none cursor-pointer" :class="showFilter || filterStatus ? 'border-[#183669]' : 'border-[#d6e0ee]'">
                <img src="/assets/icons/filter.svg" alt="Filter Icon" class="h-5 w-5 shrink-0 object-contain pointer-events-none" />
                <span v-if="filterStatus" class="absolute top-2.5 right-2.5 h-2 w-2 rounded-full bg-[#ef4444] ring-2 ring-[#eef2f7]"></span>
              </button>

              <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-2">
                <div v-if="showFilter" class="absolute right-0 top-14 z-40 w-48 rounded-xl border border-gray-200 bg-white p-2 shadow-lg font-inter">
                  <p class="px-3 py-1.5 text-xs font-bold text-gray-400 uppercase">Filter Status</p>
                  <button @click="filterStatus = ''; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 font-medium cursor-pointer" :class="!filterStatus ? 'bg-gray-100' : ''">Semua Data</button>
                  <button @click="filterStatus = 'Menunggu'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-gray-600 hover:bg-gray-100 font-medium cursor-pointer" :class="filterStatus === 'Menunggu' ? 'bg-gray-100' : ''">Menunggu</button>
                  <button @click="filterStatus = 'Disetujui'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-green-600 hover:bg-green-50 font-medium cursor-pointer" :class="filterStatus === 'Disetujui' ? 'bg-green-50' : ''">Disetujui</button>
                  <button @click="filterStatus = 'Ditolak'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50 font-medium cursor-pointer" :class="filterStatus === 'Ditolak' ? 'bg-red-50' : ''">Ditolak</button>
                </div>
              </Transition>
            </div>
          </div>
        </div>

        <!-- Overlay penutup filter jika klik di luar -->
        <div v-if="showFilter" @click="showFilter = false" class="fixed inset-0 z-30"></div>

        <!-- TABEL DATA -->
        <div class="overflow-x-auto lg:overflow-visible rounded-[12px] bg-white shadow-sm ring-1 ring-[#d6e0ee]">
          <div class="overflow-x-auto lg:overflow-visible">
            <table class="w-full min-w-[1040px] table-fixed border-separate border-spacing-0 text-sm">
              <thead class="bg-[#416f65]">
                <tr class="h-[48px]">
                  <th class="w-[50px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0 rounded-tl-[12px]">
                    <button type="button" @click="handleSort('id')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer">
                      <span>No</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'id'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <th class="w-[200px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    <button type="button" @click="handleSort('nama_mahasiswa')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer">
                      <span>Nama Mahasiswa</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'nama_mahasiswa'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <th class="w-[130px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    <button type="button" @click="handleSort('nim')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer">
                      <span>NIM</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'nim'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <th class="w-[230px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    <button type="button" @click="handleSort('nama_kegiatan')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer">
                      <span>Nama Kegiatan</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'nama_kegiatan'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <th class="w-[160px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">Jenis Roadmap</th>
                  <th class="w-[120px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">Bukti</th>
                  <th class="w-[130px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">Status</th>
                  <th class="w-[140px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none rounded-tr-[12px]">Aksi</th>
                </tr>
              </thead>
              
              <tbody class="[&_tr:not(:first-child)_td]:border-t [&_tr:not(:first-child)_td]:border-[#d6e0ee] font-inter text-[14px] text-[#435b76]">
                <tr v-for="(item, index) in paginatedKegiatan" :key="item.id" class="h-[52px] transition-colors hover:bg-[#f7f9fd]">
                  <td class="px-3 py-2.5 text-center font-medium">{{ (currentPage - 1) * rowsPerPage + index + 1 }}</td>
                  <td class="px-3 py-2.5 text-left font-medium text-[#233547] truncate" :title="item.nama_mahasiswa">{{ item.nama_mahasiswa }}</td>
                  <td class="px-3 py-2.5 text-center font-medium">{{ item.nim }}</td>
                  <td class="px-3 py-2.5 text-left truncate" :title="item.nama_kegiatan">{{ item.nama_kegiatan }}</td>
                  <td class="px-3 py-2.5 text-center">
                    <span :class="[
                      'shrink-0 inline-flex items-center justify-center rounded-full px-2.5 py-0.5 font-inter text-[11px] font-semibold border',
                      item.jenis_roadmap === 'Professional' || item.jenis_roadmap === 'Profesional' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' :
                      item.jenis_roadmap === 'Bisnis' ? 'bg-amber-50 text-amber-700 border-amber-200' :
                      item.jenis_roadmap === 'Birokrat' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                      item.jenis_roadmap === 'Akademisi' ? 'bg-purple-50 text-purple-700 border-purple-200' :
                      'bg-slate-100 text-slate-700 border-slate-200'
                    ]">
                      {{ item.jenis_roadmap }}
                    </span>
                  </td>
                  <td class="px-3 py-2.5 text-center">
                    <button @click="openImage(item.gambar)" class="font-inter text-[14px] font-medium text-[#3b82f6] hover:text-blue-700 hover:underline transition-colors cursor-pointer truncate max-w-[100px] inline-block align-middle" :title="item.gambar">
                      {{ item.gambar }}
                    </button>
                  </td>
                  <td class="px-3 py-2.5 text-center">
                    <div class="mx-auto inline-flex w-fit items-center justify-center gap-1.5 rounded-full px-3 py-1 font-inter text-[11px] font-semibold border"
                         :class="{
                           'bg-gray-100 text-gray-600 border-gray-200': item.status === 'Menunggu',
                           'bg-green-50 text-green-600 border-green-200': item.status === 'Disetujui',
                           'bg-red-50 text-red-600 border-red-200': item.status === 'Ditolak'
                         }">
                      <svg v-if="item.status === 'Menunggu'" class="h-3.5 w-3.5 shrink-0 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="9" />
                        <polyline points="12 7 12 12 15 15" />
                      </svg>
                      <svg v-if="item.status === 'Disetujui'" class="h-3.5 w-3.5 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <svg v-if="item.status === 'Ditolak'" class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <span>{{ item.status }}</span>
                    </div>
                  </td>
                  <td class="px-3 py-2.5 text-center">
                    <div class="flex items-center justify-center gap-2">
                      <AcceptButton @click="handleApprove(item)" />
                      <RejectButton @click="openReject(item)" />
                      <PreviewButtonTable @click="openDetail(item)" />
                    </div>
                  </td>
                </tr>
                <tr v-if="paginatedKegiatan.length === 0">
                  <td colspan="8" class="px-4 py-12 text-center text-gray-500 font-medium">Data tidak ditemukan sesuai filter/pencarian Anda.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <TablePagination 
          :current-page="currentPage" 
          :total-pages="totalPages" 
          :rows-per-page="rowsPerPage"
          @update:current-page="currentPage = $event"
          @update:rows-per-page="rowsPerPage = $event; currentPage = 1"
        />
      </div>
    </section>

    <!-- MODAL DETAIL / PREVIEW HASIL KEGIATAN MAHASISWA -->
    <ModalFormHasilAktivitas
      :show="showDetailModal"
      :data="selectedItem"
      @close="showDetailModal = false"
      @approve="handleApprove"
      @reject="openReject"
    />

    <!-- MODAL GAMBAR (LIGHTBOX) DARI TABEL -->
    <Teleport to="body">
      <Transition
        enter-active-class="ease-out duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="ease-in duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="showImageModal"
          class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/80 backdrop-blur-md p-4 transition-all"
          @click="showImageModal = false"
        >
          <Transition
            enter-active-class="ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="ease-in duration-150"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
          >
            <div
              v-if="showImageModal"
              class="relative flex items-center justify-center bg-transparent"
              @click.stop
            >
              <button
                type="button"
                @click="showImageModal = false"
                class="absolute top-3.5 right-3.5 z-20 flex h-9 w-9 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/85 backdrop-blur-xs shadow-md transition hover:scale-105 active:scale-95 focus:outline-none cursor-pointer"
                title="Tutup Preview"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
              <img
                :src="selectedImage"
                alt="Zoomed Preview"
                class="max-h-[82vh] max-w-[88vw] w-auto h-auto min-w-[280px] sm:min-w-[460px] rounded-xl object-contain shadow-2xl"
              />
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

    <!-- Modal Penolakan -->
    <RejectModal :show="showRejectModal" @close="showRejectModal = false" @submit="handleReject"/>

  </AdminLayout>
</template>