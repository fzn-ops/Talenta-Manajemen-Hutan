<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head } from '@inertiajs/vue3';

import AdminLayout from '@/Layouts/dashboard/AdminLayout.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import SearchBarTable from '@/Components/dashboard/SearchBarTable.vue';
import TablePagination from '@/Components/dashboard/TablePagination.vue';
import EditButtonTable from '@/Components/dashboard/EditButtonTable.vue';
import DeleteButtonTable from '@/Components/dashboard/DeleteButtonTable.vue';
import DeleteModal from '@/Components/dashboard/DeleteModal.vue'; 

// Data Dummy (Deadline diubah ke YYYY-MM-DD agar mudah difilter dengan kalender)
const aktivitasData = ref([
  { id: 1, judul: 'Pelatihan Kepemimpinan Dasar', kategori: ['Profesional', 'Bisnis', 'Akademisi', 'Birokrat'], deadline: '2029-07-10', gambar: ['123.jpg', '234.jpg', '456.jpg'], deskripsi: 'Deskripsi kegiatan...' },
  { id: 2, judul: 'Seminar Nasional Akademisi', kategori: ['Akademisi', 'Birokrat'], deadline: '2029-08-15', gambar: ['123.jpg', '234.jpg'], deskripsi: 'Deskripsi kegiatan...' },
  { id: 3, judul: 'Workshop Bisnis Digital', kategori: ['Bisnis'], deadline: '2029-07-10', gambar: ['123.jpg'], deskripsi: 'Deskripsi kegiatan...' },
]);

const searchQuery = ref('');
const showFilter = ref(false);

const sortColumn = ref('id');
const sortDirection = ref('asc');

// State untuk Filter Kategori & Waktu
const filters = ref({
  kategori: [],
  waktu: ''
});

// State untuk Modals
const showFormModal = ref(false);
const showImageModal = ref(false);
const showDeleteModal = ref(false);

const selectedItem = ref(null);
const selectedImage = ref('');
const isEditing = ref(false);

// State Form Tambah/Edit
const form = ref({
  id: null,
  judul: '',
  deskripsi: '',
  deadline: '',
  kategori: [],
  gambar: []
});

const listKategori = ['Profesional', 'Bisnis', 'Akademisi', 'Birokrat'];

const toast = ref({ show: false, message: '', type: 'success', isError: false });

const showToast = (message, type = 'success') => {
  toast.value.show = false; 
  setTimeout(() => {
    toast.value = { show: true, message, type };
    setTimeout(() => { toast.value.show = false; }, 3500);
  }, 50);
};

