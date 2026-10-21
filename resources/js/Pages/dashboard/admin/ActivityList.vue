<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';

import AdminLayout from '@/Layouts/dashboard/AdminLayout.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import SearchBarTable from '@/Components/dashboard/SearchBarTable.vue';
import TablePagination from '@/Components/dashboard/TablePagination.vue';
import EditButtonTable from '@/Components/dashboard/EditButtonTable.vue';
import DeleteButtonTable from '@/Components/dashboard/DeleteButtonTable.vue';
import DeleteModal from '@/Components/dashboard/DeleteModal.vue'; 
import ModalFormListAktivitas from '@/Components/dashboard/admin/ModalFormListAktivitas.vue';

const props = defineProps({
  aktivitasData: Object, 
  filters: Object,     
});

const deletedImageIds = ref([]);

// ==============================
// 1. STATE FILTER & PENCARIAN
// ==============================
// Nilai default diambil dari props (URL) jika ada
const searchQuery = ref(props.filters?.search || '');
const showFilter = ref(false);

const sortColumn = ref(props.filters?.sort || 'updated_at');
const sortDirection = ref(props.filters?.direction || 'desc');

const filterState = ref({
  // Jika dari backend dikirim string dipisah koma, kita jadikan array lagi
  kategori: props.filters?.kategori ? props.filters.kategori.split(',') : [],
  waktu: props.filters?.waktu || '' // Ini bisa deadline spesifik atau format YYYY-MM
});

const listKategori = ['Profesional', 'Bisnis', 'Akademisi', 'Birokrat'];

// ====== Pagination ===========
const currentPage = ref(props.aktivitasData?.current_page || 1);
const totalPages = computed(() => props.aktivitasData?.last_page || 1);
const rowsPerPage = ref(props.aktivitasData?.per_page || 10);

const getRowNumber = (index) => {
  return (props.aktivitasData?.from || 1) + index;
};

onMounted(() => {
  if (window.innerWidth < 768) {
    rowsPerPage.value = 5;
  }
});

// Loading state untuk efek blur dan mencegah spamming saat filtering/sorting
const isLoading = ref(false);

// ==============================
// 2. LOGIKA FETCH KE BACKEND (PENTING)
// ==============================
const fetchAktivitas = (page = null) => {
  isLoading.value = true;
  const targetPage = page !== null ? page : currentPage.value;
  router.get('/admin/aktivitas/list', {
    search: searchQuery.value || undefined,
    kategori: filterState.value.kategori.length ? filterState.value.kategori.join(',') : undefined,
    waktu: filterState.value.waktu || undefined,
    sort: sortColumn.value || undefined,
    direction: sortDirection.value || undefined,
    per_page: rowsPerPage.value || undefined,
    page: targetPage > 1 ? targetPage : undefined
  }, {
    preserveState: true,  // Pertahankan state (form ga kerest)
    preserveScroll: true, // Layar ga loncat ke atas
    replace: true,        // URL terganti tanpa numpuk history browser
    onFinish: () => {
      isLoading.value = false;
    }
  });
};

// Debounce untuk Search dan Filter
let timeout = null;
watch([searchQuery, filterState], () => {
  currentPage.value = 1;
  clearTimeout(timeout);
  timeout = setTimeout(() => {
    fetchAktivitas(1);
  }, 300);
}, { deep: true });

watch(() => props.aktivitasData?.current_page, (newPage) => {
  if (newPage && newPage !== currentPage.value) {
    currentPage.value = newPage;
  }
});

// Reset Filter
const resetFilters = () => {
  if (isLoading.value) return;
  filterState.value = { kategori: [], waktu: '' };
  searchQuery.value = '';
  showFilter.value = false;
  currentPage.value = 1;
  fetchAktivitas(1);
};

// Table Columns definition
const columns = [
  { key: 'judul', label: 'Judul', sortable: true, width: 'w-[300px]' },
  { key: 'kategori', label: 'Kategori', sortable: false, width: 'w-[230px]' },
  { key: 'deadline', label: 'Deadline', sortable: true, width: 'w-[160px]' },
  { key: 'gambar', label: 'Gambar', sortable: false, width: 'w-[180px]' },
  { key: 'action', label: 'Aksi', sortable: false, width: 'w-[110px]' },
];

const handleSort = (column) => {
  if (isLoading.value) return;

  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortColumn.value = column;
    sortDirection.value = 'asc';
  }
  fetchAktivitas(currentPage.value);
};

const handlePageChange = (newPage) => {
  if (isLoading.value) return;
  currentPage.value = newPage;
  fetchAktivitas(newPage);
};

