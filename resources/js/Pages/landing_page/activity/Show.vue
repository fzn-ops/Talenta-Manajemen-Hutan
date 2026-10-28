<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/landing_page/main.vue';
import ImagePreviewModal from '@/Components/dashboard/ImagePreviewModal.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';

// Menerima props 'aktivitasDetail' yang dikirim dari ActivityController Laravel
const props = defineProps({
  aktivitasDetail: {
    type: Object,
    required: true
  }
});

// Toast notification state
const toast = ref({
  show: false,
  message: '',
  type: 'success'
});

const showToast = (message, type = 'success') => {
  toast.value = { show: true, message, type };
  setTimeout(() => {
    toast.value.show = false;
  }, 3000);
};

// Helper function untuk mengambil URL gambar yang aman
const getImageUrl = (path) => {
  if (!path) return 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=800&auto=format&fit=crop';
  if (typeof path === 'string' && (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('blob:') || path.startsWith('data:'))) {
    return path;
  }
  const cleanPath = String(path).replace(/^\/?(public\/|storage\/)+/, '');
  return `/storage/${cleanPath}`;
};

// Ambil semua gambar aktivitas
const allActivityImages = computed(() => {
  return Array.isArray(props.aktivitasDetail?.gambar_aktivitas) ? props.aktivitasDetail.gambar_aktivitas : [];
});

// URL poster yang sedang aktif ditampilkan di flyer utama
const activePosterUrl = ref('');

const mainPosterUrl = computed(() => {
  if (activePosterUrl.value) return activePosterUrl.value;
  const list = allActivityImages.value;
  if (list.length === 0) return 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=800&auto=format&fit=crop';
  const utama = list.find(g => g.utama == 1 || g.utama === true) || list[0];
  return getImageUrl(utama?.gambar);
});

// Helper modal preview gambar (lightbox)
const showImageModal = ref(false);
const previewImages = ref([]);
const previewIndex = ref(0);
const previewLabel = ref('Poster');

const openImagePreview = (target, label = 'Poster') => {
  if (!target) return;
  previewLabel.value = label;
  const list = allActivityImages.value.map(g => getImageUrl(g?.gambar)).filter(Boolean);
  if (list.length > 0) {
    previewImages.value = list;
    const targetUrl = typeof target === 'string' ? target : getImageUrl(target);
    const foundIdx = list.indexOf(targetUrl);
    previewIndex.value = foundIdx >= 0 ? foundIdx : 0;
  } else {
    previewImages.value = [typeof target === 'string' ? target : getImageUrl(target)];
    previewIndex.value = 0;
  }
  showImageModal.value = true;
};

// Format tanggal deadline
const formatTanggal = (dateString) => {
  if (!dateString) return '-';
  const options = { day: 'numeric', month: 'long', year: 'numeric' };
  return new Date(dateString).toLocaleDateString('id-ID', options);
};

// Fungsi chip kategori bidang
const getCategoryChipClass = (cat) => {
  switch (cat) {
    case 'Profesional': return 'bg-indigo-50 text-indigo-700 border-indigo-200';
    case 'Bisnis': return 'bg-amber-50 text-amber-700 border-amber-200';
    case 'Birokrat': return 'bg-emerald-50 text-emerald-700 border-emerald-200';
    case 'Akademisi': return 'bg-purple-50 text-purple-700 border-purple-200';
    default: return 'bg-slate-100 text-slate-700 border-slate-200';
  }
};

const truncatedTitle = computed(() => {
  const title = props.aktivitasDetail?.judul || '';
  const maxLength = 35;
  if (title.length > maxLength) {
    return title.substring(0, maxLength) + '...';
  }
  return title;
});

