<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import MahasiswaLayout from '@/Layouts/dashboard/MahasiswaLayout.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import LoadingSuccessModal from '@/Components/dashboard/mahasiswa/LoadingSuccessModal.vue';

// ==========================================
// 1. DATA DUMMY & USER STATE
// ==========================================
const page = usePage();
const currentUser = computed(() => {
  const user = page.props.auth?.user;
  return { name: user?.nama || user?.name || 'Fauzan Fuadiansyah', nim: user?.nim || 'J0403231085' };
});

const activity = ref({
  id: 1,
  judul: 'Lomba Agustusan MNH',
  deadline: '16 Agustus 2026',
  kategori: ['Profesional', 'Bisnis', 'Birokrat', 'Akademisi'],
  deskripsi: [
    'Lorem ipsum dolor sit amet, cupidatat eiusmod duis ut. Magna dolore dolor ex elit sed non cillum do aliqua adipiscing ad.',
    'Commodo in non exercitation nulla enim qui aliquip nulla. Esse adipiscing ex anim fugiat labore mollit mollit.',
    'Velit quis aliqua sed dolore excepteur irure cillum labore duis.'
  ],
  link_pendaftaran: 'https://ipb.link/daftar-lomba-agustusan-manhut-26',
  gambar_utama: 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=800&auto=format&fit=crop',
  thumbnails: [null, null, null]
});

const getCategoryChipClass = (cat) => {
  switch (cat) {
    case 'Profesional':
      return 'bg-indigo-50 text-indigo-700 border-indigo-200';
    case 'Bisnis':
      return 'bg-amber-50 text-amber-700 border-amber-200';
    case 'Birokrat':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200';
    case 'Akademisi':
      return 'bg-purple-50 text-purple-700 border-purple-200';
    default:
      return 'bg-slate-100 text-slate-700 border-slate-200';
  }
};

// ==========================================
// 2. STATE FORM REGISTRASI & AUTOCOMPLETE
// ==========================================
const form = ref({
  nama_tim: '',
  peserta: [`${currentUser.value.name} - ${currentUser.value.nim}`], 
  bukti: null
});

const errors = ref({ nama_tim: '', peserta: [''], bukti: '' });

const dummyStudents = [
  { name: 'Fauzan Fuadiansyah', nim: 'J0403231085' },
  { name: 'Rio Ferddinansya', nim: 'J0403231086' },
  { name: 'Budi Santoso', nim: 'J0403231087' },
  { name: 'Siti Aminah', nim: 'J0403231088' },
  { name: 'Ahmad Maulana', nim: 'J0403231089' },
  { name: 'Kevin Pratama', nim: 'J0403231090' },
];

const activeDropdown = ref(null);

const getFilteredStudents = (query) => {
  if (!query) return dummyStudents;
  const lowerQuery = query.toLowerCase();
  return dummyStudents.filter(s => s.name.toLowerCase().includes(lowerQuery) || s.nim.toLowerCase().includes(lowerQuery));
};

const selectStudent = (index, student) => {
  form.value.peserta[index] = `${student.name} - ${student.nim}`;
  activeDropdown.value = null;
  errors.value.peserta[index] = '';
};

const addPeserta = () => {
  form.value.peserta.push('');
  errors.value.peserta.push('');
};

const removePeserta = (index) => {
  if (form.value.peserta.length > 1) {
    form.value.peserta.splice(index, 1);
    errors.value.peserta.splice(index, 1);
  }
};

// ==========================================
// 3. LOGIC UPLOAD FILE (MATCH PROFILE.VUE)
// ==========================================
const fileInput = ref(null);
const imagePreview = ref(null);
const isDragging = ref(false);
const previewingImage = ref(null);

const triggerUpload = () => fileInput.value?.click();

const processFile = (file) => {
  if (!file) return;

  if (!file.type.startsWith('image/')) {
    showToast('File harus berupa gambar (JPG, JPEG, PNG)!', 'error');
    return;
  }

  if (file.size > 10 * 1024 * 1024) {
    showToast('Ukuran gambar maksimal adalah 10MB!', 'error');
    return;
  }

  form.value.bukti = file;
  imagePreview.value = URL.createObjectURL(file);
  errors.value.bukti = ''; 
};

const handleFileUpload = (event) => {
  processFile(event.target.files?.[0]);
};

const handleDrop = (event) => {
  isDragging.value = false;
  processFile(event.dataTransfer?.files?.[0]);
};

const removeImage = () => {
  form.value.bukti = null;
  imagePreview.value = null;
  if (fileInput.value) fileInput.value.value = '';
};

