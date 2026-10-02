<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';

import AdminLayout from '@/Layouts/dashboard/AdminLayout.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import SearchBarTable from '@/Components/dashboard/SearchBarTable.vue';
import TablePagination from '@/Components/dashboard/TablePagination.vue';
import RejectModal from '@/Components/dashboard/RejectModal.vue';

import AcceptButton from '@/Components/dashboard/admin/AcceptButton.vue';
import RejectButton from '@/Components/dashboard/admin/RejectButton.vue';
import PreviewButtonTable from '@/Components/dashboard/PreviewButtonTable.vue';

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
      item.judul.toLowerCase().includes(query) ||
      item.kategori.toLowerCase().includes(query)
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

const totalPages = computed(() => Math.ceil(processedKegiatan.value.length / rowsPerPage.value));

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
    <section class="mx-auto w-full max-w-[1520px] px-4 py-6 font-poppins sm:px-6 sm:py-8 lg:px-8">
      <div class="space-y-6">
        
        <!-- TOAST NOTIFICATION -->
        <Transition enter-active-class="transition-all transform duration-500 ease-out" enter-from-class="translate-x-12 opacity-0" enter-to-class="translate-x-0 opacity-100" leave-active-class="transition-all transform duration-300 ease-in" leave-from-class="translate-x-0 opacity-100" leave-to-class="translate-x-12 opacity-0">
          <div v-if="toast.show" class="fixed top-8 right-8 z-[100]">
            <ToastNotification :message="toast.message" :show="true" :type="toast.type" @close="toast.show = false"/>
          </div>
        </Transition>

        <div class="space-y-1.5">
          <h1 class="mt-1 text-[34px] font-bold leading-[1.02] tracking-[-0.03em] text-[#17334F] sm:text-[42px] lg:text-[48px]">Persetujuan Aktivitas</h1>
          <p class="mt-1.5 font-inter text-[14px] font-medium leading-tight text-[#4d6786] sm:text-[16px]">Kelola pengajuan persetujuan aktivitas di sini!</p>
        </div>

        <!-- TOP BAR -->
        <div class="flex flex-row items-center gap-2.5 sm:gap-3 w-full">
          <SearchBarTable class="w-full flex-1 min-w-0" placeholder="Cari Aktivitas disini..." v-model="searchQuery"/>

          <!-- Filter Dropdown Container -->
          <div class="flex items-center justify-end gap-2 sm:gap-3 shrink-0">
            <div class="relative">
              <button @click="showFilter = !showFilter" class="relative flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-[10px] border-2 bg-transparent text-[#183669] transition-colors hover:border-[#8ea9cb] focus:outline-none select-none cursor-pointer" :class="showFilter || filterStatus ? 'border-[#183669]' : 'border-[#d6e0ee]'">
                <img src="/assets/icons/filter.svg" alt="Filter Icon" class="h-5 w-5 shrink-0 object-contain pointer-events-none" />
                <span v-if="filterStatus" class="absolute top-2.5 right-2.5 h-2 w-2 rounded-full bg-[#ef4444] ring-2 ring-[#eef2f7]"></span>
              </button>

              <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-2">
                <div v-if="showFilter" class="absolute right-0 top-14 z-40 w-48 rounded-xl border border-gray-200 bg-white p-2 shadow-lg">
                  <p class="px-3 py-1.5 text-xs font-bold text-gray-400 uppercase">Filter Status</p>
                  <button @click="filterStatus = ''; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 font-medium" :class="!filterStatus ? 'bg-gray-100' : ''">Semua Data</button>
                  <button @click="filterStatus = 'Menunggu'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-gray-600 hover:bg-gray-100 font-medium" :class="filterStatus === 'Menunggu' ? 'bg-gray-100' : ''">Menunggu</button>
                  <button @click="filterStatus = 'Disetujui'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-green-600 hover:bg-green-50 font-medium" :class="filterStatus === 'Disetujui' ? 'bg-green-50' : ''">Disetujui</button>
                  <button @click="filterStatus = 'Ditolak'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50 font-medium" :class="filterStatus === 'Ditolak' ? 'bg-red-50' : ''">Ditolak</button>
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
                  <th class="w-[250px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    <button type="button" @click="handleSort('judul')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer">
                      <span>Judul</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'judul'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <th class="w-[210px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    <button type="button" @click="handleSort('kategori')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer">
                      <span>Kategori</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'kategori'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <th class="w-[130px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    <button type="button" @click="handleSort('deadline')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer">
                      <span>Deadline</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'deadline'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <th class="w-[120px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">Gambar</th>
                  <th class="w-[130px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    <button type="button" @click="handleSort('status')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer">
                      <span>Status</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'status'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <th class="w-[140px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0 rounded-tr-[12px]">Aksi</th>
                </tr>
              </thead>
              
              <tbody class="[&_tr:not(:first-child)_td]:border-t [&_tr:not(:first-child)_td]:border-[#d6e0ee] font-inter text-[14px] text-[#435b76]">
                <tr v-for="(item, index) in paginatedKegiatan" :key="item.id" class="h-[52px] transition-colors hover:bg-[#f7f9fd]">
                  <td class="px-3 py-2.5 text-center">{{ (currentPage - 1) * rowsPerPage + index + 1 }}</td>
                  <td class="px-3 py-2.5 text-left font-medium text-[#233547] truncate" :title="item.judul">{{ item.judul }}</td>
                  <td class="px-3 py-2.5 text-left align-middle relative">
                    <div class="flex items-center gap-1.5 flex-nowrap w-full" v-if="item.kategori">
                      <!-- Render first 2 chips -->
                      <template v-for="(cat, catIdx) in item.kategori.split(',').map(s => s.trim()).slice(0, 2)" :key="catIdx">
                        <span :class="[
                          'shrink-0 inline-flex items-center justify-center rounded-full px-2.5 py-0.5 font-inter text-[11px] font-semibold border',
                          cat === 'Profesional' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' :
                          cat === 'Bisnis' ? 'bg-amber-50 text-amber-700 border-amber-200' :
                          cat === 'Birokrat' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                          cat === 'Akademisi' ? 'bg-purple-50 text-purple-700 border-purple-200' :
                          'bg-slate-100 text-slate-700 border-slate-200'
                        ]">
                          {{ cat }}
                        </span>
                      </template>
                      
                      <!-- +X Chip for remainder -->
                      <div v-if="item.kategori.split(',').length > 2" class="group relative flex shrink-0">
                        <button type="button" class="inline-flex h-5 items-center justify-center rounded-full bg-slate-100 px-1.5 border border-slate-200 text-[10px] font-bold text-slate-600 transition hover:bg-slate-200 focus:outline-none focus:bg-slate-200 cursor-pointer">
                          +{{ item.kategori.split(',').length - 2 }}
                        </button>
                        
                        <!-- Hover Tooltip Container (Hidden by default to prevent vertical scroll overflow) -->
                        <div class="hidden group-hover:block group-focus-within:block absolute left-0 top-full z-[60] mt-1.5 shadow-lg rounded-lg border border-gray-200 bg-white p-2 animate-in fade-in slide-in-from-top-1 duration-200">
                          <div class="flex flex-col gap-1.5 min-w-fit whitespace-nowrap">
                            <span v-for="(cat, catIdx) in item.kategori.split(',').map(s => s.trim()).slice(2)" :key="catIdx" :class="[
                              'inline-flex items-center justify-center rounded-full px-2.5 py-0.5 font-inter text-[11px] font-semibold border',
                              cat === 'Profesional' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' :
                              cat === 'Bisnis' ? 'bg-amber-50 text-amber-700 border-amber-200' :
                              cat === 'Birokrat' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                              cat === 'Akademisi' ? 'bg-purple-50 text-purple-700 border-purple-200' :
                              'bg-slate-100 text-slate-700 border-slate-200'
                            ]">
                              {{ cat }}
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-3 py-2.5 text-center">{{ item.deadline }}</td>
                  <td class="px-3 py-2.5 text-center">
                    <button @click="openImage(item.gambar)" class="text-[#3b82f6] hover:text-blue-700 hover:underline transition-colors font-medium">
                      {{ item.gambar }}
                    </button>
                  </td>
                  <td class="px-3 py-2.5 text-center">
                    <div class="mx-auto inline-flex w-fit items-center justify-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium border"
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
                  <td colspan="7" class="px-4 py-12 text-center text-gray-500 font-medium">Data tidak ditemukan sesuai filter/pencarian Anda.</td>
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

    <!-- MODAL GAMBAR (LIGHTBOX) -->
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
                :src="`https://picsum.photos/seed/${selectedImage}/800/600`"
                alt="Zoomed Preview"
                class="max-h-[82vh] max-w-[88vw] w-auto h-auto min-w-[280px] sm:min-w-[460px] rounded-xl object-contain shadow-2xl"
              />
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

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