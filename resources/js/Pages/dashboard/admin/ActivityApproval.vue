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

import ModalFormPersetujuanAktivitas from '@/Components/dashboard/admin/ModalFormPersetujuanAktivitas.vue';

const props = defineProps({
  pengajuanData: Object,
  filters: Object,
});

// ==============================
// 1. STATE FILTER & PENCARIAN
// ==============================
const searchQuery = ref(props.filters?.search || '');
const filterStatus = ref(props.filters?.status || '');
const showFilter = ref(false);

const sortColumn = ref(props.filters?.sort || 'id');
const sortDirection = ref(props.filters?.direction || 'desc');

// Pagination
const currentPage = ref(props.pengajuanData?.current_page || 1);
const totalPages = computed(() => props.pengajuanData?.last_page || 1);
const rowsPerPage = ref(props.pengajuanData?.per_page || 10);

onMounted(() => {
  if (window.innerWidth < 768) {
    rowsPerPage.value = 5;
  }
});

// Loading state untuk efek blur tabel saat filtering/sorting
const isLoading = ref(false);

// ==============================
// 2. FETCH KE BACKEND
// ==============================
const fetchPengajuan = (page = null) => {
  isLoading.value = true;
  const targetPage = page !== null ? page : currentPage.value;

  router.get('/admin/aktivitas/persetujuan', {
    search: searchQuery.value || undefined,
    status: filterStatus.value || undefined,
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
    }
  });
};

// Debounce untuk Search dan Filter
let timeout = null;
watch([searchQuery, filterStatus], () => {
  currentPage.value = 1;
  clearTimeout(timeout);
  timeout = setTimeout(() => {
    fetchPengajuan(1);
  }, 300);
});

watch(() => props.pengajuanData?.current_page, (newPage) => {
  if (newPage && newPage !== currentPage.value) {
    currentPage.value = newPage;
  }
});

// ==============================
// 3. FUNGSI SORTING & PAGINASI
// ==============================
const handleSort = (column) => {
  if (isLoading.value) return;

  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortColumn.value = column;
    sortDirection.value = 'asc';
  }
  fetchPengajuan(currentPage.value);
};

const handlePageChange = (newPage) => {
  if (isLoading.value) return;
  currentPage.value = newPage;
  fetchPengajuan(newPage);
};

const handleRowsPerPageChange = (newRows) => {
  if (isLoading.value) return;
  rowsPerPage.value = newRows;
  currentPage.value = 1;
  fetchPengajuan(1);
};

// ==============================
// 4. MODALS & TOAST STATE
// ==============================
const showDetailModal = ref(false);
const showRejectModal = ref(false);
const showConfirmApproveModal = ref(false);
const itemToApprove = ref(null);
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

// Pantau flash message dari Laravel redirect
const page = usePage();
watch(() => page.props.flash?.success, (msg) => {
  if (msg) showToast(msg, 'success');
}, { immediate: true });
watch(() => page.props.flash?.error, (msg) => {
  if (msg) showToast(msg, 'error');
}, { immediate: true });

// ==============================
// 5. HELPER FORMATTER
// ==============================
const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  try {
    const options = { day: 'numeric', month: 'long', year: 'numeric' };
    return new Date(dateStr).toLocaleDateString('id-ID', options);
  } catch (e) {
    return dateStr;
  }
};

const getCategories = (kategori) => {
  if (!kategori) return [];
  if (Array.isArray(kategori)) return kategori;
  return kategori.split(',').map((s) => s.trim()).filter(Boolean);
};

const getGambarName = (item) => {
  const images = item.gambar_pengajuan_aktivitas || (item.gambar ? [item.gambar] : []);
  if (!images || images.length === 0) return '-';
  const first = images[0];
  const rawPath = typeof first === 'object' && first.gambar ? first.gambar : (typeof first === 'string' ? first : (first.name || 'gambar.jpg'));
  const cleanPath = rawPath.split('?')[0];
  const fileName = cleanPath.split('/').pop() || 'gambar.jpg';
  return images.length > 1 ? `${fileName} (+${images.length - 1})` : fileName;
};

