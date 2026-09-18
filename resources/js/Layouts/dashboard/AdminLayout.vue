<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import SidebarAdmin from '@/Components/dashboard/admin/SidebarAdmin.vue';
import TopbarAdmin from '@/Components/dashboard/admin/TopbarAdmin.vue';
import ModalLogoutConfirmation from '@/Components/dashboard/ModalLogoutConfirmation.vue';

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
const showLogoutModal = ref(false);
const isLoggingOut = ref(false);
const mainContentRef = ref(null);

const handleLogout = () => {
	isLoggingOut.value = true;
	router.post(route('logout'), {}, {
		onFinish: () => {
			isLoggingOut.value = false;
			showLogoutModal.value = false;
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

let modalObserver = null;
let removeRouterListener = null;
let rafId = null;

const checkHasOpenModal = () => {
	if (typeof document === 'undefined') return false;
	// Look for dialogs that are currently open
	const openDialogs = document.querySelectorAll('dialog[open]');
	if (openDialogs.length > 0) return true;

	// Look for fixed full-screen modal overlays that are currently visible
	const modals = document.querySelectorAll('.fixed.inset-0.z-50, .fixed.inset-0.z-\\[60\\], .fixed.inset-0.z-\\[100\\]');
	for (const modal of modals) {
		const parentDialog = modal.closest('dialog');
		if (parentDialog && !parentDialog.open) continue;

		if (modal.offsetWidth === 0 && modal.offsetHeight === 0) continue;

		try {
			const style = window.getComputedStyle(modal);
			if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') {
				continue;
			}
		} catch {
			continue;
		}

		return true;
	}
	return false;
};

const updateModalScrollLock = () => {
	if (typeof document === 'undefined') return;
	const shouldLock = showLogoutModal.value || checkHasOpenModal() || (isMobile.value && showingSidebar.value);
	if (mainContentRef.value) {
		if (shouldLock) {
			mainContentRef.value.style.overflowY = 'hidden';
		} else {
			mainContentRef.value.style.overflowY = 'auto';
		}
	}
};

const scheduleModalCheck = () => {
	if (rafId) cancelAnimationFrame(rafId);
	rafId = requestAnimationFrame(updateModalScrollLock);
};

watch([showLogoutModal, showingSidebar], scheduleModalCheck);

onMounted(() => {
	updateViewport();
	window.addEventListener('resize', updateViewport);

	// MutationObserver to detect child / teleported modals and lock main scroll
	modalObserver = new MutationObserver(() => {
		scheduleModalCheck();
	});
	modalObserver.observe(document.body, { childList: true, subtree: true });

	// Clean up on Inertia navigation
	removeRouterListener = router.on('navigate', () => {
		scheduleModalCheck();
	});
});

onBeforeUnmount(() => {
	window.removeEventListener('resize', updateViewport);
	if (rafId) cancelAnimationFrame(rafId);
	if (modalObserver) {
		modalObserver.disconnect();
		modalObserver = null;
	}
	if (removeRouterListener) {
		removeRouterListener();
		removeRouterListener = null;
	}
	if (mainContentRef.value) {
		mainContentRef.value.style.overflowY = 'auto';
	}
});
</script>

<template>
	<div class="fixed inset-0 flex h-full w-full overflow-hidden bg-white font-poppins text-gray-800">
		<!-- Mobile Backdrop Overlay (Covers Topbar & Content, click to close) -->
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
				class="fixed inset-0 z-40 bg-[#102653]/35 backdrop-blur-xs cursor-pointer md:hidden"
				aria-hidden="true"
				@click="showingSidebar = false"
			></div>
		</Transition>

		<!-- Sidebar Admin Component -->
		<SidebarAdmin
			:collapsed="sidebarCollapsed"
			:mobile="isMobile"
			@navigate="isMobile && (showingSidebar = false)"
			@logout="showLogoutModal = true"
		/>

		<!-- Content Wrapper -->
		<div class="flex flex-col flex-1 min-w-0 h-full overflow-hidden bg-white">
			<!-- Topbar Admin Component -->
			<TopbarAdmin
				class="shrink-0"
				@toggle="toggleSidebar"
				@logout="showLogoutModal = true"
			/>

			<!-- Main Page Content -->
			<main
				ref="mainContentRef"
				class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden bg-white"
			>
				<slot />
			</main>
		</div>

		<!-- Modal Logout Confirmation -->
		<ModalLogoutConfirmation
			:show="showLogoutModal"
			:loading="isLoggingOut"
			@close="showLogoutModal = false"
			@confirm="handleLogout"
		/>
	</div>
</template>
