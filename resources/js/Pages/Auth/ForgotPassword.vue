<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

defineProps({
  status: {
    type: String,
  },
});

const form = useForm({
  email: '',
});

const cooldown = ref(0);
let timer = null;

const startCooldown = () => {
  // Set waktu kedaluwarsa 60 detik dari sekarang (dalam milidetik)
  const expirationTime = Date.now() + 60 * 1000;
  localStorage.setItem('forgot_password_cooldown', expirationTime);

  cooldown.value = 60;
  if (timer) clearInterval(timer);

  timer = setInterval(() => {
    const remaining = Math.round((localStorage.getItem('forgot_password_cooldown') - Date.now()) / 1000);
    if (remaining > 0) {
      cooldown.value = remaining;
    } else {
      cooldown.value = 0;
      clearInterval(timer);
      localStorage.removeItem('forgot_password_cooldown');
    }
  }, 1000);
};

onMounted(() => {
  const expirationTime = localStorage.getItem('forgot_password_cooldown');
  if (expirationTime) {
    const remaining = Math.round((expirationTime - Date.now()) / 1000);
    if (remaining > 0) {
      cooldown.value = remaining;
      timer = setInterval(() => {
        const currentRemaining = Math.round((localStorage.getItem('forgot_password_cooldown') - Date.now()) / 1000);
        if (currentRemaining > 0) {
          cooldown.value = currentRemaining;
        } else {
          cooldown.value = 0;
          clearInterval(timer);
          localStorage.removeItem('forgot_password_cooldown');
        }
      }, 1000);
    } else {
      localStorage.removeItem('forgot_password_cooldown');
    }
  }
});

// Bersihkan interval saat komponen ditutup
onUnmounted(() => {
  if (timer) clearInterval(timer);
});

const submit = () => {
  if (cooldown.value > 0) return;

  form.post(route('password.email'), {
    onSuccess: () => {
      startCooldown();
    },
  });
};
</script>

<template>
  <Head title="Forgot Password" />

  <!-- Background hijau penuh layar dengan ornamen (Sama seperti Login) -->
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
    <!-- KARTU FORGOT PASSWORD                      -->
    <!-- ========================================== -->
    <div class="relative z-10 mx-4 w-full max-w-[420px] rounded-2xl bg-[#fcfcfb] p-8 shadow-2xl sm:p-10">
      
      <!-- Judul -->
      <h1 class="mb-4 text-center text-3xl font-extrabold text-[#152c5b]">
        Lupa Password
      </h1>

      <!-- Deskripsi -->
      <div class="mb-6 text-center text-[13.5px] font-medium leading-relaxed text-[#5c6b89]">
        Tidak masalah. Masukkan email Anda dan kami akan mengirimkan tautan untuk mengatur ulang password.
      </div>

      <!-- Pesan Sukses (Muncul kalau email berhasil dikirim) -->
      <div 
        v-if="status" 
        class="mb-6 rounded-lg bg-green-50 p-4 text-center text-sm font-bold text-green-600 border border-green-200"
      >
        {{ status }}
      </div>

      <form @submit.prevent="submit" class="flex flex-col gap-6">

        <!-- Pesan Error Validasi -->
        <div v-if="form.errors.email" class="rounded-lg bg-red-50 p-2 text-center text-xs font-bold text-red-600 border border-red-200">
          {{ form.errors.email }}
        </div>

        <!-- Input Email -->
        <div class="relative">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pb-2 text-[#152c5b]">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
            </svg>
          </div>
          <input 
            id="email"
            type="email" 
            v-model="form.email" 
            placeholder="Alamat Email" 
            class="w-full border-0 border-b-2 border-gray-400 bg-transparent py-2 pl-9 pr-4 text-sm font-semibold text-[#152c5b] placeholder-gray-400 transition-colors focus:border-[#152c5b] focus:ring-0" 
            required 
            autofocus
            autocomplete="username"
          />
        </div>

        <div class="mt-2 flex flex-col gap-4">
          <!-- Tombol Submit dengan Indikator Cooldown -->
          <button 
            type="submit" 
            :disabled="form.processing || cooldown > 0" 
            class="w-full rounded-full bg-[#4b857a] py-3 text-[15px] font-bold text-white shadow-md transition-all duration-300 hover:-translate-y-1 hover:bg-[#3b6b62] hover:shadow-lg focus:outline-none disabled:opacity-70 disabled:hover:translate-y-0"
          >
            <span v-if="cooldown > 0">Kirim Ulang dalam {{ cooldown }} detik</span>
            <span v-else>Kirim Link Reset</span>
          </button>

          <!-- Kembali ke Login -->
          <div class="text-center">
            <Link href="/login" class="text-[13px] font-bold text-[#152c5b] transition-colors hover:text-[#4b857a]">
              Kembali ke Halaman Login
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