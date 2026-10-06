<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head } from '@inertiajs/vue3';

import AdminLayout from '@/Layouts/dashboard/AdminLayout.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import SearchBarTable from '@/Components/dashboard/SearchBarTable.vue';
import TablePagination from '@/Components/dashboard/TablePagination.vue';
import DeleteModal from '@/Components/dashboard/DeleteModal.vue'; 
import EditButtonTable from '@/Components/dashboard/EditButtonTable.vue';
import DeleteButtonTable from '@/Components/dashboard/DeleteButtonTable.vue';
import DatePicker from '@/Components/dashboard/DatePicker.vue';

// ==========================================
// 1. DATA DUMMY
// ==========================================
const karirList = ref([
  { id: 1, posisi: 'Frontend Developer', instansi: 'PT Teknologi Indonesia', deskripsi: 'Membangun antarmuka web yang interaktif.', kualifikasi: 'S1 - Semester 7\nMenguasai Vue dan Tailwind.', logo: 'logo-frontend.jpg', deadline: '2029-07-10', tautan: 'https://test.com/pendaftaran-1' },
  { id: 2, posisi: 'Data Analyst Intern', instansi: 'Bank Central Asia', deskripsi: 'Menganalisis data transaksi nasabah.', kualifikasi: 'S1 - Semester 5\nBisa SQL dan Python.', logo: 'logo-data.jpg', deadline: '2029-08-12', tautan: 'https://test.com/pendaftaran-2' },
]);

// ==========================================
// 2. STATE FILTER, SEARCH & SORTING
// ==========================================
const searchQuery = ref('');
const showFilter = ref(false);

const sortColumn = ref('id');
const sortDirection = ref('asc');

const filters = ref({ kategori: [], waktu: '' });
const listKategori = ['S1 - Semester 5', 'S1 - Semester 7', 'S1 - Lulusan', 'D3 / Vokasi'];

const resetFilters = () => {
  filters.value.kategori = [];
  filters.value.waktu = '';
  showFilter.value = false;
};

const handleSort = (column) => {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortColumn.value = column;
    sortDirection.value = 'asc';
  }
};

// Format Tanggal untuk tampilan di tabel
const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const options = { day: 'numeric', month: 'long', year: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};

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
    // Coba format tanggal bahasa Indonesia: "10 Juli 2029", "01 November 2029"
    const parts = trimmed.split(/[\s,/-]+/);
    if (parts.length >= 3) {
      const day = parseInt(parts[0], 10);
      const monthStr = parts[1].toLowerCase();
      const year = parseInt(parts[2], 10);
      
      if (!isNaN(day) && monthMapIndo[monthStr] !== undefined && !isNaN(year)) {
        return new Date(year, monthMapIndo[monthStr], day).getTime();
      }
    }

    // Coba format standar ISO (YYYY-MM-DD)
    const parsedTime = Date.parse(trimmed);
    if (!isNaN(parsedTime)) {
      return parsedTime;
    }
  }
  return 0;
};

