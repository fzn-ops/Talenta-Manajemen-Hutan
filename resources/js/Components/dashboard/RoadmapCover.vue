<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  src: {
    type: [String, null],
    default: null
  },
  alt: {
    type: String,
    default: 'Roadmap'
  },
  category: {
    type: String,
    default: 'Roadmap'
  }
});

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

const getCardGradient = (cat) => {
  switch ((cat || '').toLowerCase().trim()) {
    case 'profesional':
      return 'from-[#143365] via-[#1a4484] to-[#2560ab]';
    case 'bisnis':
      return 'from-[#0f3d4c] via-[#16566c] to-[#1e788e]';
    case 'birokrasi':
    case 'birokrat':
      return 'from-[#22285e] via-[#313b82] to-[#4553a6]';
    case 'akademisi':
      return 'from-[#382a57] via-[#4d3a76] to-[#6a4f9d]';
    default:
      return 'from-[#143365] via-[#1a4484] to-[#2560ab]';
  }
};
</script>

<template>
  <div class="relative h-full w-full overflow-hidden">
    <!-- REAL IMAGE IF AVAILABLE AND NOT BROKEN -->
    <img
      v-if="computedSrc && !isError"
      :src="computedSrc"
      :alt="alt"
      class="h-full w-full object-cover"
      @error="isError = true"
    />

    <!-- SOFT GEOMETRIC MURAL PATTERN FALLBACK (DEFAULT SEPERTI SEBELUMNYA) -->
    <div
      v-else
      :class="['relative h-full w-full bg-gradient-to-br flex items-center justify-center overflow-hidden select-none', getCardGradient(category)]"
    >
      <!-- Geometric SVG Mural Pattern -->
      <svg
        class="absolute inset-0 h-full w-full object-cover pointer-events-none opacity-40 mix-blend-screen"
        viewBox="0 0 400 240"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <circle cx="360" cy="30" r="110" fill="white" fill-opacity="0.2" />
        <circle cx="40" cy="220" r="90" fill="white" fill-opacity="0.15" />
        <path d="M-20 60 L120 -10 L180 90 L60 140 Z" fill="white" fill-opacity="0.08" />
        <path d="M120 -10 L280 20 L240 130 L180 90 Z" fill="white" fill-opacity="0.12" />
        <path d="M280 20 L420 -30 L380 90 L240 130 Z" fill="white" fill-opacity="0.06" />
        <path d="M180 90 L240 130 L320 220 L160 200 Z" fill="white" fill-opacity="0.09" />
        <path d="M60 140 L180 90 L160 200 L40 230 Z" fill="white" fill-opacity="0.05" />
        <path d="M240 130 L380 90 L430 200 L320 220 Z" fill="white" fill-opacity="0.11" />
        <path d="M-10 180 C80 140, 160 220, 260 170 C330 130, 380 180, 420 160" stroke="white" stroke-opacity="0.22" stroke-width="1.5" stroke-dasharray="4 4" fill="none" />
        <path d="M-10 200 C90 160, 180 240, 280 190 C350 150, 390 200, 430 180" stroke="white" stroke-opacity="0.15" stroke-width="1.5" fill="none" />
      </svg>

      <!-- Clean Typography Content -->
      <div class="relative z-10 flex flex-col items-center justify-center text-center px-4">
        <span class="font-poppins text-[15px] sm:text-[16px] font-bold tracking-wide text-white drop-shadow-xs">
          {{ category || 'Roadmap' }}
        </span>
        <p class="mt-0.5 font-inter text-[11px] sm:text-[12px] font-medium text-white/80">
          Talenta Manajemen Hutan
        </p>
      </div>
    </div>
  </div>
</template>

