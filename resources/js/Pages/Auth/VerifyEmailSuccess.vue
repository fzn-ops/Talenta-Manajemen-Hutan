<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col justify-center items-center bg-[#f4f7fa] p-4 font-poppins">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
            
            <!-- Ikon Centang Hijau (Success) -->
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-50 mb-6">
                <svg class="h-10 w-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h2 class="text-[22px] font-bold text-[#112340] mb-2">Verifikasi Berhasil!</h2>
            
            <p class="text-[#8c9eb5] text-[14px] leading-relaxed mb-8">
                Terima kasih, alamat email kamu sudah berhasil diverifikasi. Akun kamu sekarang sudah aman dan siap digunakan.
            </p>

            <!-- Tombol Lanjut ke Profile -->
            <Link 
                href="/mahasiswa/profile" 
                class="inline-flex w-full justify-center items-center rounded-xl bg-[#183669] px-6 py-3 text-[15px] font-semibold text-white transition-all hover:bg-[#122b54] active:scale-[0.98]"
            >
                Login
            </Link>

        </div>
    </div>
</template>

