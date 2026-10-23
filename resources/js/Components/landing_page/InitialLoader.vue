<script setup>
import { ref, onMounted } from 'vue';

const showPreloader = ref(false);
const isSlidingUp = ref(false);

onMounted(() => {
    const preloaderShown = sessionStorage.getItem('preloader_shown');

    if (!preloaderShown) {
        showPreloader.value = true;

        setTimeout(() => {
            isSlidingUp.value = true; // Picu kelas tirai geser ke atas
            setTimeout(() => {
                showPreloader.value = false;
                sessionStorage.setItem('preloader_shown', 'true');
            }, 1200);
        }, 1200);
    }
});
</script>

<template>
  <div 
    v-if="showPreloader" 
    id="premium-preloader" 
    :class="['fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-[#4b857a] w-full h-full origin-top', { 'luxury-curtain-up': isSlidingUp }]"
  >
    <!-- Konten Preloader -->
    <div class="relative flex flex-col items-center justify-center">
        <!-- Teks Brand: Talenta -->
        <h1 class="text-white text-2xl md:text-3xl font-light tracking-[0.4em] uppercase mb-6 animate-luxury-fade-up">
            Talenta<span class="font-bold text-white">Manhut</span>
        </h1>
        
        <!-- Garis Indikator -->
        <div class="w-[200px] h-[1px] bg-white/20 relative overflow-hidden">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 h-full bg-white animate-luxury-expand-line"></div>
        </div>
        
        <!-- Teks Loading -->
        <div class="absolute -bottom-10 text-[10px] tracking-[0.3em] text-white/70 font-mono uppercase animate-luxury-fade-delay">
            Menyiapkan Modul
        </div>
    </div>
  </div>
</template>

<style scoped>
.luxury-curtain-up {
    transform: translateY(-100%);
    transition: transform 1.2s cubic-bezier(0.77, 0, 0.175, 1);
}

/* Animasi Muncul Teks */
@keyframes luxuryFadeUp {
    0% { opacity: 0; transform: translateY(15px); }
    100% { opacity: 1; transform: translateY(0); }
}

.animate-luxury-fade-up {
    animation: luxuryFadeUp 1.2s ease-out forwards;
}

.animate-luxury-fade-delay {
    animation: luxuryFadeUp 1s ease-out 0.8s forwards;
    opacity: 0;
}

/* Animasi Garis Bergerak */
@keyframes luxuryExpandLine {
    0% { width: 0%; opacity: 0; }
    50% { width: 100%; opacity: 1; }
    100% { width: 100%; opacity: 0; }
}

.animate-luxury-expand-line {
    animation: luxuryExpandLine 2s cubic-bezier(0.65, 0, 0.35, 1) infinite;
}
</style>