// Format Tanggal untuk tampilan di tabel
const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const options = { day: 'numeric', month: 'long', year: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
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

// Filter Data (Pencarian, Kategori, dan Waktu)
const processedData = computed(() => {
  let data = [...aktivitasData.value];
  
  // 1. Filter Search (Judul)
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    data = data.filter(item => item.judul.toLowerCase().includes(query));
  }

  // 2. Filter Kategori
  if (filters.value.kategori.length > 0) {
    data = data.filter(item => 
      item.kategori.some(kat => filters.value.kategori.includes(kat))
    );
  }

  // 3. Filter Waktu / Deadline
  if (filters.value.waktu) {
    data = data.filter(item => item.deadline === filters.value.waktu);
  }

  // 4. Sorting
  if (sortColumn.value) {
    data.sort((a, b) => {
      let valA = a[sortColumn.value];
      let valB = b[sortColumn.value];

      if (Array.isArray(valA)) valA = valA.join(', ');
      if (Array.isArray(valB)) valB = valB.join(', ');

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
  return processedData.value.slice(start, start + rowsPerPage.value);
});

const totalPages = computed(() => Math.ceil(processedData.value.length / rowsPerPage.value) || 1);

watch([searchQuery, filters, rowsPerPage], () => {
  currentPage.value = 1;
}, { deep: true });

// Reset Filter
const resetFilters = () => {
  filters.value = { kategori: [], waktu: '' };
  showFilter.value = false;
};

// Aksi Modals
const openTambah = () => {
  isEditing.value = false;
  form.value = { id: null, judul: '', deskripsi: '', deadline: '', kategori: [], gambar: [] };
  showFormModal.value = true;
};

const openEdit = (item) => {
  isEditing.value = true;
  form.value = { ...item, kategori: [...item.kategori], gambar: [...item.gambar] };
  showFormModal.value = true;
};

const confirmDelete = (item) => {
  selectedItem.value = item;
  showDeleteModal.value = true;
};

const openImage = (gambar) => {
  if (typeof gambar === 'object' && gambar.url) {
    selectedImage.value = gambar.url;
  } else if (typeof gambar === 'string' && (gambar.startsWith('http') || gambar.startsWith('/'))) {
    selectedImage.value = gambar;
  } else {
    selectedImage.value = `https://picsum.photos/seed/${gambar}/800/600`;
  }
  showImageModal.value = true;
};

// ==========================================
// LOGIC UPLOAD GAMBAR FISIK DARI PERANGKAT
// ==========================================
const fileInput = ref(null);

const triggerFileInput = () => {
  if (fileInput.value) {
    fileInput.value.click();
  }
};

const handleFileUpload = (event) => {
  const files = Array.from(event.target.files);
  if (!files.length) return;

  const sisaSlot = 3 - form.value.gambar.length;
  const filesToAdd = files.slice(0, sisaSlot);

  filesToAdd.forEach(file => {
    if (file.size > 10 * 1024 * 1024) {
      showToast(`File ${file.name} terlalu besar! Maksimal 10MB.`, 'error');
      return;
    }

    const previewUrl = URL.createObjectURL(file);

    form.value.gambar.push({
      file: file,
      url: previewUrl,
      isNew: true
    });
  });

  event.target.value = '';
};

const removeImage = (index) => {
  const removed = form.value.gambar.splice(index, 1)[0];
  if (removed && removed.isNew) {
    URL.revokeObjectURL(removed.url); 
  }
};

const getImageSrc = (gbr) => {
  if (typeof gbr === 'object' && gbr.url) {
    return gbr.url;
  }
  return `https://picsum.photos/seed/${gbr}/200/200`;
};

// Aksi Submit
const handleSave = () => {
  showFormModal.value = false;
  showToast(isEditing.value ? 'Aktivitas berhasil diperbarui!' : 'Aktivitas berhasil ditambahkan!', 'success');
};

const handleDelete = () => {
  showDeleteModal.value = false;
  showToast('Aktivitas berhasil dihapus!', 'success');
};
</script>

<template>
  <Head title="List Aktivitas"/>

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
          <h1 class="mt-1 text-[34px] font-bold leading-[1.02] tracking-[-0.03em] text-[#17334F] sm:text-[42px] lg:text-[48px]">List Aktivitas</h1>
          <p class="mt-1.5 font-inter text-[14px] font-medium leading-tight text-[#4d6786] sm:text-[16px]">Lihat seberapa banyak mahasiswa yang mengikuti aktivitas Talenta!</p>
        </div>

        <!-- ACTION BAR (Searchbar with Action Buttons below on mobile, inline on desktop) -->
        <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:gap-3">
          <!-- Search Input Component -->
          <SearchBarTable
            v-model="searchQuery"
            placeholder="Cari Aktivitas disini..."
          />

          <!-- Action Buttons Row -->
          <div class="flex items-center justify-end gap-2 sm:gap-3 w-full sm:w-auto shrink-0">
            <!-- Filter Dropdown Container -->
            <div class="relative">
              <button
                type="button"
                @click="showFilter = !showFilter"
                class="relative flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-[10px] border-2 bg-transparent text-[#183669] transition-colors focus:outline-none select-none cursor-pointer"
                :class="showFilter || filters.kategori.length > 0 || filters.waktu
                  ? 'border-[#183669]'
                  : 'border-[#d6e0ee] hover:border-[#8ea9cb]'"
                title="Filter Aktivitas"
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
                      Filter Aktivitas
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
                      Kategori
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
              title="Tambah Aktivitas"
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
                  <th class="w-[300px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    <button type="button" @click="handleSort('judul')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer">
                      <span>Judul</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'judul'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <th class="w-[230px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    <button type="button" @click="handleSort('kategori')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer">
                      <span>Kategori</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'kategori'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <th class="w-[160px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    <button type="button" @click="handleSort('deadline')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer">
                      <span>Deadline</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'deadline'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <th class="w-[180px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">Gambar</th>
                  <th class="w-[110px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none rounded-tr-[12px]">Aksi</th>
                </tr>
              </thead>
              
              <tbody class="[&_tr:not(:first-child)_td]:border-t [&_tr:not(:first-child)_td]:border-[#d6e0ee] font-inter text-[14px] text-[#435b76]">
                <tr v-for="(item, index) in paginatedData" :key="item.id" class="h-[52px] transition-colors hover:bg-[#f7f9fd]">
                  <td class="px-3 py-2.5 text-center">{{ (currentPage - 1) * rowsPerPage + index + 1 }}</td>
                  <td class="px-3 py-2.5 text-left font-medium text-[#233547] truncate" :title="item.judul">{{ item.judul }}</td>
                  <td class="px-3 py-2.5 text-left align-middle relative">
                    <div class="flex items-center gap-1.5 flex-nowrap w-full" v-if="item.kategori">
                      <template v-for="(cat, catIdx) in (Array.isArray(item.kategori) ? item.kategori : item.kategori.split(',').map(s => s.trim())).slice(0, 2)" :key="catIdx">
                        <span :class="[
                          'shrink-0 inline-flex items-center justify-center rounded-full px-2.5 py-0.5 font-inter text-[11px] font-semibold border',
                          cat === 'Profesional' || cat === 'Professional' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' :
                          cat === 'Bisnis' ? 'bg-amber-50 text-amber-700 border-amber-200' :
                          cat === 'Birokrat' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                          cat === 'Akademisi' ? 'bg-purple-50 text-purple-700 border-purple-200' :
                          'bg-slate-100 text-slate-700 border-slate-200'
                        ]">
                          {{ cat }}
                        </span>
                      </template>
                      
                      <div v-if="(Array.isArray(item.kategori) ? item.kategori : item.kategori.split(',')).length > 2" class="group relative flex shrink-0">
                        <button type="button" class="inline-flex h-5 items-center justify-center rounded-full bg-slate-100 px-1.5 border border-slate-200 text-[10px] font-bold text-slate-600 transition hover:bg-slate-200 focus:outline-none focus:bg-slate-200 cursor-pointer">
                          +{{ (Array.isArray(item.kategori) ? item.kategori : item.kategori.split(',')).length - 2 }}
                        </button>
                        
                        <div class="hidden group-hover:block group-focus-within:block absolute left-0 top-full z-[60] mt-1.5 shadow-lg rounded-lg border border-gray-200 bg-white p-2 animate-in fade-in slide-in-from-top-1 duration-200">
                          <div class="flex flex-col gap-1.5 min-w-fit whitespace-nowrap">
                            <span v-for="(cat, catIdx) in (Array.isArray(item.kategori) ? item.kategori : item.kategori.split(',').map(s => s.trim())).slice(2)" :key="catIdx" :class="[
                              'inline-flex items-center justify-center rounded-full px-2.5 py-0.5 font-inter text-[11px] font-semibold border',
                              cat === 'Profesional' || cat === 'Professional' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' :
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
                  <td class="px-3 py-2.5 text-center">{{ formatDate(item.deadline) }}</td>
                  <td class="px-3 py-2.5 text-center">
                    <div class="flex items-center justify-center gap-1.5 flex-wrap">
                      <span v-for="(gbr, i) in item.gambar" :key="i" class="inline-flex items-center">
                        <button @click="openImage(gbr)" class="text-[#3b82f6] hover:text-blue-700 hover:underline transition-colors font-medium text-xs cursor-pointer">
                          {{ typeof gbr === 'object' && gbr.file ? gbr.file.name : (typeof gbr === 'string' ? gbr : 'gambar') }}
                        </button>
                        <span v-if="i < item.gambar.length - 1" class="text-gray-400 ml-1">,</span>
                      </span>
                      <span v-if="!item.gambar || item.gambar.length === 0" class="text-gray-400 text-xs">-</span>
                    </div>
                  </td>
                  <td class="px-3 py-2.5 text-center">
                    <div class="flex items-center justify-center gap-2">
                      <EditButtonTable @click="openEdit(item)" />
                      <DeleteButtonTable @click="confirmDelete(item)" />
                    </div>
                  </td>
                </tr>
                <tr v-if="paginatedData.length === 0">
                  <td colspan="6" class="px-4 py-12 text-center text-gray-500 font-medium">Data tidak ditemukan sesuai filter/pencarian Anda.</td>
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

    <!-- MODAL FORM TAMBAH / EDIT AKTIVITAS -->
    <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showFormModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div class="absolute inset-0" @click="showFormModal = false"></div>
        <Transition enter-active-class="transition duration-300 ease-out delay-75" enter-from-class="opacity-0 translate-y-4 scale-95" enter-to-class="opacity-100 translate-y-0 scale-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0 scale-100" leave-to-class="opacity-0 translate-y-4 scale-95">
          <div v-if="showFormModal" class="relative w-full max-w-5xl rounded-2xl bg-white p-8 shadow-2xl overflow-y-auto max-h-[95vh]">
            
            <h2 class="mb-6 text-2xl font-bold text-[#1a2b4c]">{{ isEditing ? 'Edit Aktivitas' : 'Form Tambah Aktivitas' }}</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
              <!-- Kolom Kiri -->
              <div class="flex flex-col gap-4">
                <div>
                  <label class="mb-1 block text-sm font-bold text-[#1a2b4c]">Judul Aktivitas<span class="text-red-500">*</span></label>
                  <p class="text-[11px] text-gray-400 mb-1.5">Masukkan judul aktivitas yang kamu ajukan di sini yah!</p>
                  <input v-model="form.judul" type="text" placeholder="Pelatihan manajer KDMP" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm outline-none focus:border-[#48796f] focus:ring-1 focus:ring-[#48796f]" />
                </div>

                <div>
                  <label class="mb-1 block text-sm font-bold text-[#1a2b4c]">Deskripsi<span class="text-red-500">*</span></label>
                  <p class="text-[11px] text-gray-400 mb-1.5">Jelaskan gambaran kegiatan ini yah!</p>
                  <textarea v-model="form.deskripsi" placeholder="Pelatihan manajer KDMP" rows="6" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm outline-none focus:border-[#48796f] focus:ring-1 focus:ring-[#48796f] resize-none"></textarea>
                </div>

                <div>
                  <label class="mb-1 block text-sm font-bold text-[#1a2b4c]">Deadline<span class="text-red-500">*</span></label>
                  <p class="text-[11px] text-gray-400 mb-1.5">Masukkan tanggal batas registrasi dari aktivitas kamu yah!</p>
                  <input v-model="form.deadline" type="date" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm outline-none focus:border-[#48796f] focus:ring-1 focus:ring-[#48796f] text-gray-700" />
                </div>
              </div>

              <!-- Kolom Kanan -->
              <div class="flex flex-col gap-6">
                
                <!-- Section Gambar Dropzone -->
                <div>
                  <label class="mb-1 block text-sm font-bold text-[#1a2b4c]">Gambar<span class="text-red-500">*</span></label>
                  <p class="text-[11px] text-gray-400 mb-3">Masukkan gambar pendukung berupa jpg/png/jpeg! (MAX 10MB, 3 Gambar)</p>
                  
                  <!-- Area Putus-Putus (Menyatukan Upload & Preview) -->
                  <div class="flex w-full flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-white p-5 min-h-[220px] transition-colors relative">
                    
                    <!-- INPUT FILE TERSEMBUNYI -->
                    <input 
                      type="file" 
                      ref="fileInput" 
                      accept="image/png, image/jpeg, image/jpg" 
                      multiple 
                      class="hidden" 
                      @change="handleFileUpload"
                    />

                    <!-- Area Preview Gambar (Jika ada gambar) -->
                    <div v-if="form.gambar.length > 0" class="mb-6 flex flex-wrap justify-center gap-4 w-full">
                      <div v-for="(gbr, idx) in form.gambar" :key="idx" class="relative h-24 w-24 rounded-xl border border-gray-200 overflow-hidden group shadow-md bg-white">
                        <img :src="getImageSrc(gbr)" class="h-full w-full object-cover" />
                        <!-- Hover Hapus -->
                        <button @click.prevent="removeImage(idx)" class="absolute inset-0 bg-red-500/80 flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-opacity backdrop-blur-sm" title="Hapus gambar">
                          <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                      </div>
                    </div>

                    <!-- Area Tombol Upload/Icon -->
                    <div v-if="form.gambar.length < 3" class="flex flex-col items-center justify-center w-full">
                      <svg v-if="form.gambar.length === 0" class="mb-3 h-12 w-12 text-[#8b9bb4]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.748 3.748 0 0118 19.5H6.75z" />
                      </svg>
                      <p v-if="form.gambar.length === 0" class="text-sm text-gray-500 mb-4 text-center">Upload gambar dari perangkat</p>
                      
                      <!-- Tombol untuk buka galeri/file explorer -->
                      <button @click.prevent="triggerFileInput" class="rounded-lg border border-gray-300 bg-white px-6 py-2 text-sm font-medium text-[#1a2b4c] shadow-sm hover:bg-gray-50 transition-colors flex items-center gap-2">
                        <span v-if="form.gambar.length > 0" class="text-lg leading-none mt-[-2px]">+</span>
                        {{ form.gambar.length > 0 ? 'Tambah Gambar' : 'Pilih File' }}
                      </button>
                    </div>

                    <!-- Notifikasi Batas Maksimal -->
                    <div v-if="form.gambar.length >= 3" class="flex items-center gap-2 text-sm text-green-600 font-medium bg-green-50 px-4 py-2 rounded-lg border border-green-200">
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                      Batas maksimal 3 gambar terpenuhi.
                    </div>

                  </div>
                </div>

                <!-- Kategori Checkbox -->
                <div>
                  <label class="mb-1 block text-sm font-bold text-[#1a2b4c]">Kategori<span class="text-red-500">*</span></label>
                  <p class="text-[11px] text-gray-400 mb-2">Masukkan kemungkinan kategori dari aktivitas kamu yah!</p>
                  <div class="flex flex-wrap gap-x-6 gap-y-3 mt-2">
                    <label v-for="kat in listKategori" :key="kat" class="flex items-center gap-2 cursor-pointer">
                      <input type="checkbox" :value="kat" v-model="form.kategori" class="h-4 w-4 rounded border-gray-300 text-[#1a2b4c] focus:ring-[#1a2b4c]">
                      <span class="text-sm text-[#3b4754] font-medium">{{ kat }}</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tombol Aksi Form -->
            <div class="mt-10 flex justify-end gap-4">
              <button @click="showFormModal = false" class="rounded-lg border border-gray-300 bg-white px-8 py-2.5 text-sm font-bold text-[#1a2b4c] transition hover:bg-gray-50">Kembali</button>
              <button @click="handleSave" class="rounded-lg bg-[#1a2b4c] px-8 py-2.5 text-sm font-bold text-white transition hover:bg-[#111d33] shadow-md">Simpan</button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>

    <DeleteModal 
      :show="showDeleteModal" 
      @close="showDeleteModal = false" 
      @confirm="handleDelete" 
    />

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
                :src="selectedImage"
                alt="Zoomed Preview"
                class="max-h-[82vh] max-w-[88vw] w-auto h-auto min-w-[280px] sm:min-w-[460px] rounded-xl object-contain shadow-2xl"
              />
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

  </AdminLayout>
</template>