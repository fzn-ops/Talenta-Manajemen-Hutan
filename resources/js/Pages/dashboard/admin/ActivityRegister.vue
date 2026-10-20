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
import DeleteButtonTable from '@/Components/dashboard/DeleteButtonTable.vue';
import DeleteModal from '@/Components/dashboard/DeleteModal.vue';

import ModalFormPendaftaranMahasiswa from '@/Components/dashboard/admin/ModalFormPendaftaranMahasiswa.vue';

const props = defineProps({
  pendaftaranData: Object,
  filters: Object
});

// 1. Inisialisasi Filter & Sort
const searchQuery = ref(props.filters?.search || '');
const filterStatus = ref(props.filters?.status || '');
const sortColumn = ref(props.filters?.sort || 'id');
const sortDirection = ref(props.filters?.direction || 'asc');
const isLoading = ref(false);
const showFilter = ref(false);

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

// 2. Data Kegiatan Langsung Diambil dari Backend
const kegiatanList = computed(() => {
  return props.pendaftaranData?.data || [];
});

const currentPage = ref(props.pendaftaranData?.current_page || 1);
const rowsPerPage = ref(props.pendaftaranData?.per_page || 10);

const getRowNumber = (index) => {
  if (sortColumn.value === 'id' && sortDirection.value === 'desc') {
    const total = props.pendaftaranData?.total || 0;
    return total - ((currentPage.value - 1) * rowsPerPage.value + index);
  }
  return (props.pendaftaranData?.from || 1) + index;
};

// 3. Fetch ke Backend
const fetchPendaftaran = (page = null) => {
  isLoading.value = true;
  const targetPage = page !== null ? page : currentPage.value;
  router.get(
    '/admin/aktivitas/bukti-pendaftaran',
    {
      search: searchQuery.value || undefined,
      status: filterStatus.value || undefined,
      sort: sortColumn.value || undefined,
      direction: sortDirection.value || undefined,
      per_page: rowsPerPage.value || undefined,
      page: targetPage > 1 ? targetPage : undefined,
    },
    { 
      preserveState: true, 
      replace: true, 
      preserveScroll: true,
      onFinish: () => {
        isLoading.value = false;
      }
    }
  );
};

const handleSort = (column) => {
  if (isLoading.value) return;
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortColumn.value = column;
    sortDirection.value = 'asc';
  }
  fetchPendaftaran(currentPage.value);
};

const handlePageChange = (newPage) => {
  if (isLoading.value) return;
  currentPage.value = newPage;
  fetchPendaftaran(newPage);
};

const handleRowsChange = (newRows) => {
  if (isLoading.value) return;
  rowsPerPage.value = newRows;
  currentPage.value = 1;
  fetchPendaftaran(1);
};

// 4. Sistem Filter Otomatis (Debounce)
let searchTimeout = null;
watch([searchQuery, filterStatus], () => {
  currentPage.value = 1;
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchPendaftaran(1);
  }, 300);
});

// ==========================================
// FUNGSI MODAL & PREVIEW DOKUMEN/GAMBAR
// ==========================================
const openDetail = (item) => {
  selectedItem.value = item;
  showDetailModal.value = true;
};

const openReject = (item) => {
  if (item) selectedItem.value = item; 
  showRejectModal.value = true;
};

const getImageSrc = (gambar) => {
  if (gambar && typeof gambar === 'object' && gambar.url) {
    return gambar.url;
  }
  if (gambar && typeof gambar === 'string') {
    if (gambar.startsWith('http://') || gambar.startsWith('https://') || gambar.startsWith('blob:') || gambar.startsWith('data:')) return gambar;
    if (gambar.startsWith('/storage/')) return gambar;
    if (gambar.startsWith('storage/')) return '/' + gambar;
    if (gambar.startsWith('/')) return gambar;
    return '/storage/' + gambar;
  }
  return `https://picsum.photos/seed/pendaftaran/800/600`;
};

const openImage = (gambar) => {
  selectedImage.value = getImageSrc(gambar);
  showImageModal.value = true;
};

const onImageError = (e) => {
  const seed = String(selectedImage.value || 'talenta').replace(/[^a-zA-Z0-9]/g, '');
  e.target.src = `https://picsum.photos/seed/${seed || 'talenta'}/800/600`;
};

