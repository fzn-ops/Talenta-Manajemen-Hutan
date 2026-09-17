<script setup>
import AdminLayout from '@/Layouts/dashboard/AdminLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import EditButtonTable from '@/Components/dashboard/EditButtonTable.vue';
import DeleteButtonTable from '@/Components/dashboard/DeleteButtonTable.vue';
import TablePagination from '@/Components/dashboard/TablePagination.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import SearchBarTable from '@/Components/dashboard/SearchBarTable.vue';
import ModalFormMahasiswa from '@/Components/dashboard/admin/ModalFormMahasiswa.vue';
import ModalImportMahasiswa from '@/Components/dashboard/admin/ModalImportMahasiswa.vue';

const props = defineProps({
	students: {
		type: Array,
		default: () => [
			{ id: 1, nim: 'J0403231085', name: 'Fauzan Fuadiansyah', email: 'novafauzan@gmail.com', phone: '' },
			{ id: 2, nim: 'J0403231075', name: 'Farhan Hakim', email: '01122005farhan@apps.ipb.ac.id', phone: '+62 895-6228-15861' },
			{ id: 3, nim: '198512102012121010', name: 'Dr. Eko Prasetyo S.Hut., Ph.D.', email: 'eko.prasetyo@apps.ipb.ac.id', phone: '+62 857-5678-9012' },
			{ id: 4, nim: '198102182006042009', name: 'Dr. Sri Wahyuni S.Hut., M.Si.', email: 'sri.wahyuni@apps.ipb.ac.id', phone: '+62 822-4567-8901' },
			{ id: 5, nim: '197308301999031008', name: 'Ir. Bambang Triyono M.For.', email: 'bambang.t@apps.ipb.ac.id', phone: '+62 819-3456-7890' },
			{ id: 6, nim: '198609252014042007', name: 'Dr. Nurul Hidayah S.Hut., M.Sc.', email: 'nurul.h@apps.ipb.ac.id', phone: '+62 812-2345-6789' },
			{ id: 7, nim: '198404172010121006', name: 'Dr. Dedi Kusnadi S.Hut., M.Si.', email: 'dedi.kusnadi@apps.ipb.ac.id', phone: '+62 878-9012-3456' },
			{ id: 8, nim: '197811092003122005', name: 'Dr. Ir. Rina Marlina M.Sc.', email: 'rina.marlina@apps.ipb.ac.id', phone: '+62 856-7890-1234' },
			{ id: 9, nim: '197001151995031004', name: 'Prof. Dr. Ir. Ahmad Fauzi M.Agr.', email: 'ahmad.fauzi@apps.ipb.ac.id', phone: '+62 821-1234-5678' },
			{ id: 10, nim: '198208222008121003', name: 'Dr. Hendra Setiawan S.Hut., Ph.D.', email: 'hendra.s@apps.ipb.ac.id', phone: '+62 815-6789-0123' },
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

// Loading Animation
const isLoading = ref(true);
onMounted(() => {
	initStudentsData();
	setTimeout(() => {
		isLoading.value = false;
	}, 250);
});

// Search Query
const searchQuery = ref('');

// Sorting
const columns = [
	{ key: 'nim', label: 'NIM', sortable: true, width: 'w-[150px]' },
	{ key: 'name', label: 'Nama Mahasiswa', sortable: true, width: 'w-[210px]' },
	{ key: 'email', label: 'Email', sortable: true, width: 'w-[190px]' },
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

	// Search Query (NIM atau Nama Mahasiswa)
	if (searchQuery.value.trim()) {
		const q = searchQuery.value.toLowerCase().trim();
		list = list.filter(
			(s) =>
				(s.name && s.name.toLowerCase().includes(q)) ||
				(s.nim && s.nim.toLowerCase().includes(q)) ||
				(s.email && s.email.toLowerCase().includes(q))
		);
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

// Reset to page 1 on search or rowsPerPage change
watch([searchQuery, rowsPerPage], () => {
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

				<!-- Action Bar (Search, Import, Tambah Button) -->
				<div class="flex items-center gap-3">
					<!-- Search Input Component -->
					<SearchBarTable
						v-model="searchQuery"
						placeholder="Cari NIM atau Nama Mahasiswa disini"
					/>

					<!-- Import Button -->
					<button
						type="button"
						@click="(e) => { e.currentTarget?.blur(); openImportModal(); }"
						class="flex h-[46px] w-[46px] sm:w-auto shrink-0 items-center justify-center gap-2 rounded-[10px] border-2 border-[#d6e0ee] bg-transparent px-0 sm:px-4 font-poppins text-[14px] font-semibold text-[#183669] transition-colors hover:border-[#8ea9cb] focus:border-[#183669] focus:outline-none focus:ring-0 focus-visible:outline-none active:scale-95 select-none cursor-pointer"
						title="Import Data Mahasiswa (Excel / CSV)"
					>
						<svg class="h-5 w-5 text-[#183669]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
						</svg>
						<span class="hidden sm:inline">Import</span>
					</button>

					<!-- Tambah Button -->
					<button
						type="button"
						@click="(e) => { e.currentTarget?.blur(); openCreateModal(); }"
						class="flex h-[46px] w-[46px] sm:w-auto shrink-0 items-center justify-center rounded-[10px] bg-[#183669] px-0 sm:px-7 font-poppins text-[15px] font-semibold text-white shadow-sm transition hover:bg-[#122b54] active:scale-95 focus:outline-none focus:ring-0 focus-visible:outline-none select-none cursor-pointer"
						title="Tambah Mahasiswa"
					>
						<svg class="h-5 w-5 sm:hidden" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
						</svg>
						<span class="hidden sm:inline">Tambah</span>
					</button>
				</div>

				<!-- Table Section -->
				<div class="overflow-x-auto rounded-[12px] bg-white shadow-sm ring-1 ring-[#d6e0ee]">
					<table class="w-full min-w-[980px] table-fixed border-collapse text-sm">
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
									<td colspan="6" class="py-8 text-center text-[#7890a8]">
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
		<div
			v-if="isDeleteModalOpen"
			class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 p-4 backdrop-blur-xs transition-opacity duration-200"
			@click.self="closeDeleteModal"
		>
			<div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl text-center">
				<div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-600 mb-4">
					<svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
						<path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
					</svg>
				</div>

				<h3 class="font-poppins text-lg font-bold text-[#17334F]">
					Hapus Data Mahasiswa?
				</h3>

				<p class="mt-2 font-inter text-sm text-[#64748b]">
					Apakah Anda yakin ingin menghapus data mahasiswa
					<span class="font-bold text-[#17334F]">"{{ deletingStudent?.name }}"</span> (NIM: {{ deletingStudent?.nim }})? Tindakan ini tidak dapat dibatalkan.
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
						@click="confirmDeleteStudent"
						class="flex items-center gap-2 rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition-colors shadow-sm disabled:opacity-50 cursor-pointer"
					>
						<svg v-if="isDeleting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
							<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
							<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
						</svg>
						<span>{{ isDeleting ? 'Menghapus...' : 'Hapus' }}</span>
					</button>
				</div>
			</div>
		</div>

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