const openImagePreview = (img) => {
  previewingImage.value = img;
};

const closeImagePreview = () => {
  previewingImage.value = null;
};

const handleKeyDown = (e) => {
  if (e.key === 'Escape' && previewingImage.value) {
    closeImagePreview();
  }
};

onMounted(() => {
  document.addEventListener('keydown', handleKeyDown);
});

onBeforeUnmount(() => {
  document.removeEventListener('keydown', handleKeyDown);
});

// ==========================================
// 4. TOAST, VALIDASI & SUBMIT LOGIC
// ==========================================
const toast = ref({ show: false, message: '', type: 'success' });
const showToast = (message, type = 'success') => {
  toast.value.show = false; 
  setTimeout(() => { toast.value = { show: true, message, type }; setTimeout(() => { toast.value.show = false; }, 3500); }, 50);
};

const isFeedbackModalOpen = ref(false);
const feedbackStatus = ref('loading');

const validateForm = () => {
  let isValid = true;
  errors.value.nama_tim = '';
  errors.value.bukti = '';
  errors.value.peserta = form.value.peserta.map(() => ''); 

  if (!form.value.nama_tim.trim()) {
    errors.value.nama_tim = 'Kolom nama tim harus diisi!';
    isValid = false;
  }

  form.value.peserta.forEach((p, index) => {
    if (!p.trim()) {
      errors.value.peserta[index] = 'Kolom nama peserta harus diisi!';
      isValid = false;
    }
  });

  if (!form.value.bukti) {
    errors.value.bukti = 'Bukti pendaftaran wajib diupload!';
    isValid = false;
  }

  return isValid;
};

const submitRegistration = () => {
  if (!validateForm()) {
    showToast('Mohon lengkapi formulir yang bertanda merah!', 'error');
    return;
  }
  
  isFeedbackModalOpen.value = true;
  feedbackStatus.value = 'loading';

  setTimeout(() => {
    feedbackStatus.value = 'success';
  }, 1500);
};

const handleFeedbackClose = () => {
  isFeedbackModalOpen.value = false;
  router.visit('/mahasiswa/aktivitas/list');
};
</script>

