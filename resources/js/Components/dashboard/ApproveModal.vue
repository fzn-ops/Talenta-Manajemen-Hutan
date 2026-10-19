<script setup>
import { onBeforeUnmount, onMounted } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Konfirmasi Persetujuan',
  },
  message: {
    type: String,
    default: '',
  },
  info: {
    type: String,
    default: '',
  },
  isLoading: {
    type: Boolean,
    default: false,
  },
  confirmText: {
    type: String,
    default: 'Ya, Setujui',
  },
  cancelText: {
    type: String,
    default: 'Batal',
  },
});

const emit = defineEmits(['close', 'confirm']);

const handleClose = () => {
  if (document.activeElement instanceof HTMLElement) {
    document.activeElement.blur();
  }
  if (!props.isLoading) {
    emit('close');
  }
};

const handleConfirm = () => {
  if (!props.isLoading) {
    emit('confirm');
  }
};

const handleKeyDown = (e) => {
  if (e.key === 'Escape' && props.show) {
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();
    handleClose();
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
      enter-active-class="ease-out duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="ease-in duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div 
        v-if="show" 
        class="fixed inset-0 z-[120] flex items-center justify-center overflow-y-auto overscroll-contain bg-slate-900/60 p-4 backdrop-blur-xs" 
        @click.self="handleClose"
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
            v-if="show"
            class="relative w-full max-w-md rounded-[18px] sm:rounded-[20px] bg-white p-6 sm:p-8 shadow-2xl text-center font-poppins border border-[#e2e8f0]"
            @click.stop
          >
            <!-- Icon Checklist Hijau Emerald -->
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
              <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>

            <!-- Title -->
            <h2 class="text-[17px] sm:text-[19px] font-bold text-[#183669] mb-3 px-2 leading-snug">
              {{ title }}
            </h2>

            <!-- Message / Slot -->
            <div class="text-xs sm:text-sm text-[#4d6786] font-inter mb-4 leading-relaxed">
              <slot>
                <p v-if="message">{{ message }}</p>
              </slot>
            </div>

            <!-- Info Box (Optional) -->
            <div 
              v-if="info || $slots.info" 
              class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50/70 p-3 text-left font-inter text-[12px] text-emerald-800 leading-relaxed"
            >
              <slot name="info">
                <span v-html="info"></span>
              </slot>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-center gap-3 sm:gap-4">
              <button 
                type="button"
                :disabled="isLoading"
                @click="handleClose" 
                class="min-w-[110px] sm:min-w-[130px] rounded-[10px] border border-[#d6e0ee] bg-white py-2.5 text-xs sm:text-sm font-bold text-[#183669] transition hover:border-[#183669] hover:bg-slate-50 cursor-pointer select-none outline-none focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ cancelText }}
              </button>
              <button 
                type="button"
                :disabled="isLoading"
                @click="handleConfirm" 
                class="min-w-[110px] sm:min-w-[130px] rounded-[10px] bg-[#10b981] py-2.5 text-xs sm:text-sm font-bold text-white transition hover:bg-[#059669] shadow-sm cursor-pointer select-none outline-none focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
              >
                <svg v-if="isLoading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ confirmText }}</span>
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