// ==========================================
// FUNGSI AKSI (APPROVE & REJECT) VIA INERTIA
// ==========================================
const handleApprove = (item) => {
  const target = item || selectedItem.value;
  if (target) {
    // Kirim request PATCH ke controller lu
    router.patch(`/admin/aktivitas/bukti-pendaftaran/${target.id}/approve`, {}, {
      preserveScroll: true,
      onSuccess: () => {
        showDetailModal.value = false;
        showToast('Pendaftaran berhasil disetujui!', 'success');
      },
      onError: () => {
        showToast('Gagal menyetujui pendaftaran.', 'error');
      }
    });
  }
};

const handleReject = (reason) => {
  if (selectedItem.value) {
    // Kirim request PATCH beserta alasan penolakan
    router.patch(`/admin/aktivitas/bukti-pendaftaran/${selectedItem.value.id}/reject`, {
      catatan_penolakan: reason // Nama field wajib sama kayak validasi di Controller
    }, {
      preserveScroll: true,
      onSuccess: () => {
        showRejectModal.value = false;
        showDetailModal.value = false; 
        showToast('Pendaftaran berhasil ditolak.', 'success'); // Success karena aksinya berhasil diproses
      },
      onError: (errors) => {
        // Misal gagal karena alasan kosong (validasi Laravel jalan)
        showToast(errors.catatan_penolakan || 'Gagal menolak pendaftaran.', 'error');
      }
    });
  }
};

const showDeleteModal = ref(false);
const itemToDelete = ref(null);

const confirmDelete = (item) => {
  itemToDelete.value = item;
  showDeleteModal.value = true;
};

const executeDelete = () => {
  if (!itemToDelete.value) return;
  const targetId = itemToDelete.value.id;
  isLoading.value = true;

  router.delete(`/admin/aktivitas/bukti-pendaftaran/${targetId}`, {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false;
      itemToDelete.value = null;
      showToast('Data pendaftaran aktivitas berhasil dihapus!', 'success');
    },
    onError: () => {
      showToast('Gagal menghapus pendaftaran aktivitas.', 'error');
    },
    onFinish: () => {
      isLoading.value = false;
    }
  });
};
</script>

