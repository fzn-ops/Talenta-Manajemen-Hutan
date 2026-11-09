<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  src: {
    type: [String, null],
    default: null
  },
  alt: {
    type: String,
    default: 'Thumbnail'
  },
  imgClass: {
    type: String,
    default: 'h-full w-full object-cover'
  },
  dark: {
    type: Boolean,
    default: false
  },
  showLabel: {
    type: Boolean,
    default: true
  },
  labelText: {
    type: String,
    default: 'Tidak ada gambar'
  },
  iconClass: {
    type: String,
    default: 'h-7 w-7 sm:h-8 sm:w-8'
  },
  activityCard: {
    type: Boolean,
    default: false
  },
  fallbackClass: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['error', 'load']);

const isError = ref(false);

watch(() => props.src, () => {
  isError.value = false;
});

const computedSrc = computed(() => {
  if (!props.src) return null;
  const path = String(props.src).trim();
  if (!path) return null;
  if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('data:') || path.startsWith('blob:')) {
    return path;
  }
  const clean = path.replace(/^\/?(public\/|storage\/)+/, '');
  return `/storage/${clean}`;
});

const handleError = (e) => {
  isError.value = true;
  emit('error', e);
};

const handleLoad = (e) => {
  emit('load', e);
};
</script>

<template>
  <div :class="['h-full w-full overflow-hidden', $attrs.class && String($attrs.class).includes('absolute') ? 'absolute' : 'relative']">
    <!-- REAL IMAGE -->
    <img
      v-if="computedSrc && !isError"
      :src="computedSrc"
      :alt="alt"
      :class="imgClass"
      @error="handleError"
      @load="handleLoad"
    />

    <!-- FALLBACK NO IMAGE -->
    <div
      v-else
      :class="[
        'h-full w-full flex flex-col items-center select-none p-3 transition-colors duration-200',
        activityCard || dark ? 'justify-center pb-20 sm:pb-24' : 'justify-center',
        dark ? 'bg-[#15232d]' : 'bg-[#eef2f7]',
        fallbackClass
      ]"
    >
      <!-- Circular/Rounded Icon Container -->
      <div
        :class="[
          'flex items-center justify-center rounded-2xl transition-transform duration-200 group-hover:scale-105',
          dark 
            ? 'bg-white/5 border border-white/10 p-3 shadow-inner text-slate-400' 
            : 'bg-white/80 border border-[#d6e0ee] p-3 shadow-2xs text-[#8ea9cb]'
        ]"
      >
        <svg
          :class="iconClass"
          viewBox="28.65 -28.65 862 862"
          fill="currentColor"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path d="M862 804.696L614.089 556.785L504.764 447.46L175.164 117.86L117.859 60.5554L57.3041 0L0 57.3041L93.8812 151.592V686.836C93.8812 731.542 130.458 768.119 175.164 768.119H710.408L804.289 862L862 804.696ZM175.164 686.836V232.874L453.15 510.86L419.011 553.533L337.728 442.989L215.805 605.554H547.844L629.126 686.836H175.164ZM290.178 117.86L208.896 36.5771H744.141C788.846 36.5771 825.423 73.1542 825.423 117.86V653.104L744.141 571.822V117.86H290.178Z" />
        </svg>
      </div>

      <!-- Optional subtle label text -->
      <span
        v-if="showLabel"
        :class="[
          'mt-2 font-inter text-[11px] sm:text-[12px] font-medium tracking-wide text-center',
          dark ? 'text-slate-400' : 'text-[#5c738e]'
        ]"
      >
        {{ labelText }}
      </span>
    </div>
  </div>
</template>

