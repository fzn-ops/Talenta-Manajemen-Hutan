<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';

// Setup form Inertia untuk lempar data ke backend Laravel
const form = useForm({
  nim: '',
  password: '',
  remember: false,
});

// State untuk toggle mata password
const showPassword = ref(false);

const submit = () => {
  // Ganti '/login' dengan rute post login kamu nanti
  form.post('/login');
};
</script>

<template>
  <!-- Background hijau penuh layar dengan overflow-hidden agar ornamen tidak bikin scrollbar -->
  <div class="relative flex min-h-screen w-full items-center justify-center overflow-hidden bg-[#4b857a] font-sans">

    <!-- ========================================== -->
    <!-- AREA SVG ORNAMEN RESPONSIVE                -->
    <!-- ========================================== -->
    
    <!-- 1. Ornamen Kiri Atas (Daun Kuning) -->
    <div class="pointer-events-none absolute -left-6 -top-6 z-0 sm:left-0 sm:top-0">
      <img src="/assets/images/icon_daun_login_kiri.svg" alt="" class="w-32 opacity-90 transition-all duration-300 sm:w-48 md:w-56 lg:w-58">
    </div>

    <!-- 2. Ornamen Kanan Atas (Garis & Bintang) -->
    <div class="pointer-events-none absolute -right-5 top-4 z-0 sm:right-4 sm:top-8 md:right-1 md:top-1">
      <img src="/assets/images/garis_bintang_login_kanan.svg" alt="" class="w-24 transition-all duration-300 sm:w-32 md:w-40 lg:w-[24rem]">
    </div>

    <!-- 3. Ornamen Kiri Bawah (Garis & Bintang) -->
    <div class="pointer-events-none absolute bottom-4 -left-2 z-0 sm:bottom-8 sm:left-4 md:bottom-1 md:left-1">
      <img src="/assets/images/garis_bintang_login_kiri.svg" alt="" class="w-24 transition-all duration-300 sm:w-32 md:w-40 lg:w-[24rem]">
    </div>

    <!-- 4. Ornamen Kanan Bawah (Daun Kuning) -->
    <div class="pointer-events-none absolute -bottom-6 -right-6 z-0 sm:bottom-0 sm:right-0">
      <img src="/assets/images/icon_daun_login_kanan.svg" alt="" class="w-32 opacity-90 transition-all duration-300 sm:w-48 md:w-56 lg:w-48">
    </div>

    <!-- ========================================== -->
    <!-- KARTU LOGIN (z-10 agar di atas ornamen)    -->
    <!-- ========================================== -->
    <div class="relative z-10 mx-4 w-full max-w-[420px] rounded-2xl bg-[#fcfcfb] p-8 shadow-2xl sm:p-10">
      
      <!-- Judul -->
      <h1 class="mb-10 text-center text-3xl font-extrabold text-[#152c5b]">
        Login Talenta
      </h1>

      <form @submit.prevent="submit" class="flex flex-col gap-6">
        
        <div class="relative">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pb-2 text-[#152c5b]">
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
          </div>
          <input 
            type="text" 
            v-model="form.nim" 
            placeholder="Masukkan NIM" 
            class="w-full border-0 border-b-2 border-gray-400 bg-transparent py-2 pl-9 pr-4 text-sm font-semibold text-[#152c5b] placeholder-gray-400 transition-colors focus:border-[#152c5b] focus:ring-0" 
            required 
          />
        </div>

        <!-- Input Password -->
        <div class="relative mt-2">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pb-2 font-black tracking-widest text-[#152c5b]">
            ***
          </div>
          <input 
            :type="showPassword ? 'text' : 'password'" 
            v-model="form.password" 
            placeholder="Password" 
            class="w-full border-0 border-b-2 border-gray-400 bg-transparent py-2 pl-10 pr-10 text-sm font-semibold text-[#152c5b] placeholder-gray-400 transition-colors focus:border-[#152c5b] focus:ring-0" 
            required 
          />
          <button 
            type="button" 
            @click="showPassword = !showPassword" 
            class="absolute inset-y-0 right-0 flex items-center pb-2 text-[#152c5b] transition-colors hover:text-[#4b857a]"
          >
            <svg v-if="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
            </svg>
          </button>
        </div>

        <!-- Checkbox Remember Me & Lupa Password -->
        <div class="group mt-2 flex items-center justify-between text-[12.5px] font-bold text-[#152c5b]">
          <label class="flex cursor-pointer items-center gap-2">
            <input 
              type="checkbox" 
              v-model="form.remember" 
              class="h-4 w-4 rounded border-gray-400 text-[#4b857a] transition-colors focus:ring-[#4b857a] group-hover:border-[#4b857a]" 
            />
            <span>Remember me</span>
          </label>

          <Link href="/forgot-password" class="transition-colors hover:text-[#4b857a]">
            Lupa Password?
          </Link>
        </div>

        <!-- Tombol Aksi (Login & Beranda) -->
        <div class="mt-4 flex flex-col gap-4">
          <!-- Tombol Login -->
          <button 
            type="submit" 
            :disabled="form.processing" 
            class="w-full rounded-full bg-[#4b857a] py-3 text-[15px] font-bold text-white shadow-md transition-all duration-300 hover:-translate-y-1 hover:bg-[#3b6b62] hover:shadow-lg focus:outline-none disabled:opacity-70 disabled:hover:translate-y-0"
          >
            Login
          </button>

          <!-- Tombol Kembali ke Beranda -->
          <div class="text-center">
            <Link href="/" class="text-[13px] font-bold text-[#152c5b] transition-colors hover:text-[#4b857a]">
              &larr; Kembali ke Beranda
            </Link>
          </div>
        </div>

      </form>
    </div>

  </div>
</template>

<style scoped>
input:-webkit-autofill,
input:-webkit-autofill:hover, 
input:-webkit-autofill:focus, 
input:-webkit-autofill:active{
    -webkit-box-shadow: 0 0 0 30px #fcfcfb inset !important;
    -webkit-text-fill-color: #152c5b !important;
}
</style>    