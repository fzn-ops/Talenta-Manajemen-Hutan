<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';

import AdminLayout from '@/Layouts/dashboard/AdminLayout.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import SearchBarTable from '@/Components/dashboard/SearchBarTable.vue';
import TablePagination from '@/Components/dashboard/TablePagination.vue';
import DeleteModal from '@/Components/dashboard/DeleteModal.vue'; 
import EditButtonTable from '@/Components/dashboard/EditButtonTable.vue';
import DeleteButtonTable from '@/Components/dashboard/DeleteButtonTable.vue';
import PreviewButtonTable from '@/Components/dashboard/PreviewButtonTable.vue';

// ==========================================
// 1. DATA DUMMY
// ==========================================
const newsList = ref([
  { id: 1, tanggal: '2026-08-17', gambar: 'thumbnail.jpg', judul: 'Kemeriahan Agustusan Manhut 2026', isi: 'Lorem Ipsum dolor sit amet, voluptate velit esse cillum dolore eu fugiat nulla pariatur.' },
  { id: 2, tanggal: '2026-08-17', gambar: 'thumbnail.jpg', judul: 'Penyambutan Mahasiswa Baru', isi: 'Lorem Ipsum dolor sit amet, voluptate velit esse cillum dolore eu fugiat nulla pariatur.' },
]);

const formatTanggal = (dateString) => {
  if (!dateString) return '';
  const options = { day: 'numeric', month: 'long', year: 'numeric' };
  return new Date(dateString).toLocaleDateString('id-ID', options);
};

// ==========================================
// 2. STATE FILTER & SEARCH
// ==========================================
const searchQuery = ref('');
const showFilter = ref(false);
const filterBulan = ref('');

const resetFilters = () => {
  filterBulan.value = '';
};

const processedNews = computed(() => {
  let data = [...newsList.value];
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    data = data.filter(item => item.judul.toLowerCase().includes(query) || item.isi.toLowerCase().includes(query));
  }
  if (filterBulan.value) {
    data = data.filter(item => item.tanggal.startsWith(filterBulan.value));
  }
  return data;
});

// ==========================================
// 3. STATE TOAST, DELETE, PREVIEW GAMBAR
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
  } else if (gambar && !gambar.startsWith('http')) {
    selectedImage.value = `https://picsum.photos/seed/${gambar}/800/600`;
  } else {
    selectedImage.value = gambar || 'https://picsum.photos/800/600';
  }
  showImageModal.value = true;
};

// ==========================================
// 4. LOGIC MODAL FORM, UPLOAD & VALIDASI
// ==========================================
const showFormModal = ref(false);
const isEditMode = ref(false);

const imageInput = ref(null);
const imagePreview = ref(null);

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
    errors.value.tanggal = 'Tanggal terbit wajib diisi!';
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

const triggerImageUpload = () => { imageInput.value.click(); };

const handleImageUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    if (file.size > 10 * 1024 * 1024) {
      showToast('Ukuran gambar maksimal 10MB!', 'error');
      return;
    }
    form.value.gambar = file;
    imagePreview.value = URL.createObjectURL(file);
    errors.value.gambar = ''; // Hilangkan error kalau udah upload
  }
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
  resetErrors(); // Bersihin error pas buka form edit
  form.value = { ...item };
  if (item.gambar && typeof item.gambar === 'object') {
    imagePreview.value = URL.createObjectURL(item.gambar);
  } else if (item.gambar) {
    imagePreview.value = item.gambar.startsWith('http') ? item.gambar : `https://picsum.photos/seed/${item.gambar}/800/600`;
  } else {
    imagePreview.value = null;
  }
  isEditMode.value = true;
  showFormModal.value = true;
};

const submitForm = () => {
  // PANGGIL VALIDASI DISINI
  if (!validateForm()) {
    showToast('Mohon lengkapi form yang bertanda merah!', 'error');
    return;
  }

  if (isEditMode.value) {
    const index = newsList.value.findIndex(k => k.id === form.value.id);
    if (index !== -1) newsList.value[index] = { ...form.value };
    showToast('Berita berhasil diperbarui!', 'success');
  } else {
    const newId = newsList.value.length ? Math.max(...newsList.value.map(k => k.id)) + 1 : 1;
    newsList.value.unshift({ ...form.value, id: newId, gambar: form.value.gambar || 'thumbnail.jpg' });
    showToast('Berita berhasil ditambahkan!', 'success');
  }
  showFormModal.value = false;
};

const openDetail = (item) => { console.log('Preview Item Berita:', item); };
</script>