<template>
  <Head :title="activity.judul" />

  <MahasiswaLayout>
    <section class="mx-auto w-full max-w-[1200px] px-4 pt-8 pb-10 font-poppins sm:px-8 sm:pt-10 sm:pb-12">
      
      <!-- TOAST NOTIFICATION -->
      <Transition enter-active-class="transition-all transform duration-500 ease-out" enter-from-class="translate-x-12 opacity-0" enter-to-class="translate-x-0 opacity-100" leave-active-class="transition-all transform duration-300 ease-in" leave-from-class="translate-x-0 opacity-100" leave-to-class="translate-x-12 opacity-0">
        <div v-if="toast.show" class="fixed top-8 right-8 z-[100]"><ToastNotification :message="toast.message" :show="true" :type="toast.type" @close="toast.show = false"/></div>
      </Transition>

      <div class="space-y-6 sm:space-y-8 w-full">
        
        <!-- BREADCRUMB (SEJAJAR PERSIS SECARA HORIZONTAL DENGAN TOMBOL DASHBOARD SIDEBAR) -->
        <div class="flex h-11 items-center text-[13px] sm:text-[14px] font-poppins text-[#475569]">
          <Link href="/mahasiswa/aktivitas/list" class="font-medium text-[#64748b] hover:text-[#183669] hover:underline transition-colors">
            List Aktivitas
          </Link>
          <span class="mx-2 text-[#94a3b8]">/</span>
          <span class="font-semibold text-[#17334F] underline decoration-[#17334F]/50 underline-offset-4 truncate max-w-[240px] sm:max-w-none">{{ activity.judul }}</span>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-[360px_1fr] xl:grid-cols-[400px_1fr] xl:gap-12 items-start w-full">
          
          <!-- KOLOM KIRI (POSTER & THUMBNAILS) -->
          <div class="flex flex-col gap-4 w-full max-w-[380px] mx-auto lg:mx-0 lg:max-w-none">
            <div class="aspect-[3/4] w-full overflow-hidden rounded-[14px] bg-slate-100 shadow-sm border border-[#d6e0ee] relative">
              <img :src="activity.gambar_utama" class="h-full w-full object-cover" alt="Poster Aktivitas" />
            </div>
            <div class="grid grid-cols-3 gap-2.5 sm:gap-3">
              <div v-for="(thumb, index) in activity.thumbnails" :key="index" class="aspect-square w-full rounded-[10px] bg-[#e2e8f0] shadow-xs border border-[#d6e0ee]/50 overflow-hidden">
                <img v-if="thumb" :src="thumb" class="h-full w-full object-cover" />
              </div>
            </div>
          </div>

          <!-- KOLOM KANAN (INFO & FORM REGISTRASI) -->
          <div class="flex flex-col w-full min-w-0">
            
            <!-- HEADER DETAIL AKTIVITAS -->
            <div class="mb-5 sm:mb-6 border-b border-[#e2e8f0] pb-5 sm:pb-6 w-full">
              <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 sm:gap-4 mb-3">
                <h1 class="font-poppins text-2xl sm:text-[32px] lg:text-[36px] font-bold text-[#17334F] leading-tight">
                  {{ activity.judul }}
                </h1>
                <div class="inline-flex items-center gap-2 text-[12.5px] sm:text-[13.5px] font-semibold text-[#183669] shrink-0 sm:mt-1 font-poppins bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-full shadow-xs self-start sm:self-auto">
                  <svg class="h-4 w-4 text-[#183669]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                  <span>Deadline: {{ activity.deadline }}</span>
                </div>
              </div>

              <!-- KATEGORI PILLS / CHIPS (MATCH ACTIVITYSUBMISSION.VUE) -->
              <div class="mt-2.5 flex flex-wrap items-center gap-1.5">
                <span
                  v-for="cat in activity.kategori"
                  :key="cat"
                  :class="[
                    'inline-flex items-center justify-center rounded-full px-2.5 py-0.5 font-poppins text-[11px] sm:text-[11.5px] font-semibold border shadow-xs',
                    getCategoryChipClass(cat)
                  ]"
                >
                  {{ cat }}
                </span>
              </div>
            </div>

            <!-- DESKRIPSI -->
            <div class="mb-6 space-y-3 font-inter text-[13.5px] sm:text-[14px] leading-relaxed text-[#475569] w-full">
              <p v-for="(paragraf, index) in activity.deskripsi" :key="index">{{ paragraf }}</p>
            </div>

            <!-- LINK PENDAFTARAN -->
            <div class="mb-7 w-full">
              <p class="font-poppins text-[12.5px] sm:text-[13px] font-semibold text-[#183669] mb-1">Link Pendaftaran:</p>
              <a :href="activity.link_pendaftaran" target="_blank" class="font-inter text-[13.5px] sm:text-sm font-semibold text-[#2563eb] underline hover:text-[#1d4ed8] transition-colors break-all">
                {{ activity.link_pendaftaran }}
              </a>
            </div>

            <!-- ========================================== -->
            <!-- FORM REGISTRASI (MAX-WIDTH SERAGAM DENGAN DESKRIPSI) -->
            <!-- ========================================== -->
            <form @submit.prevent="submitRegistration" novalidate class="flex flex-col gap-4 sm:gap-5 w-full">
              
              <!-- Nama Tim -->
              <div class="w-full">
                <label class="block font-poppins text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
                  Nama Tim<span class="text-red-500">*</span>
                </label>
                <p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">Masukan nama tim kamu saat registrasi</p>
                <input 
                  type="text" 
                  v-model="form.nama_tim" 
                  placeholder="Masukan Nama Tim" 
                  @input="errors.nama_tim = ''"
                  class="mt-1 sm:mt-1.5 h-[42px] sm:h-[44px] w-full rounded-[10px] border bg-white px-3.5 font-inter text-[13px] sm:text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0"
                  :class="[
                    errors.nama_tim 
                      ? 'border-red-400 focus:border-red-500 bg-red-50/20' 
                      : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white'
                  ]" 
                />
                <p v-if="errors.nama_tim" class="mt-1 font-inter text-[11px] font-medium text-red-500 flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  {{ errors.nama_tim }}
                </p>
              </div>

              <!-- Nama Peserta -->
              <div class="w-full">
                <label class="block font-poppins text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
                  Nama Peserta<span class="text-red-500">*</span>
                </label>
                <p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">Masukkan nama peserta, termasuk namamu. Jika hanya kamu, cukup masukkan namamu.</p>
                
                <div class="flex flex-col gap-2.5 pt-1 w-full">
                  <div v-for="(peserta, index) in form.peserta" :key="index" class="flex flex-col gap-1 w-full">
                    <div class="flex items-start gap-2 sm:gap-2.5 w-full">
                      
                      <div class="relative flex-1 min-w-0">
                        <input 
                          type="text" 
                          v-model="form.peserta[index]" 
                          placeholder="Masukkan Nama - NIM" 
                          @input="activeDropdown = index; errors.peserta[index] = ''"
                          @focus="activeDropdown = index"
                          @blur="activeDropdown = null"
                          class="mt-1 sm:mt-1.5 h-[42px] sm:h-[44px] w-full rounded-[10px] border bg-white px-3.5 font-inter text-[13px] sm:text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0"
                          :class="[
                            errors.peserta[index] 
                              ? 'border-red-400 focus:border-red-500 bg-red-50/20' 
                              : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white'
                          ]" 
                        />
                        
                        <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="transform scale-95 opacity-0 translate-y-1" enter-to-class="transform scale-100 opacity-100 translate-y-0" leave-active-class="transition duration-100 ease-in" leave-from-class="transform scale-100 opacity-100 translate-y-0" leave-to-class="transform scale-95 opacity-0 translate-y-1">
                          <ul v-if="activeDropdown === index && getFilteredStudents(form.peserta[index]).length > 0" class="absolute left-0 right-0 top-full z-50 mt-1 max-h-48 overflow-y-auto rounded-[10px] border border-[#d6e0ee] bg-white py-1.5 shadow-xl font-inter">
                            <li v-for="student in getFilteredStudents(form.peserta[index])" :key="student.nim" @mousedown.prevent="selectStudent(index, student)" class="cursor-pointer px-4 py-2 text-sm transition-colors hover:bg-[#f0f4f9] hover:text-[#183669] flex flex-col">
                              <span class="font-bold text-gray-800">{{ student.name }}</span>
                              <span class="text-xs text-gray-500 font-medium">{{ student.nim }}</span>
                            </li>
                          </ul>
                        </Transition>
                      </div>
                      
                      <button 
                        v-if="index === 0" 
                        type="button" 
                        @click="addPeserta" 
                        class="mt-1 sm:mt-1.5 flex h-[42px] sm:h-[44px] w-[42px] sm:w-[44px] shrink-0 items-center justify-center rounded-[10px] border border-[#d6e0ee] bg-white text-[#183669] transition-colors hover:border-[#183669] hover:bg-[#fafcff] active:scale-95 focus:outline-none cursor-pointer shadow-xs"
                        title="Tambah Peserta"
                      >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                      </button>
                      
                      <button 
                        v-else 
                        type="button" 
                        @click="removePeserta(index)" 
                        class="mt-1 sm:mt-1.5 flex h-[42px] sm:h-[44px] w-[42px] sm:w-[44px] shrink-0 items-center justify-center rounded-[10px] border border-red-200 bg-white text-red-500 transition-colors hover:border-red-400 hover:bg-red-50 active:scale-95 focus:outline-none cursor-pointer shadow-xs"
                        title="Hapus Peserta"
                      >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" /></svg>
                      </button>
                    </div>
                    
                    <p v-if="errors.peserta[index]" class="mt-0.5 font-inter text-[11px] font-medium text-red-500 flex items-center gap-1">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                      {{ errors.peserta[index] }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- ========================================== -->
              <!-- Bukti Pendaftaran (DROPZONE & PREVIEW SAMA DENGAN MODAL) -->
              <!-- ========================================== -->
              <div class="w-full">
                <label class="block font-poppins text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
                  Bukti Pendaftaran<span class="text-red-500">*</span>
                </label>
                <p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">Masukan bukti pendaftaran berupa jpg/png/jpeg (MAX 10MB)</p>
                
                <input 
                  type="file" 
                  ref="fileInput" 
                  accept="image/png, image/jpeg, image/jpg" 
                  class="hidden" 
                  @change="handleFileUpload" 
                />

                <!-- DROP ZONE -->
                <div
                  @dragover.prevent="isDragging = true"
                  @dragleave.prevent="isDragging = false"
                  @drop.prevent="handleDrop"
                  :class="[
                    'mt-1.5 flex flex-col items-center justify-center rounded-[12px] border-2 border-dashed p-3.5 text-center transition-colors min-h-[145px] w-full',
                    errors.bukti
                      ? 'border-red-400 bg-red-50/20'
                      : isDragging
                        ? 'border-[#183669] bg-[#183669]/5'
                        : 'border-[#183669]/30 bg-[#fafcff] hover:border-[#183669]/60'
                  ]"
                >
                  <!-- State 1: Belum Ada Gambar -->
                  <div
                    v-if="!imagePreview"
                    class="flex flex-col items-center justify-center py-3"
                  >
                    <svg
                      class="h-9 w-9 text-[#8c9eb5]"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="1.8"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"
                      />
                    </svg>

                    <p class="mt-1.5 font-inter text-[12px] text-[#7188a3]">
                      Upload gambar atau seret gambar ke form ini
                    </p>

                    <p class="font-inter text-[10px] text-[#94a3b8] mt-0.5">
                      Format .JPG, .PNG, .JPEG • Maksimal 10MB
                    </p>

                    <button
                      type="button"
                      @click="triggerUpload"
                      class="mt-2.5 rounded-[8px] border border-[#a6b7cb] bg-white px-5 py-1.5 font-inter text-[12px] font-semibold text-[#5a718d] transition hover:bg-slate-50 hover:border-[#183669] hover:text-[#183669] shadow-xs cursor-pointer"
                    >
                      Upload
                    </button>
                  </div>

                  <!-- State 2: Sudah Ada Gambar -->
                  <div
                    v-else
                    class="w-full flex justify-center py-1"
                  >
                    <div class="relative aspect-[4/3] h-36 sm:h-40 rounded-[10px] border border-[#d6e0ee] bg-white overflow-hidden shadow-xs group">
                      <img
                        :src="imagePreview"
                        alt="Preview Bukti Pendaftaran"
                        class="h-full w-full object-cover"
                      />

                      <!-- ACTION OVERLAY (DELETE, PREVIEW, REPLACE) -->
                      <div class="absolute inset-0 bg-black/40 flex flex-col justify-between p-1.5 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                        <!-- DELETE -->
                        <div class="flex justify-end w-full">
                          <button
                            type="button"
                            @click.stop.prevent="removeImage"
                            class="flex h-6 w-6 items-center justify-center rounded-full bg-red-600/90 text-white shadow-sm hover:bg-red-700 hover:scale-110 active:scale-95 transition cursor-pointer"
                            title="Hapus Bukti"
                          >
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                          </button>
                        </div>

                        <!-- PREVIEW + REPLACE -->
                        <div class="flex items-center justify-center gap-2 w-full pb-0.5">
                          <button
                            type="button"
                            @click.stop.prevent="openImagePreview(imagePreview)"
                            class="flex h-7 w-7 items-center justify-center rounded-full bg-black/60 text-white shadow-sm hover:bg-black/85 hover:scale-110 active:scale-95 transition cursor-pointer"
                            title="Lihat Gambar Penuh"
                          >
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6" />
                            </svg>
                          </button>

                          <button
                            type="button"
                            @click.stop.prevent="triggerUpload"
                            class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-500/90 text-white shadow-sm hover:bg-amber-600 hover:scale-110 active:scale-95 transition cursor-pointer"
                            title="Ganti Gambar"
                          >
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                            </svg>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <p v-if="errors.bukti" class="mt-1 font-inter text-[11px] font-medium text-red-500 flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  {{ errors.bukti }}
                </p>
              </div>

              <!-- SUBMIT BUTTON -->
              <div class="mt-2 flex justify-end w-full">
                <button 
                  type="submit" 
                  class="w-full sm:w-auto rounded-[10px] bg-[#183669] px-8 py-2.5 font-poppins text-sm font-semibold text-white shadow-sm transition hover:bg-[#132b53] active:scale-[0.98] focus:outline-none cursor-pointer"
                >
                  Daftar Aktivitas
                </button>
              </div>

            </form>

          </div>
        </div>
      </div>
    </section>
    
    <!-- LIGHTBOX PREVIEW MODAL -->
    <Teleport to="body">
      <Transition enter-active-class="ease-out duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="previewingImage" class="fixed inset-0 z-[120] flex items-center justify-center bg-slate-900/80 backdrop-blur-md p-4 transition-all" @click="closeImagePreview">
          <Transition enter-active-class="ease-out duration-200" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="ease-in duration-150" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="previewingImage" class="relative flex items-center justify-center bg-transparent" @click.stop>
              <button
                type="button"
                @click="closeImagePreview"
                class="absolute top-3.5 right-3.5 z-20 flex h-9 w-9 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/85 backdrop-blur-xs shadow-md transition hover:scale-105 active:scale-95 focus:outline-none cursor-pointer"
                title="Tutup Preview"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
              <img :src="previewingImage" alt="Zoomed Preview" class="max-h-[82vh] max-w-[88vw] w-auto h-auto min-w-[280px] sm:min-w-[460px] rounded-xl object-contain shadow-2xl" />
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

    <!-- SUCCESS / FEEDBACK MODAL -->
    <LoadingSuccessModal 
      :show="isFeedbackModalOpen" 
      :status="feedbackStatus" 
      title="Registrasi Berhasil!" 
      message="Kamu telah sukses terdaftar dalam aktivitas ini. Silakan pantau pengumuman lebih lanjut."
      @close="handleFeedbackClose"
    />

  </MahasiswaLayout>
</template>