// ==========================================
// 3. LOGIC FILTERING & SORTING
// ==========================================
const processedKarir = computed(() => {
  let data = [...karirList.value];

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    data = data.filter(item => 
      item.posisi.toLowerCase().includes(query) ||
      item.instansi.toLowerCase().includes(query) ||
      item.kualifikasi.toLowerCase().includes(query)
    );
  }

  if (filters.value.kategori.length > 0) {
    data = data.filter(item => filters.value.kategori.some(kat => item.kualifikasi.includes(kat)));
  }

  if (filters.value.waktu) {
    data = data.filter(item => item.deadline === filters.value.waktu);
  }

  if (sortColumn.value) {
    data.sort((a, b) => {
      let valA = a[sortColumn.value];
      let valB = b[sortColumn.value];

      if (sortColumn.value === 'deadline' || sortColumn.value === 'tanggal' || sortColumn.value === 'waktu' || sortColumn.value === 'created_at') {
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

const currentPage = ref(1);
const rowsPerPage = ref(10);

onMounted(() => {
  if (window.innerWidth < 768) {
    rowsPerPage.value = 5;
  }
});

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * rowsPerPage.value;
  return processedKarir.value.slice(start, start + rowsPerPage.value);
});

const totalPages = computed(() => Math.ceil(processedKarir.value.length / rowsPerPage.value) || 1);

watch([searchQuery, filters, rowsPerPage], () => {
  currentPage.value = 1;
}, { deep: true });

// ==========================================
// 4. STATE TOAST, DELETE, PREVIEW
// ==========================================
const toast = ref({ show: false, message: '', type: 'success' });
const showToast = (message, type = 'success') => {
  toast.value.show = false; 
  setTimeout(() => {
    toast.value = { show: true, message, type };
    setTimeout(() => { toast.value.show = false; }, 3500);
  }, 50);
};

const showDeleteModal = ref(false);
const itemToDelete = ref(null);

const openDeleteModal = (item) => {
  itemToDelete.value = item;
  showDeleteModal.value = true;
};

const executeDelete = () => {
  if (!itemToDelete.value) return;
  karirList.value = karirList.value.filter(k => k.id !== itemToDelete.value.id);
  showDeleteModal.value = false;
  itemToDelete.value = null;
  showToast('Data karir berhasil dihapus!', 'success');
};

const showImageModal = ref(false);
const selectedImage = ref('');

const openImage = (gambar) => {
  if (gambar && typeof gambar === 'object' && gambar.name) {
    selectedImage.value = URL.createObjectURL(gambar);
  } else if (gambar && !gambar.startsWith('http')) {
    selectedImage.value = `https://picsum.photos/seed/${gambar}/800/600`;
  } else {
    selectedImage.value = gambar || 'https://picsum.photos/800/600';
  }
  showImageModal.value = true;
};

// ==========================================
// 5. LOGIC MODAL FORM, UPLOAD & VALIDASI
// ==========================================
const showFormModal = ref(false);
const isEditMode = ref(false);

const logoInput = ref(null);
const logoPreview = ref(null);

const form = ref({ id: null, posisi: '', instansi: '', deskripsi: '', kualifikasi: '', tautan: '', deadline: '', logo: null });

// STATE ERRORS UNTUK VALIDASI
const errors = ref({ posisi: '', instansi: '', deskripsi: '', kualifikasi: '', tautan: '', deadline: '', logo: '' });

const resetErrors = () => {
  errors.value = { posisi: '', instansi: '', deskripsi: '', kualifikasi: '', tautan: '', deadline: '', logo: '' };
};

const validateForm = () => {
  resetErrors();
  let isValid = true;

  if (!form.value.posisi.trim()) {
    errors.value.posisi = 'Posisi wajib diisi.';
    isValid = false;
  }
  if (!form.value.instansi.trim()) {
    errors.value.instansi = 'Nama instansi wajib diisi.';
    isValid = false;
  }
  if (!form.value.deskripsi.trim()) {
    errors.value.deskripsi = 'Deskripsi wajib diisi.';
    isValid = false;
  }
  if (!form.value.kualifikasi.trim()) {
    errors.value.kualifikasi = 'Kualifikasi wajib diisi.';
    isValid = false;
  }
  if (!form.value.tautan.trim()) {
    errors.value.tautan = 'Tautan registrasi wajib diisi.';
    isValid = false;
  }
  if (!form.value.deadline) {
    errors.value.deadline = 'Deadline wajib dipilih.';
    isValid = false;
  }
  if (!form.value.logo) {
    errors.value.logo = 'Logo instansi wajib diunggah.';
    isValid = false;
  }

  return isValid;
};

const triggerLogoUpload = () => { logoInput.value?.click(); };

const handleLogoUpload = (event) => {
  const file = event.target.files?.[0];
  if (file) {
    if (file.size > 10 * 1024 * 1024) {
      showToast('Ukuran gambar maksimal 10MB!', 'error');
      return;
    }
    form.value.logo = file;
    logoPreview.value = URL.createObjectURL(file);
    errors.value.logo = '';
  }
};

const resetForm = () => {
  form.value = { id: null, posisi: '', instansi: '', deskripsi: '', kualifikasi: '', tautan: '', deadline: '', logo: null };
  logoPreview.value = null;
  if (logoInput.value) logoInput.value.value = '';
  resetErrors();
};

const openTambah = () => {
  resetForm();
  isEditMode.value = false;
  showFormModal.value = true;
};

const handleEdit = (item) => {
  resetErrors();
  form.value = { ...item };
  if (item.logo && typeof item.logo === 'object') {
    logoPreview.value = URL.createObjectURL(item.logo);
  } else if (item.logo) {
    logoPreview.value = item.logo.startsWith('http') ? item.logo : `https://picsum.photos/seed/${item.logo}/800/600`;
  } else {
    logoPreview.value = null;
  }
  isEditMode.value = true;
  showFormModal.value = true;
};

const submitForm = () => {
  if (!validateForm()) {
    return;
  }

  if (isEditMode.value) {
    const index = karirList.value.findIndex(k => k.id === form.value.id);
    if (index !== -1) {
      karirList.value[index] = { ...form.value, logo: typeof form.value.logo === 'object' ? form.value.logo.name : form.value.logo };
    }
    showToast('Data karir berhasil diperbarui!', 'success');
  } else {
    const newId = karirList.value.length ? Math.max(...karirList.value.map(k => k.id)) + 1 : 1;
    karirList.value.unshift({ ...form.value, id: newId, logo: form.value.logo.name || 'logo-baru.jpg' });
    showToast('Data karir berhasil ditambahkan!', 'success');
  }
  showFormModal.value = false;
};
</script>

<template>
  <Head title="Daftar Karir"/>

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
          <h1 class="mt-1 text-[34px] font-bold leading-[1.02] tracking-[-0.03em] text-[#17334F] sm:text-[42px] lg:text-[48px]">Daftar Karir</h1>
          <p class="mt-1.5 font-inter text-[14px] font-medium leading-tight text-[#4d6786] sm:text-[16px]">Lihat seberapa banyak mahasiswa yang mengikuti aktivitas Talenta!</p>
        </div>

        <!-- ACTION BAR -->
        <div class="flex flex-row items-center gap-2 sm:gap-3 w-full">
          <!-- Search Input Component -->
          <SearchBarTable
            class="w-full flex-1 min-w-0"
            v-model="searchQuery"
            placeholder="Cari Karir disini..."
          />

          <!-- Action Buttons Row -->
          <div class="flex items-center justify-end gap-2 sm:gap-3 shrink-0">
            <!-- Filter Dropdown Container -->
            <div class="relative">
              <button
                type="button"
                @click="showFilter = !showFilter"
                class="relative flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-[10px] border-2 bg-transparent text-[#183669] transition-colors focus:outline-none select-none cursor-pointer"
                :class="showFilter || filters.kategori.length > 0 || filters.waktu
                  ? 'border-[#183669]'
                  : 'border-[#d6e0ee] hover:border-[#8ea9cb]'"
                title="Filter Karir"
              >
                <img
                  src="/assets/icons/filter.svg"
                  alt="Filter Icon"
                  class="h-5 w-5 shrink-0 object-contain pointer-events-none"
                />
                <!-- Red active indicator dot -->
                <span
                  v-if="filters.kategori.length > 0 || filters.waktu"
                  class="absolute top-2.5 right-2.5 h-2 w-2 rounded-full bg-[#ef4444] ring-2 ring-[#eef2f7]"
                ></span>
              </button>

              <!-- Dropdown Menu Filter -->
              <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="transform scale-95 opacity-0 translate-y-1"
                enter-to-class="transform scale-100 opacity-100 translate-y-0"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="transform scale-100 opacity-100 translate-y-0"
                leave-to-class="transform scale-95 opacity-0 translate-y-1"
              >
                <div
                  v-if="showFilter"
                  class="absolute right-0 top-14 w-80 rounded-[14px] border border-[#d6e0ee] bg-white p-4 shadow-2xl ring-1 ring-black/10 z-40 font-inter"
                >
                  <div class="flex items-center justify-between border-b border-[#f0f4f9] pb-2 mb-3">
                    <p class="font-poppins text-xs font-bold text-[#183669]">
                      Filter Karir
                    </p>
                    <button
                      v-if="filters.kategori.length > 0 || filters.waktu"
                      type="button"
                      @click="resetFilters"
                      class="font-inter text-[11px] font-semibold text-[#dc2626] hover:underline cursor-pointer"
                    >
                      Reset Semua
                    </button>
                  </div>

                  <div class="mb-3.5">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#7188a3] mb-2">
                      Kualifikasi
                    </p>
                    <div class="grid grid-cols-2 gap-2">
                      <label v-for="kat in listKategori" :key="kat" class="flex items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-[#f8fafc] cursor-pointer transition select-none">
                        <input type="checkbox" :value="kat" v-model="filters.kategori" class="h-4 w-4 rounded border-[#cbd5e1] text-[#183669] focus:ring-0 cursor-pointer">
                        <span class="text-xs font-medium text-[#334155]">{{ kat }}</span>
                      </label>
                    </div>
                  </div>

                  <div class="border-t border-[#f0f4f9] pt-3 mb-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#7188a3] mb-2">
                      Waktu (Deadline)
                    </p>
                    <input type="date" v-model="filters.waktu" class="w-full rounded-lg border border-[#cbd5e1] px-3 py-2 text-xs outline-none focus:border-[#183669] text-gray-700 font-inter" />
                  </div>

                  <div class="flex items-center justify-end border-t border-[#f0f4f9] pt-3">
                    <button @click="showFilter = false" class="rounded-lg bg-[#183669] px-4 py-1.5 text-xs font-semibold text-white transition hover:bg-[#122b54] cursor-pointer">Tutup</button>
                  </div>
                </div>
              </Transition>
            </div>

            <!-- Tambah Button -->
            <button
              type="button"
              @click="(e) => { e.currentTarget?.blur(); openTambah(); }"
              class="flex h-[46px] w-[46px] sm:w-auto shrink-0 items-center justify-center gap-2 rounded-[10px] bg-[#183669] px-0 sm:px-7 font-poppins text-[15px] font-semibold text-white shadow-sm transition hover:bg-[#122b54] active:scale-95 focus:outline-none select-none cursor-pointer"
              title="Tambah Karir"
            >
              <svg class="h-5 w-5 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
              <span class="hidden sm:inline">Tambah</span>
            </button>
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
                  <th class="w-[180px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    <button type="button" @click="handleSort('instansi')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer">
                      <span>Instansi</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'instansi'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <th class="w-[160px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    <button type="button" @click="handleSort('posisi')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer">
                      <span>Posisi</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'posisi'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <!-- <th class="w-[130px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">Logo</th> -->
                  <th class="w-[150px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    <button type="button" @click="handleSort('deadline')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer">
                      <span>Deadline</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'deadline'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <th class="w-[200px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">Kualifikasi</th>
                  <th class="w-[150px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">Tautan</th>
                  <th class="w-[110px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none rounded-tr-[12px]">Aksi</th>
                </tr>
              </thead>
              
              <tbody class="[&_tr:not(:first-child)_td]:border-t [&_tr:not(:first-child)_td]:border-[#d6e0ee] font-inter text-[14px] text-[#435b76]">
                <tr v-for="(item, index) in paginatedData" :key="item.id" class="h-[52px] transition-colors hover:bg-[#f7f9fd]">
                  <td class="px-3 py-2.5 text-center font-medium">{{ (currentPage - 1) * rowsPerPage + index + 1 }}</td>
                  <td class="px-3 py-2.5 text-left font-medium text-[#233547] truncate" :title="item.instansi">{{ item.instansi }}</td>
                  <td class="px-3 py-2.5 text-left font-medium text-[#233547] truncate" :title="item.posisi">{{ item.posisi }}</td>
                  <!-- <td class="px-3 py-2.5 text-center">
                    <button @click="openImage(item.logo)" class="font-inter text-[14px] font-medium text-[#3b82f6] hover:text-blue-700 hover:underline transition-colors cursor-pointer truncate max-w-[110px] inline-block align-middle" :title="typeof item.logo === 'object' ? item.logo.name : item.logo">
                      {{ (item.logo && typeof item.logo === 'object' && item.logo.name) ? item.logo.name : item.logo }}
                    </button>
                  </td> -->
                  <td class="px-3 py-2.5 text-center font-medium">{{ formatDate(item.deadline) }}</td>
                  <td class="px-3 py-2.5 text-left truncate" :title="item.kualifikasi">{{ item.kualifikasi }}</td>
                  <td class="px-3 py-2.5 text-center">
                    <a v-if="item.tautan" :href="item.tautan" target="_blank" class="font-inter text-[14px] font-medium text-[#3b82f6] hover:text-blue-700 hover:underline transition-colors truncate max-w-[130px] inline-block align-middle" :title="item.tautan">
                      {{ item.tautan }}
                    </a>
                    <span v-else class="font-inter text-[14px] text-gray-400">-</span>
                  </td>
                  <td class="px-3 py-2.5 text-center">
                    <div class="flex items-center justify-center gap-2">
                      <EditButtonTable @click="handleEdit(item)" />
                      <DeleteButtonTable @click="openDeleteModal(item)" />
                    </div>
                  </td>
                </tr>
                <tr v-if="paginatedData.length === 0">
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

    <!-- Modal Delete -->
    <DeleteModal 
      :show="showDeleteModal" 
      title="Hapus Data Karir?" 
      :message="`Apakah Anda yakin ingin menghapus data karir posisi ${itemToDelete?.posisi}? Data yang dihapus tidak dapat dikembalikan.`" 
      @close="showDeleteModal = false" 
      @confirm="executeDelete" 
    />

    <!-- ========================================== -->
    <!-- MODAL FORM TAMBAH & EDIT DENGAN VALIDASI -->
    <!-- ========================================== -->
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
          v-if="showFormModal"
          class="fixed inset-0 z-50 overflow-y-auto overscroll-contain bg-slate-900/40 backdrop-blur-xs p-3 sm:p-6 flex justify-center items-start min-h-screen"
          @click.self="showFormModal = false"
        >
          <Transition
            enter-active-class="ease-out duration-200"
            enter-from-class="opacity-0 scale-95 translate-y-2"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="ease-in duration-150"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 translate-y-2"
          >
            <div
              v-if="showFormModal"
              class="relative w-full max-w-4xl my-auto transform rounded-[16px] sm:rounded-[20px] bg-white p-4 sm:p-7 shadow-2xl font-poppins border border-[#e2e8f0] overflow-visible"
            >
              <!-- Modal Title -->
              <div class="mb-4 sm:mb-6">
                <h2 class="text-[18px] sm:text-[22px] font-bold text-[#183669] tracking-tight">
                  {{ isEditMode ? 'Edit Karir' : 'Form Tambah Karir' }}
                </h2>
              </div>

              <form @submit.prevent="submitForm" novalidate>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                  
                  <!-- Kolom Kiri -->
                  <div class="flex flex-col gap-3.5 sm:gap-4">
                    <!-- Posisi -->
                    <div>
                      <label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
                        Posisi<span class="text-red-500">*</span>
                      </label>
                      <p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">
                        Masukkan posisi karir yang dibuka yah!
                      </p>
                      <input 
                        type="text" 
                        v-model="form.posisi" 
                        @input="errors.posisi = ''"
                        placeholder="Contoh: Frontend Developer" 
                        class="mt-1 sm:mt-1.5 h-[42px] sm:h-[44px] w-full rounded-[10px] border bg-white px-3.5 font-inter text-[13px] sm:text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0" 
                        :class="errors.posisi ? 'border-red-400 focus:border-red-500 bg-red-50/20' : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white'"
                      />
                      <p v-if="errors.posisi" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
                        <svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ errors.posisi }}</span>
                      </p>
                    </div>

                    <!-- Nama Instansi -->
                    <div>
                      <label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
                        Nama Instansi<span class="text-red-500">*</span>
                      </label>
                      <p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">
                        Masukkan nama instansi / perusahaan di sini!
                      </p>
                      <input 
                        type="text" 
                        v-model="form.instansi" 
                        @input="errors.instansi = ''"
                        placeholder="Contoh: PT Teknologi Bangsa" 
                        class="mt-1 sm:mt-1.5 h-[42px] sm:h-[44px] w-full rounded-[10px] border bg-white px-3.5 font-inter text-[13px] sm:text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0" 
                        :class="errors.instansi ? 'border-red-400 focus:border-red-500 bg-red-50/20' : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white'"
                      />
                      <p v-if="errors.instansi" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
                        <svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ errors.instansi }}</span>
                      </p>
                    </div>

                    <!-- Deskripsi -->
                    <div>
                      <label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
                        Deskripsi<span class="text-red-500">*</span>
                      </label>
                      <p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">
                        Jelaskan gambaran pekerjaan ini yah!
                      </p>
                      <textarea 
                        v-model="form.deskripsi" 
                        @input="errors.deskripsi = ''"
                        placeholder="Deskripsi kegiatan atau pekerjaan..." 
                        rows="3" 
                        class="mt-1 sm:mt-1.5 w-full min-h-[90px] rounded-[10px] border bg-white px-3.5 py-2.5 font-inter text-[13px] sm:text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0 resize-y"
                        :class="errors.deskripsi ? 'border-red-400 focus:border-red-500 bg-red-50/20' : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white'"
                      ></textarea>
                      <p v-if="errors.deskripsi" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
                        <svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ errors.deskripsi }}</span>
                      </p>
                    </div>

                    <!-- Kualifikasi -->
                    <div>
                      <label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
                        Kualifikasi<span class="text-red-500">*</span>
                      </label>
                      <p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">
                        Sebutkan kualifikasi yang dibutuhkan!
                      </p>
                      <textarea 
                        v-model="form.kualifikasi" 
                        @input="errors.kualifikasi = ''"
                        placeholder="S1 Semester 7, menguasai skill..." 
                        rows="3" 
                        class="mt-1 sm:mt-1.5 w-full min-h-[90px] rounded-[10px] border bg-white px-3.5 py-2.5 font-inter text-[13px] sm:text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0 resize-y"
                        :class="errors.kualifikasi ? 'border-red-400 focus:border-red-500 bg-red-50/20' : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white'"
                      ></textarea>
                      <p v-if="errors.kualifikasi" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
                        <svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ errors.kualifikasi }}</span>
                      </p>
                    </div>
                  </div>

                  <!-- Kolom Kanan -->
                  <div class="flex flex-col gap-3.5 sm:gap-4">
                    <!-- Logo Upload -->
                    <div>
                      <label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
                        Logo Instansi<span class="text-red-500">*</span>
                      </label>
                      <p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5 mb-1.5">
                        Masukkan logo instansi (JPG/PNG, MAX 10MB)
                      </p>
                      
                      <input type="file" ref="logoInput" accept="image/png, image/jpeg, image/jpg" class="hidden" @change="handleLogoUpload" />

                      <div 
                        @click="triggerLogoUpload" 
                        :class="[
                          'relative flex min-h-[145px] w-full flex-col items-center justify-center rounded-[12px] border-2 border-dashed p-3.5 text-center transition-colors cursor-pointer group select-none',
                          errors.logo ? 'border-red-400 bg-red-50/20' : 'border-[#183669]/30 bg-[#fafcff] hover:border-[#183669]/60'
                        ]"
                      >
                        <template v-if="logoPreview">
                          <div class="relative h-[120px] w-full max-w-[180px] rounded-[10px] overflow-hidden border border-[#d6e0ee] bg-white p-2">
                            <img :src="logoPreview" alt="Preview" class="h-full w-full object-contain" />
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                              <span class="rounded-[8px] bg-white px-3 py-1 text-[11px] font-bold text-[#183669] shadow-md">Ganti Logo</span>
                            </div>
                          </div>
                        </template>
                        <template v-else>
                          <svg class="h-9 w-9 text-[#8c9eb5] group-hover:text-[#183669] transition-colors" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                          </svg>
                          <p class="mt-1.5 font-inter text-[12px] text-[#7188a3]">
                            Upload logo atau seret file ke form ini
                          </p>
                          <button type="button" class="mt-2 rounded-[8px] border border-[#a6b7cb] bg-white px-5 py-1 font-inter text-[12px] font-semibold text-[#5a718d] transition hover:bg-slate-50 shadow-xs cursor-pointer">Upload</button>
                        </template>
                      </div>
                      <p v-if="errors.logo" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
                        <svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ errors.logo }}</span>
                      </p>
                    </div>

                    <!-- Tautan -->
                    <div>
                      <label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
                        Tautan Registrasi<span class="text-red-500">*</span>
                      </label>
                      <p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">
                        Masukkan tautan pendaftaran karir di sini!
                      </p>
                      <input 
                        type="text" 
                        v-model="form.tautan" 
                        @input="errors.tautan = ''"
                        placeholder="https://..." 
                        class="mt-1 sm:mt-1.5 h-[42px] sm:h-[44px] w-full rounded-[10px] border bg-white px-3.5 font-inter text-[13px] sm:text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0" 
                        :class="errors.tautan ? 'border-red-400 focus:border-red-500 bg-red-50/20' : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white'"
                      />
                      <p v-if="errors.tautan" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
                        <svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ errors.tautan }}</span>
                      </p>
                    </div>

                    <!-- Deadline (DatePicker) -->
                    <div>
                      <label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
                        Deadline<span class="text-red-500">*</span>
                      </label>
                      <p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">
                        Masukkan batas waktu registrasi karir!
                      </p>
                      <div class="mt-1 sm:mt-1.5">
                        <DatePicker
                          v-model="form.deadline"
                          :has-error="!!errors.deadline"
                          placeholder="Pilih tanggal deadline"
                          @update:modelValue="errors.deadline = ''"
                        />
                      </div>
                      <p v-if="errors.deadline" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
                        <svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ errors.deadline }}</span>
                      </p>
                    </div>

                  </div>
                </div>

                <!-- Tombol Aksi Form (Center di Mobile & Kanan di Desktop) -->
                <div class="mt-6 sm:mt-8 flex flex-row items-center justify-center sm:justify-end gap-3 sm:gap-4 pt-3 sm:pt-4 border-t border-slate-100">
                  <button
                    type="button"
                    @click="showFormModal = false"
                    class="h-[42px] sm:h-[44px] min-w-[120px] sm:min-w-[140px] px-5 sm:px-6 rounded-[10px] border border-[#d6e0ee] bg-white font-poppins text-[13px] sm:text-[14px] font-bold text-[#183669] transition hover:border-[#183669] hover:bg-slate-50 focus:border-[#183669] focus:outline-none active:scale-98 cursor-pointer select-none"
                  >
                    Kembali
                  </button>
                  <button
                    type="submit"
                    class="h-[42px] sm:h-[44px] min-w-[120px] sm:min-w-[140px] px-5 sm:px-6 rounded-[10px] bg-[#183669] font-poppins text-[13px] sm:text-[14px] font-bold text-white shadow-sm transition hover:bg-[#122b54] active:scale-98 focus:outline-none cursor-pointer select-none whitespace-nowrap"
                  >
                    Simpan
                  </button>
                </div>
              </form>
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

  </AdminLayout>

  <!-- ========================================== -->
  <!-- MODAL GAMBAR (LIGHTBOX) -->
  <!-- ========================================== -->
  <Teleport to="body">
    <Transition enter-active-class="ease-out duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showImageModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/80 backdrop-blur-md p-4 transition-all" @click="showImageModal = false">
        <Transition enter-active-class="ease-out duration-200" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="ease-in duration-150" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
          <div v-if="showImageModal" class="relative flex items-center justify-center bg-transparent" @click.stop>
            <button type="button" @click="showImageModal = false" class="absolute top-3.5 right-3.5 z-20 flex h-9 w-9 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/85 backdrop-blur-xs shadow-md transition hover:scale-105 active:scale-95 focus:outline-none cursor-pointer" title="Tutup Preview">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <img :src="selectedImage" alt="Zoomed Preview" class="max-h-[82vh] max-w-[88vw] w-auto h-auto min-w-[280px] sm:min-w-[460px] rounded-xl object-contain shadow-2xl" />
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>

</template>