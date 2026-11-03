<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  roadmapTitle: {
    type: String,
    default: 'Roadmap',
  },
  previewUrl: {
    type: String,
    required: true,
  },
  downloadUrl: {
    type: String,
    required: true,
  },
});

const emit = defineEmits(['close']);

const isIframeLoading = ref(true);

watch(
  () => props.show,
  (val) => {
    if (val) {
      isIframeLoading.value = true;
    }
  }
);

const handleIframeLoad = () => {
  isIframeLoading.value = false;
};

const handleDownload = () => {
  const link = document.createElement('a');
  link.href = props.downloadUrl;
  link.setAttribute('download', '');
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

const openInNewTab = () => {
  window.open(props.previewUrl, '_blank');
};
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-black/65 backdrop-blur-xs font-inter select-none"
        @click.self="emit('close')"
      >
        <div
          class="relative w-full max-w-4xl rounded-2xl bg-white shadow-2xl border border-[#d6e0ee] flex flex-col max-h-[92vh] overflow-hidden animate-in fade-in zoom-in-95 duration-200"
          role="dialog"
          aria-modal="true"
        >
          <!-- MODAL HEADER -->
          <div class="flex items-center justify-between border-b border-[#f0f4f9] px-4 py-3 sm:px-6 sm:py-3.5 bg-[#fafcff]">
            <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
              <div class="min-w-0">
                <h3 class="font-poppins text-sm sm:text-base font-bold text-[#17334F] leading-tight truncate">
                  Pratinjau Sertifikat
                </h3>
                <p class="text-[11px] sm:text-xs text-[#7188a3] truncate mt-0.5">
                  {{ roadmapTitle }}
                </p>
              </div>
            </div>

            <button
              type="button"
              @click="emit('close')"
              class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition cursor-pointer shrink-0 ml-2"
              title="Tutup"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- MODAL BODY: EXACT A4 LANDSCAPE ASPECT RATIO (297:210) -->
          <div class="relative p-2 sm:p-4 bg-slate-100/60 overflow-hidden flex items-center justify-center">
            <!-- Loading Spinner Skeleton -->
            <div
              v-if="isIframeLoading"
              class="absolute inset-2 sm:inset-4 z-10 flex flex-col items-center justify-center rounded-xl bg-slate-100/90 backdrop-blur-xs"
            >
              <div class="h-8 w-8 sm:h-10 sm:w-10 animate-spin rounded-full border-3 border-[#183669] border-t-transparent"></div>
              <p class="mt-2 text-xs font-semibold text-[#183669]">Menyiapkan dokumen sertifikat...</p>
            </div>

            <!-- PDF Iframe: Exactly A4 Landscape Ratio (Aspect 297/210) to avoid any blank black space -->
            <div class="w-full aspect-[297/210] max-h-[68vh] rounded-xl overflow-hidden border border-[#d6e0ee] bg-white shadow-xs">
              <iframe
                :src="`${previewUrl}#toolbar=0&navpanes=0&scrollbar=0&view=Fit`"
                class="w-full h-full border-0"
                @load="handleIframeLoad"
              ></iframe>
            </div>
          </div>

          <!-- MODAL FOOTER -->
          <div class="flex flex-col sm:flex-row items-center justify-between gap-2.5 sm:gap-4 border-t border-[#f0f4f9] px-4 py-3 sm:px-6 sm:py-3.5 bg-white">
            <!-- Info Text -->
            <div class="text-[11px] sm:text-xs text-[#7188a3] text-center sm:text-left flex items-center justify-center sm:justify-start gap-1.5 order-2 sm:order-1 w-full sm:w-auto">
              <svg class="h-3.5 w-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>Dokumen resmi Departemen Manajemen Hutan IPB</span>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 w-full sm:w-auto justify-end order-1 sm:order-2">
              <button
                type="button"
                @click="openInNewTab"
                class="hidden md:inline-flex items-center justify-center gap-1.5 rounded-xl border border-[#d6e0ee] bg-white px-3.5 py-2 text-xs font-semibold text-[#17334F] hover:bg-slate-50 transition cursor-pointer"
                title="Buka pratinjau di tab baru"
              >
                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                </svg>
                <span>Buka di Tab Baru</span>
              </button>

              <button
                type="button"
                @click="emit('close')"
                class="flex-1 sm:flex-initial rounded-xl border border-[#d6e0ee] bg-white px-4 py-2.5 sm:py-2 text-xs font-semibold text-[#64748b] hover:bg-slate-50 hover:text-[#17334F] transition cursor-pointer text-center"
              >
                Tutup
              </button>

              <button
                type="button"
                @click="handleDownload"
                class="flex-2 sm:flex-initial inline-flex items-center justify-center gap-2 rounded-xl bg-[#183669] px-4 sm:px-5 py-2.5 sm:py-2 text-xs font-bold text-white shadow-xs hover:bg-[#122b54] active:scale-95 transition cursor-pointer text-center"
              >
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span class="truncate">Unduh Sertifikat (PDF)</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