const getImageSrc = (val) => {
  if (!val) return '';
  if (typeof val === 'object' && val.gambar) val = val.gambar;
  if (typeof val === 'object' && val.url) return val.url;
  if (typeof val === 'string') {
    if (val.startsWith('http://') || val.startsWith('https://')) return val;
    if (val.startsWith('/')) return val;
    return '/' + val;
  }
  return `https://picsum.photos/seed/aktivitas/800/600`;
};

const openImage = (item) => {
  const images = item?.gambar_pengajuan_aktivitas || (item?.gambar ? [item.gambar] : []);
  if (!images || images.length === 0) return;
  const first = images[0];
  selectedImage.value = getImageSrc(first);
  showImageModal.value = true;
};

// ==============================
// 6. ACTION HANDLERS
// ==============================
const openDetail = (item) => {
  selectedItem.value = item;
  showDetailModal.value = true;
};

const promptApprove = (item) => {
  const target = item || selectedItem.value;
  if (!target) return;

  if (target.status === 'disetujui') {
    showToast('Aktivitas ini sudah disetujui sebelumnya.', 'error');
    return;
  }

  itemToApprove.value = target;
  showConfirmApproveModal.value = true;
};

const executeApprove = () => {
  if (!itemToApprove.value) return;
  const targetId = itemToApprove.value.id;

  showConfirmApproveModal.value = false;
  showDetailModal.value = false;
  isLoading.value = true;

  router.post(`/admin/aktivitas/persetujuan/${targetId}/approve`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      itemToApprove.value = null;
    },
    onError: () => {
      showToast('Gagal menyetujui aktivitas.', 'error');
    },
    onFinish: () => {
      isLoading.value = false;
    }
  });
};

const openReject = (item) => {
  if (item) selectedItem.value = item; 
  showRejectModal.value = true;
};