<template>
  <Head title="Pendaftaran Aktivitas Mahasiswa"/>

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
          <h1 class="mt-1 text-[34px] font-bold leading-[1.02] tracking-[-0.03em] text-[#17334F] sm:text-[42px] lg:text-[48px]">Pendaftaran Aktivitas Mahasiswa</h1>
          <p class="mt-1.5 font-inter text-[14px] font-medium leading-tight text-[#4d6786] sm:text-[16px]">Yuk lihat bukti registrasi dan tentukan apakah bukti tersebut valid atau tidak!</p>
        </div>

        <!-- TOP BAR -->
        <div class="flex flex-row items-center gap-2.5 sm:gap-3 w-full">
          <SearchBarTable class="w-full flex-1 min-w-0" placeholder="Cari Aktivitas disini" v-model="searchQuery"/>

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
                  <button @click="filterStatus = 'menunggu'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-gray-600 hover:bg-gray-100 font-medium" :class="filterStatus === 'menunggu' ? 'bg-gray-100' : ''">Menunggu</button>
                  <button @click="filterStatus = 'disetujui'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-green-600 hover:bg-green-50 font-medium" :class="filterStatus === 'disetujui' ? 'bg-green-50' : ''">Disetujui</button>
                  <button @click="filterStatus = 'ditolak'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50 font-medium" :class="filterStatus === 'ditolak' ? 'bg-red-50' : ''">Ditolak</button>
                </div>
              </Transition>
            </div>
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
                    <button type="button" :disabled="isLoading" @click="handleSort('id')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer disabled:cursor-wait disabled:opacity-85">
                      <span>No</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'id'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <th class="w-[200px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    <button type="button" :disabled="isLoading" @click="handleSort('nama_tim')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer disabled:cursor-wait disabled:opacity-85">
                      <span>Nama Tim</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'nama_tim'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <th class="w-[280px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    <button type="button" :disabled="isLoading" @click="handleSort('peserta')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer disabled:cursor-wait disabled:opacity-85">
                      <span>Nama Peserta</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'peserta'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <th class="w-[180px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">Bukti Registrasi</th>
                  <th class="w-[150px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">Status</th>
                  <th class="w-[170px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none rounded-tr-[12px]">Aksi</th>
                </tr>
              </thead>
              
              <tbody :class="['[&_tr:not(:first-child)_td]:border-t [&_tr:not(:first-child)_td]:border-[#d6e0ee] font-inter text-[14px] text-[#435b76] transition-all duration-200', isLoading ? 'opacity-40 blur-[1px] pointer-events-none select-none cursor-wait' : '']">
                <tr v-for="(item, index) in kegiatanList" :key="item.id" class="h-[52px] transition-colors hover:bg-[#f7f9fd]">
                  <td class="px-3 py-2.5 text-center font-medium">{{ getRowNumber(index) }}</td>
                  <td class="px-3 py-2.5 text-left font-medium text-[#233547] truncate" :title="item.nama_tim">{{ item.nama_tim }}</td>
                  <td class="px-3 py-2.5 text-left truncate" :title="item.peserta.map(p => p.nama).join(', ')">
                    {{ item.peserta.map(p => p.nama).join(', ') }}
                  </td>
                  <td class="px-3 py-2.5 text-center">
                    <button @click="openImage(item.bukti_pendaftaran || item.gambar)" class="font-inter text-[14px] font-medium text-[#3b82f6] hover:text-blue-700 hover:underline transition-colors cursor-pointer truncate max-w-[110px] inline-block align-middle" :title="typeof item.bukti_pendaftaran === 'object' ? item.bukti_pendaftaran.name : item.bukti_pendaftaran">
                      {{ (item.bukti_pendaftaran && typeof item.bukti_pendaftaran === 'object' && item.bukti_pendaftaran.name) ? item.bukti_pendaftaran.name : item.bukti_pendaftaran }}
                    </button>
                  </td>
                  <td class="px-3 py-2.5 text-center">
                    <div class="mx-auto flex w-fit items-center justify-center gap-1.5 rounded-full px-3 py-1 font-inter text-[11px] font-semibold border"
                         :class="{
                           'bg-gray-100 text-gray-600 border-gray-200': item.status === 'menunggu',
                           'bg-green-50 text-green-600 border-green-200': item.status === 'disetujui',
                           'bg-red-50 text-red-600 border-red-200': item.status === 'ditolak'
                         }">
                      <svg v-if="item.status === 'menunggu'" class="h-3.5 w-3.5 shrink-0 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="9" />
                        <polyline points="12 7 12 12 15 15" />
                      </svg>
                      <svg v-if="item.status === 'disetujui'" class="h-3.5 w-3.5 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <svg v-if="item.status === 'ditolak'" class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <span>{{ item.status }}</span>
                    </div>
                  </td>
                  <td class="px-3 py-2.5 text-center">
                    <div class="flex items-center justify-center gap-1.5 sm:gap-2">
                      <AcceptButton :disabled="item.status === 'disetujui'" @click="handleApprove(item)" />
                      <RejectButton :disabled="item.status === 'ditolak' || item.status === 'disetujui'" @click="openReject(item)" />
                      <PreviewButtonTable @click="openDetail(item)" />
                      <DeleteButtonTable
                        label="Hapus Pendaftaran"
                        title="Hapus Pendaftaran"
                        @click="confirmDelete(item)" 
                      />
                    </div>
                  </td>
                </tr>
                <tr v-if="kegiatanList.length === 0">
                  <td colspan="6" class="px-4 py-12 text-center text-gray-500 font-medium">Data tidak ditemukan sesuai filter/pencarian Anda.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <TablePagination 
            :current-page="pendaftaranData?.current_page || 1" 
            :total-pages="pendaftaranData?.last_page || 1" 
            :rows-per-page="pendaftaranData?.per_page || 10"
            @update:current-page="handlePageChange"
            @update:rows-per-page="handleRowsChange"
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
                @error="onImageError"
                class="max-h-[82vh] max-w-[88vw] w-auto h-auto min-w-[280px] sm:min-w-[460px] rounded-xl object-contain shadow-2xl"
              />
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

    <!-- MODAL DETAIL (PERSETUJUAN REGISTRASI) -->
    <ModalFormPendaftaranMahasiswa
      :show="showDetailModal"
      :data="selectedItem"
      @close="showDetailModal = false"
      @approve="handleApprove"
      @reject="openReject"
    />

    <!-- MODAL REJECT -->
    <RejectModal :show="showRejectModal" @close="showRejectModal = false" @submit="handleReject"/>

    <!-- MODAL DELETE -->
    <DeleteModal
      :show="showDeleteModal"
      :is-loading="isLoading"
      @close="showDeleteModal = false"
      @confirm="executeDelete"
    >
      <template #title>
        Hapus Pendaftaran?
      </template>
      <template #message>
        Apakah Anda yakin ingin menghapus data pendaftaran tim <span class="font-bold text-[#17334F]">"{{ itemToDelete?.nama_tim }}"</span>? Tindakan ini tidak dapat dibatalkan.
      </template>
      <template #confirm-text>
        Hapus Pendaftaran
      </template>
    </DeleteModal>

  </AdminLayout>
</template>