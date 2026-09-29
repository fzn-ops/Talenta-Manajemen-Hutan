<script setup>
import AdminLayout from '@/Layouts/dashboard/AdminLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import EditButtonTable from '@/Components/dashboard/EditButtonTable.vue';
import DeleteButtonTable from '@/Components/dashboard/DeleteButtonTable.vue';
import PreviewButtonTable from '@/Components/dashboard/PreviewButtonTable.vue';
import TablePagination from '@/Components/dashboard/TablePagination.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import SearchBarTable from '@/Components/dashboard/SearchBarTable.vue';
import ModalFormRoadmap from '@/Components/dashboard/admin/ModalFormRoadmap.vue';

const props = defineProps({
	roadmaps: {
		type: Array,
		default: () => [
			{
				id: 1,
				title: 'Persiapan Karir Software Engineer & Fullstack Developer',
				category: 'Profesional',
				task_count: 10,
				student_count: 57,
				updated_at: '19/10/2026',
			},
			{
				id: 2,
				title: 'Inkubasi Bisnis & Rintisan Startup Berbasis Hasil Hutan',
				category: 'Bisnis',
				task_count: 8,
				student_count: 42,
				updated_at: '18/10/2026',
			},
			{
				id: 3,
				title: 'Jalur Masuk ASN & Karir Birokrasi Lingkungan Hidup',
				category: 'Birokrasi',
				task_count: 12,
				student_count: 35,
				updated_at: '15/10/2026',
			},
			{
				id: 4,
				title: 'Persiapan Studi Lanjut S2/S3 & Publikasi Ilmiah Kehutanan',
				category: 'Akademisi',
				task_count: 6,
				student_count: 28,
				updated_at: '12/10/2026',
			},
			{
				id: 5,
				title: 'Sertifikasi Konsultan Lingkungan & AMDAL Profesional',
				category: 'Profesional',
				task_count: 14,
				student_count: 63,
				updated_at: '10/10/2026',
			},
			{
				id: 6,
				title: 'Pengembangan Usaha Ekowisata & Agroforestry Berkelanjutan',
				category: 'Bisnis',
				task_count: 9,
				student_count: 31,
				updated_at: '08/10/2026',
			},
		],
	},
});

const page = usePage();

// Toast State
const toast = ref({
	show: false,
	type: 'success',
	title: '',
	message: '',
});

const showToast = (type, title, message) => {
	toast.value = {
		show: true,
		type,
		title,
		message,
	};
};

const closeToast = () => {
	toast.value.show = false;
};

// Flash Session Watcher
watch(
	() => page.props?.flash,
	(flash) => {
		if (flash?.success) {
			showToast('success', 'Berhasil', flash.success);
		}
		if (flash?.error) {
			showToast('error', 'Gagal', flash.error);
		}
	},
	{ immediate: true, deep: true }
);

// Reactive Roadmaps List
const roadmapsList = ref([]);

const initRoadmapsData = () => {
	const sourceData = props.roadmaps || [];
	roadmapsList.value = sourceData.map((item, index) => ({
		id: item.id || index + 1,
		title: item.title || item.namaRoadmap || item.name || '',
		category: item.category || item.kategori || 'Profesional',
		task_count: item.task_count || item.taskCount || item.tasks || 10,
		student_count: item.student_count || item.studentCount || item.students || 0,
		updated_at: item.updated_at || item.diperbarui || '19/10/2026',
	}));
};

watch(
	() => props.roadmaps,
	() => {
		initRoadmapsData();
	},
	{ immediate: true, deep: true }
);

// Filter Category State & Logic
const isFilterOpen = ref(false);
const selectedCategories = ref([]);
const categoryFilterOptions = ['Profesional', 'Bisnis', 'Birokrasi', 'Akademisi'];
const filterContainerRef = ref(null);
const filterDropdownStyle = ref({});

const hasActiveFilters = computed(() => {
	return selectedCategories.value.length > 0;
});