// Fitur Share
const handleShare = async () => {
  const currentUrl = typeof window !== 'undefined' ? window.location.href : '';
  const shareData = {
    title: props.aktivitasDetail?.judul || 'Aktivitas Talenta',
    text: `Lihat aktivitas: ${props.aktivitasDetail?.judul || ''}`,
    url: currentUrl
  };

  if (typeof navigator !== 'undefined' && navigator.share && navigator.canShare && navigator.canShare(shareData)) {
    try {
      await navigator.share(shareData);
      return;
    } catch (err) {
      if (err.name !== 'AbortError') {
        console.error('Error web share:', err);
      }
    }
  }

  // Fallback ke Clipboard
  if (typeof navigator !== 'undefined' && navigator.clipboard) {
    try {
      await navigator.clipboard.writeText(currentUrl);
      showToast('Tautan aktivitas berhasil disalin ke clipboard!', 'success');
      return;
    } catch (err) {
      console.error('Error copy clipboard:', err);
    }
  }

  // Fallback manual prompt
  prompt('Salin tautan aktivitas ini:', currentUrl);
};
</script>

<template>
  <MainLayout>
    <!-- TOAST NOTIFICATION -->
    <Transition enter-active-class="transition-all transform duration-500 ease-out" enter-from-class="translate-x-12 opacity-0" enter-to-class="translate-x-0 opacity-100" leave-active-class="transition-all transform duration-300 ease-in" leave-from-class="translate-x-0 opacity-100" leave-to-class="translate-x-12 opacity-0">
      <div v-if="toast.show" class="fixed top-8 right-8 z-[100]">
        <ToastNotification :message="toast.message" :show="true" :type="toast.type" @close="toast.show = false" />
      </div>
    </Transition>

    <div class="min-h-screen w-full bg-[#fcfcfb] pt-8 pb-14 font-poppins text-[#152c5b] lg:pt-14 lg:pb-20">
      <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 md:px-10">
        
        <!-- BREADCRUMB NAVIGATION -->
        <nav class="mb-5 flex items-center text-sm font-medium sm:text-base">
          <Link :href="route('activities')" class="text-[#64748b] transition-colors hover:text-[#183669] hover:underline">
            Aktivitas
          </Link>

          <span class="mx-2 text-gray-400">/</span>

          <span class="font-semibold text-[#17334F] underline decoration-2 underline-offset-4 truncate" :title="aktivitasDetail?.judul">
            {{ truncatedTitle }}
          </span>
        </nav>

        <!-- MAIN CONTENT (GRID LAYOUT: MATCH EXACTLY WITH ACTIVITYREGISTER) -->
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-[360px_1fr] xl:grid-cols-[400px_1fr] xl:gap-12 items-start w-full">
          
          <!-- KIRI: FLYER & THUMBNAILS (PERSIS SEPERTI ACTIVITYREGISTER) -->
          <div class="flex flex-col gap-3.5 sm:gap-4 w-full max-w-[380px] mx-auto lg:mx-0 lg:max-w-none">
            
            <!-- MAIN POSTER WITH ZOOM AND TYPE BADGE (NO HOVER SCALE ANIMATION) -->
            <div 
              @click="openImagePreview(mainPosterUrl)"
              class="group aspect-[3/4] w-full overflow-hidden rounded-[14px] bg-slate-100 shadow-sm border border-[#d6e0ee] relative cursor-pointer select-none"
            >
              <!-- Main Poster Image (Static - gausah animasi hover) -->
              <img 
                :src="mainPosterUrl" 
                class="h-full w-full object-cover" 
                :alt="aktivitasDetail?.judul || 'Poster Aktivitas'" 
              />
              
              <!-- Badge Jenis di Kiri Atas -->
              <div class="absolute left-3.5 top-3.5 z-10 pointer-events-none">
                <div
                  :class="[
                    'inline-flex h-[26px] items-center justify-center rounded-full px-2.5 sm:px-3 text-[10.5px] sm:text-[11px] font-bold uppercase tracking-wider shadow-xs select-none antialiased leading-none',
                    String(aktivitasDetail?.jenis || '').toLowerCase() === 'lomba'
                      ? 'bg-amber-500 text-white border border-amber-400/40'
                      : 'bg-blue-600 text-white border border-blue-400/30'
                  ]"
                >
                  <span>{{ String(aktivitasDetail?.jenis || '').toLowerCase() === 'lomba' ? 'Lomba' : 'Umum' }}</span>
                </div>
              </div>

              <!-- Badge Partisipan di Kanan Atas -->
              <div class="absolute right-3.5 top-3.5 z-10 pointer-events-none">
                <div class="inline-flex h-[26px] items-center justify-center gap-1.5 rounded-full bg-white/95 px-2.5 sm:px-3 text-[11px] font-bold text-[#183669] border border-slate-200/90 shadow-xs backdrop-blur-sm select-none antialiased leading-none">
                  <svg class="h-3.5 w-3.5 text-[#183669] shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                  </svg>
                  <span>{{ aktivitasDetail?.partisipan || 0 }}</span>
                </div>
              </div>
            </div>

            <!-- THUMBNAILS DI BAWAH POSTER (Langsung terlihat di layar pertama tanpa scroll) -->
            <div v-if="allActivityImages.length > 1" class="grid grid-cols-3 gap-2 sm:gap-2.5">
              <div 
                v-for="(imgObj, index) in allActivityImages" 
                :key="index" 
                @click="activePosterUrl = getImageUrl(imgObj?.gambar)"
                class="aspect-square w-full rounded-[10px] bg-[#e2e8f0] shadow-xs overflow-hidden cursor-pointer transition-all"
                :class="mainPosterUrl === getImageUrl(imgObj?.gambar) 
                  ? 'border-2 border-slate-700 opacity-100 ring-1 ring-slate-400/30' 
                  : 'border border-slate-200/90 opacity-60 hover:opacity-100'"
              >
                <img 
                  :src="getImageUrl(imgObj?.gambar)" 
                  class="h-full w-full object-cover" 
                  alt="Thumbnail Aktivitas" 
                />
              </div>
            </div>

            <!-- Petunjuk Ukuran Penuh -->
            <p class="text-center font-inter text-[11px] sm:text-[11.5px] text-slate-400 italic select-none">
              *Tekan gambar untuk melihat ukuran penuh
            </p>
          </div>

          <!-- KANAN: INFORMASI & DETAIL -->
          <div class="flex flex-col min-w-0">
            
            <!-- Judul & Deadline -->
            <div class="mb-4 flex flex-col items-start justify-between gap-3 border-b border-gray-200 pb-4 sm:flex-row sm:items-start lg:border-none lg:pb-0">
              <h1 class="text-2xl font-bold tracking-tight text-[#17334F] sm:text-3xl lg:text-[32px] leading-tight">
                {{ aktivitasDetail?.judul }}
              </h1>
              
              <!-- Deadline Badge -->
              <div class="flex shrink-0 items-center gap-1.5 text-xs sm:text-sm font-semibold text-[#183669] bg-[#f0f4f9] px-3 py-1.5 rounded-lg border border-[#d6e0ee]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span>Deadline: {{ formatTanggal(aktivitasDetail?.deadline) }}</span>
              </div>
            </div>

            <!-- Tags Kategori Tracks -->
            <div v-if="aktivitasDetail?.kategori && aktivitasDetail.kategori.length > 0" class="mb-5 flex flex-wrap items-center gap-1.5 sm:gap-2">
              <span 
                v-for="tag in aktivitasDetail.kategori" 
                :key="tag" 
                :class="[
                  'inline-flex items-center justify-center rounded-full px-3 py-0.5 font-inter text-[11px] font-semibold border shadow-xs', 
                  getCategoryChipClass(tag)
                ]"
              >
                {{ tag }}
              </span>
            </div>

            <!-- Deskripsi Rich Text (Dukungan HTML dari Editor / Formatting) -->
            <div class="mb-8 w-full">
              <h2 class="mb-2 font-poppins text-[15px] font-bold text-[#17334F]">
                Deskripsi Aktivitas
              </h2>
              <div 
                class="font-inter text-[14px] leading-relaxed text-[#475569] w-full rich-text-content"
                v-html="aktivitasDetail?.deskripsi || 'Tidak ada deskripsi.'"
              ></div>
            </div>

            <!-- Tombol Aksi (Link Daftar, Ikuti Aktivitas, dan Tombol Share Bundar) -->
            <div class="mt-auto flex flex-wrap items-center justify-start gap-3 sm:justify-end sm:gap-3.5 pt-5 border-t border-gray-100">
              <a 
                v-if="aktivitasDetail?.link_pendaftaran"
                :href="aktivitasDetail.link_pendaftaran" 
                target="_blank"
                rel="noopener noreferrer"
                class="rounded-full bg-[#183669] px-7 py-2.5 text-xs sm:text-sm font-bold text-white shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#122b54] hover:shadow-md cursor-pointer"
              >
                Link Daftar
              </a>
              
              <Link 
                :href="route('mahasiswa.aktivitas.pendaftaran', aktivitasDetail?.id)"
                class="rounded-full bg-[#183669] px-7 py-2.5 text-xs sm:text-sm font-bold text-white shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#122b54] hover:shadow-md cursor-pointer"
              >
                Ikuti Aktivitas
              </Link>

              <!-- Tombol Share (Bundar Berisi Ikon Share) -->
              <button 
                type="button"
                @click="handleShare"
                title="Bagikan aktivitas ini"
                class="flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-full bg-[#183669] text-white shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#122b54] hover:shadow-md cursor-pointer shrink-0"
              >
                <img 
                  src="/assets/icons/share_icon.svg" 
                  alt="Share" 
                  class="h-4 w-4 sm:h-5 sm:w-5 object-contain"
                />
              </button>
            </div>

          </div>
          
        </div>
      </div>
      
    </div>

    <!-- MODAL GAMBAR (LIGHTBOX) TERPUSAT -->
    <ImagePreviewModal
      :show="showImageModal"
      :images="previewImages"
      :initial-index="previewIndex"
      :label="previewLabel"
      @close="showImageModal = false"
    />
  </MainLayout>
