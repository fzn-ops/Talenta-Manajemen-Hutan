<script setup>
import { onBeforeUnmount, onMounted } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Hapus Data?'
  },
  message: {
    type: String,
    default: 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.'
  },
  isLoading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close', 'confirm']);

const handleKeyDown = (e) => {
  if (e.key === 'Escape' && props.show) {
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();
    if (document.activeElement instanceof HTMLElement) {
      document.activeElement.blur();
    }
    if (!props.isLoading) {
      emit('close');
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
      <div v-if="show" class="fixed inset-0 z-[120] flex items-center justify-center overflow-y-auto overscroll-contain bg-black/50 p-4 backdrop-blur-xs transition-opacity duration-200" @click.self="emit('close')">
        
        <Transition 
          enter-active-class="transition duration-300 ease-out delay-75" 
          enter-from-class="opacity-0 translate-y-4 scale-95" 
          enter-to-class="opacity-100 translate-y-0 scale-100" 
          leave-active-class="transition duration-200 ease-in" 
          leave-from-class="opacity-100 translate-y-0 scale-100" 
          leave-to-class="opacity-0 translate-y-4 scale-95"
        >
          <div v-if="show" class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl text-center font-poppins">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-600">
              <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </div>
            <h3 class="font-poppins text-lg font-bold text-[#17334F]">
              <slot name="title">{{ title }}</slot>
            </h3>
            <p class="mt-2 font-inter text-sm text-[#64748b]">
              <slot name="message">{{ message }}</slot>
            </p>
            
            <div class="mt-6 flex items-center justify-center gap-3">
              <button 
                type="button"
                :disabled="isLoading"
                @click="emit('close')" 
                class="rounded-lg border border-[#d6e0ee] px-4 py-2.5 text-sm font-medium text-[#475569] hover:bg-slate-50 transition-colors disabled:opacity-50 cursor-pointer outline-none focus:outline-none focus:ring-0"
                style="outline: none !important; box-shadow: none !important;"
              >
                Batal
              </button>
              <button 
                type="button"
                :disabled="isLoading"
                @click="emit('confirm')" 
                class="flex items-center gap-2 rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition-colors shadow-sm disabled:opacity-50 cursor-pointer outline-none focus:outline-none focus:ring-0"
                style="outline: none !important; box-shadow: none !important;"
              >
                <svg v-if="isLoading" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span><slot name="confirm-text">{{ isLoading ? 'Menghapus...' : 'Hapus' }}</slot></span>
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>