const calculateFilterPlacement = () => {
	if (!filterContainerRef.value || typeof window === 'undefined') return;
	const rect = filterContainerRef.value.getBoundingClientRect();
	const windowWidth = window.innerWidth;
	const padding = 16; // Safe screen margin

	const desiredWidth = Math.min(260, windowWidth - padding * 2);

	const style = {
		width: `${desiredWidth}px`,
		maxWidth: `calc(100vw - ${padding * 2}px)`,
	};

	let targetScreenLeft = rect.right - desiredWidth;

	if (targetScreenLeft < padding) {
		targetScreenLeft = padding;
	}
	if (targetScreenLeft + desiredWidth > windowWidth - padding) {
		targetScreenLeft = windowWidth - padding - desiredWidth;
	}

	const relativeLeft = targetScreenLeft - rect.left;
	style.left = `${relativeLeft}px`;
	style.right = 'auto';

	const spaceBelow = window.innerHeight - rect.bottom;
	const spaceAbove = rect.top;
	const dropdownHeight = 260;

	if (spaceBelow < dropdownHeight && spaceAbove > spaceBelow) {
		style.bottom = 'calc(100% + 8px)';
		style.top = 'auto';
	} else {
		style.top = 'calc(100% + 8px)';
		style.bottom = 'auto';
	}

	filterDropdownStyle.value = style;
};

const toggleFilterDropdown = () => {
	const willOpen = !isFilterOpen.value;
	if (willOpen) {
		calculateFilterPlacement();
	}
	isFilterOpen.value = willOpen;
};

const closeFilterDropdown = () => {
	isFilterOpen.value = false;
};

const handleWindowChange = () => {
	if (isFilterOpen.value) {
		calculateFilterPlacement();
	}
};

const resetAllFilters = () => {
	selectedCategories.value = [];
};

const getCountByCategory = (category) => {
	return roadmapsList.value.filter((r) => (r.category || '').toLowerCase() === category.toLowerCase()).length;
};

const getCardGradient = (category) => {
	switch ((category || '').toLowerCase()) {
		case 'profesional':
			return 'from-[#143365] via-[#1a4484] to-[#2560ab]';
		case 'bisnis':
			return 'from-[#0f3d4c] via-[#16566c] to-[#1e788e]';
		case 'birokrasi':
			return 'from-[#22285e] via-[#313b82] to-[#4553a6]';
		case 'akademisi':
			return 'from-[#382a57] via-[#4d3a76] to-[#6a4f9d]';
		default:
			return 'from-[#143365] via-[#1a4484] to-[#2560ab]';
	}
};

// Loading State
const isLoading = ref(true);
onMounted(() => {
	initRoadmapsData();
	setTimeout(() => {
		isLoading.value = false;
	}, 200);
	document.addEventListener('click', closeFilterDropdown);
	window.addEventListener('resize', handleWindowChange, { passive: true });
	window.addEventListener('scroll', handleWindowChange, { passive: true });
});

onBeforeUnmount(() => {
	document.removeEventListener('click', closeFilterDropdown);
	window.removeEventListener('resize', handleWindowChange);
	window.removeEventListener('scroll', handleWindowChange);
});

// Search Query
const searchQuery = ref('');

// Filtered Roadmaps
const filteredRoadmaps = computed(() => {
	let list = [...roadmapsList.value];

	// Search Query
	if (searchQuery.value.trim()) {
		const q = searchQuery.value.toLowerCase().trim();
		list = list.filter(
			(r) =>
				(r.title && r.title.toLowerCase().includes(q)) ||
				(r.category && r.category.toLowerCase().includes(q))
		);
	}

	// Category Filter
	if (selectedCategories.value.length > 0) {
		list = list.filter((r) =>
			selectedCategories.value.some((c) => c.toLowerCase() === (r.category || '').toLowerCase())
		);
	}

	return list;
});

