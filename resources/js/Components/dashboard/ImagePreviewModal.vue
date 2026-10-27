<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  images: {
    type: [Array, String, Object],
    default: () => [],
  },
  initialIndex: {
    type: Number,
    default: 0,
  },
  label: {
    type: String,
    default: 'Gambar',
  },
});

const emit = defineEmits(['close', 'change']);

const activeIndex = ref(0);
const isHoveringNav = ref(false);

// Format any image input (string, object with url/file/gambar) to a valid image src string
const getSrc = (item) => {
  if (!item) return '';
  if (typeof item === 'object' && item.url) return item.url;
  if (typeof item === 'object' && item.file) return URL.createObjectURL(item.file);
  if (typeof item === 'object' && item.gambar) return getSrc(item.gambar);
  if (typeof item === 'string') {
    if (item.startsWith('http://') || item.startsWith('https://') || item.startsWith('blob:') || item.startsWith('data:')) {
      return item;
    }
    if (item.startsWith('/storage/')) return item;
    if (item.startsWith('storage/')) return '/' + item;
    if (item.startsWith('/')) return item;
    return '/storage/' + item;
  }
  return `https://picsum.photos/seed/talenta/800/600`;
};

// Normalize images prop into an array
const normalizedImages = computed(() => {
  if (!props.images) return [];
  if (Array.isArray(props.images)) return props.images.filter(Boolean);
  return [props.images].filter(Boolean);
});

// Current active image URL
const currentImageSrc = computed(() => {
  if (normalizedImages.value.length === 0) return '';
  const item = normalizedImages.value[activeIndex.value] || normalizedImages.value[0];
  return getSrc(item);
});

const onImageError = (e) => {
  e.target.src = 'https://picsum.photos/seed/talenta/800/600';
};

const goToIndex = (idx) => {
  if (idx >= 0 && idx < normalizedImages.value.length) {
    activeIndex.value = idx;
    emit('change', idx);
  }
};

const prevImage = () => {
  if (normalizedImages.value.length <= 1) return;
  const newIdx = (activeIndex.value - 1 + normalizedImages.value.length) % normalizedImages.value.length;
  goToIndex(newIdx);
};

const nextImage = () => {
  if (normalizedImages.value.length <= 1) return;
  const newIdx = (activeIndex.value + 1) % normalizedImages.value.length;
  goToIndex(newIdx);
};

// Sync activeIndex whenever show becomes true or initialIndex changes
watch(
  () => [props.show, props.initialIndex],
  ([isOpen, idx]) => {
    if (isOpen) {
      const targetIdx = typeof idx === 'number' && idx >= 0 && idx < normalizedImages.value.length ? idx : 0;
      activeIndex.value = targetIdx;
      isHoveringNav.value = false;
    }
  },
  { immediate: true }
);

// Mobile swipe handling
const touchStartX = ref(0);
const touchEndX = ref(0);

const handleTouchStart = (e) => {
  if (!e.changedTouches || e.changedTouches.length === 0) return;
  touchStartX.value = e.changedTouches[0].clientX;
};

const handleTouchEnd = (e) => {
  if (!e.changedTouches || e.changedTouches.length === 0) return;
  touchEndX.value = e.changedTouches[0].clientX;
  const diff = touchEndX.value - touchStartX.value;
  if (Math.abs(diff) > 40 && normalizedImages.value.length > 1) {
    if (diff > 0) {
      prevImage();
    } else {
      nextImage();
    }
  }
};

// Keyboard navigation (Escape to close, ArrowLeft / ArrowRight to slide)
const handleKeyDown = (e) => {
  if (!props.show) return;
  if (e.key === 'Escape') {
    emit('close');
  } else if (e.key === 'ArrowLeft') {
    prevImage();
  } else if (e.key === 'ArrowRight') {
    nextImage();
  }
};

onMounted(() => {
  document.addEventListener('keydown', handleKeyDown);
});

