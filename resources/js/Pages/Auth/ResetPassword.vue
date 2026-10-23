<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

// State untuk toggle lihat password
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
  <Head title="Reset Password" />

  <!-- Background hijau penuh layar dengan ornamen -->
  <div class="relative flex min-h-screen w-full items-center justify-center overflow-hidden bg-[#4b857a] font-sans">

    <!-- Ornamen SVG Kiri Atas -->
    <div class="pointer-events-none absolute -left-6 -top-6 z-0 sm:left-0 sm:top-0">
      <img src="/assets/images/icon_daun_login_kiri.svg" alt="" class="w-32 opacity-90 transition-all duration-300 sm:w-48 md:w-56 lg:w-58">
    </div>

    <!-- Ornamen SVG Kanan Atas -->
    <div class="pointer-events-none absolute -right-5 top-4 z-0 sm:right-4 sm:top-8 md:right-1 md:top-1">
      <img src="/assets/images/garis_bintang_login_kanan.svg" alt="" class="w-24 transition-all duration-300 sm:w-32 md:w-40 lg:w-[24rem]">
    </div>

    <!-- Ornamen SVG Kiri Bawah -->
    <div class="pointer-events-none absolute bottom-4 -left-2 z-0 sm:bottom-8 sm:left-4 md:bottom-1 md:left-1">
      <img src="/assets/images/garis_bintang_login_kiri.svg" alt="" class="w-24 transition-all duration-300 sm:w-32 md:w-40 lg:w-[24rem]">
    </div>

    <!-- Ornamen SVG Kanan Bawah -->
    <div class="pointer-events-none absolute -bottom-6 -right-6 z-0 sm:bottom-0 sm:right-0">
      <img src="/assets/images/icon_daun_login_kanan.svg" alt="" class="w-32 opacity-90 transition-all duration-300 sm:w-48 md:w-56 lg:w-48">
    </div>

    <!-- KARTU RESET PASSWORD -->
    <div class="relative z-10 mx-4 w-full max-w-[420px] rounded-2xl bg-[#fcfcfb] p-8 shadow-2xl sm:p-10">
      
      <h1 class="mb-4 text-center text-3xl font-extrabold text-[#152c5b]">
        Reset Password
      </h1>

      <div class="mb-6 text-center text-[13.5px] font-medium leading-relaxed text-[#5c6b89]">
        Silakan masukkan password baru Anda di bawah ini.
      </div>

      <form @submit.prevent="submit" class="flex flex-col gap-5">

        <!-- Pesan Error Global / Email -->
        <div v-if="form.errors.email" class="rounded-lg bg-red-50 p-2 text-center text-xs font-bold text-red-600 border border-red-200">
          {{ form.errors.email }}
        </div>

        <!-- Input Email (Readonly) -->
        <div class="flex flex-col">
          <div class="relative flex items-center">
            <div class="pointer-events-none absolute left-0 flex items-center text-[#152c5b]">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
              </svg>
            </div>
            <input 
              id="email"
              type="email" 
              v-model="form.email" 
              placeholder="Alamat Email" 
              class="w-full border-0 border-b-2 border-gray-400 bg-transparent py-2 pl-8 pr-4 text-sm font-semibold text-[#152c5b] placeholder-gray-400 transition-colors focus:border-[#152c5b] focus:ring-0" 
              readonly
            />
          </div>
        </div>

        <!-- Input Password Baru -->
        <div class="flex flex-col">
          <div class="relative flex items-center">
            <!-- Ikon Kunci Kiri -->
            <div class="pointer-events-none absolute left-0 flex items-center text-[#152c5b]">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
              </svg>
            </div>
            
            <!-- Field Input -->
            <input 
              id="password"
              :type="showPassword ? 'text' : 'password'" 
              v-model="form.password" 
              placeholder="Password Baru" 
              class="w-full border-0 border-b-2 border-gray-400 bg-transparent py-2 pl-8 pr-10 text-sm font-semibold text-[#152c5b] placeholder-gray-400 transition-colors focus:border-[#152c5b] focus:ring-0" 
              required 
              autofocus
              autocomplete="new-password"
            />

            <!-- Tombol Toggle Mata Kanan -->
            <button 
              type="button" 
              @click="showPassword = !showPassword" 
              class="absolute right-0 flex items-center pr-1 text-[#152c5b] transition-colors hover:text-[#4b857a] focus:outline-none"
            >
              <!-- Ikon Mata Terbuka -->
              <svg v-if="showPassword" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <!-- Ikon Mata Dicoret / Tertutup -->
              <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
              </svg>
            </button>
          </div>
          <!-- Pesan Error Password -->
          <div v-if="form.errors.password" class="mt-1 text-[12px] font-bold text-red-500">
            {{ form.errors.password }}
          </div>
        </div>

        <!-- Input Konfirmasi Password Baru -->
        <div class="flex flex-col">
          <div class="relative flex items-center">
            <!-- Ikon Kunci Kiri -->
            <div class="pointer-events-none absolute left-0 flex items-center text-[#152c5b]">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
              </svg>
            </div>

            <!-- Field Input -->
            <input 
              id="password_confirmation"
              :type="showPasswordConfirmation ? 'text' : 'password'" 
              v-model="form.password_confirmation" 
              placeholder="Konfirmasi Password Baru" 
              class="w-full border-0 border-b-2 border-gray-400 bg-transparent py-2 pl-8 pr-10 text-sm font-semibold text-[#152c5b] placeholder-gray-400 transition-colors focus:border-[#152c5b] focus:ring-0" 
              required 
              autocomplete="new-password"
            />

            <!-- Tombol Toggle Mata Kanan -->
            <button 
              type="button" 
              @click="showPasswordConfirmation = !showPasswordConfirmation" 
              class="absolute right-0 flex items-center pr-1 text-[#152c5b] transition-colors hover:text-[#4b857a] focus:outline-none"
            >
              <!-- Ikon Mata Terbuka -->
              <svg v-if="showPasswordConfirmation" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <!-- Ikon Mata Dicoret / Tertutup -->
              <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
              </svg>
            </button>
          </div>
          <!-- Pesan Error Konfirmasi Password -->
          <div v-if="form.errors.password_confirmation" class="mt-1 text-[12px] font-bold text-red-500">
            {{ form.errors.password_confirmation }}
          </div>
        </div>

        <div class="mt-2 flex flex-col gap-4">
          <button 
            type="submit" 
            :disabled="form.processing" 
            class="w-full rounded-full bg-[#4b857a] py-3 text-[15px] font-bold text-white shadow-md transition-all duration-300 hover:-translate-y-1 hover:bg-[#3b6b62] hover:shadow-lg focus:outline-none disabled:opacity-70 disabled:hover:translate-y-0"
          >
            Simpan Password Baru
          </button>
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