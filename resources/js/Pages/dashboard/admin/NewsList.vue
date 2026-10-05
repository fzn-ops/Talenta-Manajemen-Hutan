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
const newsList = ref([
  { id: 1, tanggal: '2026-08-17', gambar: 'thumbnail.jpg', judul: 'Kemeriahan Agustusan Manhut 2026', isi: 'Lorem Ipsum dolor sit amet, voluptate velit esse cillum dolore eu fugiat nulla pariatur.' },
  { id: 2, tanggal: '2026-08-17', gambar: 'thumbnail.jpg', judul: 'Penyambutan Mahasiswa Baru', isi: 'Lorem Ipsum dolor sit amet, voluptate velit esse cillum dolore eu fugiat nulla pariatur.' },
]);

// ==========================================
// 2. STATE FILTER, SEARCH & SORTING
// ==========================================
const searchQuery = ref('');
const showFilter = ref(false);
const filterBulan = ref('');

const sortColumn = ref('id');
const sortDirection = ref('asc');

const resetFilters = () => {
  filterBulan.value = '';
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
const formatTanggal = (dateString) => {
  if (!dateString) return '-';
  const options = { day: 'numeric', month: 'long', year: 'numeric' };
  return new Date(dateString).toLocaleDateString('id-ID', options);
};

// ==========================================
// 3. LOGIC FILTERING & SORTING
// ==========================================
const processedNews = computed(() => {
  let data = [...newsList.value];

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    data = data.filter(item => 
      item.judul.toLowerCase().includes(query) || 
      item.isi.toLowerCase().includes(query)
    );
  }

  if (filterBulan.value) {
    data = data.filter(item => item.tanggal.startsWith(filterBulan.value));
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

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * rowsPerPage.value;
  return processedNews.value.slice(start, start + rowsPerPage.value);
});

const totalPages = computed(() => Math.ceil(processedNews.value.length / rowsPerPage.value) || 1);

watch([searchQuery, filterBulan, rowsPerPage], () => {
  currentPage.value = 1;
}, { deep: true });

// ==========================================
// 4. STATE TOAST, DELETE, PREVIEW GAMBAR
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
  newsList.value = newsList.value.filter(k => k.id !== itemToDelete.value.id);
  showDeleteModal.value = false;
  itemToDelete.value = null;
  showToast('Berita berhasil dihapus!', 'success');
};

const showImageModal = ref(false);
const selectedImage = ref('');

