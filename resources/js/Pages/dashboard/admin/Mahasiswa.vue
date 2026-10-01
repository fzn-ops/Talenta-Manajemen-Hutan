<script setup>
import AdminLayout from '@/Layouts/dashboard/AdminLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import EditButtonTable from '@/Components/dashboard/EditButtonTable.vue';
import DeleteButtonTable from '@/Components/dashboard/DeleteButtonTable.vue';
import TablePagination from '@/Components/dashboard/TablePagination.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import SearchBarTable from '@/Components/dashboard/SearchBarTable.vue';
import ModalFormMahasiswa from '@/Components/dashboard/admin/ModalFormMahasiswa.vue';
import ModalImportMahasiswa from '@/Components/dashboard/admin/ModalImportMahasiswa.vue';
import ModalBulkDeleteMahasiswa from '@/Components/dashboard/admin/ModalBulkDeleteMahasiswa.vue';
import DeleteModal from '@/Components/dashboard/DeleteModal.vue';

const props = defineProps({
	students: {
		type: Array,
		default: () => [
			{ id: 1, nim: 'J0403231085', name: 'Fauzan Fuadiansyah', angkatan: '60', talent_mapping: 'Profesional', email: 'novafauzan@gmail.com', phone: '+62 812-3456-7890' },
			{ id: 2, nim: 'J0403231075', name: 'Farhan Hakim', angkatan: '60', talent_mapping: 'Bisnis', email: '01122005farhan@apps.ipb.ac.id', phone: '+62 895-6228-15861' },
			{ id: 3, nim: 'E1401201012', name: 'Eko Prasetyo', angkatan: '62', talent_mapping: 'Akademisi', email: 'eko.prasetyo@apps.ipb.ac.id', phone: '+62 857-5678-9012' },
			{ id: 4, nim: 'E1401211025', name: 'Sri Wahyuni', angkatan: '61', talent_mapping: 'Birokrat', email: 'sri.wahyuni@apps.ipb.ac.id', phone: '+62 822-4567-8901' },
			{ id: 5, nim: 'E1401221044', name: 'Bambang Triyono', angkatan: '62', talent_mapping: 'Profesional', email: 'bambang.t@apps.ipb.ac.id', phone: '+62 819-3456-7890' },
			{ id: 6, nim: 'E1401221089', name: 'Nurul Hidayah', angkatan: '62', talent_mapping: 'Bisnis', email: 'nurul.h@apps.ipb.ac.id', phone: '+62 812-2345-6789' },
			{ id: 7, nim: 'E1401201067', name: 'Dedi Kusnadi', angkatan: '60', talent_mapping: 'Birokrat', email: 'dedi.kusnadi@apps.ipb.ac.id', phone: '+62 878-9012-3456' },
			{ id: 8, nim: 'E1401211053', name: 'Rina Marlina', angkatan: '61', talent_mapping: 'Akademisi', email: 'rina.marlina@apps.ipb.ac.id', phone: '+62 856-7890-1234' },
			{ id: 9, nim: 'E1401211098', name: 'Ahmad Fauzi', angkatan: '61', talent_mapping: 'Profesional', email: 'ahmad.fauzi@apps.ipb.ac.id', phone: '+62 821-1234-5678' },
			{ id: 10, nim: 'E1401201015', name: 'Hendra Setiawan', angkatan: '60', talent_mapping: 'Bisnis', email: 'hendra.s@apps.ipb.ac.id', phone: '+62 815-6789-0123' },
		],
	},
	mahasiswa: {
		type: Array,
		default: null,
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

// Reactive Students List
const students = ref([]);

const initStudentsData = () => {
	const sourceData = props.mahasiswa || props.students || [];
	students.value = sourceData.map((item, index) => ({
		id: item.id || index + 1,
		nim: item.nim || item.nip || '',
		name: item.name || item.namaMahasiswa || item.namaDosen || item.nama || '',
		angkatan: item.angkatan || '',
		talent_mapping: item.talent_mapping || item.talentMapping || item.talent || item.hasilTalentMapping || '',
		username: item.username || '',
		password: item.password || '',
		email: item.email || '',
		phone: item.phone || item.noHp || item.nomorHandphone || '',
	}));
};

watch(
	() => [props.students, props.mahasiswa],
	() => {
		initStudentsData();
	},
	{ immediate: true, deep: true }
);

// Filter State & Logic (Angkatan & Talent Mapping)
const isFilterOpen = ref(false);
const selectedAngkatan = ref([]);
const selectedTalentMapping = ref([]);
const talentMappingFilterOptions = ['Profesional', 'Bisnis', 'Birokrat', 'Akademisi'];
const filterContainerRef = ref(null);
const filterDropdownStyle = ref({});

const hasActiveFilters = computed(() => {
	return selectedAngkatan.value.length > 0 || selectedTalentMapping.value.length > 0;
});

const calculateFilterPlacement = () => {
	if (!filterContainerRef.value || typeof window === 'undefined') return;
	const rect = filterContainerRef.value.getBoundingClientRect();
	const windowWidth = window.innerWidth;
	const padding = 16; // Margin aman dari tepi layar

	// Lebar dropdown responsif
	const desiredWidth = Math.min(276, windowWidth - padding * 2);

	const style = {
		width: `${desiredWidth}px`,
		maxWidth: `calc(100vw - ${padding * 2}px)`,
	};

	// Posisi horizontal: default rata kanan dengan tombol filter
	let targetScreenLeft = rect.right - desiredWidth;

	// Jika menabrak tepi kiri layar, geser agar tidak terpotong (beri ruang aman)
	if (targetScreenLeft < padding) {
		targetScreenLeft = padding;
	}

	// Jika menabrak tepi kanan layar, kunci di batas kanan
	if (targetScreenLeft + desiredWidth > windowWidth - padding) {
		targetScreenLeft = windowWidth - padding - desiredWidth;
	}

	// Konversi koordinat layar ke offset relatif tombol
	const relativeLeft = targetScreenLeft - rect.left;
	style.left = `${relativeLeft}px`;
	style.right = 'auto';

	// Cek posisi vertikal (buka ke atas jika sisa ruang di bawah kurang dari 300px dan ruang atas lebih luas)
	const spaceBelow = window.innerHeight - rect.bottom;
	const spaceAbove = rect.top;
	const dropdownHeight = 300;

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
	selectedAngkatan.value = [];
	selectedTalentMapping.value = [];
};

const availableAngkatans = computed(() => {
	const angkatans = new Set();
	students.value.forEach((s) => {
		if (s.angkatan) {
			angkatans.add(s.angkatan.toString());
		}
	});
	return Array.from(angkatans).sort((a, b) => a.localeCompare(b, undefined, { numeric: true }));
});

const getStudentCountByAngkatan = (angkatan) => {
	return students.value.filter((s) => s.angkatan?.toString() === angkatan.toString()).length;
};

const getStudentCountByTalentMapping = (talent) => {
	return students.value.filter((s) => (s.talent_mapping || '').toLowerCase() === talent.toLowerCase()).length;
};

// Loading Animation
const isLoading = ref(true);
onMounted(() => {
	initStudentsData();
	setTimeout(() => {
		isLoading.value = false;
	}, 250);
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

// Sorting
const columns = [
	{ key: 'nim', label: 'NIM', sortable: true, width: 'w-[130px]' },
	{ key: 'name', label: 'Nama Mahasiswa', sortable: true, width: 'w-[190px]' },
	{ key: 'angkatan', label: 'Angkatan', sortable: true, width: 'w-[100px]' },
	{ key: 'talent_mapping', label: 'Talent Mapping', sortable: true, width: 'w-[150px]' },
	{ key: 'email', label: 'Email', sortable: true, width: 'w-[180px]' },
	{ key: 'phone', label: 'Nomor Handphone', sortable: true, width: 'w-[150px]' },
	{ key: 'action', label: 'Aksi', sortable: false, width: 'w-[90px]' },
];

const sortKey = ref('id');
const sortDirection = ref('asc');

const toggleSort = (key) => {
	if (sortKey.value === key) {
		sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
	} else {
		sortKey.value = key;
		sortDirection.value = key === 'id' ? 'asc' : 'asc';
	}
	currentPage.value = 1;
};

// Filtered and Sorted Students
const filteredAndSortedStudents = computed(() => {
	let list = [...students.value];

	// Search Query (NIM, Nama Mahasiswa, Angkatan, Talent Mapping, Email, Phone)
	if (searchQuery.value.trim()) {
		const q = searchQuery.value.toLowerCase().trim();
		list = list.filter(
			(s) =>
				(s.name && s.name.toLowerCase().includes(q)) ||
				(s.nim && s.nim.toLowerCase().includes(q)) ||
				(s.angkatan && s.angkatan.toString().toLowerCase().includes(q)) ||
				(s.talent_mapping && s.talent_mapping.toLowerCase().includes(q)) ||
				(s.email && s.email.toLowerCase().includes(q)) ||
				(s.phone && s.phone.toLowerCase().includes(q))
		);
	}

	// Filter by Angkatan
	if (selectedAngkatan.value.length > 0) {
		list = list.filter((s) => selectedAngkatan.value.includes(s.angkatan?.toString()));
	}

	// Filter by Talent Mapping
	if (selectedTalentMapping.value.length > 0) {
		list = list.filter((s) => selectedTalentMapping.value.includes(s.talent_mapping));
	}

	// Sorting
	if (sortKey.value === 'id') {
		list.sort((a, b) => {
			const idA = Number(a.id) || 0;
			const idB = Number(b.id) || 0;
			return sortDirection.value === 'asc' ? idA - idB : idB - idA;
		});
	} else if (sortKey.value) {
		list.sort((a, b) => {
			const valA = (a[sortKey.value] ?? '').toString().toLowerCase();
			const valB = (b[sortKey.value] ?? '').toString().toLowerCase();

			if (valA === valB) return 0;

			if (sortDirection.value === 'asc') {
				return valA > valB ? 1 : -1;
			}
			return valA < valB ? 1 : -1;
		});
	}

	return list;
});

// Pagination State & Controls
const currentPage = ref(1);
const rowsPerPage = ref(10);

const totalPages = computed(() => {
	const count = Math.ceil(filteredAndSortedStudents.value.length / rowsPerPage.value);
	return count > 0 ? count : 1;
});

const paginatedStudents = computed(() => {
	const start = (currentPage.value - 1) * rowsPerPage.value;
	return filteredAndSortedStudents.value.slice(start, start + rowsPerPage.value);
});

// Reset to page 1 on search, filter, or rowsPerPage change
watch([searchQuery, selectedAngkatan, selectedTalentMapping, rowsPerPage], () => {
	currentPage.value = 1;
});

// MODAL TAMBAH / EDIT MAHASISWA
const isFormModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const selectedStudent = ref(null);

const openCreateModal = () => {
	isEditing.value = false;
	editingId.value = null;
	selectedStudent.value = null;
	isFormModalOpen.value = true;
};

const openEditModal = (student) => {
	isEditing.value = true;
	editingId.value = student.id;
	selectedStudent.value = { ...student };
	isFormModalOpen.value = true;
};

const handleFormSubmit = (formData) => {
	if (isEditing.value && editingId.value) {
		const targetIndex = students.value.findIndex((s) => s.id === editingId.value);
		if (targetIndex !== -1) {
			students.value[targetIndex] = {
				...students.value[targetIndex],
				...formData,
			};
		}
		isFormModalOpen.value = false;
		showToast('success', 'Berhasil Diperbarui', `Data mahasiswa "${formData.name}" berhasil diperbarui.`);
	} else {
		const newId = students.value.length > 0 ? Math.max(...students.value.map((s) => s.id)) + 1 : 1;
		students.value.unshift({
			id: newId,
			...formData,
		});
		isFormModalOpen.value = false;
		showToast('success', 'Berhasil Ditambahkan', `Akun mahasiswa "${formData.name}" berhasil ditambahkan.`);
	}
};

// MODAL IMPORT DATA MAHASISWA
const isImportModalOpen = ref(false);

const openImportModal = () => {
	isImportModalOpen.value = true;
};

const handleImportSubmit = (importedStudents) => {
	if (Array.isArray(importedStudents) && importedStudents.length > 0) {
		students.value.unshift(...importedStudents);
		showToast('success', 'Import Berhasil', `${importedStudents.length} data mahasiswa berhasil diimpor ke sistem.`);
	}
};

// MODAL BULK DELETE MAHASISWA
const isBulkDeleteModalOpen = ref(false);

const openBulkDeleteModal = () => {
	isBulkDeleteModalOpen.value = true;
};

const closeBulkDeleteModal = () => {
	isBulkDeleteModalOpen.value = false;
};

const handleBulkDeleteConfirm = ({ angkatans, count }) => {
	setTimeout(() => {
		students.value = students.value.filter((s) => !angkatans.includes(s.angkatan?.toString()));
		isBulkDeleteModalOpen.value = false;
		showToast('success', 'Berhasil Dihapus', `${count} data mahasiswa angkatan ${angkatans.join(', ')} berhasil dihapus.`);
	}, 300);
};

// MODAL DELETE CONFIRMATION
const isDeleteModalOpen = ref(false);
const deletingStudent = ref(null);
const isDeleting = ref(false);

const openDeleteModal = (student) => {
	deletingStudent.value = student;
	isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
	if (isDeleting.value) return;
	isDeleteModalOpen.value = false;
	deletingStudent.value = null;
};

const confirmDeleteStudent = () => {
	if (!deletingStudent.value) return;
	const student = deletingStudent.value;
	isDeleting.value = true;

	setTimeout(() => {
		students.value = students.value.filter((s) => s.id !== student.id);
		isDeleting.value = false;
		isDeleteModalOpen.value = false;
		deletingStudent.value = null;
		showToast('success', 'Berhasil Dihapus', `Data mahasiswa "${student.name}" berhasil dihapus.`);
	}, 200);
};
</script>

<template>
	<Head title="Daftar Mahasiswa" />

	<AdminLayout>
		<section class="mx-auto w-full max-w-[1520px] px-4 py-6 font-poppins sm:px-6 sm:py-8 lg:px-8">
			<div class="space-y-6">
				<!-- Header Title & Subtitle -->
				<div class="space-y-1.5">
					<h1 class="mt-1 text-[34px] font-bold leading-[1.02] tracking-[-0.03em] text-[#17334F] sm:text-[42px] lg:text-[48px]">
						Daftar Mahasiswa
					</h1>
					<p class="mt-1.5 font-inter text-[14px] font-medium leading-tight text-[#4d6786] sm:text-[16px]">
						Lihat data mahasiswa, perbarui, atau tambahkan mahasiswa baru
					</p>
				</div>

				<!-- Action Bar (Search, Filter, Import, Bulk Delete, Tambah Button) -->
				<div class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:gap-3">
					<!-- Search Input Component (Full width on mobile, flex-1 on tablet/desktop) -->
					<SearchBarTable
						v-model="searchQuery"
						placeholder="Cari NIM atau Nama Mahasiswa disini"
					/>

					<!-- Action Buttons Row (Right aligned on mobile & desktop) -->
					<div class="flex items-center justify-end gap-2 sm:gap-3 w-full sm:w-auto shrink-0">
						<!-- Single Filter Button with Unified Dropdown -->
						<div ref="filterContainerRef" class="relative" @click.stop @keydown.escape="isFilterOpen = false">
							<button
								type="button"
								@click="toggleFilterDropdown"
								class="relative flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-[10px] border-2 bg-transparent text-[#183669] transition-colors focus:outline-none select-none cursor-pointer"
								:class="isFilterOpen || hasActiveFilters
									? 'border-[#183669]'
									: 'border-[#d6e0ee] hover:border-[#8ea9cb]'"
								title="Filter Mahasiswa"
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

							<!-- Unified Filter Dropdown Menu (Angkatan & Talent Mapping) -->
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
											Filter Mahasiswa
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

									<div class="mt-2.5 max-h-[300px] overflow-y-auto space-y-3 pr-1">
										<!-- Section 1: Filter Angkatan -->
										<div>
											<p class="px-2 text-[11px] font-semibold uppercase tracking-wider text-[#7188a3]">
												Angkatan
											</p>
											<div class="mt-1 space-y-0.5">
												<label
													v-for="ang in availableAngkatans"
													:key="ang"
													class="flex items-center justify-between rounded-lg px-2 py-1.5 hover:bg-[#f8fafc] cursor-pointer transition select-none"
												>
													<div class="flex items-center gap-2.5">
														<input
															type="checkbox"
															:value="ang"
															v-model="selectedAngkatan"
															class="h-4 w-4 rounded border-[#cbd5e1] text-[#183669] focus:ring-0 focus:ring-offset-0 focus:outline-none focus-visible:outline-none cursor-pointer"
														/>
														<span class="text-xs font-medium text-[#334155]">
															Angkatan {{ ang }}
														</span>
													</div>
													<span class="text-[11px] font-semibold text-[#64748b] bg-[#f1f5f9] px-1.5 py-0.5 rounded">
														{{ getStudentCountByAngkatan(ang) }}
													</span>
												</label>
												<div v-if="availableAngkatans.length === 0" class="py-1 px-2 text-xs text-gray-400">
													Tidak ada data angkatan
												</div>
											</div>
										</div>

										<!-- Section 2: Filter Talent Mapping -->
										<div class="border-t border-[#f0f4f9] pt-2">
											<p class="px-2 text-[11px] font-semibold uppercase tracking-wider text-[#7188a3]">
												Talent Mapping
											</p>
											<div class="mt-1 space-y-0.5">
												<label
													v-for="talent in talentMappingFilterOptions"
													:key="talent"
													class="flex items-center justify-between rounded-lg px-2 py-1.5 hover:bg-[#f8fafc] cursor-pointer transition select-none"
												>
													<div class="flex items-center gap-2.5">
														<input
															type="checkbox"
															:value="talent"
															v-model="selectedTalentMapping"
															class="h-4 w-4 rounded border-[#cbd5e1] text-[#183669] focus:ring-0 focus:ring-offset-0 focus:outline-none focus-visible:outline-none cursor-pointer"
														/>
														<span class="text-xs font-medium text-[#334155]">
															{{ talent }}
														</span>
													</div>
													<span class="text-[11px] font-semibold text-[#64748b] bg-[#f1f5f9] px-1.5 py-0.5 rounded">
														{{ getStudentCountByTalentMapping(talent) }}
													</span>
												</label>
											</div>
										</div>
									</div>
								</div>
							</Transition>
						</div>

						<!-- Import Button -->
						<button
							type="button"
							@click="(e) => { e.currentTarget?.blur(); openImportModal(); }"
							class="flex h-[46px] w-[46px] sm:w-auto shrink-0 items-center justify-center gap-2 rounded-[10px] border-2 border-[#d6e0ee] bg-transparent px-0 sm:px-4 font-poppins text-[14px] font-semibold text-[#183669] transition-colors hover:border-[#8ea9cb] focus:border-[#183669] focus:outline-none active:scale-95 select-none cursor-pointer"
							title="Import Data Mahasiswa (Excel / CSV)"
						>
							<svg class="h-5 w-5 shrink-0 text-[#183669]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
							</svg>
							<span class="hidden sm:inline">Import</span>
						</button>

						<!-- Bulk Delete Button -->
						<button
							type="button"
							@click="(e) => { e.currentTarget?.blur(); openBulkDeleteModal(); }"
							class="flex h-[46px] w-[46px] sm:w-auto shrink-0 items-center justify-center gap-2 rounded-[10px] bg-red-600 px-0 sm:px-4 font-poppins text-[14px] font-semibold text-white shadow-sm transition hover:bg-red-700 active:scale-95 focus:outline-none select-none cursor-pointer"
							title="Hapus Banyak Data Mahasiswa"
						>
							<svg class="h-[18px] w-auto shrink-0 text-white" viewBox="0 0 14 18" fill="currentColor">
								<path d="M0.942857 15.6444C0.942857 16.72 1.79143 17.6 2.82857 17.6H10.3714C11.4086 17.6 12.2571 16.72 12.2571 15.6444V5.86667C12.2571 4.79111 11.4086 3.91111 10.3714 3.91111H2.82857C1.79143 3.91111 0.942857 4.79111 0.942857 5.86667V15.6444ZM12.2571 0.977778H9.9L9.23057 0.283556C9.06086 0.107556 8.81571 0 8.57057 0H4.62943C4.38429 0 4.13914 0.107556 3.96943 0.283556L3.3 0.977778H0.942857C0.424286 0.977778 0 1.41778 0 1.95556C0 2.49333 0.424286 2.93333 0.942857 2.93333H12.2571C12.7757 2.93333 13.2 2.49333 13.2 1.95556C13.2 1.41778 12.7757 0.977778 12.2571 0.977778Z" />
							</svg>
							<span class="hidden sm:inline">Hapus Banyak</span>
						</button>

						<!-- Tambah Button -->
						<button
							type="button"
							@click="(e) => { e.currentTarget?.blur(); openCreateModal(); }"
							class="flex h-[46px] w-[46px] sm:w-auto shrink-0 items-center justify-center gap-2 rounded-[10px] bg-[#183669] px-0 sm:px-7 font-poppins text-[15px] font-semibold text-white shadow-sm transition hover:bg-[#122b54] active:scale-95 focus:outline-none select-none cursor-pointer"
							title="Tambah Mahasiswa"
						>
							<svg class="h-5 w-5 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
							</svg>
							<span class="hidden sm:inline">Tambah</span>
						</button>
					</div>
				</div>

				<!-- Table Section -->
				<div class="overflow-x-auto rounded-[12px] bg-white shadow-sm ring-1 ring-[#d6e0ee]">
					<table class="w-full min-w-[1040px] table-fixed border-collapse text-sm">
						<!-- Table Header (Matching signature green styling, centered) -->
						<thead class="bg-[#416f65]">
							<tr class="h-[48px]">
								<!-- No -->
								<th class="w-[50px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0">
									<button
										type="button"
										@click="toggleSort('id')"
										class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer"
										title="Urutkan No"
									>
										<span>No</span>
										<span class="absolute left-full ml-0.5 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
											<svg
												v-if="sortKey === 'id'"
												:class="[
													'h-3.5 w-3.5 text-white transition-transform duration-200',
													sortDirection === 'desc' ? 'rotate-180' : ''
												]"
												viewBox="0 0 20 20"
												fill="currentColor"
											>
												<path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" />
											</svg>
											<svg
												v-else
												class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100"
												viewBox="0 0 20 20"
												fill="currentColor"
											>
												<path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" />
											</svg>
										</span>
									</button>
								</th>

								<!-- Columns (Centered Headers) -->
								<th
									v-for="col in columns"
									:key="col.key"
									:class="[
										col.width,
										'px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 last:border-r-0 lg:border-r-0'
									]"
								>
									<button
										v-if="col.sortable"
										type="button"
										@click="toggleSort(col.key)"
										class="group inline-flex items-center justify-center gap-1 mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer"
										:title="`Urutkan ${col.label}`"
									>
										<span>{{ col.label }}</span>
										<span class="inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
											<svg
												v-if="sortKey === col.key"
												:class="[
													'h-3.5 w-3.5 text-white transition-transform duration-200',
													sortDirection === 'desc' ? 'rotate-180' : ''
												]"
												viewBox="0 0 20 20"
												fill="currentColor"
											>
												<path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" />
											</svg>
											<svg
												v-else
												class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100"
												viewBox="0 0 20 20"
												fill="currentColor"
											>
												<path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" />
											</svg>
										</span>
									</button>
									<span v-else class="whitespace-nowrap">{{ col.label }}</span>
								</th>
							</tr>
						</thead>

						<!-- Table Body -->
						<tbody class="divide-y divide-[#d6e0ee] font-inter text-[14px] text-[#435b76]">
							<!-- Skeleton Loading Rows -->
							<template v-if="isLoading">
								<tr
									v-for="n in 6"
									:key="`skeleton-${n}`"
									class="h-[52px] animate-pulse bg-white"
								>
									<td class="px-3 py-2.5 text-center">
										<div class="mx-auto h-4 w-5 rounded-md bg-slate-200"></div>
									</td>
									<td class="px-3 py-2.5">
										<div class="h-4 w-28 rounded-md bg-slate-200"></div>
									</td>
									<td class="px-3 py-2.5">
										<div class="h-4 w-44 rounded-md bg-slate-200"></div>
									</td>
									<td class="px-3 py-2.5 text-center">
										<div class="mx-auto h-4 w-16 rounded-md bg-slate-200"></div>
									</td>
									<td class="px-3 py-2.5 text-center">
										<div class="mx-auto h-4 w-24 rounded-md bg-slate-200"></div>
									</td>
									<td class="px-3 py-2.5">
										<div class="h-4 w-36 rounded-md bg-slate-200"></div>
									</td>
									<td class="px-3 py-2.5 text-center">
										<div class="mx-auto h-4 w-24 rounded-md bg-slate-200"></div>
									</td>
									<td class="px-3 py-2.5 text-center">
										<div class="flex items-center justify-center gap-2">
											<div class="h-7 w-7 rounded-lg bg-slate-200"></div>
											<div class="h-7 w-7 rounded-lg bg-slate-200"></div>
										</div>
									</td>
								</tr>
							</template>

							<!-- Real Data Rows -->
							<template v-else>
								<tr
									v-for="(student, idx) in paginatedStudents"
									:key="`student-${student.id}`"
									class="h-[52px] transition-colors hover:bg-[#f7f9fd]"
								>
									<!-- No -->
									<td class="px-3 py-2.5 text-center font-medium">
										{{ (currentPage - 1) * rowsPerPage + idx + 1 }}
									</td>

									<!-- NIM -->
									<td class="px-3 py-2.5 text-left font-medium text-[#435b76]" :title="student.nim">
										<span class="block truncate">{{ student.nim }}</span>
									</td>

									<!-- Nama Mahasiswa -->
									<td class="px-3 py-2.5 text-left font-medium text-[#2f4b6e]" :title="student.name">
										<span class="block truncate">{{ student.name }}</span>
									</td>

									<!-- Angkatan -->
									<td class="px-3 py-2.5 text-center font-medium text-[#435b76]" :title="student.angkatan">
										<span v-if="student.angkatan" class="block truncate">{{ student.angkatan }}</span>
										<span v-else class="block truncate text-[#7890a8]">-</span>
									</td>

									<!-- Talent Mapping -->
									<td class="px-3 py-2.5 text-center" :title="student.talent_mapping">
										<span
											v-if="student.talent_mapping && student.talent_mapping !== '-'"
											:class="[
												'inline-flex items-center justify-center rounded-full px-2.5 py-0.5 font-inter text-[11px] font-semibold border',
												student.talent_mapping === 'Profesional' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' :
												student.talent_mapping === 'Bisnis' ? 'bg-amber-50 text-amber-700 border-amber-200' :
												student.talent_mapping === 'Birokrat' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
												student.talent_mapping === 'Akademisi' ? 'bg-purple-50 text-purple-700 border-purple-200' :
												'bg-slate-100 text-slate-700 border-slate-200'
											]"
										>
											{{ student.talent_mapping }}
										</span>
										<span v-else class="block truncate text-[#7890a8]">-</span>
									</td>

									<!-- Email -->
									<td :class="['px-3 py-2.5', student.email && student.email !== '-' ? 'text-left' : 'text-center']" :title="student.email">
										<a
											v-if="student.email && student.email !== '-'"
											:href="`mailto:${student.email}`"
											class="block truncate text-[#2a68c4] underline decoration-[#2a68c4] transition hover:text-[#1d4d96]"
										>
											{{ student.email }}
										</a>
										<span v-else class="block truncate text-[#7890a8]">-</span>
									</td>

									<!-- Nomor Handphone -->
									<td class="px-3 py-2.5 text-center" :title="student.phone">
										<span v-if="student.phone && student.phone !== '-'" class="block truncate">
											{{ student.phone }}
										</span>
										<span v-else class="block truncate text-[#7890a8]">-</span>
									</td>

									<!-- Aksi (Edit & Delete Buttons) -->
									<td class="px-3 py-2.5 text-center">
										<div class="flex items-center justify-center gap-2">
											<EditButtonTable :label="`Edit ${student.name}`" @click="openEditModal(student)" />
											<DeleteButtonTable :label="`Hapus ${student.name}`" @click="openDeleteModal(student)" />
										</div>
									</td>
								</tr>

								<!-- Empty Search Results -->
								<tr v-if="filteredAndSortedStudents.length === 0">
									<td colspan="8" class="py-8 text-center text-[#7890a8]">
										Tidak ada data mahasiswa yang sesuai pencarian.
									</td>
								</tr>
							</template>
						</tbody>
					</table>
				</div>

				<!-- Pagination Component -->
				<TablePagination
					:current-page="currentPage"
					:total-pages="totalPages"
					:rows-per-page="rowsPerPage"
					@update:current-page="currentPage = $event"
					@update:rows-per-page="rowsPerPage = $event; currentPage = 1"
				/>
			</div>
		</section>

		<!-- MODAL FORM TAMBAH / EDIT MAHASISWA -->
		<ModalFormMahasiswa
			:show="isFormModalOpen"
			:is-editing="isEditing"
			:initial-data="selectedStudent || {}"
			:editing-id="editingId"
			:existing-students="students"
			@close="isFormModalOpen = false"
			@submit="handleFormSubmit"
		/>

		<!-- MODAL IMPORT DATA MAHASISWA -->
		<ModalImportMahasiswa
			:show="isImportModalOpen"
			:existing-students="students"
			@close="isImportModalOpen = false"
			@import="handleImportSubmit"
		/>

		<!-- MODAL DELETE CONFIRMATION -->
		<DeleteModal
			:show="isDeleteModalOpen"
			:is-loading="isDeleting"
			@close="closeDeleteModal"
			@confirm="confirmDeleteStudent"
		>
			<template #title>
				Hapus Data Mahasiswa?
			</template>
			<template #message>
				Apakah Anda yakin ingin menghapus data mahasiswa
				<span class="font-bold text-[#17334F]">"{{ deletingStudent?.name }}"</span> (NIM: {{ deletingStudent?.nim }})? Tindakan ini tidak dapat dibatalkan.
			</template>
			<template #confirm-text>
				{{ isDeleting ? 'Menghapus...' : 'Hapus' }}
			</template>
		</DeleteModal>

		<!-- MODAL BULK DELETE MAHASISWA -->
		<ModalBulkDeleteMahasiswa
			:show="isBulkDeleteModalOpen"
			:students="students"
			@close="closeBulkDeleteModal"
			@confirm="handleBulkDeleteConfirm"
		/>

		<!-- TOAST NOTIFICATION -->
		<ToastNotification
			:show="toast.show"
			:type="toast.type"
			:title="toast.title"
			:message="toast.message"
			@close="closeToast"
		/>
	</AdminLayout>
</template>
