<script setup>
import MahasiswaLayout from '@/Layouts/dashboard/MahasiswaLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import TablePagination from '@/Components/dashboard/TablePagination.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import SearchBarTable from '@/Components/dashboard/SearchBarTable.vue';
import AddActivityModal from '@/Components/dashboard/mahasiswa/AddActivityModal.vue';

const props = defineProps({
	roadmaps: {
		type: Array,
		default: () => [
			{
				id: 1,
				title: 'Persiapan Karir Software Engineer & Fullstack Developer',
				category: 'Profesional',
				categories_label: 'Profesional | Bisnis | Birokrat | Akademisi',
				task_count: 40,
				completed_tasks: 10,
				thumbnail: '',
				updated_at: '19/10/2026',
			},
			{
				id: 2,
				title: 'Inkubasi Bisnis & Rintisan Startup Berbasis Hasil Hutan',
				category: 'Bisnis',
				categories_label: 'Profesional | Bisnis | Birokrat | Akademisi',
				task_count: 40,
				completed_tasks: 10,
				thumbnail: '',
				updated_at: '18/10/2026',
			},
			{
				id: 3,
				title: 'Jalur Masuk ASN & Karir Birokrasi Lingkungan Hidup',
				category: 'Birokrasi',
				categories_label: 'Profesional | Bisnis | Birokrat | Akademisi',
				task_count: 40,
				completed_tasks: 10,
				thumbnail: '',
				updated_at: '15/10/2026',
			},
			{
				id: 4,
				title: 'Persiapan Studi Lanjut S2/S3 & Publikasi Ilmiah Kehutanan',
				category: 'Akademisi',
				categories_label: 'Profesional | Bisnis | Birokrat | Akademisi',
				task_count: 30,
				completed_tasks: 15,
				thumbnail: '',
				updated_at: '12/10/2026',
			},
			{
				id: 5,
				title: 'Sertifikasi Konsultan Lingkungan & AMDAL Profesional',
				category: 'Profesional',
				categories_label: 'Profesional | Bisnis | Birokrat | Akademisi',
				task_count: 24,
				completed_tasks: 8,
				thumbnail: '',
				updated_at: '10/10/2026',
			},
			{
				id: 6,
				title: 'Pengembangan Usaha Ekowisata & Agroforestry Berkelanjutan',
				category: 'Bisnis',
				categories_label: 'Profesional | Bisnis | Birokrat | Akademisi',
				task_count: 35,
				completed_tasks: 20,
				thumbnail: '',
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
		categories_label: item.categories_label || 'Profesional | Bisnis | Birokrat | Akademisi',
		task_count: item.task_count || item.taskCount || item.tasks || 40,
		completed_tasks: item.completed_tasks ?? item.completedCount ?? 10,
		thumbnail: item.thumbnail || item.gambar || '',
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
const categoryFilterOptions = ['Profesional', 'Bisnis', 'Birokrat', 'Akademisi'];
const filterContainerRef = ref(null);
const filterDropdownStyle = ref({});

const hasActiveFilters = computed(() => {
	return selectedCategories.value.length > 0;
});

const calculateFilterPlacement = () => {
	if (!filterContainerRef.value || typeof window === 'undefined') return;
	const rect = filterContainerRef.value.getBoundingClientRect();
	const windowWidth = window.innerWidth;
	const padding = 16;

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
	return roadmapsList.value.filter((r) => {
		const cat = (r.category || '').toLowerCase();
		const target = category.toLowerCase();
		return cat === target || (target === 'birokrat' && cat === 'birokrasi');
	}).length;
};

const getCategoryBadges = (item) => {
	if (!item) return [];
	if (Array.isArray(item.kategori)) return item.kategori;
	if (Array.isArray(item.categories)) return item.categories;
	if (Array.isArray(item.category)) return item.category;
	const val = item.category || item.kategori || '';
	if (typeof val === 'string' && val.trim()) {
		if (val.includes('|')) return val.split('|').map((s) => s.trim()).filter(Boolean);
		if (val.includes(',')) return val.split(',').map((s) => s.trim()).filter(Boolean);
		return [val.trim()];
	}
	return ['Profesional'];
};

const getCategoryBadgeClass = (category) => {
	const cat = (category || '').trim();
	if (cat === 'Profesional' || cat === 'Professional') {
		return 'bg-indigo-50 text-indigo-700 border-indigo-200';
	}
	if (cat === 'Bisnis') {
		return 'bg-amber-50 text-amber-700 border-amber-200';
	}
	if (cat === 'Birokrat' || cat === 'Birokrasi') {
		return 'bg-emerald-50 text-emerald-700 border-emerald-200';
	}
	if (cat === 'Akademisi') {
		return 'bg-purple-50 text-purple-700 border-purple-200';
	}
	return 'bg-slate-100 text-slate-700 border-slate-200';
};

const getCardGradient = (category) => {
	switch ((category || '').toLowerCase()) {
		case 'profesional':
			return 'from-[#143365] via-[#1a4484] to-[#2560ab]';
		case 'bisnis':
			return 'from-[#0f3d4c] via-[#16566c] to-[#1e788e]';
		case 'birokrasi':
		case 'birokrat':
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
				(r.category && r.category.toLowerCase().includes(q)) ||
				(r.categories_label && r.categories_label.toLowerCase().includes(q))
		);
	}

	// Category Filter
	if (selectedCategories.value.length > 0) {
		list = list.filter((r) => {
			const cat = (r.category || '').toLowerCase();
			return selectedCategories.value.some((c) => {
				const target = c.toLowerCase();
				return cat === target || (target === 'birokrat' && cat === 'birokrasi');
			});
		});
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

// Navigate to Roadmap Detail / Activity
const handleCardClick = (roadmap) => {
	router.visit(`/mahasiswa/roadmap/${roadmap.id || 1}`);
};

const isModalOpen = ref(false);
const selectedData = ref(null);

const openTambah = () => {
    selectedData.value = null;
    isModalOpen.value = true; 
};


const handleModalSubmit = (selectedIds) => {
/* 	console.log('Selected Roadmap IDs:', selectedIds);
	isModalOpen.value = false;
	console.log('Data yang mau ditambah:', dataDariModal); */

    // 2. TUTUP MODALNYA
    isModalOpen.value = false;
    
	toast.value = {
        show: true,
        type: 'success',
        title: 'Roadmap berhasil ditambahkan',
        message: 'Roadmap berhasil ditambahkan ke daftar Anda.',
    };

	setTimeout(() => {
        toast.value.show = false;
    }, 3000);
};

</script>

<template>
	<Head title="Roadmap - Mahasiswa" />
	<MahasiswaLayout>
		<section class="mx-auto w-full max-w-[1520px] px-4 py-6 font-poppins sm:px-6 sm:py-8 lg:px-8">
			<div class="space-y-6">
				<!-- Header Title & Subtitle (Matches User Mockup) -->
				<div class="space-y-1.5">
					<h1 class="text-[34px] font-extrabold leading-[1.05] tracking-tight text-[#17334F] sm:text-[42px] lg:text-[46px]">
						Roadmap
					</h1>
					<p class="font-inter text-[14px] font-normal leading-normal text-[#4d6786] sm:text-[15px]">
						Yuk Pelajari Roadmap Yang Kamu Ikuti!
					</p>
				</div>

				<!-- Action Bar (Search, Filter, & Tambah) in a single responsive row -->
				<div class="flex flex-row items-center gap-2.5 sm:gap-3 w-full">
					<!-- Search Input Component -->
					<SearchBarTable
						class="w-full flex-1 min-w-0"
						v-model="searchQuery"
						placeholder="Cari roadmap disini"
					/>

					<!-- Filter Button & Tambah Container -->
					<div class="flex items-center justify-end shrink-0 gap-2 sm:gap-3">
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
						<button
              				type="button"
              				@click="openTambah"
              				class="flex h-[46px] w-[46px] sm:w-auto shrink-0 items-center justify-center gap-2 rounded-[10px] bg-[#183669] px-0 sm:px-7 font-poppins text-[15px] font-semibold text-white shadow-sm transition hover:bg-[#122b54] active:scale-95 focus:outline-none select-none cursor-pointer">
              				<span class="hidden sm:inline">Tambah</span>
              				<svg class="h-5 w-5 sm:hidden shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              				  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              				</svg>
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
							class="flex flex-col justify-between rounded-[14px] border border-[#d6e0ee] bg-white p-4 sm:p-5 shadow-xs animate-pulse"
						>
							<div>
								<div class="h-[175px] sm:h-[190px] w-full rounded-[10px] bg-slate-200"></div>
								<!-- Progress Bar Skeleton -->
								<div class="mt-4 flex items-center justify-between gap-3">
									<div class="h-4 w-20 rounded bg-slate-200"></div>
									<div class="h-2 flex-1 rounded-full bg-slate-200"></div>
								</div>
								<!-- Title Skeleton -->
								<div class="mt-3 space-y-2">
									<div class="h-4.5 w-full rounded bg-slate-200"></div>
									<div class="h-4.5 w-3/4 rounded bg-slate-200"></div>
								</div>
								<!-- Categories Skeleton -->
								<div class="mt-3 h-3.5 w-48 rounded bg-slate-200"></div>
							</div>
						</div>
					</div>

					<!-- Real Cards Grid -->
					<div v-else-if="paginatedRoadmaps.length > 0" class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 sm:gap-6">
						<div
							v-for="item in paginatedRoadmaps"
							:key="`roadmap-${item.id}`"
							@click="handleCardClick(item)"
							class="group flex flex-col justify-between rounded-[14px] border border-[#d6e0ee] bg-white p-4 sm:p-5 shadow-xs transition-colors duration-150 sm:hover:bg-[#fafcff] sm:hover:border-[#a6b7cb] cursor-pointer"
							:title="`Buka detail ${item.title}`"
						>
							<div>
								<!-- Top Cover Banner (Rounded [10px]) -->
								<div class="relative flex h-[175px] sm:h-[190px] w-full items-center justify-center overflow-hidden rounded-[10px] bg-slate-100 shadow-inner">
									<img
										v-if="item.thumbnail"
										:src="item.thumbnail"
										:alt="item.title"
										class="h-full w-full object-cover"
									/>
									<!-- Soft Geometric Mural Pattern Fallback -->
									<div
										v-else
										:class="['relative h-full w-full bg-gradient-to-br flex items-center justify-center overflow-hidden select-none', getCardGradient(item.category)]"
									>
										<!-- Geometric SVG Mural Pattern -->
										<svg
											class="absolute inset-0 h-full w-full object-cover pointer-events-none opacity-40 mix-blend-screen"
											viewBox="0 0 400 240"
											fill="none"
											xmlns="http://www.w3.org/2000/svg"
										>
											<circle cx="360" cy="30" r="110" fill="white" fill-opacity="0.2" />
											<circle cx="40" cy="220" r="90" fill="white" fill-opacity="0.15" />
											<path d="M-20 60 L120 -10 L180 90 L60 140 Z" fill="white" fill-opacity="0.08" />
											<path d="M120 -10 L280 20 L240 130 L180 90 Z" fill="white" fill-opacity="0.12" />
											<path d="M280 20 L420 -30 L380 90 L240 130 Z" fill="white" fill-opacity="0.06" />
											<path d="M180 90 L240 130 L320 220 L160 200 Z" fill="white" fill-opacity="0.09" />
											<path d="M60 140 L180 90 L160 200 L40 230 Z" fill="white" fill-opacity="0.05" />
											<path d="M240 130 L380 90 L430 200 L320 220 Z" fill="white" fill-opacity="0.11" />
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

								<!-- Progress Bar Row (As in Mockup) -->
								<div class="mt-4 flex items-center justify-between gap-3">
									<span class="font-inter text-[12px] sm:text-[13px] font-semibold text-[#475569] shrink-0">
										{{ item.completed_tasks ?? 0 }}/{{ item.task_count || 40 }} Task
									</span>
									<div class="h-2 flex-1 rounded-full bg-[#e2e8f0] overflow-hidden">
										<div
											class="h-full rounded-full bg-[#183669] transition-all duration-300"
											:style="{
												width: `${Math.min(100, Math.round(((item.completed_tasks ?? 0) / (item.task_count || 40)) * 100))}%`
											}"
										></div>
									</div>
								</div>

								<!-- Roadmap Title -->
								<h3
									class="mt-2.5 line-clamp-2 text-[16px] sm:text-[17px] font-bold leading-snug text-[#17334F] transition sm:group-hover:text-[#183669]"
									:title="item.title"
								>
									{{ item.title }}
								</h3>

								<!-- Category Badges -->
								<div class="mt-2.5 flex flex-wrap items-center gap-1.5">
									<span
										v-for="(cat, catIdx) in getCategoryBadges(item)"
										:key="catIdx"
										:class="[
											'inline-flex items-center justify-center rounded-full px-2.5 py-0.5 font-inter text-[11px] font-semibold border',
											getCategoryBadgeClass(cat)
										]"
									>
										{{ cat }}
									</span>
								</div>
							</div>
						</div>
					</div>

					<!-- Empty State -->
					<div
						v-else
						class="flex flex-col items-center justify-center rounded-[20px] border border-dashed border-[#d6e0ee] bg-white px-6 py-16 text-center"
					>
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
					:current-page="currentPage"
					:total-pages="totalPages"
					:total-items="filteredRoadmaps.length"
					:rows-per-page="rowsPerPage"
					:rows-options="rowsOptions"
					@update:currentPage="(page) => (currentPage = page)"
					@update:rowsPerPage="(rows) => (rowsPerPage = rows)"
				/>
			</div>
		</section>

		<!-- Toast Notification -->
		<ToastNotification
			:show="toast.show"
			:type="toast.type"
			:title="toast.title"
			:message="toast.message"
			@close="closeToast"
		/>

		<!-- Add Activity Modal -->
		<AddActivityModal
			v-model:show="isModalOpen"
			:selected-data="selectedData"
			@close="isModalOpen = false"
			@submit="handleModalSubmit"
		/>
	</MahasiswaLayout>
</template>