onBeforeUnmount(() => {
  document.removeEventListener('keydown', handleKeyDown);
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
        class="fixed inset-0 z-[120] flex items-center justify-center bg-slate-900/80 backdrop-blur-md p-3 sm:p-4 transition-all"
        @click="emit('close')"
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
            class="relative flex flex-col items-center justify-center bg-transparent max-w-full max-h-full touch-pan-y select-none"
            @click.stop
            @touchstart="handleTouchStart"
            @touchend="handleTouchEnd"
            @mouseenter="isHoveringNav = true"
            @mouseleave="isHoveringNav = false"
          >
            <!-- Close button -->
            <button
              type="button"
              @click="emit('close')"
              class="absolute top-3.5 right-3.5 z-30 flex h-9 w-9 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/85 backdrop-blur-xs shadow-md transition hover:scale-105 active:scale-95 focus:outline-none cursor-pointer"
              title="Tutup Preview"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>

            <!-- Prev button (Desktop only: slide in from left on hover) -->
            <button
              v-if="normalizedImages.length > 1"
              type="button"
              @click="prevImage"
              :class="[
                'absolute left-3.5 top-1/2 -translate-y-1/2 z-30 hidden sm:flex h-10 w-10 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/85 backdrop-blur-xs shadow-lg transition-all duration-300 ease-out hover:scale-110 active:scale-95 focus:outline-none cursor-pointer',
                isHoveringNav ? 'opacity-100 translate-x-0 pointer-events-auto' : 'opacity-0 -translate-x-6 pointer-events-none'
              ]"
              :title="label + ' Sebelumnya'"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
              </svg>
            </button>

            <!-- Next button (Desktop only: slide in from right on hover) -->
            <button
              v-if="normalizedImages.length > 1"
              type="button"
              @click="nextImage"
              :class="[
                'absolute right-3.5 top-1/2 -translate-y-1/2 z-30 hidden sm:flex h-10 w-10 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/85 backdrop-blur-xs shadow-lg transition-all duration-300 ease-out hover:scale-110 active:scale-95 focus:outline-none cursor-pointer',
                isHoveringNav ? 'opacity-100 translate-x-0 pointer-events-auto' : 'opacity-0 translate-x-6 pointer-events-none'
              ]"
              :title="label + ' Berikutnya'"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
              </svg>
            </button>

            <!-- Image -->
            <img
              :src="currentImageSrc"
              :alt="label + ' Preview'"
              @error="onImageError"
              class="max-h-[74vh] sm:max-h-[82vh] max-w-[90vw] sm:max-w-[88vw] w-auto h-auto min-w-[260px] sm:min-w-[460px] rounded-xl object-contain shadow-2xl"
            />

            <!-- Bottom Indicator (Mobile: below image, zero obstruction | Desktop: floating bottom-4 on hover) -->
            <div
              v-if="normalizedImages.length > 1"
              :class="[
                'z-30 flex items-center justify-center gap-2 rounded-full bg-black/75 px-3.5 py-1.5 backdrop-blur-md text-white shadow-lg whitespace-nowrap select-none',
                'relative mt-3 sm:mt-0',
                'sm:absolute sm:bottom-4 sm:left-1/2 sm:-translate-x-1/2 sm:transition-all sm:duration-300 sm:ease-out',
                isHoveringNav ? 'sm:opacity-100 sm:translate-y-0 sm:pointer-events-auto' : 'sm:opacity-0 sm:translate-y-6 sm:pointer-events-none'
              ]"
            >
              <!-- Mobile Prev Arrow -->
              <button
                type="button"
                @click.stop="prevImage"
                class="sm:hidden flex h-6 w-6 items-center justify-center rounded-full text-white/80 hover:text-white hover:bg-white/10 active:scale-90 transition cursor-pointer -ml-1"
                :title="label + ' Sebelumnya'"
              >
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
              </button>

              <span class="whitespace-nowrap font-medium text-[11px] sm:text-xs">
                {{ label }} {{ activeIndex + 1 }} dari {{ normalizedImages.length }}
              </span>

              <div class="flex items-center gap-1.5 mx-0.5 shrink-0">
                <button
                  v-for="(_, dotIdx) in normalizedImages"
                  :key="dotIdx"
                  type="button"
                  @click="goToIndex(dotIdx)"
                  class="h-2 rounded-full transition-all cursor-pointer"
                  :class="activeIndex === dotIdx ? 'w-4 bg-white' : 'w-2 bg-white/40 hover:bg-white/70'"
                  :title="'Buka ' + label + ' ' + (dotIdx + 1)"
                />
              </div>

              <!-- Mobile Next Arrow -->
              <button
                type="button"
                @click.stop="nextImage"
                class="sm:hidden flex h-6 w-6 items-center justify-center rounded-full text-white/80 hover:text-white hover:bg-white/10 active:scale-90 transition cursor-pointer -mr-1"
                :title="label + ' Berikutnya'"
              >
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