</template>

<style scoped>
.rich-text-content :deep(p) {
  margin-bottom: 0.75rem;
  line-height: 1.65;
}
.rich-text-content :deep(p:last-child) {
  margin-bottom: 0;
}
.rich-text-content :deep(b),
.rich-text-content :deep(strong) {
  font-weight: 700;
  color: #0f2347;
}
.rich-text-content :deep(i),
.rich-text-content :deep(em) {
  font-style: italic;
}
.rich-text-content :deep(u) {
  text-decoration: underline;
}
.rich-text-content :deep(s),
.rich-text-content :deep(strike) {
  text-decoration: line-through;
}
.rich-text-content :deep(ul) {
  display: block;
  list-style-type: disc;
  padding-left: 1.5rem;
  margin: 0.5rem 0;
}
.rich-text-content :deep(ol) {
  display: block;
  list-style-type: decimal;
  padding-left: 1.5rem;
  margin: 0.5rem 0;
}
.rich-text-content :deep(li) {
  display: list-item;
  margin: 0.2rem 0;
}
.rich-text-content :deep(a) {
  color: #2563eb;
  text-decoration: underline;
  font-weight: 500;
  word-break: break-all;
  transition: color 0.15s ease;
}
.rich-text-content :deep(a:hover) {
  color: #1d4ed8;
}
.rich-text-content :deep(blockquote) {
  display: block;
  border-left: 3.5px solid #183669;
  background: #f1f5f9;
  padding: 0.5rem 1rem;
  margin: 0.65rem 0;
  color: #334155;
  font-style: italic;
  border-radius: 0 6px 6px 0;
}
.rich-text-content :deep(code) {
  background: #e2e8f0;
  color: #b91c1c;
  padding: 0.15rem 0.35rem;
  border-radius: 4px;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  font-size: 0.875em;
}
.rich-text-content :deep(hr) {
  border: 0;
  height: 1px;
  background: #cbd5e1;
  margin: 1rem 0;
}
</style>