<template>
  <Head title="Daftar Berita"/>

  <AdminLayout>
    <div class="p-8 font-sans bg-[#fcfcfc] min-h-screen relative overflow-hidden">
      
      <!-- TOAST NOTIFICATION -->
      <Transition enter-active-class="transition-all transform duration-500 ease-out" enter-from-class="translate-x-12 opacity-0" enter-to-class="translate-x-0 opacity-100" leave-active-class="transition-all transform duration-300 ease-in" leave-from-class="translate-x-0 opacity-100" leave-to-class="translate-x-12 opacity-0">
        <div v-if="toast.show" class="fixed top-8 right-8 z-[100]">
          <ToastNotification :message="toast.message" :show="true" :type="toast.type" @close="toast.show = false"/>
        </div>
      </Transition>

      <div class="mb-6 flex flex-col gap-5">
        <div class="space-y-1.5">
          <h1 class="mt-1 text-[34px] font-bold leading-[1.02] tracking-[-0.03em] text-[#17334F] sm:text-[42px] lg:text-[48px]">Daftar Berita</h1>
          <p class="mt-1.5 font-inter text-[14px] font-medium leading-tight text-[#4d6786] sm:text-[16px]">Lihat berita terkini yang sudah dibuat, atau tambah berita terbaru!</p>
        </div>

        <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:gap-3">
          <SearchBarTable v-model="searchQuery" placeholder="Cari Berita disini..." class="flex-1 w-full" />
          <div class="flex items-center justify-end gap-2 sm:gap-3 w-full sm:w-auto shrink-0">
            <div class="relative">
              <button @click="showFilter = !showFilter" class="relative flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-[10px] border-2 bg-transparent text-[#183669] transition-colors focus:outline-none select-none cursor-pointer" :class="showFilter || filterBulan ? 'border-[#183669]' : 'border-[#d6e0ee] hover:border-[#8ea9cb]'">
                <img src="/assets/icons/filter.svg" alt="Filter Icon" class="h-5 w-5 shrink-0 object-contain pointer-events-none" onerror="this.style.display='none'" />
                <span v-if="filterBulan" class="absolute top-2.5 right-2.5 h-2 w-2 rounded-full bg-[#ef4444] ring-2 ring-[#eef2f7]"></span>
              </button>
              <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="transform scale-95 opacity-0 translate-y-1" enter-to-class="transform scale-100 opacity-100 translate-y-0" leave-active-class="transition duration-100 ease-in" leave-from-class="transform scale-100 opacity-100 translate-y-0" leave-to-class="transform scale-95 opacity-0 translate-y-1">
                <div v-if="showFilter" class="absolute right-0 top-14 w-80 rounded-[14px] border border-[#d6e0ee] bg-white p-4 shadow-2xl ring-1 ring-black/10 z-40 font-inter">
                  <div class="flex items-center justify-between border-b border-[#f0f4f9] pb-2 mb-3">
                    <p class="font-poppins text-xs font-bold text-[#183669]">Filter Berita</p>
                    <button v-if="filterBulan" type="button" @click="resetFilters" class="font-inter text-[11px] font-semibold text-[#dc2626] hover:underline cursor-pointer">Reset Semua</button>
                  </div>
                  <div class="mb-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#7188a3] mb-2">Bulan Pembuatan</p>
                    <input type="month" v-model="filterBulan" class="w-full rounded-lg border border-[#cbd5e1] px-3 py-2 text-xs outline-none focus:border-[#183669] text-gray-700 font-inter" />
                  </div>
                  <div class="flex items-center justify-end border-t border-[#f0f4f9] pt-3">
                    <button @click="showFilter = false" class="rounded-lg bg-[#183669] px-4 py-1.5 text-xs font-semibold text-white transition hover:bg-[#122b54] cursor-pointer">Tutup</button>
                  </div>
                </div>
              </Transition>
            </div>
            <button type="button" @click="openTambah" class="flex h-[46px] w-[46px] sm:w-auto shrink-0 items-center justify-center gap-2 rounded-[10px] bg-[#183669] px-0 sm:px-7 font-poppins text-[15px] font-semibold text-white shadow-sm transition hover:bg-[#122b54] active:scale-95 focus:outline-none select-none cursor-pointer">
              <svg class="h-5 w-5 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
              <span class="hidden sm:inline">Tambah</span>
            </button>
          </div>
        </div>
      </div>

      <!-- TABEL DATA -->
      <div class="overflow-hidden rounded-t-xl border border-gray-200 bg-white shadow-sm mb-6">
        <div class="overflow-x-auto">
          <table class="w-full text-center text-sm whitespace-nowrap">
            <thead class="bg-[#48796f] text-white select-none">
              <tr>
                <th class="px-4 py-4 font-semibold w-16">No</th>
                <th class="px-4 py-4 font-semibold w-32">Tanggal</th>
                <th class="px-4 py-4 font-semibold">Gambar</th>
                <th class="px-4 py-4 font-semibold max-w-[200px]">Judul Berita</th>
                <th class="px-4 py-4 font-semibold max-w-[300px]">Isi Berita</th>
                <th class="px-4 py-4 font-semibold w-36">Aksi</th>
              </tr>
            </thead>
            <tbody class="text-gray-600">
              <tr v-for="(item, index) in processedNews" :key="item.id" class="border-b border-gray-200 hover:bg-gray-50/70 transition-colors">
                <td class="px-4 py-4">{{ index + 1 }}</td>
                <td class="px-4 py-4">{{ formatTanggal(item.tanggal) }}</td>
                <td class="px-4 py-4">
                  <button @click="openImage(item.gambar)" class="text-[#3b82f6] hover:text-blue-700 hover:underline transition-colors font-medium underline">
                    {{ (item.gambar && typeof item.gambar === 'object' && item.gambar.name) ? item.gambar.name : item.gambar }}
                  </button>
                </td>
                <td class="px-4 py-4 font-medium text-[#233547] max-w-[200px] truncate" :title="item.judul">{{ item.judul }}</td>
                <td class="px-4 py-4 max-w-[300px] truncate" :title="item.isi">{{ item.isi }}</td>
                <td class="px-4 py-4">
                  <div class="flex items-center justify-center gap-2">
                    <EditButtonTable @click="handleEdit(item)" />
                    <DeleteButtonTable @click="openDeleteModal(item)" />
                  </div>
                </td>
              </tr>
              <tr v-if="processedNews.length === 0">
                <td colspan="6" class="px-4 py-8 text-center text-gray-500 font-medium">Data berita tidak ditemukan.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <TablePagination :links="[]" :currentPage="1" :rowsPerPage="10"/>
    </div>

    <!-- Modal Delete -->
    <DeleteModal :show="showDeleteModal" title="Hapus Data Berita?" :message="`Apakah Anda yakin ingin menghapus berita dengan judul '${itemToDelete?.judul}'? Data yang dihapus tidak dapat dikembalikan.`" @close="showDeleteModal = false" @confirm="executeDelete" />

    <!-- ========================================== -->
    <!-- MODAL FORM TAMBAH & EDIT (DENGAN VALIDASI) -->
    <!-- ========================================== -->
    <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showFormModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm md:p-8">
        <div class="absolute inset-0" @click="showFormModal = false"></div>
        <Transition enter-active-class="transition duration-300 ease-out delay-75" enter-from-class="opacity-0 translate-y-4 scale-95" enter-to-class="opacity-100 translate-y-0 scale-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0 scale-100" leave-to-class="opacity-0 translate-y-4 scale-95">
          <div v-if="showFormModal" class="relative w-full max-w-[1100px] max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-8 shadow-2xl">
            
            <h2 class="mb-8 text-2xl font-bold text-[#1a2b4c]">
              {{ isEditMode ? 'Edit Berita' : 'Tambah Berita' }}
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
              
              <!-- KOLOM KIRI -->
              <div class="flex flex-col gap-6">
                <!-- Judul Berita -->
                <div>
                  <label class="block text-sm font-bold text-[#1a2b4c]">Judul Berita<span class="text-red-500">*</span></label>
                  <p class="text-[10px] text-gray-400 mb-2">Masukkan judul berita yang menarik perhatian!</p>
                  <input 
                    type="text" 
                    v-model="form.judul" 
                    placeholder="Contoh: Seminar Nasional Manhut 2026" 
                    @input="errors.judul = ''"
                    :class="[
                      'w-full rounded-lg border px-4 py-2.5 text-sm text-gray-700 outline-none transition-colors',
                      errors.judul ? 'border-red-500 focus:border-red-600 bg-red-50/30' : 'border-gray-300 focus:border-[#152c5b]'
                    ]" 
                  />
                  <!-- Error Message -->
                  <p v-if="errors.judul" class="mt-1 text-xs font-semibold text-red-500 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ errors.judul }}
                  </p>
                </div>

                <!-- Isi Berita -->
                <div class="flex-1 flex flex-col">
                  <label class="block text-sm font-bold text-[#1a2b4c]">Isi Berita<span class="text-red-500">*</span></label>
                  <p class="text-[10px] text-gray-400 mb-2">Tuliskan konten berita secara lengkap di sini.</p>
                  <textarea 
                    v-model="form.isi" 
                    placeholder="Tulis isi berita..." 
                    @input="errors.isi = ''"
                    :class="[
                      'w-full flex-1 min-h-[250px] rounded-lg border px-4 py-3 text-sm text-gray-700 outline-none transition-colors leading-relaxed resize-none',
                      errors.isi ? 'border-red-500 focus:border-red-600 bg-red-50/30' : 'border-gray-300 focus:border-[#152c5b]'
                    ]"
                  ></textarea>
                  <p v-if="errors.isi" class="mt-1 text-xs font-semibold text-red-500 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ errors.isi }}
                  </p>
                </div>
              </div>

              <!-- KOLOM KANAN -->
              <div class="flex flex-col gap-6">
                
                <!-- Tanggal Terbit -->
                <div>
                  <label class="block text-sm font-bold text-[#1a2b4c]">Tanggal Terbit<span class="text-red-500">*</span></label>
                  <p class="text-[10px] text-gray-400 mb-2">Pilih tanggal perilisan berita.</p>
                  <input 
                    type="date" 
                    v-model="form.tanggal" 
                    @change="errors.tanggal = ''"
                    :class="[
                      'w-full rounded-lg border px-4 py-2.5 text-sm text-gray-700 outline-none transition-colors',
                      errors.tanggal ? 'border-red-500 focus:border-red-600 bg-red-50/30' : 'border-gray-300 focus:border-[#152c5b]'
                    ]" 
                  />
                  <p v-if="errors.tanggal" class="mt-1 text-xs font-semibold text-red-500 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ errors.tanggal }}
                  </p>
                </div>

                <!-- Thumbnail -->
                <div>
                  <label class="block text-sm font-bold text-[#1a2b4c]">Gambar / Thumbnail<span class="text-red-500">*</span></label>
                  <p class="text-[10px] text-gray-400 mb-2">Upload thumbnail berita (JPG/PNG, MAX 10MB)</p>
                  
                  <input type="file" ref="imageInput" accept="image/png, image/jpeg, image/jpg" class="hidden" @change="handleImageUpload" />

                  <div 
                    @click="triggerImageUpload" 
                    :class="[
                      'relative flex h-[250px] w-full flex-col items-center justify-center overflow-hidden rounded-xl border-2 border-dashed bg-white p-2 cursor-pointer group transition hover:bg-gray-50',
                      errors.gambar ? 'border-red-500 bg-red-50/30' : 'border-gray-300'
                    ]"
                  >
                    <template v-if="imagePreview">
                      <img :src="imagePreview" alt="Preview" class="h-full w-full object-contain rounded-lg shadow-sm" />
                      <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                        <span class="rounded-lg bg-white px-4 py-2 text-xs font-bold text-[#1a2b4c] shadow-md">Ganti Gambar</span>
                      </div>
                    </template>
                    <template v-else>
                      <svg :class="['mb-3 h-14 w-14 transition-colors', errors.gambar ? 'text-red-400' : 'text-gray-400 group-hover:text-[#152c5b]']" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                      </svg>
                      <p :class="['mb-4 text-[10px] font-medium', errors.gambar ? 'text-red-500' : 'text-gray-400']">Upload gambar atau seret gambar ke form ini</p>
                      <button type="button" :class="['rounded-lg border bg-white px-6 py-1.5 text-xs font-bold shadow-sm transition', errors.gambar ? 'border-red-400 text-red-500' : 'border-gray-300 text-gray-500 group-hover:border-[#152c5b] group-hover:text-[#152c5b]']">
                        Upload
                      </button>
                    </template>
                  </div>
                  <p v-if="errors.gambar" class="mt-1.5 text-xs font-semibold text-red-500 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ errors.gambar }}
                  </p>
                </div>

              </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="mt-12 flex justify-end gap-4 border-t border-gray-100 pt-6">
              <button @click="showFormModal = false" class="rounded-lg border border-gray-300 bg-white w-32 py-2.5 text-sm font-bold text-[#1a2b4c] transition hover:bg-gray-50">Kembali</button>
              <button @click="submitForm" class="rounded-lg bg-[#152c5b] w-32 py-2.5 text-sm font-bold text-white transition hover:bg-[#0e1d3e] shadow-md">Simpan</button>
            </div>
            
          </div>
        </Transition>
      </div>
    </Transition>

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