const handleRowsPerPageChange = (newRows) => {
  if (isLoading.value) return;
  rowsPerPage.value = newRows;
  currentPage.value = 1;
  fetchAktivitas(1);
};

// ==============================
// 4. UI STATE & TOAST
// ==============================
const showFormModal = ref(false);
const showImageModal = ref(false);
const showDeleteModal = ref(false);

const selectedItem = ref(null);
const selectedActivityData = ref(null);
const selectedImage = ref('');
const isEditing = ref(false);

const toast = ref({ show: false, message: '', type: 'success', isError: false });

const showToast = (message, type = 'success') => {
  toast.value.show = false; 
  setTimeout(() => {
    toast.value = { show: true, message, type };
    setTimeout(() => { toast.value.show = false; }, 3500);
  }, 50);
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const options = { day: 'numeric', month: 'long', year: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};

// ==============================
// 5. AKSI MODALS & CRUD (Konek ke Laravel)
// ==============================
const openTambah = () => {
  isEditing.value = false;
  selectedActivityData.value = { id: null, judul: '', deskripsi: '', deadline: '', kategori: [], gambar: [] };
  showFormModal.value = true;
};


const getImageSrc = (gambar) => {
  if (!gambar) return '';
  if (typeof gambar === 'object' && gambar.url) return gambar.url;
  if (typeof gambar === 'object' && gambar.gambar) gambar = gambar.gambar;
  if (typeof gambar === 'string') {
    if (gambar.startsWith('http://') || gambar.startsWith('https://') || gambar.startsWith('blob:') || gambar.startsWith('data:')) {
      return gambar;
    }
    if (gambar.startsWith('/storage/')) return gambar;
    if (gambar.startsWith('storage/')) return '/' + gambar;
    if (gambar.startsWith('/')) return gambar;
    return '/storage/' + gambar;
  }
  return '';
};

const activeImageList = ref([]);
const activeImageIndex = ref(0);

const openImage = (target, index = 0) => {
  if (!target) return;
  if (typeof target === 'object' && target.gambar_aktivitas) {
    const list = (target.gambar_aktivitas || []).map(g => g.gambar).filter(Boolean);
    if (list.length > 0) {
      activeImageList.value = list;
      activeImageIndex.value = index >= 0 && index < list.length ? index : 0;
      selectedImage.value = getImageSrc(list[activeImageIndex.value]);
      showImageModal.value = true;
      return;
    }
  }
  const src = getImageSrc(target);
  selectedImage.value = src;
  activeImageList.value = [src];
  activeImageIndex.value = 0;
  showImageModal.value = true;
};

const prevImage = () => {
  if (activeImageList.value.length <= 1) return;
  activeImageIndex.value = (activeImageIndex.value - 1 + activeImageList.value.length) % activeImageList.value.length;
  selectedImage.value = getImageSrc(activeImageList.value[activeImageIndex.value]);
};

const nextImage = () => {
  if (activeImageList.value.length <= 1) return;
  activeImageIndex.value = (activeImageIndex.value + 1) % activeImageList.value.length;
  selectedImage.value = getImageSrc(activeImageList.value[activeImageIndex.value]);
};

// Touch swipe support on mobile
const touchStartX = ref(0);
const touchEndX = ref(0);

const handleTouchStart = (e) => {
  if (!e.changedTouches || e.changedTouches.length === 0) return;
  touchStartX.value = e.changedTouches[0].clientX;
};

const handleTouchEnd = (e) => {
  if (!e.changedTouches || e.changedTouches.length === 0) return;
  touchEndX.value = e.changedTouches[0].clientX;
  const diff = touchEndX.value - touchStartX.value;
  if (Math.abs(diff) > 40 && activeImageList.value.length > 1) {
    if (diff > 0) {
      prevImage();
    } else {
      nextImage();
    }
  }
};

// Hover state for navigation arrows & indicator on desktop
const isHoveringNav = ref(false);

// Keyboard navigation (Esc to close, Arrow keys to slide)
const handleModalKeyDown = (e) => {
  if (!showImageModal.value) return;
  if (e.key === 'Escape') {
    showImageModal.value = false;
  } else if (e.key === 'ArrowLeft') {
    prevImage();
  } else if (e.key === 'ArrowRight') {
    nextImage();
  }
};

watch(showImageModal, (isOpen) => {
  if (isOpen) {
    window.addEventListener('keydown', handleModalKeyDown);
  } else {
    window.removeEventListener('keydown', handleModalKeyDown);
    isHoveringNav.value = false;
  }
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleModalKeyDown);
});

