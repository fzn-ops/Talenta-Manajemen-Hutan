<script setup>
import { Link } from '@inertiajs/vue3';
const stats = [
  { value: '120', label: 'Profesional' },
  { value: '120', label: 'Bisnis' },
  { value: '120', label: 'Birokrat' },
  { value: '120', label: 'Akademisi' },
  { value: '120', label: 'Total Aktivitas' },
];
</script>

<template>
  <section class="relative w-full overflow-hidden bg-[#fcfcfb] py-16 lg:py-24 font-sans text-[#152c5b]">
    
    <!-- ========================================== -->
    <!-- ORNAMEN DEKORASI (Bintang & Garis Putus)   -->
    <!-- ========================================== -->
    
    <!-- Garis Putus (Atas Tengah) -->
<!--     <svg class="absolute -top-40 left-[35%] w-[500px] h-[500px] opacity-40 pointer-events-none" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
      <circle cx="50" cy="50" r="48" stroke="#152c5b" stroke-width="0.5" stroke-dasharray="2 3" fill="none" />
    </svg> -->

    <!-- Garis Putus (Bawah Tengah) -->
<!--     <svg class="absolute -bottom-48 left-[25%] w-[450px] h-[450px] opacity-40 pointer-events-none" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
      <circle cx="50" cy="50" r="48" stroke="#152c5b" stroke-width="0.5" stroke-dasharray="2 3" fill="none" />
    </svg> -->

    <!-- Garis Putus (Kanan Bawah) -->
    <svg class="absolute -bottom-24 -right-16 w-[350px] h-[350px] opacity-40 pointer-events-none" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
      <circle cx="50" cy="50" r="48" stroke="#152c5b" stroke-width="0.5" stroke-dasharray="2 3" fill="none" />
    </svg>

    <!-- Bintang (Kiri Atas) -->
    <svg class="absolute top-10 left-[5%] w-8 h-8 text-[#7bb896] pointer-events-none md:left-[8%]" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
      <path d="M12 0C12 6.6 17.4 12 24 12C17.4 12 12 17.4 12 24C12 17.4 6.6 12 0 12C6.6 12 12 6.6 12 0Z" />
    </svg>

    <!-- Bintang (Tengah, antara Teks & Gambar) -->
    <svg class="absolute top-[25%] left-[48%] w-7 h-7 text-[#7bb896] pointer-events-none lg:left-[51%]" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
      <path d="M12 0C12 6.6 17.4 12 24 12C17.4 12 12 17.4 12 24C12 17.4 6.6 12 0 12C6.6 12 12 6.6 12 0Z" />
    </svg>

    <!-- Bintang (Kiri Bawah) -->
    <svg class="absolute bottom-12 left-[12%] w-6 h-6 text-[#7bb896] pointer-events-none" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
      <path d="M12 0C12 6.6 17.4 12 24 12C17.4 12 12 17.4 12 24C12 17.4 6.6 12 0 12C6.6 12 12 6.6 12 0Z" />
    </svg>


    <!-- ========================================== -->
    <!-- KONTEN UTAMA (Inner Container)             -->
    <!-- ========================================== -->
    <div class="relative z-10 mx-auto flex w-full max-w-7xl flex-col items-center justify-between gap-12 px-6 lg:flex-row lg:gap-8 xl:px-10">
      
      <!-- KIRI: TEKS & STATISTIK -->
      <div class="flex w-full flex-col items-start lg:w-[55%]">
        
        <!-- Judul -->
        <h2 class="mb-6 text-4xl font-extrabold leading-[1.15] text-[#152c5b] md:text-5xl lg:text-[54px] lg:tracking-tight">
          Pelajari lebih lanjut <br />
          Talenta milikmu!
        </h2>

        <!-- Deskripsi -->
        <div class="mb-10 space-y-5 pr-4 text-[13px] font-medium leading-relaxed text-[#475569] sm:text-sm lg:pr-10">
          <p>
            Lorem ipsum dolor sit amet, voluptate ut nostrud consequat ut nulla. Reprehenderit 
            laborum consectetur ad laboris adipisicing nostrud in veniam anim. Et enim nostrud nisi 
            consectetur deserunt sunt eu sunt. Ut in aliquip in tempor consectetur deserunt culpa 
            voluptate.
          </p>
          <p>
            Ad velit elit sed dolor nisi nisi ad esse dolor. Ut et id cillum ex cillum ut est dolore. Ut 
            consequat elit nisi tempor non cillum in nisi sunt voluptate. Ut cupidatat aute ullamco 
            dolor minim sed mollit duis nisi in. Et irure aute dolor sunt quis commodo eu fugiat sunt.
          </p>
        </div>

        <!-- STATISTIK (Sejajar Lurus 5 Kolom pakai Flex) -->
        <div class="mb-12 flex w-full max-w-[500px] items-center justify-between gap-2">
          <div v-for="stat in stats" :key="stat.label" class="flex flex-col items-center text-center">
            <span class="text-2xl font-extrabold text-[#152c5b] sm:text-[28px]">
              {{ stat.value }}
            </span>
            <span class="mt-1 text-[9px] font-bold text-[#152c5b] sm:text-[11px]">
              {{ stat.label }}
            </span>
          </div>
        </div>

        <!-- Tombol -->
        <Link 
          href="#selengkapnya" 
          class="inline-flex items-center justify-center rounded-full bg-[#152c5b] px-9 py-3.5 text-sm font-bold tracking-wide text-white transition-all duration-300 hover:-translate-y-1 hover:bg-[#0f1f43] hover:shadow-lg"
        >
          Selengkapnya
        </Link>
      </div>

      <!-- KANAN: WADAH GAMBAR (KOTAK) -->
      <div class="flex w-full justify-center lg:w-[45%] lg:justify-end">
        <div class="relative flex w-full max-w-[520px] aspect-[4/3.2] items-center justify-center overflow-hidden rounded-[2.5rem] bg-[#e6efeb] shadow-sm">
          
          <!-- Teks Placeholder (Hilang kalau gambar sukses dimuat) -->
          <span class="absolute text-sm font-bold text-[#7bb896]/60">
            [ Tempat Gambar Ilustrasi ]
          </span>

          <img 
            src="/images/ilustrasi-talenta.png" 
            alt="Ilustrasi Petualang Talenta" 
            class="relative z-10 h-full w-full object-cover transition-transform duration-500 hover:scale-[1.03]"
            @error="(e) => e.target.style.display = 'none'"
          />
        </div>
      </div>
    </div>
  </section>
</template>