// Pagination State & Controls
const isMobileScreen = () => typeof window !== 'undefined' && window.innerWidth < 640;
const currentPage = ref(1);
const rowsPerPage = ref(isMobileScreen() ? 3 : 6);
const rowsOptions = [3, 6, 9, 12, 18];

const totalPages = computed(() => {
	const count = Math.ceil(filteredRoadmaps.value.length / rowsPerPage.value);
	return count > 0 ? count : 1;
});

const paginatedRoadmaps = computed(() => {
	const start = (currentPage.value - 1) * rowsPerPage.value;
	return filteredRoadmaps.value.slice(start, start + rowsPerPage.value);
});

watch([searchQuery, selectedCategories, rowsPerPage], () => {
	currentPage.value = 1;
});

// MODAL TAMBAH / EDIT ROADMAP
const isFormModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const selectedRoadmap = ref(null);

const openCreateModal = () => {
	isEditing.value = false;
	editingId.value = null;
	selectedRoadmap.value = { title: '', category: '', thumbnail: '' };
	isFormModalOpen.value = true;
};

const openEditModal = (roadmap) => {
	isEditing.value = true;
	editingId.value = roadmap.id;
	selectedRoadmap.value = { ...roadmap };
	isFormModalOpen.value = true;
};

const handlePreview = (roadmap) => {
	router.visit(`/admin/roadmap/${roadmap.id || 1}`);
};

const handleFormSubmit = (formData) => {
	const now = new Date();
	const formattedDate = `${String(now.getDate()).padStart(2, '0')}/${String(now.getMonth() + 1).padStart(2, '0')}/${now.getFullYear()}`;

	if (isEditing.value && editingId.value) {
		const targetIndex = roadmapsList.value.findIndex((r) => r.id === editingId.value);
		if (targetIndex !== -1) {
			roadmapsList.value[targetIndex] = {
				...roadmapsList.value[targetIndex],
				...formData,
				updated_at: formattedDate,
			};
		}
		isFormModalOpen.value = false;
		showToast('success', 'Berhasil Diperbarui', `Roadmap "${formData.title}" berhasil diperbarui.`);
	} else {
		const newId = roadmapsList.value.length > 0 ? Math.max(...roadmapsList.value.map((r) => r.id)) + 1 : 1;
		roadmapsList.value.unshift({
			id: newId,
			...formData,
			task_count: 0,
			student_count: 0,
			updated_at: formattedDate,
		});
		isFormModalOpen.value = false;
		showToast('success', 'Berhasil Ditambahkan', `Roadmap "${formData.title}" berhasil ditambahkan.`);
	}
};

// MODAL DELETE CONFIRMATION
const isDeleteModalOpen = ref(false);
const deletingRoadmap = ref(null);
const isDeleting = ref(false);

const openDeleteModal = (roadmap) => {
	deletingRoadmap.value = roadmap;
	isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
	if (isDeleting.value) return;
	isDeleteModalOpen.value = false;
	deletingRoadmap.value = null;
};

const confirmDeleteRoadmap = () => {
	if (!deletingRoadmap.value) return;
	const roadmap = deletingRoadmap.value;
	isDeleting.value = true;

	setTimeout(() => {
		roadmapsList.value = roadmapsList.value.filter((r) => r.id !== roadmap.id);
		isDeleting.value = false;
		isDeleteModalOpen.value = false;
		deletingRoadmap.value = null;
		showToast('success', 'Berhasil Dihapus', `Roadmap "${roadmap.title}" berhasil dihapus.`);
	}, 200);
};
</script>

