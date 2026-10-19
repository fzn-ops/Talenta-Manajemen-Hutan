<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';

import AdminLayout from '@/Layouts/dashboard/AdminLayout.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import SearchBarTable from '@/Components/dashboard/SearchBarTable.vue';
import TablePagination from '@/Components/dashboard/TablePagination.vue';
import DeleteModal from '@/Components/dashboard/DeleteModal.vue'; 
import EditButtonTable from '@/Components/dashboard/EditButtonTable.vue';
import DeleteButtonTable from '@/Components/dashboard/DeleteButtonTable.vue';
import ModalFormKarir from '@/Components/dashboard/admin/ModalFormKarir.vue';

const props = defineProps({
  karirData: Object,
  filters: Object,
});

// ==========================================
// 1. STATE FILTER, SEARCH & SORTING (BACKEND)
// ==========================================
const searchQuery = ref(props.filters?.search || '');
const showFilter = ref(false);

const sortColumn = ref(props.filters?.sort || 'id');
const sortDirection = ref(props.filters?.direction || 'asc');

const filters = ref({
  kategori: props.filters?.kategori
    ? (Array.isArray(props.filters.kategori) ? props.filters.kategori : props.filters.kategori.split(','))
    : [],
  waktu: props.filters?.waktu || ''
});
const listKategori = ['S1 - Semester 5', 'S1 - Semester 7', 'S1 - Lulusan', 'D3 / Vokasi'];

const currentPage = ref(props.karirData?.current_page || 1);
const rowsPerPage = ref(Number(props.filters?.per_page || props.karirData?.per_page || 10));

const totalPages = computed(() => props.karirData?.last_page || 1);
const totalItems = computed(() => props.karirData?.total || 0);
const paginatedData = computed(() => props.karirData?.data || []);

const getRowNumber = (index) => {
  if (sortColumn.value === 'id' && sortDirection.value === 'desc') {
    const total = totalItems.value;
    return total - ((currentPage.value - 1) * rowsPerPage.value + index);
  }
  return ((props.karirData?.current_page || currentPage.value) - 1) * (props.karirData?.per_page || rowsPerPage.value) + index + 1;
};

// Loading state untuk mencegah spamming klik filter/sort/pagination sebelum render tuntas
const isLoading = ref(false);

// ==========================================
// 2. FETCH KE BACKEND LARAVEL
// ==========================================
const fetchKarir = (page = null) => {
  isLoading.value = true;
  const targetPage = page !== null ? page : currentPage.value;
  router.get('/admin/karir', {
    search: searchQuery.value || undefined,
    kategori: filters.value.kategori.length ? filters.value.kategori.join(',') : undefined,
    waktu: filters.value.waktu || undefined,
    sort: sortColumn.value || undefined,
    direction: sortDirection.value || undefined,
    per_page: rowsPerPage.value || undefined,
    page: targetPage > 1 ? targetPage : undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    onFinish: () => {
      isLoading.value = false;
    },
  });
};

// Debounce untuk Search dan Filter
let timeout = null;
watch([searchQuery, filters], () => {
  currentPage.value = 1;
  clearTimeout(timeout);
  timeout = setTimeout(() => {
    fetchKarir(1);
  }, 300);
}, { deep: true });

const resetFilters = () => {
  if (isLoading.value) return;
  filters.value.kategori = [];
  filters.value.waktu = '';
  searchQuery.value = '';
  showFilter.value = false;
  currentPage.value = 1;
  fetchKarir(1);
};

const handleSort = (column) => {
  // Cegah spam click jika request/render sebelumnya belum selesai
  if (isLoading.value) return;

  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortColumn.value = column;
    sortDirection.value = 'asc';
  }
  fetchKarir(currentPage.value);
};

const handlePageChange = (newPage) => {
  if (isLoading.value) return;
  currentPage.value = newPage;
  fetchKarir(newPage);
};

const handleRowsPerPageChange = (newRows) => {
  if (isLoading.value) return;
  rowsPerPage.value = newRows;
  currentPage.value = 1;
  fetchKarir(1);
};

