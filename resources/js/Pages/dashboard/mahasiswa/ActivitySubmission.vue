<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head } from '@inertiajs/vue3';

import MahasiswaLayout from '@/Layouts/dashboard/MahasiswaLayout.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import SearchBarTable from '@/Components/dashboard/SearchBarTable.vue';
import TablePagination from '@/Components/dashboard/TablePagination.vue';
import DeleteModal from '@/Components/dashboard/DeleteModal.vue'; 
import EditButtonTable from '@/Components/dashboard/EditButtonTable.vue';
import DeleteButtonTable from '@/Components/dashboard/DeleteButtonTable.vue';

import ActivitySubmissionModal from '@/Components/dashboard/mahasiswa/ActivitySubmissionModal.vue';

// 10 Data Dummy
const aktivitasList = ref([
  { id: 1, judul: 'Pelatihan Manajemen Risiko Kehutanan', deskripsi: 'Pelatihan mitigasi dan tata kelola risiko bisnis kehutanan.', kategori: 'Profesional, Bisnis', deadline: '10 Juli 2029', gambar: '123.jpg', status: 'Menunggu', catatan_penolakan: '' },
  { id: 2, judul: 'Seminar Nasional Teknologi Hijau & AI', deskripsi: 'Seminar implementasi IoT dan AI untuk restorasi hutan.', kategori: 'Akademisi, Profesional', deadline: '12 Agustus 2029', gambar: '456.jpg', status: 'Diterima', catatan_penolakan: '' },
  { id: 3, judul: 'Kompetisi Proposal Bisnis Plan Agroforestry', deskripsi: 'Kompetisi rancang model bisnis hasil hutan bukan kayu.', kategori: 'Bisnis', deadline: '15 September 2029', gambar: '789.jpg', status: 'Ditolak', catatan_penolakan: 'Gambar bukti sertifikat kurang jelas, mohon lampirkan scan dokumen beresolusi tinggi.' },
  { id: 4, judul: 'Workshop Kewirausahaan Digital Hasil Hutan', deskripsi: 'Workshop strategi pemasaran digital komoditas lestari.', kategori: 'Bisnis, Profesional', deadline: '20 Oktober 2029', gambar: '101.jpg', status: 'Menunggu', catatan_penolakan: '' },
  { id: 5, judul: 'Sosialisasi Sertifikasi SVLK & Perdagangan Karbon', deskripsi: 'Sosialisasi kepatuhan sertifikasi kayu dan kredit karbon.', kategori: 'Birokrat, Akademisi', deadline: '01 November 2029', gambar: '112.jpg', status: 'Ditolak', catatan_penolakan: 'Format surat tugas belum sesuai dengan ketentuan kampus.' },
  { id: 6, judul: 'Hackathon Konservasi Biodiversitas Tropis', deskripsi: 'Pengembangan software pemantauan satwa liar berbasis drone.', kategori: 'Profesional, Akademisi', deadline: '10 Desember 2029', gambar: '131.jpg', status: 'Diterima', catatan_penolakan: '' },
  { id: 7, judul: 'Pelatihan Pemetaan GIS dan Remote Sensing', deskripsi: 'Pelatihan analisis tutupan tajuk pohon berbasis citra satelit.', kategori: 'Profesional', deadline: '15 Januari 2030', gambar: '141.jpg', status: 'Menunggu', catatan_penolakan: '' },
  { id: 8, judul: 'Forum Diskusi Ekosistem Mangrove Berkelanjutan', deskripsi: 'Forum advokasi perlindungan hutan mangrove pesisir.', kategori: 'Bisnis, Profesional', deadline: '20 Februari 2030', gambar: '151.jpg', status: 'Diterima', catatan_penolakan: '' },
  { id: 9, judul: 'Pengabdian Masyarakat Restorasi Lahan Kritis', deskripsi: 'Penanaman bibit pohon endemik bersama kelompok tani hutan.', kategori: 'Akademisi, Birokrat', deadline: '05 Maret 2030', gambar: '161.jpg', status: 'Menunggu', catatan_penolakan: '' },
  { id: 10, judul: 'Webinar Sertifikasi AMDAL dan Audit Lingkungan', deskripsi: 'Webinar regulasi izin lingkungan hidup dan AMDAL.', kategori: 'Profesional, Akademisi', deadline: '12 April 2030', gambar: '171.jpg', status: 'Ditolak', catatan_penolakan: 'Dokumentasi kegiatan belum lengkap.' },
]);

