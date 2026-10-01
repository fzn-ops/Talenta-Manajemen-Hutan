<script setup>
import { ref, computed } from 'vue';
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
  { id: 1, judul: 'Pelatihan Kepemimpinan Dasar', kategori: ['Profesional', 'Bisnis'], deadline: '2029-07-10', gambar: ['123.jpg', '234.jpg', '456.jpg'], deskripsi: 'Deskripsi kegiatan...' },
  { id: 2, judul: 'Seminar Nasional Akademisi', kategori: ['Akademisi', 'Birokrat'], deadline: '2029-08-15', gambar: ['123.jpg', '234.jpg'], deskripsi: 'Deskripsi kegiatan...' },
  { id: 3, judul: 'Workshop Bisnis Digital', kategori: ['Bisnis'], deadline: '2029-07-10', gambar: ['123.jpg'], deskripsi: 'Deskripsi kegiatan...' },
]);

const searchQuery = ref('');
const showFilter = ref(false);

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

  return data;
});

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
  // Antisipasi jika yg di-klik adalah objek file asli yg punya .url
  if (typeof gambar === 'object' && gambar.url) {
    selectedImage.value = gambar.url;
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

  // Hitung sisa slot
  const sisaSlot = 3 - form.value.gambar.length;
  const filesToAdd = files.slice(0, sisaSlot);

  filesToAdd.forEach(file => {
    // Cek batas maksimal ukuran 10MB
    if (file.size > 10 * 1024 * 1024) {
      showToast(`File ${file.name} terlalu besar! Maksimal 10MB.`, 'error');
      return;
    }

    const previewUrl = URL.createObjectURL(file);

    form.value.gambar.push({
      file: file,       // Ini yang bakal dikirim ke API/Inertia
      url: previewUrl,  // URL preview sementara
      isNew: true       // Tandai sbg file baru dari perangkat
    });
  });

  // Reset input agar bisa pilih file yang sama lagi kalau dihapus
  event.target.value = '';
};

const removeImage = (index) => {
  const removed = form.value.gambar.splice(index, 1)[0];
  // Bersihkan memory browser jika yg dihapus adalah file preview baru
  if (removed && removed.isNew) {
    URL.revokeObjectURL(removed.url); 
  }
};

const getImageSrc = (gbr) => {
  if (typeof gbr === 'object' && gbr.url) {
    return gbr.url; // Gambar baru hasil upload dari perangkat
  }
  return `https://picsum.photos/seed/${gbr}/200/200`; // Gambar dummy database
};
// ==========================================


