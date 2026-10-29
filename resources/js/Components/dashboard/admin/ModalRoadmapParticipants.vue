<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import CardSkeleton from '@/Components/dashboard/CardSkeleton.vue';
import TablePagination from '@/Components/dashboard/TablePagination.vue';

const props = defineProps({
	show: {
		type: Boolean,
		default: false,
	},
	roadmapId: {
		type: [Number, String],
		required: true,
	},
	roadmapTitle: {
		type: String,
		default: '',
	},
	initialMateriId: {
		type: [Number, String, null],
		default: null,
	},
});

const emit = defineEmits(['close']);

// Tab Mode: 'mahasiswa' or 'materi'
const activeTab = ref('mahasiswa');

// Data State
const isLoading = ref(false);
const errorMsg = ref('');
const participants = ref([]);
const materials = ref([]);
const totalTasks = ref(0);

// Global Search Query (shared or tab-scoped)
const searchQuery = ref('');

// Filter Angkatan (available in BOTH tabs!)
const selectedAngkatan = ref('all');
const isAngkatanDropdownOpen = ref(false);
const angkatanDropdownRef = ref(null);

// Accordion expanded student ID set (for Mahasiswa Tab checklist)
const expandedStudentIds = ref(new Set());

const toggleStudentExpand = (studentId) => {
	if (expandedStudentIds.value.has(studentId)) {
		expandedStudentIds.value.delete(studentId);
	} else {
		expandedStudentIds.value.add(studentId);
	}
};

// Filters for Materi Tab
const selectedBulanFilter = ref('all');
const isBulanDropdownOpen = ref(false);
const bulanDropdownRef = ref(null);

const selectedMateriId = ref(null);
const isMateriDropdownOpen = ref(false);
const materiDropdownRef = ref(null);

const materiStatusFilter = ref('all'); // 'all', 'completed', 'pending'

// Pagination State (Client-Side Pagination)
const currentPage = ref(1);
const rowsPerPage = ref(5);

// State Loading saat User Filter/Search
const isFiltering = ref(false);
let filterTimeout = null;

// Track last count rendered to match skeleton count precisely
const lastRenderedCount = ref(5);

const triggerFilterLoading = () => {
	isFiltering.value = true;
	clearTimeout(filterTimeout);
	filterTimeout = setTimeout(() => {
		isFiltering.value = false;
	}, 220);
};

// Reset page to 1 & trigger skeleton when searching or filtering
watch([searchQuery, selectedAngkatan, selectedBulanFilter, selectedMateriId, materiStatusFilter, activeTab], () => {
	currentPage.value = 1;
	triggerFilterLoading();
});

// Fetch participants & materials data via API
const fetchParticipants = async () => {
	if (!props.roadmapId) return;
	isLoading.value = true;
	errorMsg.value = '';

	try {
		const res = await fetch(`/admin/roadmap/${props.roadmapId}/participants`, {
			headers: {
				Accept: 'application/json',
				'X-Requested-With': 'XMLHttpRequest',
			},
		});

		if (!res.ok) {
			throw new Error('Gagal memuat data partisipan.');
		}

		const json = await res.json();
		if (json.success && json.data) {
			participants.value = json.data.participants || [];
			materials.value = json.data.materials || [];
			totalTasks.value = json.data.total_tasks || 0;

			// If initialMateriId is provided, pre-select it and sync month filter
			if (props.initialMateriId && materials.value.some((m) => m.id === props.initialMateriId)) {
				const found = materials.value.find((m) => m.id === props.initialMateriId);
				selectedMateriId.value = props.initialMateriId;
				selectedBulanFilter.value = found ? found.bulan_ke : 'all';
				activeTab.value = 'materi';
			} else if (!selectedMateriId.value && materials.value.length > 0) {
				selectedMateriId.value = materials.value[0].id;
				selectedBulanFilter.value = 'all';
			}
		} else {
			participants.value = [];
			materials.value = [];
		}
	} catch (err) {
		console.error(err);
		errorMsg.value = err.message || 'Terjadi kesalahan saat memuat data.';
	} finally {
		isLoading.value = false;
	}
};

// Re-fetch / re-initialize whenever modal is opened
watch(
	() => props.show,
	(isOpen) => {
		if (isOpen) {
			searchQuery.value = '';
			selectedAngkatan.value = 'all';
			materiStatusFilter.value = 'all';
			isAngkatanDropdownOpen.value = false;
			isBulanDropdownOpen.value = false;
			isMateriDropdownOpen.value = false;
			expandedStudentIds.value = new Set();

			if (props.initialMateriId) {
				selectedMateriId.value = props.initialMateriId;
				activeTab.value = 'materi';
			} else {
				activeTab.value = 'mahasiswa';
			}

			fetchParticipants();
		}
	}
);

// Close handlers
const handleClose = () => {
	isAngkatanDropdownOpen.value = false;
	isBulanDropdownOpen.value = false;
	isMateriDropdownOpen.value = false;
	emit('close');
};

const handleKeyDown = (e) => {
	if (e.key === 'Escape' && props.show) {
		if (isAngkatanDropdownOpen.value || isBulanDropdownOpen.value || isMateriDropdownOpen.value) {
			isAngkatanDropdownOpen.value = false;
			isBulanDropdownOpen.value = false;
			isMateriDropdownOpen.value = false;
			return;
		}
		handleClose();
	}
};

// Click outside dropdowns
const handleDocumentClick = (e) => {
	if (angkatanDropdownRef.value && !angkatanDropdownRef.value.contains(e.target)) {
		isAngkatanDropdownOpen.value = false;
	}
	if (bulanDropdownRef.value && !bulanDropdownRef.value.contains(e.target)) {
		isBulanDropdownOpen.value = false;
	}
	if (materiDropdownRef.value && !materiDropdownRef.value.contains(e.target)) {
		isMateriDropdownOpen.value = false;
	}
};

onMounted(() => {
	window.addEventListener('keydown', handleKeyDown);
	document.addEventListener('click', handleDocumentClick);
});

