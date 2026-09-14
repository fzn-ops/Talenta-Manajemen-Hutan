<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
</script>

<template>
	<Head title="Dashboard Admin" />

	<AdminLayout>
		<template #header>
			<h1 class="text-xl font-bold text-gray-800">Dashboard</h1>
		</template>

		<!-- Content Page (Putih Bersih) -->
		<div class="w-full h-full min-h-[400px] rounded-xl bg-white border border-dashed border-gray-200 p-8 flex items-center justify-center text-gray-400 font-medium">
			Halaman Admin Dashboard
		</div>
	</AdminLayout>
</template>
