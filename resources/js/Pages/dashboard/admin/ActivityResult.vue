<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';

import AdminLayout from '@/Layouts/dashboard/AdminLayout.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import SearchBarTable from '@/Components/dashboard/SearchBarTable.vue';
import TablePagination from '@/Components/dashboard/TablePagination.vue';
import RejectModal from '@/Components/dashboard/RejectModal.vue';
import AcceptButton from '@/Components/dashboard/admin/AcceptButton.vue';
import RejectButton from '@/Components/dashboard/admin/RejectButton.vue';
import PreviewButtonTable from '@/Components/dashboard/PreviewButtonTable.vue';

// Data Dummy Sesuai Gambar Hasil Aktivitas
const kegiatan = ref([
  { id: 1, nama_mahasiswa: 'Fauzan Fuadiansyah', nim: 'J0403231085', jenis_roadmap: 'Professional', nama_kegiatan: 'Hackaton V2.0 Tahun 2026', deskripsi: 'Lorem ipsum dolor sit amet, duis officia reprehenderit esse duis. Dolore cupidatat aliqua veniam eu culpa reprehenderit in excepteur exercitation adipiscing. Aliquip esse laborum cupidatat duis in excepteur enim dolore sed incididunt proident aliquip. Aute consectetur nulla proident ea sunt qui in culpa est.\n\nDuis consequat proident culpa est tempor nulla in. Sunt anim ad occaecat pariatur exercitation reprehenderit proident duis tempor proident cillum nisi. Aliqua nulla laboris esse dolor ea esse anim. Pariatur excepteur enim in labore esse eu proident sint exercitation labore laborum. Dolore laboris veniam qui officia velit irure sint commodo excepteur. Excepteur incididunt excepteur aute laborum ullamco eiusmod consectetur ut voluptate sunt officia laboris.', gambar: '123.jpg', status: 'Menunggu', alasan_tolak: '' },
  { id: 2, nama_mahasiswa: 'Fauzan Fuadiansyah', nim: 'J0403231085', jenis_roadmap: 'Professional', nama_kegiatan: 'Hackaton V2.0 Tahun 2026', deskripsi: 'Deskripsi hasil kegiatan...', gambar: '123.jpg', status: 'Menunggu', alasan_tolak: '' },
  { id: 3, nama_mahasiswa: 'Fauzan Fuadiansyah', nim: 'J0403231085', jenis_roadmap: 'Professional', nama_kegiatan: 'Hackaton V2.0 Tahun 2026', deskripsi: 'Deskripsi hasil kegiatan...', gambar: '123.jpg', status: 'Menunggu', alasan_tolak: '' },
  { id: 4, nama_mahasiswa: 'Fauzan Fuadiansyah', nim: 'J0403231085', jenis_roadmap: 'Professional', nama_kegiatan: 'Hackaton V2.0 Tahun 2026', deskripsi: 'Deskripsi hasil kegiatan...', gambar: '123.jpg', status: 'Menunggu', alasan_tolak: '' },
  { id: 5, nama_mahasiswa: 'Fauzan Fuadiansyah', nim: 'J0403231085', jenis_roadmap: 'Professional', nama_kegiatan: 'Hackaton V2.0 Tahun 2026', deskripsi: 'Deskripsi hasil kegiatan...', gambar: '123.jpg', status: 'Disetujui', alasan_tolak: '' },
  { id: 6, nama_mahasiswa: 'Fauzan Fuadiansyah', nim: 'J0403231085', jenis_roadmap: 'Professional', nama_kegiatan: 'Hackaton V2.0 Tahun 2026', deskripsi: 'Deskripsi hasil kegiatan...', gambar: '123.jpg', status: 'Menunggu', alasan_tolak: '' },
  { id: 7, nama_mahasiswa: 'Fauzan Fuadiansyah', nim: 'J0403231085', jenis_roadmap: 'Professional', nama_kegiatan: 'Hackaton V2.0 Tahun 2026', deskripsi: 'Deskripsi hasil kegiatan...', gambar: '123.jpg', status: 'Ditolak', alasan_tolak: 'Kurang Mantap' },
  { id: 8, nama_mahasiswa: 'Fauzan Fuadiansyah', nim: 'J0403231085', jenis_roadmap: 'Professional', nama_kegiatan: 'Hackaton V2.0 Tahun 2026', deskripsi: 'Deskripsi hasil kegiatan...', gambar: '123.jpg', status: 'Menunggu', alasan_tolak: '' },
  { id: 9, nama_mahasiswa: 'Fauzan Fuadiansyah', nim: 'J0403231085', jenis_roadmap: 'Professional', nama_kegiatan: 'Hackaton V2.0 Tahun 2026', deskripsi: 'Deskripsi hasil kegiatan...', gambar: '123.jpg', status: 'Menunggu', alasan_tolak: '' },
  { id: 10, nama_mahasiswa: 'Fauzan Fuadiansyah', nim: 'J0403231085', jenis_roadmap: 'Professional', nama_kegiatan: 'Hackaton V2.0 Tahun 2026', deskripsi: 'Deskripsi hasil kegiatan...', gambar: '123.jpg', status: 'Menunggu', alasan_tolak: '' },
]);