const openEdit = (item) => {
  isEditing.value = true;
  let existingImages = [];
  
  if (item.gambar_aktivitas && item.gambar_aktivitas.length > 0) {
    existingImages = item.gambar_aktivitas.map(g => {
      return {
        id: g.id, 
        url: getImageSrc(g.gambar), 
        isNew: false
      };
    });
  }

  // 2. MASUKKAN DATA KE FORM
  selectedActivityData.value = {
    ...item,
    gambar: existingImages 
  };
  
  showFormModal.value = true;
};

const confirmDelete = (item) => {
  selectedItem.value = item;
  showDeleteModal.value = true;
};

const executeDelete = () => {
    if (!selectedItem.value) return;

    router.delete(`/admin/aktivitas/${selectedItem.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            selectedItem.value = null;
            showToast('Aktivitas berhasil dihapus!', 'success');
        },
        onError: (errors) => {
            console.error("Gagal menghapus:", errors);
            alert("Gagal menghapus aktivitas. Cek console.");
        }
    });
};

// Tambahin parameter formData di dalam kurungnya
const handleFormSubmit = (formData) => { 
  // 1. CLONE data form yang dikirim dari modal
  const payload = { ...formData }; 

  // 2. EKSTRAK HANYA FILE FISIKNYA AJA DARI ARRAY GAMBAR
  let newImages = [];
  if (payload.gambar && Array.isArray(payload.gambar)) {
      newImages = payload.gambar
          .filter(item => item.isNew) // Cuma ambil gambar baru
          .map(item => item.file);    // Ambil fisik file-nya
  }
      
  // 3. Timpa array gambar dengan file fisik baru
  payload.gambar = newImages;

  // 4. AMBIL deleted_images DARI FORM DATA KIRIMAN MODAL (BAGIAN INI YANG WAJIB ADA!)
  if (isEditing.value) {
      payload.deleted_images = formData.deleted_images || []; 
  }

  // 5. KIRIM DATA KE LARAVEL
  if (isEditing.value) {
    // UPDATE DATA
    router.post(`/admin/aktivitas/${payload.id}`, {
      _method: 'put', 
      ...payload
    }, {
      forceFormData: true, 
      preserveScroll: true,
      onSuccess: () => {
        showFormModal.value = false;
        showToast('Aktivitas berhasil diperbarui!', 'success');
      },
      onError: (errors) => {
        console.error("Daftar Error Validasi dari Laravel:", errors);
        alert("Gagal simpan! Cek console.");
      }
    });
  } else {
    // CREATE DATA (Mode tambah tidak butuh deleted_images)
    router.post('/admin/aktivitas/store', payload, {
      forceFormData: true, 
      preserveScroll: true,
      onSuccess: () => {
        showFormModal.value = false;
        showToast('Aktivitas berhasil ditambahkan!', 'success');
      },
      onError: (errors) => {
        console.error("Daftar Error Validasi dari Laravel:", errors);
        alert("Gagal simpan! Cek console.");
      }
    });
  }
};



// Fungsi buat motong nama file yang kepanjangan
const formatFilename = (path) => {
    if (!path) return '';
    
    // Ambil nama filenya aja dari path (bersihkan query string)
    const filename = path.split('?')[0].split('/').pop() || 'gambar.jpg';
    
    // Batas maksimal huruf yang mau ditampilin (misal 16)
    const maxLength = 16;
    
    if (filename.length <= maxLength) return filename;

    // Cari letak titik terakhir buat misahin nama dan ekstensi (contoh: .png, .jpeg)
    const lastDotIndex = filename.lastIndexOf('.');
    
    // Kalau kebetulan file gak ada ekstensinya
    if (lastDotIndex === -1) {
        return filename.substring(0, maxLength - 3) + '...';
    }

    const name = filename.substring(0, lastDotIndex);
    const ext = filename.substring(lastDotIndex); 

    // Hitung sisa ruang buat nama file (Batas Maksimal - panjang ekstensi - 3 titik)
    const keepLength = Math.max(1, maxLength - ext.length - 3);
    
    return name.substring(0, keepLength) + '...' + ext;
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

        <!-- ACTION BAR -->
        <div class="flex flex-row items-center gap-2 sm:gap-3 w-full">
          <!-- Search Input Component -->
          <SearchBarTable
            class="w-full flex-1 min-w-0"
            v-model="searchQuery"
            placeholder="Cari Aktivitas disini..."
          />

          <!-- Action Buttons Row -->
          <div class="flex items-center justify-end gap-2 sm:gap-3 shrink-0">
            <!-- Filter Dropdown Container -->
            <div class="relative">
              <button
                type="button"
                @click="showFilter = !showFilter"
                class="relative flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-[10px] border-2 bg-transparent text-[#183669] transition-colors focus:outline-none select-none cursor-pointer"
                :class="showFilter || filterState.kategori?.length > 0 || filterState.waktu
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
                  v-if="filterState.kategori?.length > 0 || filterState.waktu"
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
                    <!-- UBAH DISINI: filters jadi filterState -->
                    <button
                      v-if="filterState.kategori?.length > 0 || filterState.waktu"
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
                        <!-- UBAH DISINI: v-model="filters" jadi v-model="filterState" -->
                        <input type="checkbox" :value="kat" v-model="filterState.kategori" class="h-4 w-4 rounded border-[#cbd5e1] text-[#183669] focus:ring-0 cursor-pointer">
                        <span class="text-xs font-medium text-[#334155]">{{ kat }}</span>
                      </label>
                    </div>
                  </div>

                  <div class="border-t border-[#f0f4f9] pt-3 mb-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#7188a3] mb-2">
                      Waktu (Deadline)
                    </p>
                    <!-- UBAH DISINI: v-model="filters" jadi v-model="filterState" -->
                    <input type="date" v-model="filterState.waktu" class="w-full rounded-lg border border-[#cbd5e1] px-3 py-2 text-xs outline-none focus:border-[#183669] text-gray-700 font-inter" />
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
        <div :class="['overflow-x-auto lg:overflow-visible rounded-[12px] bg-white shadow-sm ring-1 ring-[#d6e0ee] transition-all', isLoading ? 'cursor-wait' : '']">
          <div class="overflow-x-auto lg:overflow-visible">
            <table class="w-full min-w-[1040px] table-fixed border-separate border-spacing-0 text-sm">
              <thead class="bg-[#416f65]">
                <tr class="h-[48px]">
                  <th class="w-[50px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0 rounded-tl-[12px]">
                    <button type="button" :disabled="isLoading" @click="handleSort('id')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer disabled:cursor-wait disabled:opacity-85" title="Urutkan No">
                      <span>No</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'id'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <th
                    v-for="col in columns"
                    :key="col.key"
                    :class="[
                      col.width,
                      'px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 last:border-r-0 lg:border-r-0 last:rounded-tr-[12px]'
                    ]"
                  >
                    <button
                      v-if="col.sortable"
                      type="button"
                      :disabled="isLoading"
                      @click="handleSort(col.key)"
                      class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer disabled:cursor-wait disabled:opacity-85"
                      :title="`Urutkan ${col.label}`"
                    >
                      <span>{{ col.label }}</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg
                          v-if="sortColumn === col.key"
                          :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          <path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" />
                        </svg>
                        <svg
                          v-else
                          class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          <path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" />
                        </svg>
                      </span>
                    </button>
                    <span v-else class="whitespace-nowrap">{{ col.label }}</span>
                  </th>
                </tr>
              </thead>
              
              <tbody :class="['[&_tr:not(:first-child)_td]:border-t [&_tr:not(:first-child)_td]:border-[#d6e0ee] font-inter text-[14px] text-[#435b76] transition-all duration-200', isLoading ? 'opacity-40 blur-[1px] pointer-events-none select-none cursor-wait' : '']">
                <tr v-for="(item,index) in aktivitasData?.data" :key="item.id" class="h-[52px] transition-colors hover:bg-[#f7f9fd]">
                  <td class="px-3 py-2.5 text-center font-medium">{{ getRowNumber(index) }}</td>
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
                  <td class="px-3 py-2.5 text-center font-medium">{{ formatDate(item.deadline) }}</td>
                  <td class="px-3 py-2.5 text-center">
                    <span v-if="!item.gambar_aktivitas || item.gambar_aktivitas.length === 0" class="font-inter text-[13px] text-gray-400">-</span>
                    <button
                      v-else
                      type="button"
                      @click="openImage(item)"
                      class="font-inter text-[13px] font-medium text-[#3b82f6] hover:text-blue-700 hover:underline transition-colors cursor-pointer inline-flex items-center justify-center gap-1 align-middle whitespace-nowrap"
                      :title="item.gambar_aktivitas.length > 1 ? `Lihat ${item.gambar_aktivitas.length} Gambar` : 'Lihat Gambar'"
                    >
                      <span>Gambar</span>
                      <span v-if="item.gambar_aktivitas.length > 1" class="text-xs font-semibold text-[#3b82f6]">({{ item.gambar_aktivitas.length }})</span>
                    </button>
                  </td>
                  <td class="px-3 py-2.5 text-center">
                    <div class="flex items-center justify-center gap-2">
                      <EditButtonTable @click="openEdit(item)" />
                      <DeleteButtonTable @click="confirmDelete(item)" />
                    </div>
                  </td>
                </tr>
                <tr v-if="!aktivitasData?.data || aktivitasData?.data?.length === 0">
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
          :disabled="isLoading"
          @update:current-page="handlePageChange"
          @update:rows-per-page="handleRowsPerPageChange"
        />
      </div>
    </section>

    <!-- Modal Form Tambah / Edit Aktivitas Component -->
    <ModalFormListAktivitas
      :show="showFormModal"
      :is-editing="isEditing"
      :initial-data="selectedActivityData"
      @close="showFormModal = false"
      @submit="handleFormSubmit"
    />

    <!-- Modal Delete Confirmation for Activity -->
    <DeleteModal 
      :show="showDeleteModal" 
      @close="showDeleteModal = false" 
      @confirm="executeDelete" 
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
              class="relative flex flex-col items-center justify-center bg-transparent max-w-full max-h-full touch-pan-y select-none"
              @click.stop
              @touchstart="handleTouchStart"
              @touchend="handleTouchEnd"
              @mouseenter="isHoveringNav = true"
              @mouseleave="isHoveringNav = false"
            >
              <!-- Close button -->
              <button
                type="button"
                @click="showImageModal = false"
                class="absolute top-3.5 right-3.5 z-30 flex h-9 w-9 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/85 backdrop-blur-xs shadow-md transition hover:scale-105 active:scale-95 focus:outline-none cursor-pointer"
                title="Tutup Preview"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>

              <!-- Prev button (slide in from left on image hover) -->
              <button
                v-if="activeImageList.length > 1"
                type="button"
                @click="prevImage"
                :class="[
                  'absolute left-3.5 top-1/2 -translate-y-1/2 z-30 hidden sm:flex h-10 w-10 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/85 backdrop-blur-xs shadow-lg transition-all duration-300 ease-out hover:scale-110 active:scale-95 focus:outline-none cursor-pointer',
                  isHoveringNav ? 'opacity-100 translate-x-0 pointer-events-auto' : 'opacity-0 -translate-x-6 pointer-events-none'
                ]"
                title="Gambar Sebelumnya"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
              </button>

              <!-- Next button (slide in from right on image hover) -->
              <button
                v-if="activeImageList.length > 1"
                type="button"
                @click="nextImage"
                :class="[
                  'absolute right-3.5 top-1/2 -translate-y-1/2 z-30 hidden sm:flex h-10 w-10 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/85 backdrop-blur-xs shadow-lg transition-all duration-300 ease-out hover:scale-110 active:scale-95 focus:outline-none cursor-pointer',
                  isHoveringNav ? 'opacity-100 translate-x-0 pointer-events-auto' : 'opacity-0 translate-x-6 pointer-events-none'
                ]"
                title="Gambar Berikutnya"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
              </button>

              <!-- Image -->
              <img
                :src="selectedImage"
                alt="Zoomed Preview"
                class="max-h-[82vh] max-w-[88vw] w-auto h-auto min-w-[280px] sm:min-w-[460px] rounded-xl object-contain shadow-2xl"
              />

              <!-- Bottom Indicator (slide in from bottom on image hover on PC; always visible on mobile) -->
              <div
                v-if="activeImageList.length > 1"
                :class="[
                  'absolute bottom-4 left-1/2 -translate-x-1/2 z-30 flex items-center gap-2 rounded-full bg-black/70 px-3.5 py-1.5 backdrop-blur-sm text-xs font-medium text-white shadow-lg transition-all duration-300 ease-out',
                  isHoveringNav ? 'opacity-100 translate-y-0 pointer-events-auto' : 'opacity-100 sm:opacity-0 sm:translate-y-6 sm:pointer-events-none'
                ]"
              >
                <span>Gambar {{ activeImageIndex + 1 }} dari {{ activeImageList.length }}</span>
                <div class="flex items-center gap-1.5 ml-1">
                  <button
                    v-for="(_, dotIdx) in activeImageList"
                    :key="dotIdx"
                    type="button"
                    @click="activeImageIndex = dotIdx; selectedImage = getImageSrc(activeImageList[dotIdx])"
                    class="h-2 rounded-full transition-all cursor-pointer"
                    :class="activeImageIndex === dotIdx ? 'w-4 bg-white' : 'w-2 bg-white/40 hover:bg-white/70'"
                    :title="'Buka Gambar ' + (dotIdx + 1)"
                  />
                </div>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

  </AdminLayout>
</template>