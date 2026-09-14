<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '@/Components/dashboard/admin/sidebar.vue';
import Topbar from '@/Components/dashboard/admin/topbar.vue';

const checkIsMobile = () => typeof window !== 'undefined' && window.innerWidth < 768;

const getSavedSidebarState = () => {
	if (typeof window === 'undefined') return true;
	if (window.innerWidth < 768) return false;
	const saved = localStorage.getItem('sidebar_expanded');
	if (saved !== null) {
		return saved === 'true';
	}
	return true;
};

const isMobile = ref(checkIsMobile());
const showingSidebar = ref(getSavedSidebarState());
const isLoggingOut = ref(false);

const handleLogout = () => {
	isLoggingOut.value = true;
	router.post(route('logout'), {}, {
		onFinish: () => {
			isLoggingOut.value = false;
			showingSidebar.value = false;
		}
	});
};

const updateViewport = () => {
	const nextIsMobile = window.innerWidth < 768;

	if (nextIsMobile !== isMobile.value) {
		if (nextIsMobile) {
			showingSidebar.value = false;
		} else {
			showingSidebar.value = getSavedSidebarState();
		}
	}

	isMobile.value = nextIsMobile;
};

const toggleSidebar = () => {
	showingSidebar.value = !showingSidebar.value;
	if (!isMobile.value) {
		localStorage.setItem('sidebar_expanded', showingSidebar.value ? 'true' : 'false');
	}
};

const sidebarCollapsed = computed(() => !showingSidebar.value);

onMounted(() => {
	updateViewport();
	window.addEventListener('resize', updateViewport);
});

onBeforeUnmount(() => {
	window.removeEventListener('resize', updateViewport);
});
</script>

<template>
	<div class="fixed inset-0 flex h-full w-full overflow-hidden bg-white font-poppins text-gray-800">
		<!-- Mobile Backdrop Overlay (Click to close) -->
		<Transition
			enter-active-class="ease-out duration-300"
			enter-from-class="opacity-0"
			enter-to-class="opacity-100"
			leave-active-class="ease-in duration-200"
			leave-from-class="opacity-100"
			leave-to-class="opacity-0"
		>
			<div
				v-if="isMobile && showingSidebar"
				class="fixed inset-0 z-40 bg-black/40 backdrop-blur-xs cursor-pointer md:hidden"
				aria-hidden="true"
				@click="showingSidebar = false"
			></div>
		</Transition>

		<!-- Sidebar Component -->
		<Sidebar
			:collapsed="sidebarCollapsed"
			:mobile="isMobile"
			@navigate="isMobile && (showingSidebar = false)"
			@logout="handleLogout"
		/>

		<!-- Content Wrapper -->
		<div class="flex flex-col flex-1 min-w-0 h-full overflow-hidden bg-white">
			<!-- Topbar Component -->
			<Topbar
				@toggle="toggleSidebar"
				@logout="handleLogout"
			/>

			<!-- Main Page Content (Putih Bersih) -->
			<main class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden bg-white p-6 sm:p-8">
				<slot />
			</main>
		</div>
	</div>
</template>