const openImage = (gambar) => {
  if (gambar && typeof gambar === 'object' && gambar.name) {
    selectedImage.value = URL.createObjectURL(gambar);
  } else if (gambar && !gambar.startsWith('http') && !gambar.startsWith('/')) {
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

const imageInput = ref(null);
const imagePreview = ref(null);
const isDragging = ref(false);

const form = ref({ id: null, judul: '', tanggal: '', isi: '', gambar: null });

// STATE ERRORS UNTUK VALIDASI
const errors = ref({ judul: '', tanggal: '', isi: '', gambar: '' });

const resetErrors = () => {
  errors.value = { judul: '', tanggal: '', isi: '', gambar: '' };
};

const validateForm = () => {
  resetErrors();
  let isValid = true;

  if (!form.value.judul.trim()) {
    errors.value.judul = 'Judul berita wajib diisi!';
    isValid = false;
  }
  if (!form.value.tanggal) {
    errors.value.tanggal = 'Tanggal terbit wajib dipilih!';
    isValid = false;
  }
  if (!form.value.isi.trim()) {
    errors.value.isi = 'Isi berita tidak boleh kosong!';
    isValid = false;
  }
  if (!form.value.gambar) {
    errors.value.gambar = 'Thumbnail berita wajib diupload!';
    isValid = false;
  }

  return isValid;
};

const triggerImageUpload = () => { imageInput.value?.click(); };

const processImageFile = (file) => {
  if (file) {
    if (file.size > 10 * 1024 * 1024) {
      showToast('Ukuran gambar maksimal 10MB!', 'error');
      return;
    }
    form.value.gambar = file;
    imagePreview.value = URL.createObjectURL(file);
    errors.value.gambar = '';
  }
};

const handleImageUpload = (event) => {
  const file = event.target.files?.[0];
  processImageFile(file);
};

const handleImageDrop = (event) => {
  isDragging.value = false;
  const file = event.dataTransfer.files?.[0];
  processImageFile(file);
};

const resetForm = () => {
  form.value = { id: null, judul: '', tanggal: '', isi: '', gambar: null };
  imagePreview.value = null;
  if (imageInput.value) imageInput.value.value = '';
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
  if (item.gambar && typeof item.gambar === 'object') {
    imagePreview.value = URL.createObjectURL(item.gambar);
  } else if (item.gambar) {
    imagePreview.value = (item.gambar.startsWith('http') || item.gambar.startsWith('/')) ? item.gambar : `https://picsum.photos/seed/${item.gambar}/800/600`;
  } else {
    imagePreview.value = null;
  }
  isEditMode.value = true;
  showFormModal.value = true;
};

const submitForm = () => {
  if (!validateForm()) {
    return;
  }

  if (isEditMode.value) {
    const index = newsList.value.findIndex(k => k.id === form.value.id);
    if (index !== -1) {
      newsList.value[index] = { 
        ...form.value, 
        gambar: typeof form.value.gambar === 'object' ? form.value.gambar.name : form.value.gambar 
      };
    }
    showToast('Berita berhasil diperbarui!', 'success');
  } else {
    const newId = newsList.value.length ? Math.max(...newsList.value.map(k => k.id)) + 1 : 1;
    newsList.value.unshift({ 
      ...form.value, 
      id: newId, 
      gambar: typeof form.value.gambar === 'object' ? form.value.gambar.name : (form.value.gambar || 'thumbnail.jpg') 
    });
    showToast('Berita berhasil ditambahkan!', 'success');
  }
  showFormModal.value = false;
};
</script>

<template>
  <Head title="Daftar Berita"/>

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
          <h1 class="mt-1 text-[34px] font-bold leading-[1.02] tracking-[-0.03em] text-[#17334F] sm:text-[42px] lg:text-[48px]">Daftar Berita</h1>
          <p class="mt-1.5 font-inter text-[14px] font-medium leading-tight text-[#4d6786] sm:text-[16px]">Lihat berita terkini yang sudah dibuat, atau tambah berita terbaru!</p>
        </div>

        <!-- ACTION BAR -->
        <div class="flex flex-row items-center gap-2 sm:gap-3 w-full">
          <!-- Search Input Component -->
          <SearchBarTable
            class="w-full flex-1 min-w-0"
            v-model="searchQuery"
            placeholder="Cari Berita disini..."
          />

          <!-- Action Buttons Row -->
          <div class="flex items-center justify-end gap-2 sm:gap-3 shrink-0">
            <!-- Filter Dropdown Container -->
            <div class="relative">
              <button
                type="button"
                @click="showFilter = !showFilter"
                class="relative flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-[10px] border-2 bg-transparent text-[#183669] transition-colors focus:outline-none select-none cursor-pointer"
                :class="showFilter || filterBulan
                  ? 'border-[#183669]'
                  : 'border-[#d6e0ee] hover:border-[#8ea9cb]'"
                title="Filter Berita"
              >
                <img
                  src="/assets/icons/filter.svg"
                  alt="Filter Icon"
                  class="h-5 w-5 shrink-0 object-contain pointer-events-none"
                />
                <!-- Red active indicator dot -->
                <span
                  v-if="filterBulan"
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
                      Filter Berita
                    </p>
                    <button
                      v-if="filterBulan"
                      type="button"
                      @click="resetFilters"
                      class="font-inter text-[11px] font-semibold text-[#dc2626] hover:underline cursor-pointer"
                    >
                      Reset Semua
                    </button>
                  </div>

                  <div class="mb-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#7188a3] mb-2">
                      Bulan Pembuatan
                    </p>
                    <input 
                      type="month" 
                      v-model="filterBulan" 
                      class="w-full rounded-lg border border-[#cbd5e1] px-3 py-2 text-xs outline-none focus:border-[#183669] text-gray-700 font-inter" 
                    />
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
              title="Tambah Berita"
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
                  <th class="w-[160px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    <button type="button" @click="handleSort('tanggal')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer">
                      <span>Tanggal</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'tanggal'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <th class="w-[280px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
                    <button type="button" @click="handleSort('judul')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer">
                      <span>Judul Berita</span>
                      <span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
                        <svg v-if="sortColumn === 'judul'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                        <svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                      </span>
                    </button>
                  </th>
                  <th class="w-[320px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">Isi Berita</th>
                  <th class="w-[130px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">Gambar</th>
                  <th class="w-[110px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none rounded-tr-[12px]">Aksi</th>
                </tr>
              </thead>
              
              <tbody class="[&_tr:not(:first-child)_td]:border-t [&_tr:not(:first-child)_td]:border-[#d6e0ee] font-inter text-[14px] text-[#435b76]">
                <tr v-for="(item, index) in paginatedData" :key="item.id" class="h-[52px] transition-colors hover:bg-[#f7f9fd]">
                  <td class="px-3 py-2.5 text-center">{{ (currentPage - 1) * rowsPerPage + index + 1 }}</td>
                  <td class="px-3 py-2.5 text-center">{{ formatTanggal(item.tanggal) }}</td>
                  <td class="px-3 py-2.5 text-left font-medium text-[#233547] truncate" :title="item.judul">{{ item.judul }}</td>
                  <td class="px-3 py-2.5 text-left truncate" :title="item.isi">{{ item.isi }}</td>
                  <td class="px-3 py-2.5 text-center">
                    <button @click="openImage(item.gambar)" class="text-[#3b82f6] hover:text-blue-700 hover:underline transition-colors font-medium text-xs cursor-pointer truncate max-w-[110px] inline-block align-middle" :title="typeof item.gambar === 'object' ? item.gambar.name : item.gambar">
                      {{ (item.gambar && typeof item.gambar === 'object' && item.gambar.name) ? item.gambar.name : item.gambar }}
                    </button>
                  </td>
                  <td class="px-3 py-2.5 text-center">
                    <div class="flex items-center justify-center gap-2">
                      <EditButtonTable @click="handleEdit(item)" />
                      <DeleteButtonTable @click="openDeleteModal(item)" />
                    </div>
                  </td>
                </tr>
                <tr v-if="paginatedData.length === 0">
                  <td colspan="6" class="px-4 py-12 text-center text-gray-500 font-medium">Data berita tidak ditemukan sesuai filter/pencarian Anda.</td>
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
      title="Hapus Data Berita?" 
      :message="`Apakah Anda yakin ingin menghapus berita dengan judul '${itemToDelete?.judul}'? Data yang dihapus tidak dapat dikembalikan.`" 
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
                  {{ isEditMode ? 'Edit Berita' : 'Form Tambah Berita' }}
                </h2>
              </div>

              <form @submit.prevent="submitForm" novalidate>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                  
                  <!-- Kolom Kiri -->
                  <div class="flex flex-col gap-3.5 sm:gap-4">
                    <!-- Judul Berita -->
                    <div>
                      <label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
                        Judul Berita<span class="text-red-500">*</span>
                      </label>
                      <p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">
                        Masukkan judul berita yang menarik perhatian!
                      </p>
                      <input 
                        type="text" 
                        v-model="form.judul" 
                        @input="errors.judul = ''"
                        placeholder="Contoh: Seminar Nasional Manhut 2026" 
                        class="mt-1 sm:mt-1.5 h-[42px] sm:h-[44px] w-full rounded-[10px] border bg-white px-3.5 font-inter text-[13px] sm:text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0" 
                        :class="errors.judul ? 'border-red-400 focus:border-red-500 bg-red-50/20' : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white'"
                      />
                      <p v-if="errors.judul" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
                        <svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ errors.judul }}</span>
                      </p>
                    </div>

                    <!-- Tanggal Terbit (DatePicker) -->
                    <div>
                      <label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
                        Tanggal Terbit<span class="text-red-500">*</span>
                      </label>
                      <p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">
                        Pilih tanggal perilisan berita.
                      </p>
                      <div class="mt-1 sm:mt-1.5">
                        <DatePicker
                          v-model="form.tanggal"
                          :has-error="!!errors.tanggal"
                          placeholder="Pilih tanggal terbit"
                          @update:modelValue="errors.tanggal = ''"
                        />
                      </div>
                      <p v-if="errors.tanggal" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
                        <svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ errors.tanggal }}</span>
                      </p>
                    </div>

                    <!-- Isi Berita -->
                    <div class="flex-1 flex flex-col">
                      <label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
                        Isi Berita<span class="text-red-500">*</span>
                      </label>
                      <p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">
                        Tuliskan konten berita secara lengkap di sini.
                      </p>
                      <textarea 
                        v-model="form.isi" 
                        @input="errors.isi = ''"
                        placeholder="Tulis isi berita..." 
                        rows="5" 
                        class="mt-1 sm:mt-1.5 w-full min-h-[140px] flex-1 rounded-[10px] border bg-white px-3.5 py-2.5 font-inter text-[13px] sm:text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0 resize-y leading-relaxed"
                        :class="errors.isi ? 'border-red-400 focus:border-red-500 bg-red-50/20' : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white'"
                      ></textarea>
                      <p v-if="errors.isi" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
                        <svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ errors.isi }}</span>
                      </p>
                    </div>
                  </div>

                  <!-- Kolom Kanan -->
                  <div class="flex flex-col gap-3.5 sm:gap-4">
                    <!-- Thumbnail Upload -->
                    <div class="flex-1 flex flex-col">
                      <label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
                        Gambar / Thumbnail<span class="text-red-500">*</span>
                      </label>
                      <p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5 mb-1.5">
                        Upload thumbnail berita (JPG/PNG, MAX 10MB)
                      </p>
                      
                      <input type="file" ref="imageInput" accept="image/png, image/jpeg, image/jpg" class="hidden" @change="handleImageUpload" />

                      <div 
                        @click="triggerImageUpload"
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleImageDrop"
                        :class="[
                          'relative flex flex-1 min-h-[200px] sm:min-h-[240px] w-full flex-col items-center justify-center rounded-[12px] border-2 border-dashed p-3.5 text-center transition-colors cursor-pointer group select-none',
                          errors.gambar ? 'border-red-400 bg-red-50/20' : (isDragging ? 'border-[#183669] bg-[#183669]/5' : 'border-[#183669]/30 bg-[#fafcff] hover:border-[#183669]/60')
                        ]"
                      >
                        <template v-if="imagePreview">
                          <div class="relative h-full w-full min-h-[170px] rounded-[10px] overflow-hidden border border-[#d6e0ee] bg-white p-2 flex items-center justify-center">
                            <img :src="imagePreview" alt="Preview" class="max-h-[220px] w-full object-contain" />
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity rounded-[10px]">
                              <span class="rounded-[8px] bg-white px-3 py-1.5 text-[12px] font-bold text-[#183669] shadow-md">Ganti Gambar</span>
                            </div>
                          </div>
                        </template>
                        <template v-else>
                          <svg class="h-10 w-10 text-[#8c9eb5] group-hover:text-[#183669] transition-colors" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                          </svg>
                          <p class="mt-2 font-inter text-[12px] text-[#7188a3]">
                            Upload gambar atau seret file ke form ini
                          </p>
                          <button type="button" class="mt-2.5 rounded-[8px] border border-[#a6b7cb] bg-white px-5 py-1.5 font-inter text-[12px] font-semibold text-[#5a718d] transition hover:bg-slate-50 shadow-xs cursor-pointer">Upload</button>
                        </template>
                      </div>
                      <p v-if="errors.gambar" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
                        <svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ errors.gambar }}</span>
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