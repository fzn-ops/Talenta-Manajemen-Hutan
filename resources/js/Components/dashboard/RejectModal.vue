<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
  show: Boolean,
  isApproved: {
    type: Boolean,
    default: false,
  },
  isLoading: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close', 'submit']);
const reason = ref('');
const customReason = ref('');

// Resizing State for Textarea (Drag-to-resize)
const MIN_REASON_HEIGHT = 80;
const MAX_REASON_HEIGHT = 260;
const customReasonHeight = ref(80);
const customReasonRef = ref(null);
const isResizing = ref(false);

const startResize = (e) => {
  if (e.type === 'mousedown') {
    e.preventDefault();
  }
  e.stopPropagation();
  isResizing.value = true;
  if (typeof document !== 'undefined') {
    document.body.style.userSelect = 'none';
  }

  const startY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;
  const startHeight = customReasonRef.value ? customReasonRef.value.offsetHeight : customReasonHeight.value;

  const onMouseMove = (moveEvent) => {
    if (!isResizing.value) return;
    if (moveEvent.cancelable && moveEvent.type.includes('touch')) {
      moveEvent.preventDefault();
    }
    const clientY = moveEvent.type.includes('touch') ? moveEvent.touches[0].clientY : moveEvent.clientY;
    const newHeight = Math.max(MIN_REASON_HEIGHT, Math.min(MAX_REASON_HEIGHT, startHeight + (clientY - startY)));
    customReasonHeight.value = newHeight;
  };

  const onMouseUp = (upEvent) => {
    upEvent?.stopPropagation();
    isResizing.value = false;
    if (typeof document !== 'undefined') {
      document.body.style.userSelect = '';
    }
    window.removeEventListener('mousemove', onMouseMove);
    window.removeEventListener('mouseup', onMouseUp);
    window.removeEventListener('touchmove', onMouseMove);
    window.removeEventListener('touchend', onMouseUp);
  };

  window.addEventListener('mousemove', onMouseMove);
  window.addEventListener('mouseup', onMouseUp);
  window.addEventListener('touchmove', onMouseMove, { passive: false });
  window.addEventListener('touchend', onMouseUp);
};

// Reset state when modal is closed
watch(() => props.show, (newVal) => {
  if (!newVal) {
    reason.value = '';
    customReason.value = '';
    customReasonHeight.value = 80;
    isOpen.value = false;
  }
});

// State untuk custom dropdown
const isOpen = ref(false);
const options = [
  'Data Kurang Lengkap',
  'Bukti Pendaftaran Kurang Jelas',
  'Lainnya'
];

const selectReason = (opt) => {
  if (props.isLoading) return;
  reason.value = opt;
  isOpen.value = false; // Tutup dropdown setelah milih
  if (opt !== 'Lainnya') {
    customReason.value = '';
  }
};

const canSubmit = computed(() => {
  if (!reason.value) return false;
  if (reason.value === 'Lainnya') {
    return customReason.value.trim().length > 0;
  }
  return true;
});

const handleSubmit = () => {
  if (!canSubmit.value || props.isLoading) return;
  const finalReason = reason.value === 'Lainnya' ? customReason.value.trim() : reason.value;
  emit('submit', finalReason);
};

const handleClose = () => {
  if (props.isLoading) return;
  if (document.activeElement instanceof HTMLElement) {
    document.activeElement.blur();
  }
  emit('close');
};