const handleReject = (reason) => {
  if (!selectedItem.value) return;
  const targetId = selectedItem.value.id;

  showRejectModal.value = false;
  showDetailModal.value = false;
  isLoading.value = true;

  router.post(`/admin/aktivitas/persetujuan/${targetId}/reject`, {
    catatan: reason
  }, {
    preserveScroll: true,
    onSuccess: () => {
      // Toast ditangani otomatis oleh flash listener
    },
    onError: () => {
      showToast('Gagal menolak aktivitas.', 'error');
    },
    onFinish: () => {
      isLoading.value = false;
    }
  });
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

        <!-- TOP BAR (SEARCH & FILTER) -->
        <div class="flex flex-row items-center gap-2.5 sm:gap-3 w-full">
          <SearchBarTable class="w-full flex-1 min-w-0" placeholder="Cari Aktivitas disini..." v-model="searchQuery"/>

          <!-- Filter Dropdown Container -->
          <div class="flex items-center justify-end gap-2 sm:gap-3 shrink-0">
            <div class="relative">
              <button 
                type="button"
                @click="showFilter = !showFilter" 
                class="relative flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-[10px] border-2 bg-transparent text-[#183669] transition-colors focus:outline-none select-none cursor-pointer" 
                :class="showFilter || filterStatus ? 'border-[#183669]' : 'border-[#d6e0ee] hover:border-[#8ea9cb]'"
                title="Filter Status"
              >
                <img src="/assets/icons/filter.svg" alt="Filter Icon" class="h-5 w-5 shrink-0 object-contain pointer-events-none" />
                <span v-if="filterStatus" class="absolute top-2.5 right-2.5 h-2 w-2 rounded-full bg-[#ef4444] ring-2 ring-[#eef2f7]"></span>
              </button>

              <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-2">
                <div v-if="showFilter" class="absolute right-0 top-14 z-40 w-48 rounded-xl border border-gray-200 bg-white p-2 shadow-lg">
                  <p class="px-3 py-1.5 text-xs font-bold text-gray-400 uppercase">Filter Status</p>
                  <button @click="filterStatus = ''; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 font-medium cursor-pointer" :class="!filterStatus ? 'bg-gray-100 font-semibold' : ''">Semua Data</button>
                  <button @click="filterStatus = 'menunggu'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-gray-600 hover:bg-gray-100 font-medium cursor-pointer" :class="filterStatus === 'menunggu' ? 'bg-gray-100 font-semibold' : ''">Menunggu</button>
                  <button @click="filterStatus = 'disetujui'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-green-600 hover:bg-green-50 font-medium cursor-pointer" :class="filterStatus === 'disetujui' ? 'bg-green-50 font-semibold' : ''">Disetujui</button>
                  <button @click="filterStatus = 'ditolak'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50 font-medium cursor-pointer" :class="filterStatus === 'ditolak' ? 'bg-red-50 font-semibold' : ''">Ditolak</button>
                </div>
              </Transition>
            </div>
          </div>
        </div>

        <!-- Overlay penutup filter jika klik di luar -->
        <div v-if="showFilter" @click="showFilter = false" class="fixed inset-0 z-30"></div>

        <!-- TABEL DATA DENGAN BLUR LOADING EFFECT -->
        <div :class="['overflow-x-auto lg:overflow-visible rounded-[12px] bg-white shadow-sm ring-1 ring-[#d6e0ee] transition-all', isLoading ? 'cursor-wait' : '']">
          <div class="overflow-x-auto lg:overflow-visible">
            <table class="w-full min-w-[1040px] table-fixed border-separate border-spacing-0 text-sm">
              <thead class="bg-[#416f65]">
                <tr class="h-[48px]">
                  
                  <!-- Kolom No -->
                  <th class="w-[60px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0 rounded-tl-[12px]">
                    <button type="button" :disabled="isLoading" @click="handleSort('id')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer disabled:cursor-wait disabled:opacity-85" title="Urutkan No">
                      <span>No</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'id'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>

                  <!-- Kolom Judul (Bebas Squish / Rusak) -->
                  <th class="w-[250px] px-3 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    <button type="button" :disabled="isLoading" @click="handleSort('judul')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer disabled:cursor-wait disabled:opacity-85" title="Urutkan Judul">
                      <span>Judul</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'judul'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>

                  <!-- Kolom Kategori -->
                  <th class="w-[210px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">Kategori</th>

                  <!-- Kolom Deadline -->
                  <th class="w-[140px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    <button type="button" :disabled="isLoading" @click="handleSort('deadline')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer disabled:cursor-wait disabled:opacity-85" title="Urutkan Deadline">
                      <span>Deadline</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'deadline'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>

                  <!-- Kolom Gambar -->
                  <th class="w-[120px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">Gambar</th>

                  <!-- Kolom Status -->
                  <th class="w-[130px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    <button type="button" :disabled="isLoading" @click="handleSort('status')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer disabled:cursor-wait disabled:opacity-85" title="Urutkan Status">
                      <span>Status</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'status'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>

                  <!-- Kolom Aksi -->
                  <th class="w-[140px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none rounded-tr-[12px]">Aksi</th>
                </tr>
              </thead>
              
              <tbody :class="['[&_tr:not(:first-child)_td]:border-t [&_tr:not(:first-child)_td]:border-[#d6e0ee] font-inter text-[14px] text-[#435b76] transition-all duration-200', isLoading ? 'opacity-40 blur-[1px] pointer-events-none select-none cursor-wait' : '']">
                <tr v-for="(item, index) in pengajuanData?.data" :key="item.id" class="h-[52px] transition-colors hover:bg-[#f7f9fd]">
                  <td class="px-3 py-2.5 text-center font-medium">{{ (pengajuanData?.from || 1) + index }}</td>
                  <td class="px-3 py-2.5 text-left font-medium text-[#233547] truncate" :title="item.judul">{{ item.judul }}</td>
                  <td class="px-3 py-2.5 text-left align-middle relative">
                    <div class="flex items-center gap-1.5 flex-nowrap w-full" v-if="getCategories(item.kategori).length > 0">
                      <!-- Render first 2 chips -->
                      <template v-for="(cat, catIdx) in getCategories(item.kategori).slice(0, 2)" :key="catIdx">
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
                      
                      <!-- +X Chip for remainder -->
                      <div v-if="getCategories(item.kategori).length > 2" class="group relative flex shrink-0">
                        <button type="button" class="inline-flex h-5 items-center justify-center rounded-full bg-slate-100 px-1.5 border border-slate-200 text-[10px] font-bold text-slate-600 transition hover:bg-slate-200 focus:outline-none focus:bg-slate-200 cursor-pointer">
                          +{{ getCategories(item.kategori).length - 2 }}
                        </button>
                        
                        <!-- Hover Tooltip Container -->
                        <div class="hidden group-hover:block group-focus-within:block absolute left-0 top-full z-[60] mt-1.5 shadow-lg rounded-lg border border-gray-200 bg-white p-2 animate-in fade-in slide-in-from-top-1 duration-200">
                          <div class="flex flex-col gap-1.5 min-w-fit whitespace-nowrap">
                            <span v-for="(cat, catIdx) in getCategories(item.kategori).slice(2)" :key="catIdx" :class="[
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
                    <span v-else class="text-xs text-gray-400 italic">-</span>
                  </td>
                  <td class="px-3 py-2.5 text-center">{{ formatDate(item.deadline) }}</td>
                  <td class="px-3 py-2.5 text-center">
                    <button 
                      type="button"
                      @click="openImage(item)" 
                      class="font-inter text-[14px] font-medium text-[#3b82f6] hover:text-blue-700 hover:underline transition-colors cursor-pointer truncate max-w-[110px] inline-block align-middle" 
                      :title="getGambarName(item)"
                    >
                      {{ getGambarName(item) }}
                    </button>
                  </td>
                  <td class="px-3 py-2.5 text-center">
                    <div class="mx-auto inline-flex w-fit items-center justify-center gap-1.5 rounded-full px-3 py-1 font-inter text-[11px] font-semibold border"
                         :class="{
                           'bg-gray-100 text-gray-600 border-gray-200': String(item.status).toLowerCase() === 'menunggu',
                           'bg-green-50 text-green-600 border-green-200': String(item.status).toLowerCase() === 'disetujui',
                           'bg-red-50 text-red-600 border-red-200': String(item.status).toLowerCase() === 'ditolak'
                         }">
                      <svg v-if="String(item.status).toLowerCase() === 'menunggu'" class="h-3.5 w-3.5 shrink-0 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="9" />
                        <polyline points="12 7 12 12 15 15" />
                      </svg>
                      <svg v-if="String(item.status).toLowerCase() === 'disetujui'" class="h-3.5 w-3.5 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <svg v-if="String(item.status).toLowerCase() === 'ditolak'" class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <span class="capitalize">{{ item.status }}</span>
                    </div>
                  </td>
                  <td class="px-3 py-2.5 text-center">
                    <div class="flex items-center justify-center gap-2">
                      <!-- Accept Button: Disabled jika status sudah disetujui -->
                      <AcceptButton 
                        :disabled="String(item.status).toLowerCase() === 'disetujui'" 
                        :title="String(item.status).toLowerCase() === 'disetujui' ? 'Aktivitas sudah disetujui' : 'Setujui Aktivitas'"
                        @click="promptApprove(item)" 
                      />
                      <!-- Reject Button: Disabled jika status sudah ditolak. Jika disetujui, bisa tolak untuk membatalkan persetujuan -->
                      <RejectButton 
                        :disabled="String(item.status).toLowerCase() === 'ditolak'" 
                        :title="String(item.status).toLowerCase() === 'ditolak' ? 'Aktivitas sudah ditolak' : (String(item.status).toLowerCase() === 'disetujui' ? 'Batalkan Persetujuan & Tolak' : 'Tolak Aktivitas')"
                        @click="openReject(item)" 
                      />
                      <PreviewButtonTable 
                        title="Lihat Detail Submisi"
                        @click="openDetail(item)" 
                      />
                    </div>
                  </td>
                </tr>
                <tr v-if="!pengajuanData?.data || pengajuanData?.data.length === 0">
                  <td colspan="7" class="px-4 py-12 text-center text-gray-500 font-medium">Data pengajuan aktivitas tidak ditemukan sesuai filter/pencarian Anda.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <TablePagination 
          :current-page="currentPage" 
          :total-pages="totalPages" 
          :rows-per-page="rowsPerPage"
          @update:current-page="handlePageChange"
          @update:rows-per-page="handleRowsPerPageChange"
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
                :src="selectedImage"
                alt="Zoomed Preview"
                class="max-h-[82vh] max-w-[88vw] w-auto h-auto min-w-[280px] sm:min-w-[460px] rounded-xl object-contain shadow-2xl"
              />
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

    <!-- MODAL DETAIL (PERSETUJUAN AKTIVITAS) -->
    <ModalFormPersetujuanAktivitas
      :show="showDetailModal"
      :data="selectedItem"
      @close="showDetailModal = false"
      @approve="promptApprove"
      @reject="openReject"
    />

    <!-- MODAL KONFIRMASI SETUJUI (Pencegah Kesalahan Fatal) -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div 
          v-if="showConfirmApproveModal" 
          class="fixed inset-0 z-[120] flex items-center justify-center overflow-y-auto overscroll-contain bg-slate-900/60 p-4 backdrop-blur-xs" 
          @click.self="showConfirmApproveModal = false"
        >
          <div class="relative w-full max-w-md rounded-[18px] sm:rounded-[20px] bg-white p-6 sm:p-8 shadow-2xl text-center font-poppins border border-[#e2e8f0]">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
              <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>

            <h2 class="text-[17px] sm:text-[19px] font-bold text-[#183669] mb-3 px-2 leading-snug">
              Setujui Pengajuan Aktivitas?
            </h2>

            <p class="text-xs sm:text-sm text-[#4d6786] font-inter mb-4 leading-relaxed">
              Apakah Anda yakin ingin menyetujui <span class="font-bold text-[#183669]">"{{ itemToApprove?.judul }}"</span>?
            </p>

            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50/70 p-3 text-left font-inter text-[12px] text-emerald-800 leading-relaxed">
              <strong>Info:</strong> Aktivitas ini akan langsung otomatis dibuat dan <strong>diterbitkan ke List Aktivitas</strong>.
            </div>

            <div class="flex items-center justify-center gap-3 sm:gap-4">
              <button 
                type="button"
                @click="showConfirmApproveModal = false" 
                class="min-w-[110px] sm:min-w-[130px] rounded-[10px] border border-[#d6e0ee] bg-white py-2.5 text-xs sm:text-sm font-bold text-[#183669] transition hover:border-[#183669] hover:bg-slate-50 cursor-pointer select-none outline-none focus:outline-none"
              >
                Batal
              </button>
              <button 
                type="button"
                @click="executeApprove" 
                class="min-w-[110px] sm:min-w-[130px] rounded-[10px] bg-[#10b981] py-2.5 text-xs sm:text-sm font-bold text-white transition hover:bg-[#059669] shadow-sm cursor-pointer select-none outline-none focus:outline-none"
              >
                Ya, Setujui
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- MODAL REJECT (Dengan Warning Jika Sudah Disetujui) -->
    <RejectModal 
      :show="showRejectModal" 
      :is-approved="String(selectedItem?.status).toLowerCase() === 'disetujui'"
      @close="showRejectModal = false" 
      @submit="handleReject"
    />

  </AdminLayout>
</template>