watch(() => props.karirData, (newVal) => {
  if (newVal) {
    currentPage.value = newVal.current_page || 1;
    if (newVal.per_page) {
      rowsPerPage.value = Number(newVal.per_page);
    }
  }
}, { deep: true });

// Format Tanggal untuk tampilan di tabel
const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const options = { day: 'numeric', month: 'long', year: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};

// ==========================================
// 3. STATE TOAST, DELETE, PREVIEW
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
  router.delete(`/admin/karir/${itemToDelete.value.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false;
      itemToDelete.value = null;
      showToast('Data karir berhasil dihapus!', 'success');
    },
    onError: (errors) => {
      console.error("Error delete karir:", errors);
      showToast('Gagal menghapus data karir.', 'error');
    }
  });
};

const showImageModal = ref(false);
const selectedImage = ref('');

const openImage = (gambar) => {
  if (gambar && typeof gambar === 'object' && gambar.name) {
    selectedImage.value = URL.createObjectURL(gambar);
  } else if (gambar && (gambar.startsWith('http') || gambar.startsWith('/'))) {
    selectedImage.value = gambar;
  } else if (gambar) {
    selectedImage.value = `/${gambar}`;
  } else {
    selectedImage.value = 'https://picsum.photos/800/600';
  }
  showImageModal.value = true;
};

// ==========================================
// 4. LOGIC MODAL FORM KARIR (CRUD INTEGRASI)
// ==========================================
const showFormModal = ref(false);
const isEditMode = ref(false);
const selectedKarir = ref(null);

const openTambah = () => {
  selectedKarir.value = null;
  isEditMode.value = false;
  showFormModal.value = true;
};

const handleEdit = (item) => {
  let logoPath = item.foto || item.logo;
  if (logoPath && !logoPath.startsWith('http') && !logoPath.startsWith('/')) {
    logoPath = '/' + logoPath;
  }
  let formattedDeadline = item.deadline;
  if (formattedDeadline && formattedDeadline.includes('T')) {
    formattedDeadline = formattedDeadline.split('T')[0];
  } else if (formattedDeadline && formattedDeadline.includes(' ')) {
    formattedDeadline = formattedDeadline.split(' ')[0];
  }

  selectedKarir.value = {
    id: item.id,
    posisi: item.posisi || '',
    instansi: item.nama_instansi || item.instansi || '',
    deskripsi: item.deskripsi || '',
    kualifikasi: item.kualifikasi || '',
    tautan: item.tautan || '',
    deadline: formattedDeadline || '',
    logo: logoPath || null,
  };
  isEditMode.value = true;
  showFormModal.value = true;
};

const handleModalSubmit = (formData) => {
  const payload = {
    posisi: formData.posisi,
    instansi: formData.instansi,
    nama_instansi: formData.instansi,
    deskripsi: formData.deskripsi,
    kualifikasi: formData.kualifikasi,
    tautan: formData.tautan,
    deadline: formData.deadline,
  };

  if (formData.logo instanceof File || (formData.logo && typeof formData.logo === 'object' && formData.logo.name)) {
    payload.logo = formData.logo;
  }

  if (isEditMode.value) {
    router.post(`/admin/karir/${formData.id}`, {
      _method: 'put',
      ...payload,
    }, {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => {
        showFormModal.value = false;
        showToast('Data karir berhasil diperbarui!', 'success');
      },
      onError: (errors) => {
        console.error("Daftar error validasi dari backend:", errors);
        const firstErr = Object.values(errors)[0];
        showToast(firstErr || 'Gagal memperbarui data karir.', 'error');
      }
    });
  } else {
    router.post('/admin/karir/store', payload, {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => {
        showFormModal.value = false;
        showToast('Data karir berhasil ditambahkan!', 'success');
      },
      onError: (errors) => {
        console.error("Daftar error validasi dari backend:", errors);
        const firstErr = Object.values(errors)[0];
        showToast(firstErr || 'Gagal menambahkan data karir.', 'error');
      }
    });
  }
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
        <div :class="['overflow-x-auto lg:overflow-visible rounded-[12px] bg-white shadow-sm ring-1 ring-[#d6e0ee] transition-all', isLoading ? 'cursor-wait' : '']">
          <div class="overflow-x-auto lg:overflow-visible">
            <table class="w-full min-w-[1040px] table-fixed border-separate border-spacing-0 text-sm">
              <thead class="bg-[#416f65]">
                <tr class="h-[48px]">
                  <th class="w-[50px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0 rounded-tl-[12px]">
                    <button 
                      type="button" 
                      :disabled="isLoading"
                      @click="handleSort('id')" 
                      class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer disabled:cursor-wait disabled:opacity-85"
                      title="Urutkan No"
                    >
                      <span>No</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'id'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <th class="w-[180px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    <button 
                      type="button" 
                      :disabled="isLoading"
                      @click="handleSort('instansi')" 
                      class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer disabled:cursor-wait disabled:opacity-85"
                      title="Urutkan Instansi"
                    >
                      <span>Instansi</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'instansi'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <th class="w-[160px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    <button 
                      type="button" 
                      :disabled="isLoading"
                      @click="handleSort('posisi')" 
                      class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer disabled:cursor-wait disabled:opacity-85"
                      title="Urutkan Posisi"
                    >
                      <span>Posisi</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'posisi'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <!-- <th class="w-[130px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">Logo</th> -->
                  <th class="w-[150px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    <button 
                      type="button" 
                      :disabled="isLoading"
                      @click="handleSort('deadline')" 
                      class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer disabled:cursor-wait disabled:opacity-85"
                      title="Urutkan Deadline"
                    >
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
              
              <tbody :class="['[&_tr:not(:first-child)_td]:border-t [&_tr:not(:first-child)_td]:border-[#d6e0ee] font-inter text-[14px] text-[#435b76] transition-opacity duration-200', isLoading ? 'opacity-40 pointer-events-none select-none cursor-wait' : '']">
                <tr v-for="(item, index) in paginatedData" :key="item.id" class="h-[52px] transition-colors hover:bg-[#f7f9fd]">
                  <td class="px-3 py-2.5 text-center font-medium">{{ getRowNumber(index) }}</td>
                  <td class="px-3 py-2.5 text-left font-medium text-[#233547] truncate" :title="item.nama_instansi || item.instansi">{{ item.nama_instansi || item.instansi }}</td>
                  <td class="px-3 py-2.5 text-left font-medium text-[#233547] truncate" :title="item.posisi">{{ item.posisi }}</td>
                  <!-- <td class="px-3 py-2.5 text-center">
                    <button @click="openImage(item.foto || item.logo)" class="font-inter text-[14px] font-medium text-[#3b82f6] hover:text-blue-700 hover:underline transition-colors cursor-pointer truncate max-w-[110px] inline-block align-middle" :title="typeof (item.foto || item.logo) === 'object' ? (item.foto || item.logo).name : (item.foto || item.logo)">
                      {{ (item.foto || item.logo && typeof (item.foto || item.logo) === 'object' && (item.foto || item.logo).name) ? (item.foto || item.logo).name : (item.foto || item.logo) }}
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
                  <td colspan="7" class="px-4 py-12 text-center text-gray-500 font-medium">Data tidak ditemukan sesuai filter/pencarian Anda.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <TablePagination 
          :current-page="currentPage" 
          :total-pages="totalPages" 
          :total-items="totalItems"
          :rows-per-page="rowsPerPage"
          :disabled="isLoading"
          @update:current-page="handlePageChange"
          @update:rows-per-page="handleRowsPerPageChange"
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

    <!-- Modal Form Tambah & Edit Karir -->
    <ModalFormKarir
      :show="showFormModal"
      :is-edit-mode="isEditMode"
      :initial-data="selectedKarir"
      @close="showFormModal = false"
      @submit="handleModalSubmit"
    />

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