const searchQuery = ref('');
const filterStatus = ref('');
const showFilter = ref(false);

const sortColumn = ref('id');
const sortDirection = ref('asc');

const currentPage = ref(1);
const rowsPerPage = ref(10);

onMounted(() => {
  if (window.innerWidth < 768) {
    rowsPerPage.value = 5;
  }
});

// Mapping bulan Indonesia untuk parsing tanggal
const monthMapIndo = {
  januari: 0, jan: 0,
  februari: 1, feb: 1,
  maret: 2, mar: 2,
  april: 3, apr: 3,
  mei: 4, may: 4,
  juni: 5, jun: 5,
  juli: 6, jul: 6,
  agustus: 7, agu: 7, ags: 7, aug: 7,
  september: 8, sep: 8, sept: 8,
  oktober: 9, okt: 9, oct: 9,
  november: 10, nov: 10,
  desember: 11, des: 11, dec: 11,
};

const parseDateValue = (val) => {
  if (!val) return 0;
  if (val instanceof Date) return val.getTime();
  if (typeof val === 'number') return val;
  
  if (typeof val === 'string') {
    const trimmed = val.trim();
    const parts = trimmed.split(/[\s,/-]+/);
    if (parts.length >= 3) {
      const day = parseInt(parts[0], 10);
      const monthStr = parts[1].toLowerCase();
      const year = parseInt(parts[2], 10);
      
      if (!isNaN(day) && monthMapIndo[monthStr] !== undefined && !isNaN(year)) {
        return new Date(year, monthMapIndo[monthStr], day).getTime();
      }
    }

    const parsedTime = Date.parse(trimmed);
    if (!isNaN(parsedTime)) {
      return parsedTime;
    }
  }
  return 0;
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

const processedAktivitas = computed(() => {
  let data = [...aktivitasList.value];

  if (filterStatus.value) {
    data = data.filter((item) => item.status === filterStatus.value);
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    data = data.filter(
      (item) =>
        item.judul.toLowerCase().includes(query) ||
        item.kategori.toLowerCase().includes(query)
    );
  }

  if (sortColumn.value) {
    data.sort((a, b) => {
      let valA = a[sortColumn.value];
      let valB = b[sortColumn.value];

      if (sortColumn.value === 'deadline' || sortColumn.value === 'tanggal') {
        const timeA = parseDateValue(valA);
        const timeB = parseDateValue(valB);
        return sortDirection.value === 'asc' ? timeA - timeB : timeB - timeA;
      }

      if (typeof valA === 'string') valA = valA.toLowerCase();
      if (typeof valB === 'string') valB = valB.toLowerCase();

      if (valA < valB) return sortDirection.value === 'asc' ? -1 : 1;
      if (valA > valB) return sortDirection.value === 'asc' ? 1 : -1;
      return 0;
    });
  }

  return data;
});

const paginatedAktivitas = computed(() => {
  const start = (currentPage.value - 1) * rowsPerPage.value;
  return processedAktivitas.value.slice(start, start + rowsPerPage.value);
});

const totalPages = computed(() => Math.ceil(processedAktivitas.value.length / rowsPerPage.value));

watch([searchQuery, filterStatus, rowsPerPage], () => {
  currentPage.value = 1;
});

// ==============================
// TOAST STATE
// ==============================
const toast = ref({ show: false, message: '', type: 'success' });
const showToast = (message, type = 'success') => {
  toast.value.show = false; 
  setTimeout(() => {
    toast.value = { show: true, message, type };
    setTimeout(() => { toast.value.show = false; }, 3500);
  }, 50);
};

// ==============================
// DELETE MODAL
// ==============================
const showDeleteModal = ref(false);
const itemToDelete = ref(null);
const openDeleteModal = (item) => {
  itemToDelete.value = item;
  showDeleteModal.value = true;
};
const executeDelete = () => {
  if (itemToDelete.value) {
    aktivitasList.value = aktivitasList.value.filter((k) => k.id !== itemToDelete.value.id);
  }
  showDeleteModal.value = false;
  itemToDelete.value = null;
  showToast('Pengajuan berhasil dihapus!', 'success');
};

// ==============================
// LIGHTBOX MODAL
// ==============================
const showImageModal = ref(false);
const selectedImage = ref('');
const openImage = (gambarArray) => {
  const gmb = Array.isArray(gambarArray) ? gambarArray[0] : gambarArray;
  if (gmb && typeof gmb === 'object' && gmb.url) {
    selectedImage.value = gmb.url;
  } else if (gmb && typeof gmb === 'object' && gmb.name) {
    selectedImage.value = URL.createObjectURL(gmb);
  } else if (gmb && typeof gmb === 'string' && (gmb.startsWith('http') || gmb.startsWith('/'))) {
    selectedImage.value = gmb;
  } else {
    selectedImage.value = `https://picsum.photos/seed/${gmb || 'dummy'}/800/600`;
  }
  showImageModal.value = true;
};

const getGambarName = (gambar) => {
  if (!gambar) return '-';
  if (Array.isArray(gambar)) {
    const first = gambar[0];
    if (!first) return '-';
    const name = typeof first === 'object' && first.name ? first.name : first;
    return gambar.length > 1 ? `${name} (+${gambar.length - 1})` : name;
  }
  return typeof gambar === 'object' && gambar.name ? gambar.name : gambar;
};

const getGambarTitle = (gambar) => {
  if (!gambar) return '';
  if (Array.isArray(gambar)) {
    return gambar.map((g) => (typeof g === 'object' && g.name ? g.name : g)).join(', ');
  }
  return typeof gambar === 'object' && gambar.name ? gambar.name : gambar;
};

// ==============================
// FORM MODAL (SUBMISSION & EDIT)
// ==============================
const isModalOpen = ref(false);
const isEditMode = ref(false);
const selectedData = ref(null);

const openTambah = () => {
  selectedData.value = null;
  isEditMode.value = false;
  isModalOpen.value = true;
};

const handleEdit = (item) => {
  selectedData.value = { ...item };
  isEditMode.value = true;
  isModalOpen.value = true;
};

const handleModalSubmit = (formData) => {
  if (isEditMode.value && formData.id) {
    const index = aktivitasList.value.findIndex((k) => k.id === formData.id);
    if (index !== -1) {
      const katString = Array.isArray(formData.kategori) ? formData.kategori.join(', ') : formData.kategori;
      aktivitasList.value[index] = { ...aktivitasList.value[index], ...formData, kategori: katString };
    }
    showToast('Pengajuan berhasil diperbarui!', 'success');
  } else {
    const newId = aktivitasList.value.length ? Math.max(...aktivitasList.value.map((k) => k.id)) + 1 : 1;
    const katString = Array.isArray(formData.kategori) ? formData.kategori.join(', ') : formData.kategori;
    aktivitasList.value.unshift({ ...formData, id: newId, kategori: katString, status: 'Menunggu' });
    showToast('Pengajuan berhasil ditambahkan!', 'success');
  }
  isModalOpen.value = false;
};
</script>

<template>
  <Head title="Pengajuan Aktivitas" />

  <MahasiswaLayout>
    <section class="mx-auto w-full max-w-[1520px] px-4 pt-6 pb-24 font-poppins sm:px-6 sm:py-8 lg:px-8">
      <div class="space-y-6">
        
        <!-- TOAST NOTIFICATION -->
        <Transition enter-active-class="transition-all transform duration-500 ease-out" enter-from-class="translate-x-12 opacity-0" enter-to-class="translate-x-0 opacity-100" leave-active-class="transition-all transform duration-300 ease-in" leave-from-class="translate-x-0 opacity-100" leave-to-class="translate-x-12 opacity-0">
          <div v-if="toast.show" class="fixed top-8 right-8 z-[100]">
            <ToastNotification :message="toast.message" :show="true" :type="toast.type" @close="toast.show = false" />
          </div>
        </Transition>

        <!-- PAGE HEADER -->
        <div class="space-y-1.5">
          <h1 class="mt-1 text-[34px] font-bold leading-[1.02] tracking-[-0.03em] text-[#17334F] sm:text-[42px] lg:text-[48px]">
            Pengajuan Aktivitas
          </h1>
          <p class="mt-1.5 font-inter text-[14px] font-medium leading-tight text-[#4d6786] sm:text-[16px]">
            Kamu punya aktivitas lain? Yuk ajukan disini!
          </p>
        </div>

        <!-- TOP BAR (SEARCH, FILTER, & TAMBAH) -->
        <div class="flex flex-row items-center gap-2.5 sm:gap-3 w-full">
          <SearchBarTable class="w-full flex-1 min-w-0" placeholder="Cari Aktivitas disini..." v-model="searchQuery" />

          <!-- Filter Dropdown Container -->
          <div class="flex items-center justify-end gap-2 sm:gap-3 shrink-0">
            <div class="relative">
              <button
                @click="showFilter = !showFilter"
                class="relative flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-[10px] border-2 bg-transparent text-[#183669] transition-colors hover:border-[#8ea9cb] focus:outline-none select-none cursor-pointer"
                :class="showFilter || filterStatus ? 'border-[#183669]' : 'border-[#d6e0ee]'"
                title="Filter Status"
              >
                <img src="/assets/icons/filter.svg" alt="Filter Icon" class="h-5 w-5 shrink-0 object-contain pointer-events-none" />
                <span v-if="filterStatus" class="absolute top-2.5 right-2.5 h-2 w-2 rounded-full bg-[#ef4444] ring-2 ring-[#eef2f7]"></span>
              </button>

              <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-2"
              >
                <div v-if="showFilter" class="absolute right-0 top-14 z-40 w-48 rounded-xl border border-gray-200 bg-white p-2 shadow-lg font-inter">
                  <p class="px-3 py-1.5 text-xs font-bold text-gray-400 uppercase">Filter Status</p>
                  <button
                    @click="filterStatus = ''; showFilter = false"
                    class="w-full rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 font-medium cursor-pointer"
                    :class="!filterStatus ? 'bg-gray-100 font-semibold text-[#183669]' : ''"
                  >
                    Semua Data
                  </button>
                  <button
                    @click="filterStatus = 'Menunggu'; showFilter = false"
                    class="w-full rounded-lg px-3 py-2 text-left text-sm text-gray-600 hover:bg-gray-100 font-medium cursor-pointer"
                    :class="filterStatus === 'Menunggu' ? 'bg-gray-100 font-semibold' : ''"
                  >
                    Menunggu
                  </button>
                  <button
                    @click="filterStatus = 'Diterima'; showFilter = false"
                    class="w-full rounded-lg px-3 py-2 text-left text-sm text-green-600 hover:bg-green-50 font-medium cursor-pointer"
                    :class="filterStatus === 'Diterima' ? 'bg-green-50 font-semibold' : ''"
                  >
                    Diterima
                  </button>
                  <button
                    @click="filterStatus = 'Ditolak'; showFilter = false"
                    class="w-full rounded-lg px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50 font-medium cursor-pointer"
                    :class="filterStatus === 'Ditolak' ? 'bg-red-50 font-semibold' : ''"
                  >
                    Ditolak
                  </button>
                </div>
              </Transition>
            </div>

            <!-- Overlay penutup filter jika klik di luar -->
            <div v-if="showFilter" @click="showFilter = false" class="fixed inset-0 z-30"></div>

            <!-- TOMBOL TAMBAH -->
            <button
              type="button"
              @click="openTambah"
              class="flex h-[46px] w-[46px] sm:w-auto shrink-0 items-center justify-center gap-2 rounded-[10px] bg-[#183669] px-0 sm:px-7 font-poppins text-[15px] font-semibold text-white shadow-sm transition hover:bg-[#122b54] active:scale-95 focus:outline-none select-none cursor-pointer"
            >
              <span class="hidden sm:inline">Tambah</span>
              <svg class="h-5 w-5 sm:hidden shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
            </button>
          </div>
        </div>

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
                    Kategori
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
                  <th class="w-[120px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    Gambar
                  </th>
                  <th class="w-[130px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    Status
                  </th>
                  <th class="w-[140px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0 rounded-tr-[12px]">
                    Aksi
                  </th>
                </tr>
              </thead>
              
              <tbody class="[&_tr:not(:first-child)_td]:border-t [&_tr:not(:first-child)_td]:border-[#d6e0ee] font-inter text-[14px] text-[#435b76]">
                <tr v-for="(item, index) in paginatedAktivitas" :key="item.id" class="h-[52px] transition-colors hover:bg-[#f7f9fd]">
                  <!-- No -->
                  <td class="px-3 py-2.5 text-center">{{ (currentPage - 1) * rowsPerPage + index + 1 }}</td>
                  
                  <!-- Judul -->
                  <td class="px-3 py-2.5 text-left font-medium text-[#233547] truncate" :title="item.judul">
                    {{ item.judul }}
                  </td>
                  
                  <!-- Kategori Chips -->
                  <td class="px-3 py-2.5 text-left align-middle relative">
                    <div class="flex items-center gap-1.5 flex-nowrap w-full" v-if="item.kategori">
                      <!-- Render first 2 chips -->
                      <template v-for="(cat, catIdx) in item.kategori.split(',').map((s) => s.trim()).slice(0, 2)" :key="catIdx">
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
                        
                        <!-- Hover Tooltip Container -->
                        <div class="hidden group-hover:block group-focus-within:block absolute left-0 top-full z-[60] mt-1.5 shadow-lg rounded-lg border border-gray-200 bg-white p-2 animate-in fade-in slide-in-from-top-1 duration-200">
                          <div class="flex flex-col gap-1.5 min-w-fit whitespace-nowrap">
                            <span v-for="(cat, catIdx) in item.kategori.split(',').map((s) => s.trim()).slice(2)" :key="catIdx" :class="[
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
                  
                  <!-- Deadline -->
                  <td class="px-3 py-2.5 text-center whitespace-nowrap">{{ item.deadline }}</td>
                  
                  <!-- Gambar -->
                  <td class="px-3 py-2.5 text-center">
                    <button
                      type="button"
                      @click="openImage(item.gambar)"
                      class="font-inter text-[14px] font-medium text-[#3b82f6] hover:text-blue-700 hover:underline transition-colors cursor-pointer truncate max-w-[110px] inline-block align-middle"
                      :title="getGambarTitle(item.gambar)"
                    >
                      {{ getGambarName(item.gambar) }}
                    </button>
                  </td>
                  
                  <!-- Status (Exact SVGs & Design dari ActivityApproval.vue) -->
                  <td class="px-3 py-2.5 text-center">
                    <div
                      class="mx-auto inline-flex w-fit items-center justify-center gap-1.5 rounded-full px-3 py-1 font-inter text-[11px] font-semibold border"
                      :class="{
                        'bg-gray-100 text-gray-600 border-gray-200': item.status === 'Menunggu',
                        'bg-green-50 text-green-600 border-green-200': item.status === 'Diterima' || item.status === 'Disetujui',
                        'bg-red-50 text-red-600 border-red-200': item.status === 'Ditolak'
                      }"
                    >
                      <svg v-if="item.status === 'Menunggu'" class="h-3.5 w-3.5 shrink-0 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="9" />
                        <polyline points="12 7 12 12 15 15" />
                      </svg>
                      <svg v-if="item.status === 'Diterima' || item.status === 'Disetujui'" class="h-3.5 w-3.5 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <svg v-if="item.status === 'Ditolak'" class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <span>{{ item.status }}</span>
                    </div>
                  </td>
                  
                  <!-- Aksi -->
                  <td class="px-3 py-2.5 text-center">
                    <div class="flex items-center justify-center gap-2">
                      <EditButtonTable
                        :disabled="item.status === 'Diterima' || item.status === 'Disetujui'"
                        :title="item.status === 'Diterima' || item.status === 'Disetujui' ? 'Aktivitas yang telah disetujui tidak dapat diedit' : 'Edit Pengajuan'"
                        @click="handleEdit(item)"
                      />
                      <DeleteButtonTable
                        :disabled="item.status !== 'Menunggu'"
                        :title="item.status !== 'Menunggu' ? 'Hanya pengajuan berstatus Menunggu yang dapat dihapus' : 'Hapus Pengajuan'"
                        @click="openDeleteModal(item)"
                      />
                    </div>
                  </td>
                </tr>
                <tr v-if="paginatedAktivitas.length === 0">
                  <td colspan="7" class="px-4 py-12 text-center text-gray-500 font-medium">
                    Data pengajuan tidak ditemukan sesuai filter/pencarian Anda.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- PAGINATION -->
        <TablePagination 
          :current-page="currentPage" 
          :total-pages="totalPages" 
          :rows-per-page="rowsPerPage"
          @update:current-page="currentPage = $event"
          @update:rows-per-page="rowsPerPage = $event; currentPage = 1"
        />
      </div>
    </section>

    <!-- MODAL DELETE -->
    <DeleteModal
      :show="showDeleteModal"
      @close="showDeleteModal = false"
      @confirm="executeDelete"
    >
      <template #title>
        Hapus Pengajuan?
      </template>
      <template #message>
        Apakah Anda yakin ingin menghapus pengajuan <span class="font-bold text-[#17334F]">"{{ itemToDelete?.judul }}"</span>? Tindakan ini tidak dapat dibatalkan.
      </template>
      <template #confirm-text>
        Hapus Pengajuan
      </template>
    </DeleteModal>

    <!-- MODAL SUBMISSION (TAMBAH / EDIT / PREVIEW) -->
    <ActivitySubmissionModal 
      :show="isModalOpen" 
      :isEditMode="isEditMode" 
      :initialData="selectedData" 
      @close="isModalOpen = false" 
      @submit="handleModalSubmit" 
    />

    <!-- MODAL GAMBAR (LIGHTBOX) -->
    <Teleport to="body">
      <Transition enter-active-class="ease-out duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="showImageModal" class="fixed inset-0 z-[120] flex items-center justify-center bg-slate-900/80 backdrop-blur-md p-4 transition-all" @click="showImageModal = false">
          <Transition enter-active-class="ease-out duration-200" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="ease-in duration-150" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showImageModal" class="relative flex items-center justify-center bg-transparent" @click.stop>
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
              <img :src="selectedImage" alt="Zoomed Preview" class="max-h-[82vh] max-w-[88vw] w-auto h-auto min-w-[280px] sm:min-w-[460px] rounded-xl object-contain shadow-2xl" />
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

  </MahasiswaLayout>
</template>