<template>
	<Head title="Daftar Roadmap" />

	<AdminLayout>
		<section class="mx-auto w-full max-w-[1520px] px-4 py-6 font-poppins sm:px-6 sm:py-8 lg:px-8">
			<div class="space-y-6">
				<!-- Header Title & Subtitle -->
				<div class="space-y-1.5">
					<h1 class="mt-1 text-[34px] font-bold leading-[1.02] tracking-[-0.03em] text-[#17334F] sm:text-[42px] lg:text-[48px]">
						Daftar Roadmap
					</h1>
					<p class="mt-1.5 font-inter text-[14px] font-medium leading-tight text-[#4d6786] sm:text-[16px]">
						Lihat seberapa banyak mahasiswa yang mengikuti aktivitas Talenta!
					</p>
				</div>

				<!-- Action Bar (Search, Filter, Tambah Button) -->
				<div class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:gap-3">
					<!-- Search Input Component -->
					<SearchBarTable
						v-model="searchQuery"
						placeholder="Cari Aktivitas disini"
					/>

					<!-- Action Buttons Row -->
					<div class="flex items-center justify-end gap-2 sm:gap-3 w-full sm:w-auto shrink-0">
						<!-- Filter Button with Unified Dropdown -->
						<div ref="filterContainerRef" class="relative" @click.stop @keydown.escape="isFilterOpen = false">
							<button
								type="button"
								@click="toggleFilterDropdown"
								class="relative flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-[10px] border-2 bg-transparent text-[#183669] transition-colors focus:outline-none select-none cursor-pointer"
								:class="isFilterOpen || hasActiveFilters
									? 'border-[#183669]'
									: 'border-[#d6e0ee] hover:border-[#8ea9cb]'"
								title="Filter Kategori Roadmap"
							>
								<img
									src="/assets/icons/filter.svg"
									alt="Filter Icon"
									class="h-5 w-5 shrink-0 object-contain pointer-events-none"
								/>
								<!-- Red active indicator dot -->
								<span
									v-if="hasActiveFilters"
									class="absolute top-2.5 right-2.5 h-2 w-2 rounded-full bg-[#ef4444] ring-2 ring-[#eef2f7]"
								></span>
							</button>

							<!-- Filter Dropdown Menu (Kategori) -->
							<Transition
								enter-active-class="transition duration-150 ease-out"
								enter-from-class="transform scale-95 opacity-0 translate-y-1"
								enter-to-class="transform scale-100 opacity-100 translate-y-0"
								leave-active-class="transition duration-100 ease-in"
								leave-from-class="transform scale-100 opacity-100 translate-y-0"
								leave-to-class="transform scale-95 opacity-0 translate-y-1"
							>
								<div
									v-if="isFilterOpen"
									:style="filterDropdownStyle"
									class="absolute z-50 rounded-[14px] border border-[#d6e0ee] bg-white p-3.5 shadow-2xl ring-1 ring-black/10 font-inter"
								>
									<!-- Header with Reset All -->
									<div class="flex items-center justify-between border-b border-[#f0f4f9] pb-2">
										<p class="font-poppins text-xs font-bold text-[#183669]">
											Filter Kategori
										</p>
										<button
											v-if="hasActiveFilters"
											type="button"
											@click="resetAllFilters"
											class="font-inter text-[11px] font-semibold text-[#dc2626] hover:underline cursor-pointer"
										>
											Reset Semua
										</button>
									</div>

									<!-- List of Categories -->
									<div class="mt-2.5 max-h-[220px] overflow-y-auto space-y-1 pr-1">
										<label
											v-for="cat in categoryFilterOptions"
											:key="cat"
											class="flex items-center justify-between rounded-lg px-2 py-1.5 hover:bg-[#f8fafc] cursor-pointer transition select-none"
										>
											<div class="flex items-center gap-2.5">
												<input
													type="checkbox"
													:value="cat"
													v-model="selectedCategories"
													class="h-4 w-4 rounded border-[#cbd5e1] text-[#183669] focus:ring-0 focus:ring-offset-0 focus:outline-none focus-visible:outline-none cursor-pointer"
												/>
												<span class="text-xs font-medium text-[#334155]">
													{{ cat }}
												</span>
											</div>
											<span class="text-[11px] font-semibold text-[#64748b] bg-[#f1f5f9] px-1.5 py-0.5 rounded">
												{{ getCountByCategory(cat) }}
											</span>
										</label>
									</div>
								</div>
							</Transition>
						</div>

						<!-- Tambah Button -->
						<button
							type="button"
							@click="(e) => { e.currentTarget?.blur(); openCreateModal(); }"
							class="flex h-[46px] w-[46px] sm:w-auto shrink-0 items-center justify-center gap-2 rounded-[10px] bg-[#183669] px-0 sm:px-7 font-poppins text-[15px] font-semibold text-white shadow-sm transition hover:bg-[#122b54] active:scale-95 focus:outline-none select-none cursor-pointer"
							title="Tambah Roadmap"
						>
							<svg class="h-5 w-5 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
							</svg>
							<span class="hidden sm:inline">Tambah</span>
						</button>
					</div>
				</div>

				<!-- Cards Grid Section -->
				<div>
					<!-- Skeleton Loading State -->
					<div v-if="isLoading" class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 sm:gap-6">
						<div
							v-for="n in 6"
							:key="`skeleton-card-${n}`"
							class="flex flex-col justify-between rounded-[10px] border border-[#d6e0ee] bg-white p-4 sm:p-5 shadow-xs animate-pulse"
						>
							<div>
								<div class="h-[175px] sm:h-[190px] w-full rounded-[10px] bg-slate-200"></div>
								<div class="mt-4 flex items-center justify-between">
									<div class="h-4 w-16 rounded bg-slate-200"></div>
									<div class="h-6 w-12 rounded-full bg-slate-200"></div>
								</div>
								<div class="mt-3 space-y-2">
									<div class="h-4.5 w-full rounded bg-slate-200"></div>
									<div class="h-4.5 w-3/4 rounded bg-slate-200"></div>
								</div>
								<div class="mt-2.5 h-3.5 w-20 rounded bg-slate-200"></div>
							</div>
							<div class="mt-5 flex items-center justify-between border-t border-[#f1f5f9] pt-3">
								<div class="h-3 w-28 rounded bg-slate-200"></div>
								<div class="flex gap-1.5">
									<div class="h-8 w-8 rounded-lg bg-slate-200"></div>
									<div class="h-8 w-8 rounded-lg bg-slate-200"></div>
									<div class="h-8 w-8 rounded-lg bg-slate-200"></div>
								</div>
							</div>
						</div>
					</div>

					<!-- Real Cards Grid -->
					<div v-else-if="paginatedRoadmaps.length > 0" class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 sm:gap-6">
						<div
							v-for="item in paginatedRoadmaps"
							:key="`roadmap-${item.id}`"
							class="group flex flex-col justify-between rounded-[10px] border border-[#d6e0ee] bg-white p-4 sm:p-5 shadow-xs transition duration-200 hover:shadow-md hover:border-[#a6b7cb]"
						>
							<div>
								<!-- Top Cover Banner (Rounded [10px] as in Figma) -->
								<div class="relative flex h-[175px] sm:h-[190px] w-full items-center justify-center overflow-hidden rounded-[10px] bg-slate-100 shadow-inner">
									<img
										v-if="item.thumbnail"
										:src="item.thumbnail"
										:alt="item.title"
										class="h-full w-full object-cover"
									/>
									<!-- Soft Geometric Mural Banner Pattern -->
									<div
										v-else
										:class="['relative h-full w-full bg-gradient-to-br flex items-center justify-center overflow-hidden select-none', getCardGradient(item.category)]"
									>
										<!-- Geometric Abstract SVG Mural Background -->
										<svg
											class="absolute inset-0 h-full w-full object-cover pointer-events-none opacity-40 mix-blend-screen"
											viewBox="0 0 400 240"
											fill="none"
											xmlns="http://www.w3.org/2000/svg"
										>
											<!-- Ambient mesh shapes & geometric curves -->
											<circle cx="360" cy="30" r="110" fill="white" fill-opacity="0.2" />
											<circle cx="40" cy="220" r="90" fill="white" fill-opacity="0.15" />
											
											<!-- Modern geometric polygon facets -->
											<path d="M-20 60 L120 -10 L180 90 L60 140 Z" fill="white" fill-opacity="0.08" />
											<path d="M120 -10 L280 20 L240 130 L180 90 Z" fill="white" fill-opacity="0.12" />
											<path d="M280 20 L420 -30 L380 90 L240 130 Z" fill="white" fill-opacity="0.06" />
											<path d="M180 90 L240 130 L320 220 L160 200 Z" fill="white" fill-opacity="0.09" />
											<path d="M60 140 L180 90 L160 200 L40 230 Z" fill="white" fill-opacity="0.05" />
											<path d="M240 130 L380 90 L430 200 L320 220 Z" fill="white" fill-opacity="0.11" />

											<!-- Soft wavy decorative contour lines -->
											<path d="M-10 180 C80 140, 160 220, 260 170 C330 130, 380 180, 420 160" stroke="white" stroke-opacity="0.22" stroke-width="1.5" stroke-dasharray="4 4" fill="none" />
											<path d="M-10 200 C90 160, 180 240, 280 190 C350 150, 390 200, 430 180" stroke="white" stroke-opacity="0.15" stroke-width="1.5" fill="none" />
										</svg>

										<!-- Clean Typography Content -->
										<div class="relative z-10 flex flex-col items-center justify-center text-center px-4">
											<span class="font-poppins text-[15px] sm:text-[16px] font-bold tracking-wide text-white drop-shadow-xs">
												{{ item.category || 'Roadmap' }}
											</span>
											<p class="mt-0.5 font-inter text-[11px] sm:text-[12px] font-medium text-white/80">
												Talenta Manajemen Hutan
											</p>
										</div>
									</div>
								</div>

								<!-- Card Meta Bar: Tasks & Student Counter -->
								<div class="mt-4 flex items-center justify-between">
									<span class="font-inter text-[13px] font-semibold text-[#475569]">
										{{ item.task_count }} Task
									</span>
									<span class="inline-flex items-center gap-1.5 rounded-full bg-[#183669] px-2.5 py-1 font-inter text-[12px] font-semibold text-white shadow-xs">
										<svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
											<path d="M7 8a3 3 0 100-6 3 3 0 000 6zM14.5 9a2.5 2.5 0 100-5 2.5 2.5 0 000 5zM1.615 16.428a1.224 1.224 0 01-.569-1.175 6.002 6.002 0 0111.908 0c.058.467-.172.92-.57 1.174A9.953 9.953 0 017 18a9.953 9.953 0 01-5.385-1.572zM14.5 16h-.106c.07-.297.088-.611.048-.933a7.47 7.47 0 00-1.588-3.755 4.502 4.502 0 015.874 4.316.892.892 0 01-.413.372H14.5z" />
										</svg>
										<span>{{ item.student_count }}</span>
									</span>
								</div>

								<!-- Roadmap Title -->
								<h3
									class="mt-2.5 line-clamp-2 text-[16px] sm:text-[17px] font-bold leading-snug text-[#17334F] transition group-hover:text-[#183669]"
									:title="item.title"
								>
									{{ item.title }}
								</h3>

								<!-- Category -->
								<p class="mt-1 font-inter text-[12px] font-medium text-[#7188a3]">
									{{ item.category }}
								</p>
							</div>

							<!-- Card Footer: Last Updated & Action Buttons -->
							<div class="mt-5 flex items-center justify-between border-t border-[#f1f5f9] pt-3">
								<span class="font-inter text-[11px] font-normal text-[#94a3b8]">
									Diperbarui : {{ item.updated_at }}
								</span>
								<div class="flex items-center gap-1.5">
									<PreviewButtonTable :label="`Lihat detail ${item.title}`" @click="handlePreview(item)" />
									<EditButtonTable :label="`Edit ${item.title}`" @click="openEditModal(item)" />
									<DeleteButtonTable :label="`Hapus ${item.title}`" @click="openDeleteModal(item)" />
								</div>
							</div>
						</div>
					</div>

					<!-- Empty State -->
					<div
						v-else
						class="flex flex-col items-center justify-center rounded-[20px] border border-dashed border-[#d6e0ee] bg-white px-6 py-16 text-center"
					>
						<!-- <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-[#7188a3] mb-3">
							<svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.284a2.25 2.25 0 00-2.012 0L2.618 5.721A1.125 1.125 0 002 6.727v11.954c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" />
							</svg>
						</div> -->
						<h3 class="font-poppins text-[16px] font-bold text-[#17334F]">
							Roadmap tidak ditemukan
						</h3>
						<p class="mt-1 font-inter text-[13px] text-[#64748b] max-w-sm">
							Tidak ada data roadmap yang sesuai dengan kata kunci atau filter yang Anda pilih.
						</p>
						<button
							v-if="searchQuery || hasActiveFilters"
							type="button"
							@click="searchQuery = ''; resetAllFilters()"
							class="mt-4 rounded-lg bg-[#183669] px-4 py-2 font-poppins text-xs font-semibold text-white transition hover:bg-[#122b54] cursor-pointer"
						>
							Bersihkan Filter & Pencarian
						</button>
					</div>
				</div>

				<!-- Pagination Component -->
				<TablePagination
					v-if="filteredRoadmaps.length > 0"
					:current-page="currentPage"
					:total-pages="totalPages"
					:rows-per-page="rowsPerPage"
					:rows-options="rowsOptions"
					item-label="Cards"
					@update:current-page="currentPage = $event"
					@update:rows-per-page="rowsPerPage = $event; currentPage = 1"
				/>
			</div>
		</section>

		<!-- MODAL FORM TAMBAH / EDIT ROADMAP -->
		<ModalFormRoadmap
			:show="isFormModalOpen"
			:is-editing="isEditing"
			:initial-data="selectedRoadmap || {}"
			:editing-id="editingId"
			@close="isFormModalOpen = false"
			@submit="handleFormSubmit"
		/>

		<!-- MODAL DELETE CONFIRMATION -->
		<div
			v-if="isDeleteModalOpen"
			class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overscroll-contain bg-black/50 p-4 backdrop-blur-xs transition-opacity duration-200"
			@click.self="closeDeleteModal"
		>
			<div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl text-center font-poppins">
				<div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-600 mb-4">
					<svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
						<path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
					</svg>
				</div>

				<h3 class="font-poppins text-lg font-bold text-[#17334F]">
					Hapus Roadmap?
				</h3>

				<p class="mt-2 font-inter text-sm text-[#64748b]">
					Apakah Anda yakin ingin menghapus roadmap
					<span class="font-bold text-[#17334F]">"{{ deletingRoadmap?.title }}"</span>? Tindakan ini tidak dapat dibatalkan.
				</p>

				<div class="mt-6 flex items-center justify-center gap-3">
					<button
						type="button"
						:disabled="isDeleting"
						@click="closeDeleteModal"
						class="rounded-lg border border-[#d6e0ee] px-4 py-2.5 text-sm font-medium text-[#475569] hover:bg-slate-50 transition-colors disabled:opacity-50 cursor-pointer"
					>
						Batal
					</button>
					<button
						type="button"
						:disabled="isDeleting"
						@click="confirmDeleteRoadmap"
						class="flex items-center gap-2 rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition-colors shadow-sm disabled:opacity-50 cursor-pointer"
					>
						<svg v-if="isDeleting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
							<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
							<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
						</svg>
						<span>{{ isDeleting ? 'Menghapus...' : 'Hapus Roadmap' }}</span>
					</button>
				</div>
			</div>
		</div>

		<!-- Toast Notification -->
		<ToastNotification
			:show="toast.show"
			:type="toast.type"
			:title="toast.title"
			:message="toast.message"
			@close="closeToast"
		/>
	</AdminLayout>
</template>