// Aksi Submit
const handleSave = () => {
  // Catatan: Karena ada upload file, pastikan nanti pakai Inertia.post/put dengan format FormData
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
    <div class="p-8 font-sans bg-[#f9fafb] min-h-screen relative overflow-hidden">
      
      <!-- TOAST NOTIFICATION -->
      <Transition enter-active-class="transition-all transform duration-500 ease-out" enter-from-class="translate-x-12 opacity-0" enter-to-class="translate-x-0 opacity-100" leave-active-class="transition-all transform duration-300 ease-in" leave-from-class="translate-x-0 opacity-100" leave-to-class="translate-x-12 opacity-0">
        <div v-if="toast.show" class="fixed top-8 right-8 z-[100]">
          <ToastNotification :message="toast.message" :show="true" :type="toast.type" @close="toast.show = false"/>
        </div>
      </Transition>

      <div class="mb-6">
        <h1 class="text-3xl font-extrabold text-[#1a2b4c]">List Aktivitas</h1>
        <p class="text-sm text-gray-500 mt-1">Lihat seberapa banyak mahasiswa yang mengikuti aktivitas Talenta!</p>
      </div>

      <!-- TOP BAR -->
      <div class="mb-4 flex items-center gap-3 w-full relative">
        <div class="flex-1 w-full flex">
          <SearchBarTable class="w-full flex-1" placeholder="Cari Aktivitas disini" v-model="searchQuery"/>
        </div>

        <!-- Filter Dropdown Container -->
        <div class="relative">
          <button @click="showFilter = !showFilter" :class="['flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border transition shadow-sm', showFilter || filters.kategori.length > 0 || filters.waktu ? 'border-[#48796f] bg-[#f0fdf4] text-[#48796f]' : 'border-gray-400 bg-white text-gray-700 hover:bg-gray-50']">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" /></svg>
          </button>

          <!-- Dropdown Menu Filter -->
          <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-2">
            <div v-if="showFilter" class="absolute right-0 top-14 w-80 rounded-xl border border-gray-200 bg-white p-5 shadow-xl z-40">
              <div class="mb-5">
                <label class="block text-sm font-bold text-[#1a2b4c] mb-3">Filter Kategori</label>
                <div class="grid grid-cols-2 gap-3">
                  <label v-for="kat in listKategori" :key="kat" class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" :value="kat" v-model="filters.kategori" class="h-4 w-4 rounded border-gray-300 text-[#48796f] focus:ring-[#48796f]">
                    <span class="text-sm text-gray-700">{{ kat }}</span>
                  </label>
                </div>
              </div>
              <div class="mb-5">
                <label class="block text-sm font-bold text-[#1a2b4c] mb-2">Filter Waktu (Deadline)</label>
                <input type="date" v-model="filters.waktu" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-[#48796f] focus:ring-1 focus:ring-[#48796f] text-gray-700" />
              </div>
              <div class="flex items-center justify-between border-t border-gray-100 pt-4 mt-2">
                <button @click="resetFilters" class="text-sm font-medium text-red-500 hover:text-red-600 transition">Reset</button>
                <button @click="showFilter = false" class="rounded-lg bg-[#1a2b4c] px-5 py-2 text-sm font-bold text-white transition hover:bg-[#111d33]">Terapkan</button>
              </div>
            </div>
          </Transition>
        </div>

        <button @click="openTambah" class="flex h-[42px] items-center justify-center rounded-lg bg-[#1a2b4c] px-6 text-sm font-semibold text-white transition hover:bg-[#111d33] shadow-sm">
          Tambah
        </button>
      </div>

      <!-- Overlay penutup filter jika klik di luar -->
      <div v-if="showFilter" @click="showFilter = false" class="fixed inset-0 z-30"></div>

      <!-- TABEL DATA -->
      <div class="overflow-hidden rounded-t-xl border border-gray-200 bg-white shadow-sm mb-6 relative z-10">
        <div class="overflow-x-auto">
          <table class="w-full text-center text-sm whitespace-nowrap">
            <thead class="bg-[#48796f] text-white select-none">
              <tr>
                <th class="px-4 py-4 font-semibold w-16">No</th>
                <th class="px-4 py-4 font-semibold text-left">Judul</th>
                <th class="px-4 py-4 font-semibold text-left">Kategori</th>
                <th class="px-4 py-4 font-semibold">Deadline</th>
                <th class="px-4 py-4 font-semibold text-left">Gambar</th>
                <th class="px-4 py-4 font-semibold w-24">Aksi</th>
              </tr>
            </thead>
            
            <tbody class="text-gray-600">
              <tr v-for="(item, index) in processedData" :key="item.id" class="border-b border-gray-200 hover:bg-gray-50/70 transition-colors">
                <td class="px-4 py-4">{{ index + 1 }}</td>
                <td class="px-4 py-4 text-left font-medium text-[#233547]">{{ item.judul }}</td>
                <td class="px-4 py-4 text-left">{{ item.kategori.join(', ') }}</td>
                <td class="px-4 py-4">{{ formatDate(item.deadline) }}</td>
                <td class="px-4 py-4 text-left">
                  <span v-for="(gbr, i) in item.gambar" :key="i" class="inline-block">
                    <!-- Format tombol untuk array object/string -->
                    <button @click="openImage(gbr)" class="text-[#3b82f6] hover:text-blue-700 hover:underline transition-colors font-medium">
                      {{ typeof gbr === 'object' ? gbr.file.name : gbr }}
                    </button>
                    <span v-if="i < item.gambar.length - 1" class="mr-1">, </span>
                  </span>
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center justify-center gap-2">
                    <EditButtonTable @click="openEdit(item)" />
                    <DeleteButtonTable @click="confirmDelete(item)" />
                  </div>
                </td>
              </tr>
              <tr v-if="processedData.length === 0">
                <td colspan="6" class="px-4 py-12 text-center text-gray-500 font-medium">Data tidak ditemukan sesuai filter/pencarian Anda.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <TablePagination :links="[]" :currentPage="1" :rowsPerPage="10"/>
    </div>

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

    <!-- MODAL PREVIEW GAMBAR DARI TABEL -->
    <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showImageModal" class="fixed inset-0 z-[70] flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm" @click="showImageModal = false">
        <div class="relative max-w-4xl rounded-2xl bg-white p-2 shadow-2xl" @click.stop>
          <button @click="showImageModal = false" class="absolute -top-4 -right-4 flex h-10 w-10 items-center justify-center rounded-full bg-red-500 text-white shadow-lg hover:bg-red-600 border-2 border-white transition-transform hover:scale-110 z-10">✕</button>
          <img :src="selectedImage" alt="Preview Gambar" class="w-full max-h-[80vh] object-contain rounded-xl" />
        </div>
      </div>
    </Transition>

  </AdminLayout>
</template>