onBeforeUnmount(() => {
	window.removeEventListener('keydown', handleKeyDown);
	document.removeEventListener('click', handleDocumentClick);
});

// Backdrop click safety
const isBackdropClick = ref(false);
const onBackdropMouseDown = (e) => {
	isBackdropClick.value = e.target === e.currentTarget;
};
const onBackdropMouseUp = (e) => {
	if (isBackdropClick.value && e.target === e.currentTarget) {
		handleClose();
	}
	isBackdropClick.value = false;
};

// Distinct Months from materials
const monthsOptions = computed(() => {
	const map = new Map();
	materials.value.forEach((m) => {
		if (!map.has(m.bulan_ke)) {
			map.set(m.bulan_ke, {
				bulan_ke: m.bulan_ke,
				bulan_name: m.bulan_name || `Bulan ${m.bulan_ke}`,
			});
		}
	});
	return Array.from(map.values()).sort((a, b) => a.bulan_ke - b.bulan_ke);
});

const selectBulanFilter = (bulanVal) => {
	selectedBulanFilter.value = bulanVal;
	isBulanDropdownOpen.value = false;

	// If currently selected materi does not match this month, auto select first in this month
	const available = filteredMaterialsForDropdown.value;
	if (available.length > 0 && !available.some((m) => m.id === selectedMateriId.value)) {
		selectedMateriId.value = available[0].id;
	}
};

// Materials filtered by selected Month for the Materi Dropdown
const filteredMaterialsForDropdown = computed(() => {
	if (selectedBulanFilter.value === 'all') {
		return materials.value;
	}
	return materials.value.filter((m) => String(m.bulan_ke) === String(selectedBulanFilter.value));
});

// Currently selected materi object
const currentSelectedMateri = computed(() => {
	return materials.value.find((m) => m.id === selectedMateriId.value) || materials.value[0] || null;
});

const selectMateri = (materiId) => {
	selectedMateriId.value = materiId;
	isMateriDropdownOpen.value = false;
};

// Distinct Angkatan Options (Shared across both tabs)
const angkatanOptions = computed(() => {
	const set = new Set();
	participants.value.forEach((p) => {
		if (p.angkatan && p.angkatan !== '-') {
			set.add(p.angkatan);
		}
	});
	return Array.from(set).sort((a, b) => b - a);
});

const selectAngkatan = (val) => {
	selectedAngkatan.value = val;
	isAngkatanDropdownOpen.value = false;
};

// Filtered List for Mahasiswa Tab
const filteredParticipants = computed(() => {
	let list = participants.value;

	if (selectedAngkatan.value !== 'all') {
		list = list.filter((p) => String(p.angkatan) === String(selectedAngkatan.value));
	}

	if (searchQuery.value.trim()) {
		const q = searchQuery.value.toLowerCase().trim();
		list = list.filter((p) => {
			const nama = (p.nama || '').toLowerCase();
			const nim = (p.nim || '').toLowerCase();
			return nama.includes(q) || nim.includes(q);
		});
	}

	return list;
});

// Students categorized by the selected materi (respects selectedAngkatan & search!)
const materiStudents = computed(() => {
	if (!currentSelectedMateri.value) {
		return { completed: [], pending: [], filteredList: [], totalInFilter: 0 };
	}

	const mId = currentSelectedMateri.value.id;
	let list = participants.value;

	// Respect Angkatan filter in Materi tab as well!
	if (selectedAngkatan.value !== 'all') {
		list = list.filter((p) => String(p.angkatan) === String(selectedAngkatan.value));
	}

	const completed = [];
	const pending = [];

	list.forEach((p) => {
		const hasCompleted = (p.completed_materi_ids || []).includes(mId);
		const studentData = {
			...p,
			is_completed_for_this_materi: hasCompleted,
		};
		if (hasCompleted) {
			completed.push(studentData);
		} else {
			pending.push(studentData);
		}
	});

	let filtered = [];
	if (materiStatusFilter.value === 'completed') {
		filtered = completed;
	} else if (materiStatusFilter.value === 'pending') {
		filtered = pending;
	} else {
		filtered = [...completed, ...pending];
	}

	if (searchQuery.value.trim()) {
		const q = searchQuery.value.toLowerCase().trim();
		filtered = filtered.filter((p) => {
			const nama = (p.nama || '').toLowerCase();
			const nim = (p.nim || '').toLowerCase();
			return nama.includes(q) || nim.includes(q);
		});
	}

	return {
		completed,
		pending,
		filteredList: filtered,
		totalInFilter: list.length,
	};
});

// Current active list depending on activeTab
const activeFullList = computed(() => {
	return activeTab.value === 'mahasiswa'
		? filteredParticipants.value
		: materiStudents.value.filteredList;
});

// Total count & Paginated List
const totalItemsInTab = computed(() => activeFullList.value.length);
const totalPagesInTab = computed(() => Math.max(1, Math.ceil(totalItemsInTab.value / rowsPerPage.value)));

const paginatedList = computed(() => {
	const start = (currentPage.value - 1) * rowsPerPage.value;
	const end = start + rowsPerPage.value;
	return activeFullList.value.slice(start, end);
});

// Simpan jumlah data yang terakhir tampil agar jumlah skeleton persis menutup data sebelumnya
watch(paginatedList, (newList) => {
	if (newList.length > 0) {
		lastRenderedCount.value = newList.length;
	}
}, { immediate: true });

// Skeleton count presisi: menggunakan data yang sedang ada, atau default 4 jika belum ada data
const preciseSkeletonCount = computed(() => {
	return lastRenderedCount.value > 0 ? lastRenderedCount.value : 4;
});

const handlePageChange = (newPage) => {
	if (currentPage.value === newPage) return;
	currentPage.value = newPage;
	triggerFilterLoading();
};

const handleRowsPerPageChange = (newRows) => {
	rowsPerPage.value = newRows;
	currentPage.value = 1;
	triggerFilterLoading();
};