const searchQuery = ref('');
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

// Filter Logic Tanpa Sorting
const processedKegiatan = computed(() => {
  let data = [...kegiatan.value];

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    data = data.filter(item => 
      item.nama_mahasiswa.toLowerCase().includes(query) ||
      item.nim.toLowerCase().includes(query) ||
      item.nama_kegiatan.toLowerCase().includes(query) ||
      item.status.toLowerCase().includes(query)
    );
  }

  return data;
});

const openDetail = (item) => {
  selectedItem.value = item;
  showDetailModal.value = true;
};

const openReject = (item) => {
  if(item) selectedItem.value = item; 
  showRejectModal.value = true;
};

const openImage = (gambar) => {
  selectedImage.value = gambar;
  showImageModal.value = true;
};

const handleApprove = () => {
  showDetailModal.value = false;
  showToast('Hasil aktivitas berhasil disetujui!', 'success');
};

const handleReject = (reason) => {
  showRejectModal.value = false;
  showDetailModal.value = false; 
  showToast(`Hasil aktivitas ditolak. Alasan: ${reason || 'Tidak ada'}`, 'error');
};
</script>

<template>
  <Head title="Daftar Hasil Aktivitas Mahasiswa"/>

  <AdminLayout>
    <div class="p-8 font-sans bg-[#fcfcfc] min-h-screen relative overflow-hidden">
      
      <!-- TOAST NOTIFICATION -->
      <Transition enter-active-class="transition-all transform duration-500 ease-out" enter-from-class="translate-x-12 opacity-0" enter-to-class="translate-x-0 opacity-100" leave-active-class="transition-all transform duration-300 ease-in" leave-from-class="translate-x-0 opacity-100" leave-to-class="translate-x-12 opacity-0">
        <div v-if="toast.show" class="fixed top-8 right-8 z-[100]">
          <ToastNotification :message="toast.message" :show="true" :type="toast.type" @close="toast.show = false"/>
        </div>
      </Transition>

      <div class="mb-6">
        <h1 class="text-3xl font-extrabold text-[#1a2b4c]">Daftar Hasil Aktivitas Mahasiswa</h1>
        <p class="text-sm text-gray-500 mt-1">Lihat seberapa banyak mahasiswa yang mengikuti aktivitas Talenta!</p>
      </div>

      <div class="mb-4 flex items-center gap-3 w-full relative">
        <div class="flex-1 w-full flex">
          <SearchBarTable class="w-full flex-1" placeholder="Cari Aktivitas disini" v-model="searchQuery"/>
        </div>

        <div class="relative">
          <button @click="showFilter = !showFilter" class="flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-gray-400 bg-white text-gray-700 transition hover:bg-gray-50">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" /></svg>
          </button>
          <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-2">
            <div v-if="showFilter" class="absolute right-0 top-12 z-40 w-48 rounded-xl border border-gray-200 bg-white p-2 shadow-lg">
              <p class="px-3 py-1.5 text-xs font-bold text-gray-400 uppercase">Filter Status</p>
              <button @click="searchQuery = ''; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 font-medium">Semua Data</button>
              <button @click="searchQuery = 'Menunggu'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-yellow-600 hover:bg-yellow-50 font-medium">Menunggu</button>
              <button @click="searchQuery = 'Disetujui'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-green-600 hover:bg-green-50 font-medium">Disetujui</button>
              <button @click="searchQuery = 'Ditolak'; showFilter = false" class="w-full rounded-lg px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50 font-medium">Ditolak</button>
            </div>
          </Transition>
        </div>
      </div>

      <!-- TABEL DATA (Tanpa Sorting) -->
      <div class="overflow-hidden rounded-t-xl border border-gray-200 bg-white shadow-sm mb-6">
        <div class="overflow-x-auto">
          <table class="w-full text-center text-sm whitespace-nowrap">
            <thead class="bg-[#48796f] text-white select-none">
              <tr>
                <th class="px-4 py-4 font-semibold w-16">No</th>
                <th class="px-4 py-4 font-semibold">Nama Mahasiswa</th>
                <th class="px-4 py-4 font-semibold">NIM</th>
                <th class="px-4 py-4 font-semibold">Jenis Roadmap</th>
                <th class="px-4 py-4 font-semibold">Nama Kegiatan</th>
                <th class="px-4 py-4 font-semibold">Bukti</th>
                <th class="px-4 py-4 font-semibold">Status</th>
                <th class="px-4 py-4 font-semibold w-32">Aksi</th>
              </tr>
            </thead>
            
            <tbody class="text-gray-600">
              <tr v-for="(item, index) in processedKegiatan" :key="item.id" class="border-b border-gray-200 hover:bg-gray-50/70 transition-colors">
                <td class="px-4 py-4">{{ index + 1 }}</td>
                <td class="px-4 py-4 font-medium text-[#233547]">{{ item.nama_mahasiswa }}</td>
                <td class="px-4 py-4">{{ item.nim }}</td>
                <td class="px-4 py-4">{{ item.jenis_roadmap }}</td>
                <td class="px-4 py-4">{{ item.nama_kegiatan }}</td>
                <td class="px-4 py-4">
                  <button @click="openImage(item.gambar)" class="text-[#3b82f6] hover:text-blue-700 hover:underline transition-colors font-medium">
                    {{ item.gambar }}
                  </button>
                </td>
                <td class="px-4 py-4">
                  <div class="mx-auto flex w-fit items-center justify-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium border"
                       :class="{
                         'bg-yellow-50 text-yellow-600 border-yellow-200': item.status === 'Menunggu',
                         'bg-green-50 text-green-600 border-green-200': item.status === 'Disetujui',
                         'bg-red-50 text-red-600 border-red-200': item.status === 'Ditolak'
                       }">
                    <svg v-if="item.status === 'Menunggu'" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" /></svg>
                    <svg v-if="item.status === 'Disetujui'" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <svg v-if="item.status === 'Ditolak'" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    {{ item.status }}
                  </div>
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center justify-center gap-2">
                    <AcceptButton @click="handleApprove(item)" />
                    <RejectButton @click="openReject(item)" />
                    <PreviewButtonTable @click="openDetail(item)" />
                  </div>
                </td>
              </tr>
              <tr v-if="processedKegiatan.length === 0">
                <td colspan="8" class="px-4 py-8 text-center text-gray-500 font-medium">Data tidak ditemukan.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <TablePagination :links="[]" :currentPage="1" :rowsPerPage="10"/>
    </div>

    <!-- MODAL GAMBAR (LIGHTBOX) -->
    <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showImageModal" class="fixed inset-0 z-[70] flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm" @click="showImageModal = false">
        <div class="relative max-w-4xl rounded-2xl bg-white p-2 shadow-2xl" @click.stop>
          <button @click="showImageModal = false" class="absolute -top-4 -right-4 flex h-10 w-10 items-center justify-center rounded-full bg-red-500 text-white shadow-lg hover:bg-red-600 border-2 border-white transition-transform hover:scale-110 z-10">✕</button>
          <img :src="`https://picsum.photos/seed/${selectedImage}/800/600`" alt="Preview Gambar" class="w-full max-h-[80vh] object-contain rounded-xl" />
        </div>
      </div>
    </Transition>

    <!-- MODAL DETAIL (HASIL KEGIATAN) -->
    <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm md:p-8">
        <div class="absolute inset-0" @click="showDetailModal = false"></div>
        <Transition enter-active-class="transition duration-300 ease-out delay-75" enter-from-class="opacity-0 translate-y-4 scale-95" enter-to-class="opacity-100 translate-y-0 scale-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0 scale-100" leave-to-class="opacity-0 translate-y-4 scale-95">
          <div v-if="showDetailModal" class="relative w-full max-w-6xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-8 shadow-2xl">
            
            <h2 class="mb-8 text-2xl font-bold text-[#1a2b4c]">Hasil Kegiatan</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
              <!-- Kolom Kiri -->
              <div class="flex flex-col gap-5">
                <div class="flex gap-4">
                  <div class="flex-1">
                    <label class="mb-1.5 block text-sm font-bold text-[#1a2b4c]">Nama</label>
                    <input type="text" readonly :value="selectedItem?.nama_mahasiswa" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-500 outline-none" />
                  </div>
                  <div class="flex-1">
                    <label class="mb-1.5 block text-sm font-bold text-[#1a2b4c]">NIM</label>
                    <input type="text" readonly :value="selectedItem?.nim" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-500 outline-none" />
                  </div>
                </div>
                <div class="flex gap-4">
                  <div class="flex-1">
                    <label class="mb-1.5 block text-sm font-bold text-[#1a2b4c]">Nama Kegiatan</label>
                    <input type="text" readonly :value="selectedItem?.nama_kegiatan" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-500 outline-none" />
                  </div>
                  <div class="flex-1">
                    <label class="mb-1.5 block text-sm font-bold text-[#1a2b4c]">Jenis Roadmap</label>
                    <input type="text" readonly :value="selectedItem?.jenis_roadmap" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-500 outline-none" />
                  </div>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-bold text-[#1a2b4c]">Deskripsi Output</label>
                  <textarea readonly rows="8" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-500 outline-none leading-relaxed">{{ selectedItem?.deskripsi }}</textarea>
                </div>
              </div>

              <!-- Kolom Kanan -->
              <div class="flex flex-col gap-6">
                <div>
                  <label class="mb-2 block text-sm font-bold text-[#1a2b4c]">Bukti Kegiatan</label>
                  <div @click="openImage(selectedItem?.gambar || 'dummy.jpeg')" class="flex h-[260px] w-full items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-white p-5 cursor-pointer group transition hover:bg-gray-50">
                    <div class="flex h-36 w-36 flex-shrink-0 items-center justify-center rounded-lg bg-gray-300 group-hover:scale-105 transition-all shadow-sm">
                      <svg class="h-14 w-14 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                    </div>
                  </div>
                </div>
                <div>
                  <label class="mb-1 block text-sm font-bold text-[#1a2b4c]">Catatan Penolakan Sebelumnya</label>
                  <p class="mb-2 text-[10px] text-gray-400">Berikut adalah catatan penolakan sebelumnya (Catatan kosong apabila submisi belum pernah ditolak)</p>
                  <div class="w-full rounded-lg border border-red-300 bg-[#fecaca] p-4 text-sm font-medium text-red-600 min-h-[120px]">
                    {{ selectedItem?.status === 'Ditolak' ? (selectedItem?.alasan_tolak || 'Data tidak lengkap atau format gambar kurang jelas.') : '' }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="mt-10 flex justify-end gap-4">
              <button @click="showDetailModal = false" class="rounded-lg border border-gray-300 bg-white px-8 py-2.5 text-sm font-bold text-gray-500 transition hover:bg-gray-50 hover:text-gray-700">Kembali</button>
              <button @click="handleApprove" class="rounded-lg bg-[#51d28c] px-8 py-2.5 text-sm font-bold text-white transition hover:bg-[#3fb874] hover:shadow-md">Setuju</button>
              <button @click="openReject(null)" class="rounded-lg bg-[#ef4444] px-8 py-2.5 text-sm font-bold text-white transition hover:bg-red-600 hover:shadow-md">Tolak</button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>

    <!-- Modal Penolakan -->
    <RejectModal :show="showRejectModal" @close="showRejectModal = false" @submit="handleReject"/>

  </AdminLayout>
</template>