const handleKeyDown = (e) => {
  if (e.key === 'Escape' && props.show) {
    if (props.isLoading) return;
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();
    if (document.activeElement instanceof HTMLElement) {
      document.activeElement.blur();
    }
    if (isOpen.value) {
      isOpen.value = false;
    } else {
      handleClose();
    }
  }
};

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown, true);
});

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeyDown, true);
});
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="show" class="fixed inset-0 z-[120] flex items-center justify-center overflow-y-auto overscroll-contain bg-slate-900/60 p-4 backdrop-blur-xs transition-opacity duration-200" @click.self="handleClose">
        
        <Transition
          enter-active-class="transition duration-300 ease-out delay-75"
          enter-from-class="opacity-0 translate-y-4 scale-95"
          enter-to-class="opacity-100 translate-y-0 scale-100"
          leave-active-class="transition duration-200 ease-in"
          leave-from-class="opacity-100 translate-y-0 scale-100"
          leave-to-class="opacity-0 translate-y-4 scale-95"
        >
          <div v-if="show" class="relative w-full max-w-md rounded-[18px] sm:rounded-[20px] bg-white p-6 sm:p-8 shadow-2xl text-center font-poppins border border-[#e2e8f0]">
            
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-600">
              <svg class="h-8 w-8" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2L1 21h22L12 2zm1 16h-2v-2h2v2zm0-4h-2V10h2v4z"/>
              </svg>
            </div>

            <h2 class="text-[17px] sm:text-[19px] font-bold text-[#183669] mb-3 px-2 leading-snug">
              {{ isApproved ? 'Batalkan Persetujuan & Tolak Pengajuan Ini?' : 'Apakah Kamu yakin Ingin Menolak Pengajuan Ini?' }}
            </h2>

            <div v-if="isApproved" class="mb-4 rounded-xl border border-amber-200 bg-amber-50/80 p-3 text-left font-inter text-[12px] text-amber-800 leading-relaxed">
              <strong>Peringatan:</strong> Pengajuan ini sebelumnya telah <strong>disetujui</strong> dan diterbitkan ke List Aktivitas. Menolak pengajuan ini akan otomatis <strong>mencabut/menghapus</strong> aktivitas terkait dari List Aktivitas.
            </div>

            <div class="text-left mb-6 relative">
              <label class="block text-xs text-red-500 mb-1.5 font-medium">*Masukan alasan dari penolakan</label>
              
              <!-- CUSTOM DROPDOWN -->
              <div class="relative">
                <!-- Tombol Dropdown -->
                <button 
                  type="button"
                  :disabled="isLoading"
                  @click="isOpen = !isOpen"
                  class="flex w-full items-center justify-between rounded-[10px] border px-3.5 py-2.5 text-sm font-medium outline-none transition-colors cursor-pointer bg-white disabled:opacity-50 disabled:cursor-not-allowed"
                  :class="isOpen ? 'border-[#183669] text-gray-800' : 'border-[#d6e0ee] text-gray-700 hover:border-[#8ea9cb]'"
                >
                  <span :class="!reason ? 'text-gray-400 font-normal text-xs sm:text-sm' : 'text-gray-800 text-xs sm:text-sm'">
                    {{ reason || 'Pilih alasan penolakan' }}
                  </span>
                  
                  <svg 
                    class="h-4 w-4 text-gray-500 transition-transform duration-200 shrink-0"
                    :class="isOpen ? 'rotate-180' : 'rotate-0'"
                    fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>

                <!-- Overlay transparan buat nutup dropdown kalau user klik di luar kotak opsi -->
                <div v-if="isOpen" @click="isOpen = false" class="fixed inset-0 z-10"></div>

                <!-- List Pilihan (Muncul Melayang) -->
                <Transition
                  enter-active-class="transition duration-150 ease-out"
                  enter-from-class="opacity-0 translate-y-1 scale-95"
                  enter-to-class="opacity-100 translate-y-0 scale-100"
                  leave-active-class="transition duration-100 ease-in"
                  leave-from-class="opacity-100 translate-y-0 scale-100"
                  leave-to-class="opacity-0 translate-y-1 scale-95"
                >
                  <div v-if="isOpen" class="absolute left-0 right-0 top-full mt-1.5 z-20 overflow-hidden rounded-[10px] border border-[#d6e0ee] bg-white shadow-xl ring-1 ring-black/5">
                    <ul class="flex flex-col py-1">
                      <li 
                        v-for="(opt, index) in options" 
                        :key="index"
                        @click="selectReason(opt)"
                        class="cursor-pointer px-4 py-2.5 text-xs sm:text-sm font-medium text-gray-700 transition-colors hover:bg-slate-50 hover:text-[#183669]"
                      >
                        {{ opt }}
                      </li>
                    </ul>
                  </div>
                </Transition>
              </div>

              <!-- FIELD ALASAN LAINNYA (DENGAN DRAG-TO-RESIZE) -->
              <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-1"
              >
                <div v-if="reason === 'Lainnya'" class="mt-3">
                  <label class="block text-xs text-[#183669] mb-1 font-semibold">
                    Keterangan Alasan Lainnya<span class="text-red-500">*</span>
                  </label>
                  <div
                    class="relative w-full rounded-[10px] border border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus-within:border-[#183669] focus-within:bg-white transition-colors duration-150 overflow-hidden"
                  >
                    <textarea
                      ref="customReasonRef"
                      v-model="customReason"
                      :disabled="isLoading"
                      placeholder="Tuliskan alasan penolakan secara spesifik..."
                      :style="{ height: `${customReasonHeight}px`, minHeight: '80px', maxHeight: '260px' }"
                      class="w-full bg-transparent px-3.5 pt-2.5 pb-2 font-inter text-xs sm:text-sm text-[#1e3456] placeholder-[#94a3b8] focus:outline-none resize-none overflow-y-auto block border-0 ring-0 focus:ring-0 focus:border-0 shadow-none disabled:opacity-50"
                    ></textarea>
                    <!-- Bottom Full-Width Resize Handle -->
                    <div
                      @mousedown="startResize($event)"
                      @touchstart.passive="startResize($event)"
                      class="group/handle flex h-[16px] w-full cursor-ns-resize items-center justify-center bg-[#f8fafc] hover:bg-[#e8edf5] active:bg-[#dbe4f2] border-t border-[#edf2f7] transition-colors select-none"
                      style="touch-action: none;"
                      title="Tarik ke bawah untuk memperbesar kolom alasan"
                    >
                      <div class="h-[3.5px] w-10 rounded-full bg-[#cbd5e1] group-hover/handle:bg-[#183669]/60 group-hover/handle:w-14 group-active/handle:bg-[#183669] transition-all duration-200"></div>
                    </div>
                  </div>
                </div>
              </Transition>
              
            </div>

            <div class="flex items-center justify-center gap-3 sm:gap-4">
              <button 
                type="button"
                :disabled="isLoading"
                @click="handleClose" 
                class="min-w-[110px] sm:min-w-[130px] rounded-[10px] border border-[#d6e0ee] bg-white py-2.5 text-xs sm:text-sm font-bold text-[#183669] transition hover:border-[#183669] hover:bg-slate-50 cursor-pointer select-none outline-none focus:outline-none focus:ring-0 disabled:opacity-50 disabled:cursor-not-allowed"
                style="outline: none !important; box-shadow: none !important;"
              >
                Tidak
              </button>
              <button 
                type="button"
                @click="handleSubmit" 
                :disabled="!canSubmit || isLoading"
                class="min-w-[110px] sm:min-w-[130px] rounded-[10px] bg-[#ef4444] py-2.5 text-xs sm:text-sm font-bold text-white transition hover:bg-red-700 shadow-sm disabled:cursor-not-allowed disabled:bg-gray-200 disabled:text-gray-400 cursor-pointer select-none outline-none focus:outline-none focus:ring-0 flex items-center justify-center gap-2"
                style="outline: none !important; box-shadow: none !important;"
              >
                <svg v-if="isLoading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Yakin</span>
              </button>
            </div>
          </div>
        </Transition>

      </div>
    </Transition>
  </Teleport>
</template>