<script setup>
import { ref } from 'vue';
import { useForm, Link, Head } from '@inertiajs/vue3';

// Setup form Inertia untuk lempar data ke backend Laravel
const form = useForm({
  nim: '',
  password: '',
  remember: false,
});

// State untuk toggle mata password
const showPassword = ref(false);

const submit = () => {
  form.post('/login');
};
</script>

<template>
  <Head title="Login - Talenta" />

  <div class="relative flex min-h-screen w-full items-center justify-center overflow-hidden bg-[#4a7c73] font-sans px-4 py-8">

    <!-- ========================================== -->
    <!-- AREA SVG ORNAMEN RESPONSIVE                -->
    <!-- ========================================== -->
    <div class="pointer-events-none absolute -left-6 -top-6 z-0 sm:left-0 sm:top-0">
      <img src="/assets/images/icon_daun_login_kiri.svg" alt="" class="w-32 opacity-90 transition-all duration-300 sm:w-48 md:w-56 lg:w-64">
    </div>

    <div class="pointer-events-none absolute -right-5 top-4 z-0 sm:right-4 sm:top-8 md:right-1 md:top-1">
      <img src="/assets/images/garis_bintang_login_kanan.svg" alt="" class="w-24 transition-all duration-300 sm:w-32 md:w-40 lg:w-[26rem]">
    </div>

    <div class="pointer-events-none absolute bottom-4 -left-2 z-0 sm:bottom-8 sm:left-4 md:bottom-1 md:left-1">
      <img src="/assets/images/garis_bintang_login_kiri.svg" alt="" class="w-24 transition-all duration-300 sm:w-32 md:w-40 lg:w-[26rem]">
    </div>

    <div class="pointer-events-none absolute -bottom-6 -right-6 z-0 sm:bottom-0 sm:right-0">
      <img src="/assets/images/icon_daun_login_kanan.svg" alt="" class="w-32 opacity-90 transition-all duration-300 sm:w-48 md:w-56 lg:w-56">
    </div>

    <!-- ========================================== -->
    <!-- KARTU LOGIN (z-10 agar di atas ornamen)    -->
    <!-- ========================================== -->
    <div class="relative z-10 w-full max-w-[500px] rounded-[24px] sm:rounded-[28px] bg-[#fcfcfb] p-7 sm:p-11 shadow-2xl transition-all">
      
      <h1 class="mb-8 sm:mb-9 text-center text-2xl sm:text-[32px] font-extrabold text-[#112340] tracking-tight">
        Login Talenta
      </h1>

      <form @submit.prevent="submit" class="flex flex-col gap-6">
        
        <!-- Input NIM -->
        <div class="relative flex flex-col">
          <div class="relative flex items-center">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex w-8 items-center justify-center text-[#112340]">
              <svg class="h-[18px] w-[18px]" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
              </svg>
            </div>
            <input 
              type="text" 
              v-model="form.nim" 
              placeholder="Username / NIM" 
              class="w-full border-0 border-b border-[#112340]/40 bg-transparent py-2.5 pl-10 pr-4 text-[14.5px] font-medium text-[#112340] placeholder-[#7188a3] transition-colors focus:border-[#112340] focus:ring-0 focus:outline-none"
              :class="form.errors.nim ? 'border-red-500 focus:border-red-500' : ''"
              required 
            />
          </div>
          <!-- PESAN ERROR NIM -->
          <span v-if="form.errors.nim" class="mt-1.5 text-[12px] font-medium text-red-500">
            {{ form.errors.nim }}
          </span>
        </div>

        <!-- Input Password -->
        <div class="relative flex flex-col">
          <div class="relative flex items-center">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex w-8 items-center justify-center">
              <img src="/assets/icons/password.svg" alt="Password icon" class="h-3.5 w-auto object-contain opacity-85" />
            </div>
            <input 
              :type="showPassword ? 'text' : 'password'" 
              v-model="form.password" 
              placeholder="Password" 
              class="w-full border-0 border-b border-[#112340]/40 bg-transparent py-2.5 pl-10 pr-10 text-[14.5px] font-medium text-[#112340] placeholder-[#7188a3] transition-colors focus:border-[#112340] focus:ring-0 focus:outline-none" 
              :class="form.errors.password ? 'border-red-500 focus:border-red-500' : ''"
              required 
            />
            <button 
              type="button" 
              @click="showPassword = !showPassword" 
              class="absolute inset-y-0 right-0 flex items-center pr-1 text-[#112340] transition-opacity hover:opacity-70 focus:outline-none cursor-pointer"
              :title="showPassword ? 'Sembunyikan password' : 'Lihat password'"
            >
              <img 
                v-if="showPassword" 
                src="/assets/icons/shown.svg" 
                alt="Lihat password" 
                class="h-3.5 w-auto object-contain opacity-75 transition hover:opacity-100"
              />
              <img 
                v-else 
                src="/assets/icons/hidden.svg" 
                alt="Sembunyikan password" 
                class="h-3.5 w-auto object-contain opacity-75 transition hover:opacity-100"
              />
            </button>
          </div>
          <span v-if="form.errors.password" class="mt-1.5 text-[12px] font-medium text-red-500">
            {{ form.errors.password }}
          </span>
        </div>

        <!-- Checkbox Remember Me & Lupa Password -->
        <div class="mt-1 flex items-center justify-between text-[13px] font-medium text-[#112340]">
          <label class="flex cursor-pointer items-center gap-2 select-none group">
            <input 
              type="checkbox" 
              v-model="form.remember" 
              class="h-4 w-4 rounded border-gray-400 text-[#4a7c73] transition-colors focus:ring-[#4a7c73] group-hover:border-[#4a7c73] cursor-pointer" 
            />
            <span class="group-hover:text-[#4a7c73] transition-colors">Remember me</span>
          </label>

          <Link href="/forgot-password" class="transition-colors hover:text-[#4a7c73] hover:underline">
            Lupa Password?
          </Link>
        </div>

        <!-- Tombol Aksi (Login & Beranda) -->
        <div class="mt-4 flex flex-col gap-4">
          <button 
            type="submit" 
            :disabled="form.processing" 
            class="w-full rounded-full bg-[#4a7c73] py-3 text-[15px] font-bold text-white shadow-md transition-all duration-200 hover:bg-[#3d6961] hover:shadow-lg focus:outline-none active:scale-[0.99] disabled:opacity-70 cursor-pointer"
          >
            <span v-if="form.processing">Mengecek...</span>
            <span v-else>Login</span>
          </button>

          <div class="text-center">
            <Link href="/" class="text-[13px] font-semibold text-[#112340] transition-colors hover:text-[#4a7c73]">
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
    -webkit-text-fill-color: #112340 !important;
}
</style>