// Stats summary
const stats = computed(() => {
	const total = participants.value.length;
	if (total === 0) {
		return { total: 0, avgProgress: 0, completedCount: 0 };
	}
	const completedCount = participants.value.filter((p) => p.progress >= 100).length;
	const sumProgress = participants.value.reduce((acc, p) => acc + (p.progress || 0), 0);
	const avgProgress = Math.round(sumProgress / total);

	return { total, avgProgress, completedCount };
});

// Avatar helper: Initials and uniform default theme styling (navy brand color)
const getInitials = (name) => {
	if (!name) return 'M';
	const parts = name.trim().split(/\s+/);
	if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
	return (parts[0][0] + parts[1][0]).toUpperCase();
};

const defaultAvatarStyle = {
	bg: 'bg-[#183669]/10',
	text: 'text-[#183669]',
	border: 'border-[#183669]/20',
};

const getAvatarStyle = () => defaultAvatarStyle;
</script>

<template>
	<Teleport to="body">
		<Transition
			enter-active-class="ease-out duration-200"
			enter-from-class="opacity-0"
			enter-to-class="opacity-100"
			leave-active-class="ease-in duration-150"
			leave-from-class="opacity-100"
			leave-to-class="opacity-0"
		>
			<div
				v-if="show"
				class="fixed inset-0 z-50 overflow-y-auto overscroll-contain bg-slate-900/50 backdrop-blur-xs p-2 sm:p-6 flex justify-center items-center min-h-screen"
				@mousedown="onBackdropMouseDown"
				@mouseup="onBackdropMouseUp"
			>
				<Transition
					enter-active-class="ease-out duration-200"
					enter-from-class="opacity-0 scale-95 translate-y-2"
					enter-to-class="opacity-100 scale-100 translate-y-0"
					leave-active-class="ease-in duration-150"
					leave-from-class="opacity-100 scale-100 translate-y-0"
					leave-to-class="opacity-0 scale-95 translate-y-2"
				>
					<div
						v-if="show"
						class="relative w-full max-w-[820px] my-auto transform rounded-[16px] sm:rounded-[20px] bg-white shadow-2xl font-poppins border border-[#e2eaf4] flex flex-col max-h-[90vh] sm:max-h-[88vh] overflow-hidden"
						@mousedown.stop
					>
						<!-- Modal Header (Minimalis & Compact) -->
						<div class="px-4 sm:px-6 py-3.5 sm:py-4 border-b border-[#eef2f6] flex items-center justify-between gap-3 bg-white">
							<div class="min-w-0 flex-1">
								<div class="flex items-center gap-2">
									<h2 class="text-[16px] sm:text-[18px] font-bold text-[#17334F] leading-tight truncate">
										{{ roadmapTitle || 'Detail Partisipan' }}
									</h2>
									<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] sm:text-[11px] font-semibold bg-[#183669]/10 text-[#183669] shrink-0">
										{{ stats.total }} Mhs
									</span>
								</div>
								<div class="flex items-center gap-3 text-[11px] sm:text-[12px] text-[#64748b] font-inter mt-0.5">
									<span>Rata-rata: <strong class="text-[#17334F]">{{ stats.avgProgress }}%</strong></span>
									<span>•</span>
									<span class="text-emerald-700 font-medium">{{ stats.completedCount }} Lulus 100%</span>
								</div>
							</div>

							<!-- Close Button -->
							<button
								type="button"
								@click="handleClose"
								class="h-7 w-7 sm:h-8 sm:w-8 shrink-0 flex items-center justify-center rounded-full bg-slate-100 text-slate-400 hover:bg-slate-200 hover:text-slate-700 transition cursor-pointer"
								title="Tutup (Esc)"
							>
								<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
								</svg>
							</button>
						</div>

						<!-- Mode Switcher Tabs (Flat Minimalist Segmented Control) -->
						<div class="px-4 sm:px-6 pt-2 pb-2 bg-[#f8fafc] border-b border-[#eef2f6] flex items-center justify-between gap-2">
							<div class="flex items-center p-1 bg-slate-200/70 rounded-xl w-full sm:w-auto">
								<button
									type="button"
									@click="activeTab = 'mahasiswa'"
									:class="[
										'flex-1 sm:flex-initial py-1 sm:py-1.5 px-3 sm:px-4 rounded-lg text-[12px] sm:text-[13px] font-semibold transition-all flex items-center justify-center gap-1.5 select-none cursor-pointer',
										activeTab === 'mahasiswa'
											? 'bg-white text-[#183669] shadow-xs font-bold'
											: 'text-slate-600 hover:text-[#183669]'
									]"
								>
									<span>Per Mahasiswa</span>
									<span
										:class="[
											'text-[10px] px-1.5 py-0.2 rounded-full font-bold',
											activeTab === 'mahasiswa' ? 'bg-[#183669]/10 text-[#183669]' : 'bg-black/5 text-slate-500'
										]"
									>
										{{ participants.length }}
									</span>
								</button>

								<button
									type="button"
									@click="activeTab = 'materi'"
									:class="[
										'flex-1 sm:flex-initial py-1 sm:py-1.5 px-3 sm:px-4 rounded-lg text-[12px] sm:text-[13px] font-semibold transition-all flex items-center justify-center gap-1.5 select-none cursor-pointer',
										activeTab === 'materi'
											? 'bg-white text-[#183669] shadow-xs font-bold'
											: 'text-slate-600 hover:text-[#183669]'
									]"
								>
									<span>Per Aktivitas</span>
									<span
										:class="[
											'text-[10px] px-1.5 py-0.2 rounded-full font-bold',
											activeTab === 'materi' ? 'bg-[#183669]/10 text-[#183669]' : 'bg-black/5 text-slate-500'
										]"
									>
										{{ materials.length }}
									</span>
								</button>
							</div>
						</div>

						<!-- Filter & Search Toolbar (Responsive Clean Toolbar) -->
						<div class="px-3.5 sm:px-6 py-2 sm:py-2.5 border-b border-[#eef2f6] flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-2.5 bg-white">
							<!-- Search Input -->
							<div class="relative flex-1 w-full min-w-0 sm:min-w-[180px]">
								<input
									v-model="searchQuery"
									type="text"
									placeholder="Cari nama atau NIM..."
									class="w-full rounded-lg border border-[#d6e0ee] bg-[#fafcff] pl-8 sm:pl-8.5 pr-7 py-1 text-[11.5px] sm:text-[12.5px] text-[#17334F] placeholder-[#94a3b8] focus:border-[#183669] focus:outline-none focus:ring-0 transition h-[34px] sm:h-[36px]"
								/>
								<svg class="h-3.5 w-3.5 text-[#8ca1b9] absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
								</svg>
								<button
									v-if="searchQuery"
									type="button"
									@click="searchQuery = ''"
									class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5 cursor-pointer"
								>
									<svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
								</button>
							</div>

							<!-- TAB 1: Standalone Angkatan Dropdown -->
							<div v-if="activeTab === 'mahasiswa' && angkatanOptions.length > 0" class="relative shrink-0 w-full sm:w-[140px]" ref="angkatanDropdownRef">
								<button
									type="button"
									@click="isAngkatanDropdownOpen = !isAngkatanDropdownOpen"
									class="h-[34px] sm:h-[36px] w-full rounded-lg border bg-[#fafcff] px-2.5 font-inter text-[11.5px] sm:text-[12.5px] transition flex items-center justify-between cursor-pointer focus:outline-none select-none"
									:class="isAngkatanDropdownOpen ? 'border-[#183669] bg-white' : 'border-[#d6e0ee] hover:border-[#183669] hover:bg-white'"
									title="Filter Angkatan"
								>
									<span class="truncate text-[#17334F] font-medium">
										{{ selectedAngkatan === 'all' ? 'Semua Angkatan' : `Angkatan ${selectedAngkatan}` }}
									</span>
									<svg
										:class="['h-3.5 w-3.5 text-[#183669] transition-transform duration-200 shrink-0 ml-1', isAngkatanDropdownOpen ? 'rotate-180' : '']"
										fill="none"
										stroke="currentColor"
										stroke-width="2"
										viewBox="0 0 24 24"
									>
										<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
									</svg>
								</button>

								<!-- Dropdown Menu List -->
								<Transition
									enter-active-class="transition duration-150 ease-out"
									enter-from-class="transform scale-95 opacity-0 -translate-y-1"
									enter-to-class="transform scale-100 opacity-100 translate-y-0"
									leave-active-class="transition duration-100 ease-in"
									leave-from-class="transform scale-100 opacity-100 translate-y-0"
									leave-to-class="transform scale-95 opacity-0 -translate-y-1"
								>
									<div
										v-if="isAngkatanDropdownOpen"
										class="absolute right-0 top-full mt-1 z-50 w-full sm:w-[160px] rounded-xl border border-[#d6e0ee] bg-white p-1 shadow-lg"
									>
										<button
											type="button"
											@click="selectAngkatan('all')"
											:class="[
												'flex w-full items-center justify-between px-2.5 py-1.5 rounded-lg text-left font-inter text-[11.5px] transition cursor-pointer',
												selectedAngkatan === 'all' ? 'bg-[#183669]/10 font-bold text-[#183669]' : 'text-[#334155] hover:bg-[#f8fafc]'
											]"
										>
											<span>Semua Angkatan</span>
											<svg v-if="selectedAngkatan === 'all'" class="h-3.5 w-3.5 text-[#183669]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
											</svg>
										</button>
										<button
											v-for="ang in angkatanOptions"
											:key="ang"
											type="button"
											@click="selectAngkatan(ang)"
											:class="[
												'flex w-full items-center justify-between px-2.5 py-1.5 rounded-lg text-left font-inter text-[11.5px] transition cursor-pointer',
												selectedAngkatan === ang ? 'bg-[#183669]/10 font-bold text-[#183669]' : 'text-[#334155] hover:bg-[#f8fafc]'
											]"
										>
											<span>Angkatan {{ ang }}</span>
											<svg v-if="selectedAngkatan === ang" class="h-3.5 w-3.5 text-[#183669]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
											</svg>
										</button>
									</div>
								</Transition>
							</div>

							<!-- TAB 2: Grid for Angkatan & Bulan on mobile, plus Materi -->
							<template v-if="activeTab === 'materi' && materials.length > 0">
								<div class="grid grid-cols-2 gap-2 w-full sm:w-auto sm:flex sm:items-center">
									<!-- Custom Angkatan Dropdown -->
									<div v-if="angkatanOptions.length > 0" class="relative w-full sm:w-[130px]" ref="angkatanDropdownRef">
										<button
											type="button"
											@click="isAngkatanDropdownOpen = !isAngkatanDropdownOpen"
											class="h-[34px] sm:h-[36px] w-full rounded-lg border bg-[#fafcff] px-2.5 font-inter text-[11px] sm:text-[12px] transition flex items-center justify-between cursor-pointer focus:outline-none select-none"
											:class="isAngkatanDropdownOpen ? 'border-[#183669] bg-white' : 'border-[#d6e0ee] hover:border-[#183669] hover:bg-white'"
											title="Filter Angkatan"
										>
											<span class="truncate text-[#17334F] font-medium">
												{{ selectedAngkatan === 'all' ? 'Semua Angkatan' : `Angkatan ${selectedAngkatan}` }}
											</span>
											<svg
												:class="['h-3.5 w-3.5 text-[#183669] transition-transform duration-200 shrink-0 ml-1', isAngkatanDropdownOpen ? 'rotate-180' : '']"
												fill="none"
												stroke="currentColor"
												stroke-width="2"
												viewBox="0 0 24 24"
											>
												<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
											</svg>
										</button>

										<!-- Dropdown Menu List -->
										<Transition
											enter-active-class="transition duration-150 ease-out"
											enter-from-class="transform scale-95 opacity-0 -translate-y-1"
											enter-to-class="transform scale-100 opacity-100 translate-y-0"
											leave-active-class="transition duration-100 ease-in"
											leave-from-class="transform scale-100 opacity-100 translate-y-0"
											leave-to-class="transform scale-95 opacity-0 -translate-y-1"
										>
											<div
												v-if="isAngkatanDropdownOpen"
												class="absolute left-0 sm:right-0 sm:left-auto top-full mt-1 z-50 w-[150px] rounded-xl border border-[#d6e0ee] bg-white p-1 shadow-lg"
											>
												<button
													type="button"
													@click="selectAngkatan('all')"
													:class="[
														'flex w-full items-center justify-between px-2.5 py-1.5 rounded-lg text-left font-inter text-[11.5px] transition cursor-pointer',
														selectedAngkatan === 'all' ? 'bg-[#183669]/10 font-bold text-[#183669]' : 'text-[#334155] hover:bg-[#f8fafc]'
													]"
												>
													<span>Semua Angkatan</span>
													<svg v-if="selectedAngkatan === 'all'" class="h-3.5 w-3.5 text-[#183669]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
														<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
													</svg>
												</button>
												<button
													v-for="ang in angkatanOptions"
													:key="ang"
													type="button"
													@click="selectAngkatan(ang)"
													:class="[
														'flex w-full items-center justify-between px-2.5 py-1.5 rounded-lg text-left font-inter text-[11.5px] transition cursor-pointer',
														selectedAngkatan === ang ? 'bg-[#183669]/10 font-bold text-[#183669]' : 'text-[#334155] hover:bg-[#f8fafc]'
													]"
												>
													<span>Angkatan {{ ang }}</span>
													<svg v-if="selectedAngkatan === ang" class="h-3.5 w-3.5 text-[#183669]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
														<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
													</svg>
												</button>
											</div>
										</Transition>
									</div>

									<!-- 1. Dropdown Filter Bulan -->
									<div class="relative w-full sm:w-[125px]" ref="bulanDropdownRef">
										<button
											type="button"
											@click="isBulanDropdownOpen = !isBulanDropdownOpen"
											class="h-[34px] sm:h-[36px] w-full rounded-lg border bg-[#fafcff] px-2.5 font-inter text-[11px] sm:text-[12px] transition flex items-center justify-between cursor-pointer focus:outline-none select-none"
											:class="isBulanDropdownOpen ? 'border-[#183669] bg-white' : 'border-[#d6e0ee] hover:border-[#183669] hover:bg-white'"
											title="Filter Bulan"
										>
											<span class="truncate text-[#17334F] font-medium">
												{{ selectedBulanFilter === 'all' ? 'Semua Bulan' : `Bulan ${selectedBulanFilter}` }}
											</span>
											<svg
												:class="['h-3.5 w-3.5 text-[#183669] transition-transform duration-200 shrink-0 ml-1', isBulanDropdownOpen ? 'rotate-180' : '']"
												fill="none"
												stroke="currentColor"
												stroke-width="2"
												viewBox="0 0 24 24"
											>
												<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
											</svg>
										</button>

										<!-- Bulan Dropdown Popover -->
										<Transition
											enter-active-class="transition duration-150 ease-out"
											enter-from-class="transform scale-95 opacity-0 -translate-y-1"
											enter-to-class="transform scale-100 opacity-100 translate-y-0"
											leave-active-class="transition duration-100 ease-in"
											leave-from-class="transform scale-100 opacity-100 translate-y-0"
											leave-to-class="transform scale-95 opacity-0 -translate-y-1"
										>
											<div
												v-if="isBulanDropdownOpen"
												class="absolute right-0 top-full mt-1 z-50 w-[140px] rounded-xl border border-[#d6e0ee] bg-white p-1 shadow-lg"
											>
												<button
													type="button"
													@click="selectBulanFilter('all')"
													:class="[
														'flex w-full items-center justify-between px-2.5 py-1.5 rounded-lg text-left font-inter text-[11.5px] transition cursor-pointer',
														selectedBulanFilter === 'all' ? 'bg-[#183669]/10 font-bold text-[#183669]' : 'text-[#334155] hover:bg-[#f8fafc]'
													]"
												>
													<span>Semua Bulan</span>
													<svg v-if="selectedBulanFilter === 'all'" class="h-3.5 w-3.5 text-[#183669]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
														<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
													</svg>
												</button>
												<button
													v-for="b in monthsOptions"
													:key="b.bulan_ke"
													type="button"
													@click="selectBulanFilter(b.bulan_ke)"
													:class="[
														'flex w-full items-center justify-between px-2.5 py-1.5 rounded-lg text-left font-inter text-[11.5px] transition cursor-pointer',
														selectedBulanFilter === b.bulan_ke ? 'bg-[#183669]/10 font-bold text-[#183669]' : 'text-[#334155] hover:bg-[#f8fafc]'
													]"
												>
													<span>{{ b.bulan_name }}</span>
													<svg v-if="selectedBulanFilter === b.bulan_ke" class="h-3.5 w-3.5 text-[#183669]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
														<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
													</svg>
												</button>
											</div>
										</Transition>
									</div>
								</div>

								<!-- 2. Dropdown Pilih Materi / Tugas (Clean minimal) -->
								<div class="relative shrink-0 w-full sm:w-[210px]" ref="materiDropdownRef">
									<button
										type="button"
										@click="isMateriDropdownOpen = !isMateriDropdownOpen"
										class="h-[34px] sm:h-[36px] w-full rounded-lg border bg-[#fafcff] px-2.5 font-inter text-[11.5px] sm:text-[12.5px] transition flex items-center justify-between cursor-pointer focus:outline-none select-none"
										:class="isMateriDropdownOpen ? 'border-[#183669] bg-white' : 'border-[#d6e0ee] hover:border-[#183669] hover:bg-white'"
										title="Pilih Materi atau Tugas"
									>
										<div class="truncate text-left flex items-center gap-1.5 min-w-0 pr-1">
											<span
												v-if="currentSelectedMateri"
												:class="[
													'px-1.5 py-0.2 rounded text-[9.5px] sm:text-[10px] font-bold shrink-0',
													currentSelectedMateri.tipe === 1 ? 'bg-amber-100 text-amber-900' : 'bg-blue-100 text-blue-900'
												]"
											>
												{{ currentSelectedMateri.tipe === 1 ? 'Tugas' : 'Materi' }}
											</span>
											<span class="truncate text-[#17334F] font-medium text-[11.5px] sm:text-[13px]">
												{{ currentSelectedMateri ? currentSelectedMateri.judul : 'Pilih Materi' }}
											</span>
										</div>
										<svg
											:class="['h-4 w-4 text-[#183669] transition-transform duration-200 shrink-0 ml-1', isMateriDropdownOpen ? 'rotate-180' : '']"
											fill="none"
											stroke="currentColor"
											stroke-width="2"
											viewBox="0 0 24 24"
										>
											<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
										</svg>
									</button>

									<!-- Materials Popover with Grouping by Month & Clean Judul -->
									<Transition
										enter-active-class="transition duration-150 ease-out"
										enter-from-class="transform scale-95 opacity-0 -translate-y-1"
										enter-to-class="transform scale-100 opacity-100 translate-y-0"
										leave-active-class="transition duration-100 ease-in"
										leave-from-class="transform scale-100 opacity-100 translate-y-0"
										leave-to-class="transform scale-95 opacity-0 -translate-y-1"
									>
										<div
											v-if="isMateriDropdownOpen"
											class="absolute right-0 top-full mt-1.5 z-50 w-full sm:w-[350px] max-h-[320px] overflow-y-auto rounded-[12px] border border-[#d6e0ee] bg-white p-1.5 shadow-2xl ring-1 ring-black/5"
										>
											<div v-if="filteredMaterialsForDropdown.length === 0" class="py-4 text-center text-xs text-slate-400 font-inter">
												Tidak ada materi pada bulan ini
											</div>

											<button
												v-for="m in filteredMaterialsForDropdown"
												:key="m.id"
												type="button"
												@click="selectMateri(m.id)"
												:class="[
													'flex w-full items-start gap-2.5 px-3 py-2.5 rounded-[8px] text-left font-inter text-[12px] sm:text-[12.5px] transition cursor-pointer',
													selectedMateriId === m.id ? 'bg-[#183669]/10 font-bold text-[#183669]' : 'text-[#334155] hover:bg-[#f8fafc]'
												]"
											>
												<span
													:class="[
														'px-1.5 py-0.5 rounded text-[10px] font-bold shrink-0 mt-0.5',
														m.tipe === 1 ? 'bg-amber-100 text-amber-900 border border-amber-200' : 'bg-blue-100 text-blue-900 border border-blue-200'
													]"
												>
													{{ m.tipe === 1 ? 'Tugas' : 'Materi' }}
												</span>

												<div class="min-w-0 flex-1">
													<!-- Clean Judul (No 'materi 1:' or 'tugas 1:') -->
													<p class="font-semibold text-xs leading-snug line-clamp-2 text-[#17334F]">{{ m.judul }}</p>
													<span class="inline-block mt-0.5 font-medium text-[10.5px] text-[#7188a3]">
														{{ m.bulan_name }}
													</span>
												</div>

												<svg v-if="selectedMateriId === m.id" class="h-4 w-4 text-[#183669] shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
													<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
												</svg>
											</button>
										</div>
									</Transition>
								</div>
							</template>
						</div>

						<!-- TAB 2 SUB-HEADER: Status Filter Tabs & Ringkasan Materi Aktif -->
						<div v-if="activeTab === 'materi' && currentSelectedMateri" class="px-3.5 sm:px-6 py-2 bg-[#f8fafc] border-b border-[#eef2f6] flex flex-col sm:flex-row sm:items-center justify-between gap-2">
							<!-- Sisi Kiri: Status Filter Tabs (Semua, Sudah, Belum) -->
							<div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar max-w-full">
								<!-- Semua -->
								<button
									type="button"
									@click="materiStatusFilter = 'all'"
									:class="[
										'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold transition cursor-pointer select-none shrink-0',
										materiStatusFilter === 'all'
											? 'bg-[#183669] text-white'
											: 'bg-white border border-[#d6e0ee] text-[#64748b] hover:bg-slate-50'
									]"
								>
									<span>Semua</span>
									<span class="text-[10px] font-bold opacity-80">({{ materiStudents.totalInFilter }})</span>
								</button>

								<!-- Sudah Mengerjakan -->
								<button
									type="button"
									@click="materiStatusFilter = 'completed'"
									:class="[
										'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold transition cursor-pointer select-none shrink-0',
										materiStatusFilter === 'completed'
											? 'bg-emerald-600 text-white'
											: 'bg-white border border-[#d6e0ee] text-[#059669] hover:bg-emerald-50'
									]"
								>
									<span>Sudah</span>
									<span class="text-[10px] font-bold opacity-80">({{ materiStudents.completed.length }})</span>
								</button>

								<!-- Belum Mengerjakan -->
								<button
									type="button"
									@click="materiStatusFilter = 'pending'"
									:class="[
										'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold transition cursor-pointer select-none shrink-0',
										materiStatusFilter === 'pending'
											? 'bg-slate-700 text-white'
											: 'bg-white border border-[#d6e0ee] text-[#64748b] hover:bg-slate-50'
									]"
								>
									<span>Belum</span>
									<span class="text-[10px] font-bold opacity-80">({{ materiStudents.pending.length }})</span>
								</button>
							</div>

							<!-- Sisi Kanan: Status & Progres Materi yang Sedang Dipilih -->
							<div class="flex items-center justify-between sm:justify-end gap-2 text-[11px] text-[#64748b] font-inter">
								<span class="truncate max-w-[200px] sm:max-w-[260px] text-[#17334F] font-medium" :title="currentSelectedMateri.judul">
									{{ currentSelectedMateri.judul }}
								</span>
								<span>•</span>
								<span class="shrink-0 font-bold text-[#183669]">
									{{ materiStudents.completed.length }}/{{ materiStudents.totalInFilter }} Selesai ({{ materiStudents.totalInFilter > 0 ? Math.round((materiStudents.completed.length / materiStudents.totalInFilter) * 100) : 0 }}%)
								</span>
							</div>
						</div>

						<!-- Modal Body: Content Area (Full height scrollable, clean minimalist cards) -->
						<div class="px-3.5 sm:px-6 py-3 overflow-y-auto flex-1 min-h-0 space-y-2">
							<!-- Loading / Filtering Skeleton State (Presisi dengan count data saat ini) -->
							<div v-if="isLoading || isFiltering" class="py-1">
								<CardSkeleton type="participant" :count="preciseSkeletonCount" :active-tab="activeTab" />
							</div>

							<!-- Error State -->
							<div v-else-if="errorMsg" class="py-10 text-center">
								<div class="h-12 w-12 mx-auto rounded-full bg-red-100 text-red-500 flex items-center justify-center mb-3">
									<svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
								</div>
								<p class="text-sm font-semibold text-red-600">{{ errorMsg }}</p>
								<button
									type="button"
									@click="fetchParticipants"
									class="mt-3 px-3 py-1.5 text-xs font-bold text-[#183669] bg-white border border-[#183669] rounded-lg hover:bg-slate-50 transition cursor-pointer"
								>
									Coba Lagi
								</button>
							</div>

							<!-- Empty State (No Participants Enrolled) -->
							<div v-else-if="participants.length === 0" class="py-12 text-center">
								<div class="h-14 w-14 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
									<svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
									</svg>
								</div>
								<h3 class="text-base font-bold text-[#17334F]">Belum Ada Partisipan</h3>
								<p class="font-inter text-xs text-[#7188a3] mt-1 max-w-sm mx-auto">
									Belum ada mahasiswa yang mendaftar ke roadmap ini. Mahasiswa dapat mendaftar melalui portal mahasiswa.
								</p>
							</div>

							<!-- ================= TAB 1: LIST PER MAHASISWA ================= -->
							<template v-else-if="activeTab === 'mahasiswa'">
								<!-- Search Empty State -->
								<div v-if="filteredParticipants.length === 0" class="py-10 text-center">
									<div class="h-12 w-12 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
										<svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
									</div>
									<p class="text-sm font-semibold text-[#17334F]">Tidak Ada Hasil</p>
									<p class="font-inter text-xs text-[#7188a3] mt-0.5">
										Tidak ditemukan mahasiswa yang cocok dengan filter pencarian.
									</p>
								</div>

								<!-- Student Cards (Paginated) -->
								<div
									v-for="(p, index) in paginatedList"
									:key="p.id"
									class="rounded-xl border border-[#eef2f6] bg-white hover:border-[#183669]/30 transition overflow-hidden shadow-2xs"
								>
									<!-- Card Main Row -->
									<div class="p-3 sm:p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-4">
										<!-- Left: Avatar & Profile Info -->
										<div class="flex items-center gap-2.5 min-w-0">
											<div
												:class="[
													'h-8 w-8 sm:h-9 sm:w-9 shrink-0 rounded-full flex items-center justify-center font-bold text-[11px] sm:text-[12px] border',
													getAvatarStyle().bg,
													getAvatarStyle().text,
													getAvatarStyle().border,
												]"
											>
												{{ getInitials(p.nama) }}
											</div>

											<div class="min-w-0 flex-1">
												<div class="flex items-center gap-1.5 flex-wrap">
													<h4 class="text-[12.5px] sm:text-[13.5px] font-bold text-[#17334F] truncate" :title="p.nama">
														{{ p.nama }}
													</h4>
													<!-- Status Pill -->
													<span
														v-if="p.progress >= 100"
														class="inline-flex items-center gap-0.5 rounded px-1.5 py-0.2 text-[9.5px] font-bold bg-emerald-50 text-emerald-700"
													>
														Lulus
													</span>
													<span
														v-else-if="p.progress > 0"
														class="inline-flex items-center rounded px-1.5 py-0.2 text-[9.5px] font-semibold bg-sky-50 text-sky-700"
													>
														Aktif
													</span>
												</div>

												<div class="flex flex-wrap items-center gap-1.5 text-[10.5px] sm:text-[11px] text-[#7188a3] font-inter">
													<span class="font-medium text-[#183669]">{{ p.nim }}</span>
													<span>•</span>
													<span>Angk. {{ p.angkatan }}</span>
													<template v-if="p.talent_mapping && p.talent_mapping !== '-'">
														<span>•</span>
														<span class="text-[#059669] font-medium truncate max-w-[120px]">
															{{ p.talent_mapping }}
														</span>
													</template>
												</div>
											</div>
										</div>

										<!-- Right: Progress Bar & Accordion Button -->
										<div class="flex items-center justify-between sm:justify-end gap-2.5 w-full sm:w-auto pt-1 sm:pt-0 border-t sm:border-t-0 border-slate-100">
											<div class="flex-1 sm:w-36 shrink-0">
												<div class="flex items-center justify-between text-[11px] mb-0.5">
													<span class="text-[10px] text-[#8ca1b9] font-inter">{{ p.completed }}/{{ p.total }} tugas</span>
													<span class="font-bold text-[11.5px]" :class="p.progress >= 100 ? 'text-[#059669]' : 'text-[#183669]'">
														{{ p.progress }}%
													</span>
												</div>
												<div class="h-1.5 w-full rounded-full bg-[#eef2f6] overflow-hidden">
													<div
														class="h-full rounded-full transition-all duration-300"
														:class="p.progress >= 100 ? 'bg-[#059669]' : 'bg-[#183669]'"
														:style="{ width: `${p.progress}%` }"
													></div>
												</div>
											</div>

											<!-- Expand / Collapse Checklist Button -->
											<button
												type="button"
												@click="toggleStudentExpand(p.id)"
												class="h-7 w-7 sm:h-8 sm:w-8 shrink-0 flex items-center justify-center rounded-lg border border-[#d6e0ee] bg-[#fafcff] text-[#183669] hover:bg-white transition cursor-pointer"
												:title="expandedStudentIds.has(p.id) ? 'Sembunyikan rincian' : 'Lihat rincian'"
											>
												<svg
													:class="['h-3.5 w-3.5 transition-transform duration-200', expandedStudentIds.has(p.id) ? 'rotate-180' : '']"
													fill="none"
													stroke="currentColor"
													stroke-width="2"
													viewBox="0 0 24 24"
												>
													<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
												</svg>
											</button>
										</div>
									</div>

									<!-- Expanded Checklist: Breakdown of Materials for This Student (Clean minimal dengan animasi smooth) -->
									<div
										class="grid transition-all duration-300 ease-in-out border-t border-[#f0f4f9] bg-[#fafcff]"
										:class="expandedStudentIds.has(p.id) ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0 border-t-transparent pointer-events-none'"
									>
										<div class="overflow-hidden">
											<div class="px-3 sm:px-3.5 pb-3 pt-2.5">
												<div class="flex items-center justify-between mb-2">
													<p class="text-[10.5px] font-bold text-[#7188a3]">
														Rincian Aktivitas
													</p>
													<span class="text-[10.5px] font-semibold text-[#183669]">
														{{ (p.completed_materi_ids || []).length }}/{{ materials.length }} Selesai
													</span>
												</div>

												<div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
													<div
														v-for="m in materials"
														:key="m.id"
														:class="[
															'px-2.5 py-1.5 rounded-lg border text-[11px] sm:text-[11.5px] flex items-center justify-between gap-2 font-inter transition',
															(p.completed_materi_ids || []).includes(m.id)
																? 'bg-emerald-50/50 border-emerald-200/80 text-emerald-950'
																: 'bg-white border-[#eef2f6] text-[#64748b]'
														]"
													>
														<div class="min-w-0 flex items-center gap-1.5">
															<svg v-if="(p.completed_materi_ids || []).includes(m.id)" class="h-3.5 w-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
																<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
															</svg>
															<span v-else class="h-2 w-2 rounded-full bg-slate-300 shrink-0"></span>

															<p class="font-medium truncate leading-tight">{{ m.judul }}</p>
														</div>

														<span class="text-[10px] text-slate-400 shrink-0">{{ m.bulan_name }}</span>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</template>

							<!-- ================= TAB 2: LIST PER MATERI & TUGAS ================= -->
							<template v-else-if="activeTab === 'materi'">
								<!-- Search Empty State -->
								<div v-if="materiStudents.filteredList.length === 0" class="py-10 text-center">
									<div class="h-10 w-10 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-1.5">
										<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
									</div>
									<p class="text-xs font-semibold text-[#17334F]">Tidak Ada Mahasiswa</p>
									<p class="font-inter text-[11px] text-[#7188a3] mt-0.5">
										Tidak ditemukan mahasiswa dengan kriteria filter saat ini.
									</p>
								</div>

								<!-- Student Cards Filtered by this Specific Materi (Paginated) -->
								<div
									v-for="(p, index) in paginatedList"
									:key="`materi-student-${p.id}`"
									:class="[
										'p-2.5 sm:p-3 rounded-xl border transition flex items-center justify-between gap-2.5 shadow-2xs',
										p.is_completed_for_this_materi
											? 'border-emerald-200/80 bg-emerald-50/20'
											: 'border-[#eef2f6] bg-white'
									]"
								>
									<div class="flex items-center gap-2.5 min-w-0 flex-1">
										<div
											:class="[
												'h-8 w-8 shrink-0 rounded-full flex items-center justify-center font-bold text-[11px] border',
												getAvatarStyle().bg,
												getAvatarStyle().text,
												getAvatarStyle().border,
											]"
										>
											{{ getInitials(p.nama) }}
										</div>

										<div class="min-w-0 flex-1">
											<h4 class="text-[12.5px] sm:text-[13px] font-bold text-[#17334F] truncate" :title="p.nama">
												{{ p.nama }}
											</h4>
											<div class="flex flex-wrap items-center gap-1.5 text-[10.5px] text-[#7188a3] font-inter">
												<span class="font-medium text-[#183669]">{{ p.nim }}</span>
												<span>•</span>
												<span>Angk. {{ p.angkatan }}</span>
												<template v-if="p.talent_mapping && p.talent_mapping !== '-'">
													<span>•</span>
													<span class="text-[#059669] font-medium truncate max-w-[120px]">
														{{ p.talent_mapping }}
													</span>
												</template>
											</div>
										</div>
									</div>

									<!-- Completion Status on this Item -->
									<div class="shrink-0">
										<span
											v-if="p.is_completed_for_this_materi"
											class="inline-flex items-center gap-1 rounded-md bg-emerald-100 text-emerald-800 px-2 py-0.5 text-[10.5px] font-bold"
										>
											<svg class="h-3 w-3 text-emerald-700 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
											</svg>
											<span>Sudah</span>
										</span>
										<span
											v-else
											class="inline-flex items-center gap-1 rounded-md bg-slate-100 text-slate-600 px-2 py-0.5 text-[10.5px] font-semibold"
										>
											<span class="h-1.5 w-1.5 rounded-full bg-slate-400 shrink-0"></span>
											<span>Belum</span>
										</span>
									</div>
								</div>
							</template>
						</div>

						<!-- Modal Footer (TablePagination & Tutup Button) -->
						<div class="px-3 sm:px-6 py-2 border-t border-[#eef2f6] bg-[#fafcff] flex flex-col sm:flex-row items-center justify-between gap-2.5 relative z-20 overflow-visible">
							<div class="w-full sm:flex-1">
								<TablePagination
									:current-page="currentPage"
									:total-pages="totalPagesInTab"
									:total-items="totalItemsInTab"
									:rows-per-page="rowsPerPage"
									:rows-options="[5, 10, 20]"
									item-label="Mhs"
									:disabled="isLoading || isFiltering"
									@update:current-page="handlePageChange"
									@update:rows-per-page="handleRowsPerPageChange"
								/>
							</div>
							<button
								type="button"
								@click="handleClose"
								class="hidden sm:inline-flex px-3.5 py-1.5 rounded-lg bg-[#183669] text-white hover:bg-[#122b54] text-[12px] font-bold transition shadow-xs cursor-pointer shrink-0"
							>
								Tutup
							</button>
						</div>
					</div>
				</Transition>
			</div>
		</Transition>
	</Teleport>
</template>
