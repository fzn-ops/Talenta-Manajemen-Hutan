<script setup>
import AdminLayout from '@/Layouts/dashboard/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import ModalFormMateriTask from '@/Components/dashboard/admin/ModalFormMateriTask.vue';
import EditButtonTable from '@/Components/dashboard/EditButtonTable.vue';
import RoadmapCardBuilder from '@/Components/dashboard/admin/RoadmapCardBuilder.vue';

const props = defineProps({
	roadmapId: {
		type: [Number, String],
		default: 1,
	},
});

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

// Roadmaps Data Store
const roadmapsData = {
	1: {
		id: 1,
		category: 'Bisnis',
		title: 'Roadmap Bisnis',
		subTitle: 'Yuk Pelajari Roadmap Yang Telah Kamu Ikuti!',
	},
	2: {
		id: 2,
		category: 'Bisnis',
		title: 'Roadmap Bisnis',
		subTitle: 'Yuk Pelajari Roadmap Yang Telah Kamu Ikuti!',
	},
	3: {
		id: 3,
		category: 'Birokrasi',
		title: 'Roadmap Birokrasi',
		subTitle: 'Yuk Pelajari Roadmap Yang Telah Kamu Ikuti!',
	},
	4: {
		id: 4,
		category: 'Akademisi',
		title: 'Roadmap Akademisi',
		subTitle: 'Yuk Pelajari Roadmap Yang Telah Kamu Ikuti!',
	},
	5: {
		id: 5,
		category: 'Profesional',
		title: 'Roadmap Profesional',
		subTitle: 'Yuk Pelajari Roadmap Yang Telah Kamu Ikuti!',
	},
};

const currentRoadmap = computed(() => {
	return roadmapsData[props.roadmapId] || {
		id: props.roadmapId,
		category: 'Bisnis',
		title: 'Roadmap Bisnis',
		subTitle: 'Yuk Pelajari Roadmap Yang Telah Kamu Ikuti!',
	};
});

// Months Data Structure with Materials & Tasks
const months = ref([
	{
		id: 1,
		name: 'Bulan 1',
		items: [
			{
				id: 101,
				type: 'Materi',
				title: 'Materi 1',
				topic: 'Cara Mendapatkan Return Usaha 100% dalam 1 Bulan',
				file: null,
				fileName: '',
				description: `Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc ac varius massa, eu pulvinar neque. Mauris viverra lectus et massa porttitor molestie. Curabitur pharetra, mauris eget bibendum convallis, quam lorem iaculis magna, in pulvinar nisi risus accumsan sem. Mauris condimentum ex et nisi facilisis vehicula. Etiam finibus vulputate dolor, a malesuada leo ultrices quis. Praesent nec porta dolor. Proin bibendum urna est, at cursus lorem condimentum ut. Aenean et dolor magna. Duis ac purus nisi. Pellentesque vulputate sapien et lorem volutpat, eget egestas orci faucibus. Praesent maximus, ex a luctus volutpat, dolor purus ultricies nibh, et facilisis magna nisi et lacus. Nullam ac tincidunt purus.

Duis nisl nulla, ultricies consequat risus vitae, ullamcorper varius metus. Vestibulum in mollis ante. Nullam ut libero enim. Vestibulum rutrum justo ex. In a ex elit. In sed elit in nibh pellentesque facilisis. Nullam a elit ipsum. Nullam tortor magna, suscipit nec consectetur at, pellentesque non nibh. Proin ut arcu aliquam, ornare risus at, viverra sem. Nulla semper posuere lacus eget tincidunt. Integer tortor justo, blandit at interdum in, varius ac justo. Phasellus eleifend a massa suscipit hendrerit. Donec quis felis est. Sed non dui sem. Sed faucibus, purus eget ornare tempus, nibh metus porta eros, semper feugiat metus risus ac mi.`,
			},
			{
				id: 102,
				type: 'Tugas',
				title: 'Tugas 1',
				topic: 'Analisis Kelayakan Finansial dan Pasar Produk Hutan',
				file: null,
				fileName: '',
				description: `Tugas mandiri untuk menganalisis potensi pasar dan perhitungan ROI serta payback period dari produk olahan hutan berkelanjutan. Kerjakan analisis sesuai dengan format dokumen yang telah disediakan.`,
			},
			{
				id: 103,
				type: 'Materi',
				title: 'Materi 2',
				topic: 'Strategi Pemasaran Digital & Branding Komoditas Kehutanan',
				file: null,
				fileName: '',
				description: `Memahami cara membangun identitas brand ramah lingkungan dan optimalisasi kanal digital seperti social media dan marketplace untuk penetrasi pasar produk kehutanan.`,
			},
			{
				id: 104,
				type: 'Tugas',
				title: 'Tugas 2',
				topic: 'Pembuatan Pitch Deck Bisnis Agroforestry',
				file: null,
				fileName: '',
				description: `Susun pitch deck sebanyak 10-12 slide yang merangkum model bisnis, value proposition, segmentasi pasar, dan proyeksi keuangan usaha rintisan Anda.`,
			},
			{
				id: 105,
				type: 'Materi',
				title: 'Materi 3',
				topic: 'Manajemen Operasional & Rantai Pasok Hasil Hutan',
				file: null,
				fileName: '',
				description: `Tata kelola logistik, standardisasi mutu, dan sertifikasi legalitas kayu dan non-kayu untuk memastikan pasokan yang stabil dan memenuhi regulasi pasar.`,
			},
			{
				id: 106,
				type: 'Tugas',
				title: 'Tugas 3',
				topic: 'Simulasi Perencanaan Rantai Pasok Berkelanjutan',
				file: null,
				fileName: '',
				description: `Lakukan simulasi alur rantai pasok dari hulu ke hilir dengan meminimalkan jejak karbon dan memaksimalkan efisiensi biaya logistik.`,
			},
		],
	},
	{
		id: 2,
		name: 'Bulan 2',
		items: [
			{
				id: 201,
				type: 'Materi',
				title: 'Materi 1',
				topic: 'Pengembangan Model Bisnis Ekowisata Berbasis Komunitas',
				file: null,
				fileName: '',
				description: `Pelajari tahapan merancang paket ekowisata, pelibatan masyarakat lokal, serta sistem bagi hasil yang adil dan berkelanjutan.`,
			},
			{
				id: 202,
				type: 'Tugas',
				title: 'Tugas 1',
				topic: 'Rancangan Program Eduwisata Hutan Pendidikan',
				file: null,
				fileName: '',
				description: `Buat proposal program eduwisata lengkap dengan jadwal kegiatan, estimasi anggaran, dan analisis dampak lingkungan.`,
			},
		],
	},
]);

// Active Tab & Selection State
const activeMonthId = ref(1);
const selectedItemId = ref(101);

const currentMonth = computed(() => {
	return months.value.find((m) => m.id === activeMonthId.value) || months.value[0];
});

const currentItem = computed(() => {
	if (!currentMonth.value || !currentMonth.value.items.length) return null;
	return currentMonth.value.items.find((i) => i.id === selectedItemId.value) || currentMonth.value.items[0];
});

// Editor State for Current Item
const isEditingContent = ref(false);
const editTopic = ref('');
const editCards = ref([]);

// Available Card Types for Add Button
const availableCardTypes = [
	{ type: 'image', label: 'Gambar', icon: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z' },
	{ type: 'text', label: 'Teks Deskripsi', icon: 'M4 6h16M4 12h16M4 18h7' },
	{ type: 'pdf', label: 'File PDF', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
	{ type: 'video', label: 'Video YouTube', icon: 'M21.582 6.186a2.665 2.665 0 0 0-1.884-1.884C18.04 3.84 12 3.84 12 3.84s-6.04 0-7.698.462a2.665 2.665 0 0 0-1.884 1.884C1.956 7.844 1.956 12 1.956 12s0 4.156.462 5.814a2.665 2.665 0 0 0 1.884 1.884C6.04 20.16 12 20.16 12 20.16s6.04 0 7.698-.462a2.665 2.665 0 0 0 1.884-1.884C22.044 16.156 22.044 12 22.044 12s0-4.156-.462-5.814zM9.954 15.496V8.504l6.505 3.496-6.505 3.496z' },
];

const showAddCardDropdown = ref(false);

const hasCardType = (type) => editCards.value.some((c) => c.type === type);

const isAllCardsAdded = computed(() => availableCardTypes.every(t => hasCardType(t.type)));

const handleAddCard = (type) => {
	if (!hasCardType(type)) {
		editCards.value.push({ id: Date.now().toString(), type, content: '', fileName: '' });
		showAddCardDropdown.value = false;
	}
};

// Initial State for dirty checking
const initialTopic = ref('');
const initialCardsString = ref('');

const stripError = (cards) => cards.map(({ hasError, ...c }) => c);

const isModified = computed(() => {
	if (editTopic.value !== initialTopic.value) return true;
	return JSON.stringify(stripError(editCards.value)) !== initialCardsString.value;
});

// Sync editor state with selected item
const syncEditorState = () => {
	isEditingContent.value = false;
	if (currentItem.value) {
		editTopic.value = currentItem.value.topic || '';
		
		if (currentItem.value.cards) {
			editCards.value = JSON.parse(JSON.stringify(currentItem.value.cards));
		} else {
			const initialCards = [];
			if (currentItem.value.file) {
				initialCards.push({
					id: Date.now() + 'f',
					type: currentItem.value.file.type.startsWith('image/') ? 'image' : 'pdf',
					content: currentItem.value.file.dataUrl || '',
					file: currentItem.value.file,
					fileName: currentItem.value.fileName || currentItem.value.file.name,
				});
			}
			if (currentItem.value.description) {
				initialCards.push({
					id: Date.now() + 'd',
					type: 'text',
					content: currentItem.value.description,
					file: null,
					fileName: '',
				});
			}
			editCards.value = initialCards;
		}

		initialTopic.value = editTopic.value;
		initialCardsString.value = JSON.stringify(stripError(editCards.value));
	} else {
		editTopic.value = '';
		editCards.value = [];
		initialTopic.value = '';
		initialCardsString.value = '[]';
	}
};

watch(
	[activeMonthId, selectedItemId],
	() => {
		syncEditorState();
	},
	{ immediate: true }
);

// Switch active month
const selectMonth = (monthId) => {
	editingItemId.value = null;
	activeMonthId.value = monthId;
	const m = months.value.find((item) => item.id === monthId);
	if (m && m.items.length > 0) {
		selectedItemId.value = m.items[0].id;
	} else {
		selectedItemId.value = null;
	}
};

// Switch active item
const selectItem = (itemId) => {
	selectedItemId.value = itemId;
};

// Save Item Content
const handleSaveItem = () => {
	if (!currentItem.value) return;

	if (editCards.value.length === 0) {
		showToast('error', 'Gagal Disimpan', 'Materi harus memiliki minimal 1 konten (Gambar, Teks, PDF, atau Video).');
		return;
	}

	let hasEmpty = false;

	// Validation: Check for empty cards and mark them
	editCards.value.forEach(c => {
		let isEmpty = false;
		if (c.type === 'text') isEmpty = !c.content || c.content.trim() === '' || c.content === '<p></p>';
		else if (c.type === 'image' || c.type === 'pdf') isEmpty = !c.content;
		else if (c.type === 'video') isEmpty = !c.content || c.content.trim() === '';
		
		c.hasError = isEmpty;
		if (isEmpty) hasEmpty = true;
	});

	if (hasEmpty) {
		showToast('error', 'Gagal Disimpan', 'Terdapat konten materi yang belum terisi dengan sempurna.');
		return;
	}

	currentItem.value.topic = editTopic.value.trim();
	currentItem.value.cards = JSON.parse(JSON.stringify(editCards.value));
	
	// Backward compatibility
	const textCard = currentItem.value.cards.find(c => c.type === 'text');
	currentItem.value.description = textCard ? textCard.content : '';
	const fileCard = currentItem.value.cards.find(c => c.type === 'image' || c.type === 'pdf');
	currentItem.value.file = fileCard ? fileCard.file : null;
	currentItem.value.fileName = fileCard ? fileCard.fileName : '';

	isEditingContent.value = false;
	showToast('success', 'Berhasil Disimpan', `Konten "${currentItem.value.title}" berhasil diperbarui.`);
};

// Reset/Cancel Item Changes
const handleCancelEdit = () => {
	syncEditorState();
	isEditingContent.value = false;
};

const startEditContent = () => {
	isEditingContent.value = true;
};

// Video Embed Helper
const getVideoEmbedUrl = (url) => {
	if (!url) return null;
	try {
		if (url.includes('youtube.com/watch')) {
			const urlObj = new window.URL(url);
			const v = urlObj.searchParams.get('v');
			return v ? `https://www.youtube.com/embed/${v}` : null;
		}
		if (url.includes('youtube.com/shorts/')) {
			const id = url.split('youtube.com/shorts/')[1].split('?')[0];
			return id ? `https://www.youtube.com/embed/${id}` : null;
		}
		if (url.includes('youtu.be/')) {
			const id = url.split('youtu.be/')[1].split('?')[0];
			return id ? `https://www.youtube.com/embed/${id}` : null;
		}
	} catch (e) {
		return null;
	}
	return null;
};

// File Upload Handlers
const triggerFileInput = () => {
	fileInputRef.value?.click();
};

const handleFileChange = (e) => {
	const file = e.target.files?.[0];
	if (!file) return;

	if (file.size > 50 * 1024 * 1024) {
		showToast('error', 'Ukuran File Terlalu Besar', 'Maksimal ukuran file adalah 50MB.');
		return;
	}

	const reader = new FileReader();
	reader.onload = (event) => {
		uploadedFile.value = {
			name: file.name,
			size: file.size,
			type: file.type,
			dataUrl: event.target.result,
		};
		if (currentItem.value) {
			currentItem.value.file = uploadedFile.value;
			currentItem.value.fileName = file.name;
		}
		showToast('success', 'File Terunggah', `File "${file.name}" berhasil dipilih.`);
	};
	reader.readAsDataURL(file);
	e.target.value = '';
};

const handleFileDrop = (e) => {
	const file = e.dataTransfer.files?.[0];
	if (!file) return;

	if (file.size > 50 * 1024 * 1024) {
		showToast('error', 'Ukuran File Terlalu Besar', 'Maksimal ukuran file adalah 50MB.');
		return;
	}

	const reader = new FileReader();
	reader.onload = (event) => {
		uploadedFile.value = {
			name: file.name,
			size: file.size,
			type: file.type,
			dataUrl: event.target.result,
		};
		if (currentItem.value) {
			currentItem.value.file = uploadedFile.value;
			currentItem.value.fileName = file.name;
		}
		showToast('success', 'File Terunggah', `File "${file.name}" berhasil diunggah.`);
	};
	reader.readAsDataURL(file);
};

const removeFile = () => {
	uploadedFile.value = null;
	if (currentItem.value) {
		currentItem.value.file = null;
		currentItem.value.fileName = '';
	}
	showToast('info', 'File Dihapus', 'File materi telah dihapus dari form.');
};

// Add Month
const handleAddMonth = () => {
	if (months.value.length >= 12) {
		showToast('error', 'Batas Maksimal', 'Anda hanya dapat menambahkan maksimal 12 bulan.');
		return;
	}

	const nextMonthNum = months.value.length + 1;
	const newMonth = {
		id: Date.now(),
		name: `Bulan ${nextMonthNum}`,
		items: [
			{
				id: Date.now() + 1,
				type: 'Materi',
				title: 'Materi 1',
				topic: 'Pengantar Modul Baru',
				file: null,
				fileName: '',
				description: 'Masukkan deskripsi dan rangkuman materi di sini...',
			},
		],
	};
	months.value.push(newMonth);
	activeMonthId.value = newMonth.id;
	selectedItemId.value = newMonth.items[0].id;
	showToast('success', 'Bulan Ditambahkan', `Tab "${newMonth.name}" berhasil ditambahkan.`);
};

// Delete Month Confirmation
const isDeleteMonthModalOpen = ref(false);
const monthToDelete = ref(null);

const confirmDeleteMonth = (month, e) => {
	e?.stopPropagation();
	if (months.value.length <= 1) {
		showToast('error', 'Tidak Dapat Dihapus', 'Minimal harus terdapat 1 bulan pada roadmap.');
		return;
	}
	monthToDelete.value = month;
	isDeleteMonthModalOpen.value = true;
};

const executeDeleteMonth = () => {
	if (!monthToDelete.value) return;
	const deletedId = monthToDelete.value.id;
	months.value = months.value.filter((m) => m.id !== deletedId);

	// Renumber months
	months.value.forEach((m, idx) => {
		m.name = `Bulan ${idx + 1}`;
	});

	if (activeMonthId.value === deletedId) {
		const firstMonth = months.value[0];
		activeMonthId.value = firstMonth.id;
		selectedItemId.value = firstMonth.items[0]?.id || null;
	}
	isDeleteMonthModalOpen.value = false;
	showToast('success', 'Bulan Dihapus', 'Bulan berhasil dihapus dari roadmap.');
};

// Add Item (Materi / Tugas) Modal
const isAddModalOpen = ref(false);

const openAddModal = () => {
	editingItemId.value = null;
	isAddModalOpen.value = true;
};

const handleSaveNewItem = (itemData) => {
	if (!currentMonth.value) return;
	const newItem = {
		id: Date.now(),
		type: itemData.type || 'Materi',
		title: itemData.title,
		topic: `Topik ${itemData.title}`,
		file: null,
		fileName: '',
		description: `Tuliskan penjelasan dan rincian mengenai ${itemData.title} di sini...`,
	};

	currentMonth.value.items.push(newItem);
	selectedItemId.value = newItem.id;
	isAddModalOpen.value = false;
	showToast('success', 'Item Ditambahkan', `"${newItem.title}" berhasil ditambahkan ke ${currentMonth.value.name}.`);
};

// Inline Edit Item Title State
const editingItemId = ref(null);
const editingItemTitle = ref('');

const adjustTextareaHeight = (el) => {
	if (!el) return;
	el.style.height = 'auto';
	el.style.height = `${Math.max(el.scrollHeight, 26)}px`;
};

const startInlineEdit = (item, e) => {
	e?.stopPropagation();
	selectedItemId.value = item.id;
	editingItemId.value = item.id;
	editingItemTitle.value = item.title;
	nextTick(() => {
		const el = document.getElementById(`inline-edit-item-${item.id}`);
		if (el) {
			adjustTextareaHeight(el);
			el.focus();
			const len = el.value.length;
			el.setSelectionRange(len, len);
		}
	});
};

const saveInlineItemTitle = (item, e) => {
	e?.stopPropagation();
	if (editingItemTitle.value.trim()) {
		item.title = editingItemTitle.value.trim();
		showToast('success', 'Judul Diperbarui', `Nama item diubah menjadi "${item.title}".`);
	}
	editingItemId.value = null;
};

const cancelInlineEdit = () => {
	editingItemId.value = null;
};

// Delete Item Confirmation
const isDeleteItemModalOpen = ref(false);
const itemToDelete = ref(null);

const confirmDeleteItem = (item, e) => {
	e?.stopPropagation();
	if (!currentMonth.value || currentMonth.value.items.length <= 1) {
		showToast('error', 'Tidak Dapat Dihapus', 'Minimal harus terdapat 1 item (Materi/Tugas) di dalam bulan ini.');
		return;
	}
	itemToDelete.value = item;
	isDeleteItemModalOpen.value = true;
};

const executeDeleteItem = () => {
	if (!currentMonth.value || !itemToDelete.value) return;
	const deletedId = itemToDelete.value.id;
	if (editingItemId.value === deletedId) {
		editingItemId.value = null;
	}
	currentMonth.value.items = currentMonth.value.items.filter((i) => i.id !== deletedId);

	if (selectedItemId.value === deletedId) {
		selectedItemId.value = currentMonth.value.items[0]?.id || null;
	}
	isDeleteItemModalOpen.value = false;
	showToast('success', 'Item Dihapus', 'Item berhasil dihapus dari daftar.');
};
</script>

<template>
	<Head :title="`Detail ${currentRoadmap.title} - Admin`" />

	<AdminLayout>
		<div class="mx-auto w-full max-w-[1520px] px-4 py-6 font-poppins sm:px-6 sm:py-8 lg:px-8 space-y-6 pb-20">
			<!-- Top Breadcrumb -->
			<nav class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-[#183669]">
				<Link href="/admin/roadmap" class="hover:underline text-[#183669] transition-colors">
					Daftar Roadmap
				</Link>
				<span class="text-[#8ca1b9]">/</span>
				<span class="text-[#7188a3] font-medium">...</span>
			</nav>

			<!-- Page Header Section -->
			<div class="space-y-1">
				<h1 class="text-[28px] sm:text-[34px] lg:text-[38px] font-extrabold leading-tight text-[#17334F] tracking-tight">
					{{ currentRoadmap.title }}
				</h1>
				<p class="font-inter text-[13px] sm:text-[14px] font-medium text-[#64748b]">
					{{ currentRoadmap.subTitle }}
				</p>
			</div>

			<!-- Main Roadmap Folder Structure (Identical to Dashboard_admin_roadmap_preview.png) -->
			<div class="mt-6 w-full max-w-full overflow-hidden">
				<!-- Folder Header Tabs Row (Seamless 0 gap between tabs and + button, horizontal scroll for many tabs) -->
				<div class="flex items-end select-none relative z-10 -mb-[1px] overflow-x-auto scrollbar-hide w-full" style="scrollbar-width: none; -ms-overflow-style: none;">
					<!-- Month Tab Buttons -->
					<button
						v-for="(m, index) in months"
						:key="m.id"
						type="button"
						@click="selectMonth(m.id)"
						:style="{ borderTopLeftRadius: '10px', borderTopRightRadius: '10px' }"
						:class="[
							'relative shrink-0 flex items-center justify-center gap-2.5 px-4 sm:px-5 h-[48px] rounded-t-[10px] font-poppins text-[13px] sm:text-[14px] font-bold transition-all cursor-pointer border-t border-r border-[#d6e0ee]',
							index === 0 ? 'border-l' : '-ml-[1px] border-l',
							activeMonthId === m.id
								? 'bg-white text-[#183669] border-b border-b-transparent z-20'
								: 'bg-[#f1f5f9] text-[#64748b] hover:bg-[#e2e8f0] hover:text-[#183669] border-b border-b-[#d6e0ee] z-10'
						]"
					>
						<span class="whitespace-nowrap">{{ m.name }}</span>

						<!-- Delete Month Button (Red Minus Circle) -->
						<span
							role="button"
							@click.stop="confirmDeleteMonth(m, $event)"
							class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#e05252] text-white transition hover:scale-110 active:scale-95 focus:outline-none cursor-pointer"
							:title="`Hapus ${m.name}`"
						>
							<svg class="h-2.5 w-2.5 text-white stroke-[3.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />
							</svg>
						</span>

						<!-- Active Blue Underline Indicator (Flush and pixel-perfect without miter artifacts) -->
						<span
							v-if="activeMonthId === m.id"
							class="absolute -bottom-[1px] -left-[1px] -right-[1px] h-[3px] bg-[#183669] pointer-events-none"
						></span>
					</button>

					<!-- Add Month Button [+] (Overlaps previous tab by 1px to complete rounded corner) -->
					<button
						v-if="months.length < 12"
						type="button"
						@click="handleAddMonth"
						:style="{ borderTopLeftRadius: '10px', borderTopRightRadius: '10px' }"
						class="relative z-10 shrink-0 flex h-[48px] w-[50px] items-center justify-center rounded-t-[10px] bg-[#183669] text-white transition hover:bg-[#122b54] active:scale-95 focus:outline-none cursor-pointer border-t border-r border-l border-[#183669] -ml-[1px]"
						title="Tambah Bulan Baru"
					>
						<svg class="h-6 w-6 text-white stroke-[3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
						</svg>
					</button>
				</div>

				<!-- Main White Card Body (Top corners flat/square so it connects seamlessly into tabs; bottom corners 10px rounded) -->
				<div
					:style="{ borderBottomLeftRadius: '10px', borderBottomRightRadius: '10px', borderTopLeftRadius: '0px', borderTopRightRadius: '0px' }"
					class="rounded-b-[10px] rounded-t-none border border-[#d6e0ee] bg-white p-5 sm:p-7 lg:p-9 shadow-xs font-poppins relative z-0"
				>
					<div class="flex flex-col lg:flex-row gap-6 lg:gap-8 items-start">
						
						<!-- ================= LEFT COLUMN: CONTENT & MEDIA (70%) ================= -->
						<div class="w-full lg:w-[68%] xl:w-[70%] space-y-5">
							
							<!-- READ-ONLY VIEW -->
							<template v-if="!isEditingContent">
								<!-- Title & Edit Button -->
								<div class="flex items-center gap-4">
									<h2 class="flex-1 font-poppins text-[22px] sm:text-[26px] font-extrabold text-[#17334F] leading-tight">
										{{ currentItem?.topic || 'Belum ada topik' }}
									</h2>
									<EditButtonTable @click="startEditContent" label="Edit Konten" />
								</div>

								<!-- Media & Content Display (Read Only) -->
								<div v-if="currentItem?.cards && currentItem.cards.length > 0" class="flex flex-col gap-6 mt-6">
									<div v-for="card in currentItem.cards" :key="card.id" class="w-full">
										<!-- IMAGE -->
										<div v-if="card.type === 'image' && card.content" class="w-full aspect-video rounded-[10px] overflow-hidden border border-[#d6e0ee] shadow-sm bg-slate-50">
											<img :src="card.content" class="w-full h-full object-cover" />
										</div>
										
										<!-- PDF -->
										<div v-else-if="card.type === 'pdf' && card.fileName" class="flex items-center gap-4 bg-[#fafcff] p-4 sm:p-5 border border-[#d6e0ee] rounded-[10px] shadow-sm">
											<svg class="h-10 w-10 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 24 24">
												<path d="M8.267 14.68c-.184 0-.308.018-.372.036v1.178c.076.018.171.023.302.023.479 0 .774-.242.774-.651 0-.366-.254-.586-.704-.586zm3.487.012c-.2 0-.33.018-.407.036v2.61c.077.018.201.018.313.018.817.006 1.349-.444 1.349-1.396.006-.83-.479-1.268-1.255-1.268z" />
												<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM9.447 15.867c-.201.764-.787 1.137-1.468 1.137h-.083v2h-1.14v-5.263c.272-.042.663-.06 1.054-.06.882 0 1.503.415 1.503 1.155 0 .587-.332 1.019-.866 1.031zm3.799 1.83h-.148c-.29 0-.586-.03-.846-.071v-4.108c.284-.047.622-.065.989-.065 1.332 0 2.256.705 2.256 2.155 0 1.487-.96 2.089-2.251 2.089zm3.504-2.812h-1.344v2.545h-1.151v-5.228h2.64v1.013h-1.489v1.658h1.344v1.012z" />
											</svg>
											<div class="flex-1 min-w-0">
												<p class="font-poppins font-bold text-[#183669] truncate text-[14px] sm:text-[15px]">{{ card.fileName }}</p>
												<p class="font-inter text-[12px] text-[#7188a3] mt-0.5">Dokumen PDF</p>
											</div>
										</div>
										
										<!-- TEXT -->
										<div v-else-if="card.type === 'text'" class="font-inter text-[13px] sm:text-[14px] leading-relaxed text-[#334155] text-justify prose prose-sm sm:prose-base max-w-none prose-p:my-2 prose-h1:text-[22px] prose-h2:text-[18px] prose-h3:text-[16px]" v-html="card.content">
										</div>
										
										<!-- VIDEO -->
										<div v-else-if="card.type === 'video' && card.content" class="aspect-video w-full min-h-[250px] sm:min-h-0 rounded-[10px] overflow-hidden border border-[#d6e0ee] bg-black shadow-sm">
											<iframe 
												v-if="getVideoEmbedUrl(card.content)"
												:src="getVideoEmbedUrl(card.content)" 
												class="w-full h-full"
												frameborder="0" 
												allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
												allowfullscreen>
											</iframe>
											<div v-else class="flex h-full flex-col items-center justify-center bg-slate-100 text-[13px] font-semibold text-slate-500 text-center px-4">
												<svg class="h-10 w-10 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
												Preview video belum didukung untuk tautan ini.<br>
												Gunakan tautan YouTube yang valid.
											</div>
										</div>
									</div>
								</div>
								
								<!-- Fallback Legacy Display (If Not Saved As Cards Yet) -->
								<div v-else>
									<!-- Media Display -->
									<div v-if="currentItem?.file" class="w-full rounded-[10px] overflow-hidden mt-6">
										<img 
											v-if="currentItem.file.type.startsWith('image/')" 
											:src="currentItem.file.dataUrl" 
											class="w-full h-auto max-h-[450px] object-cover rounded-[10px] border border-[#d6e0ee]" 
										/>
										<div v-else class="flex flex-col items-center justify-center p-10 bg-[#f8fafc] border border-[#d6e0ee] rounded-[10px]">
											<svg class="h-16 w-16 text-[#183669] mb-2" viewBox="0 0 64 64" fill="currentColor">
												<path d="M40 2H12C8.686 2 6 4.686 6 8V56C6 59.314 8.686 62 12 62H52C55.314 62 58 59.314 58 56V20L40 2ZM32 22L44 34H36V46H28V34H20L32 22ZM38 20V6L54 22H38V20Z" />
											</svg>
											<p class="font-poppins font-bold text-[#183669]">{{ currentItem.file.name }}</p>
										</div>
									</div>

									<!-- Description Display -->
									<div class="font-inter text-[13px] sm:text-[14px] leading-relaxed text-[#334155] whitespace-pre-wrap text-justify mt-6">
										{{ currentItem?.description }}
									</div>
								</div>
							</template>

							<!-- EDIT MODE -->
							<template v-else>
								<!-- 1. Topic Title Field -->
								<div class="flex items-center gap-3 relative">
									<!-- Editable Dashed Input Box -->
									<div
										:style="{ borderRadius: '10px' }"
										class="flex-1 rounded-[10px] border-2 border-dashed border-[#183669] bg-white px-4 py-2.5 transition-colors focus-within:bg-[#fafcff]"
									>
										<input
											v-model="editTopic"
											type="text"
											placeholder="Cara Mendapatkan Return Usaha 100% dalam 1 Bulan"
											class="w-full border-none bg-transparent p-0 font-poppins text-[15px] sm:text-[16px] font-bold text-[#183669] placeholder-[#94a3b8] focus:outline-none focus:ring-0"
										/>
									</div>

									<!-- Add Card Dropdown Button -->
									<div class="relative">
										<button 
											type="button" 
											@click="showAddCardDropdown = !showAddCardDropdown"
											:disabled="isAllCardsAdded"
											:class="[
												'flex h-12 w-12 items-center justify-center rounded-[10px] border-2 transition-all shadow-sm',
												isAllCardsAdded 
													? 'cursor-not-allowed border-slate-300 bg-slate-50 text-slate-400' 
													: 'border-[#183669] bg-[#183669] text-white hover:bg-[#122b54] active:scale-95'
											]"
											title="Tambah Konten"
										>
											<svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
										</button>

										<!-- Dropdown Overlay & Menu -->
										<div v-if="showAddCardDropdown" class="fixed inset-0 z-40" @click="showAddCardDropdown = false"></div>
										<div v-if="showAddCardDropdown" class="absolute right-0 top-full mt-2 w-56 rounded-[10px] bg-white shadow-xl ring-1 ring-black/5 z-50 overflow-hidden border border-[#d6e0ee]">
											<div class="p-1.5 flex flex-col gap-0.5">
												<div class="px-2 pt-1 pb-2 text-[11px] font-bold text-[#8ca1b9] uppercase tracking-wider">Tambah Konten</div>
												<button
													v-for="typeObj in availableCardTypes"
													:key="typeObj.type"
													type="button"
													@click="handleAddCard(typeObj.type)"
													:disabled="hasCardType(typeObj.type)"
													:class="[
														'flex w-full items-center gap-2.5 rounded-[6px] px-3 py-2.5 text-[13px] font-semibold transition-colors',
														hasCardType(typeObj.type)
															? 'cursor-not-allowed text-slate-400 bg-slate-50 opacity-60'
															: 'text-[#183669] hover:bg-[#f0f4f9] active:bg-[#e8eef8]'
													]"
												>
													<svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
														<path stroke-linecap="round" stroke-linejoin="round" :d="typeObj.icon" />
													</svg>
													{{ typeObj.label }}
												</button>
											</div>
										</div>
									</div>
								</div>

								<!-- Card Builder Area -->
								<div class="mt-6 border-t border-[#d6e0ee] pt-5">
									<RoadmapCardBuilder v-model="editCards" @error="(msg) => showToast('error', 'Gagal', msg)" />
								</div>
								
								<!-- Action Buttons -->
								<div class="flex items-center justify-end gap-3 mt-6 pt-5 border-t border-[#d6e0ee]">
									<!-- Cancel Button -->
									<button
										type="button"
										@click="handleCancelEdit"
										class="flex items-center gap-2 rounded-[8px] border border-[#e05252] bg-white px-5 py-2.5 text-[13px] font-semibold text-[#e05252] shadow-sm transition hover:bg-red-50 focus:outline-none"
									>
										Batal
									</button>

									<!-- Save Button -->
									<button
										type="button"
										@click="handleSaveItem"
										:disabled="!isModified"
										:class="[
											'flex items-center gap-2 rounded-[8px] px-6 py-2.5 text-[13px] font-semibold shadow-sm transition focus:outline-none',
											isModified 
												? 'bg-[#183669] text-white hover:bg-[#122b54] cursor-pointer' 
												: 'bg-slate-100 text-slate-400 cursor-not-allowed border border-slate-200'
										]"
									>
										<svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
											<path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7l-4-4zm-7 16a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm3-10H6V5h9v4z"/>
										</svg>
										Simpan Materi
									</button>
								</div>
							</template>
						</div>

						<!-- ================= RIGHT COLUMN: MATERI & TUGAS LIST (30%) ================= -->
						<div class="w-full lg:w-[32%] xl:w-[30%] space-y-3">
							
							<!-- List of Items in Active Month -->
							<div class="space-y-2.5">
								<div
									v-for="item in currentMonth?.items || []"
									:key="item.id"
									@click="selectItem(item.id)"
									:style="{ borderRadius: '10px' }"
									:class="[
										'group flex items-center justify-between rounded-[10px] p-2.5 px-3 transition-colors cursor-pointer select-none',
										selectedItemId === item.id
											? 'bg-[#183669] text-white shadow-xs'
											: 'bg-white text-[#183669] border border-[#d6e0ee] hover:border-[#183669] shadow-xs'
									]"
								>
									<!-- Left Title Block (default 1 line, expands up to 2 lines) -->
									<div class="flex-1 min-w-0 py-0.5 px-1 mr-1">
										<!-- Inline Edit Mode (dashed border field with transparent background) -->
										<template v-if="editingItemId === item.id">
											<textarea
												:id="`inline-edit-item-${item.id}`"
												v-model="editingItemTitle"
												rows="1"
												spellcheck="false"
												autocomplete="off"
												@input="adjustTextareaHeight($event.target)"
												@click.stop
												@keydown.enter.exact.prevent="saveInlineItemTitle(item, $event)"
												@keydown.esc="cancelInlineEdit"
												:style="{
													wordBreak: 'break-word',
													overflowWrap: 'anywhere'
												}"
												:class="[
													'w-full resize-none rounded-[6px] border border-dashed bg-transparent px-2 py-0 font-poppins text-[13.5px] sm:text-[14px] font-bold leading-[24px] focus:outline-none focus:ring-0 transition-none block overflow-hidden',
													selectedItemId === item.id
														? 'border-white text-white placeholder-white/60 focus:border-white'
														: 'border-[#183669] text-[#183669] placeholder-[#183669]/60 focus:border-[#183669]'
												]"
												placeholder="Nama materi/tugas..."
											></textarea>
										</template>

										<!-- Normal Display Mode (1 line default, max 2 lines with ...) -->
										<template v-else>
											<span
												:class="[
													'font-poppins text-[13.5px] sm:text-[14px] font-bold leading-[26px] line-clamp-2 block px-2',
													selectedItemId === item.id ? 'text-white' : 'text-[#183669]'
												]"
												:style="{
													display: '-webkit-box',
													WebkitLineClamp: 2,
													WebkitBoxOrient: 'vertical',
													overflow: 'hidden',
													textOverflow: 'ellipsis',
													wordBreak: 'break-word',
													overflowWrap: 'anywhere'
												}"
												:title="item.title"
											>
												{{ item.title }}
											</span>
										</template>
									</div>

									<!-- Right Action Buttons (Edit/Submit & Delete) -->
									<div class="flex items-center gap-1.5 shrink-0" @click.stop>
										<!-- Submit Button when in Edit Mode -->
										<button
											v-if="editingItemId === item.id"
											type="button"
											@click="saveInlineItemTitle(item, $event)"
											:class="[
												'flex h-7 w-7 items-center justify-center rounded-[6px] transition hover:opacity-90 active:scale-95 focus:outline-none cursor-pointer shadow-xs',
												selectedItemId === item.id
													? 'bg-emerald-500 text-white hover:bg-emerald-600'
													: 'bg-emerald-600 text-white hover:bg-emerald-700'
											]"
											:title="`Simpan perubahan ${item.title}`"
										>
											<svg class="h-4 w-4 stroke-[3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
											</svg>
										</button>

										<!-- Edit Button when Normal -->
										<button
											v-else
											type="button"
											@click="startInlineEdit(item, $event)"
											class="flex h-7 w-7 items-center justify-center rounded-[6px] transition hover:opacity-85 active:scale-95 focus:outline-none cursor-pointer bg-[#ffd56a] text-[#f4a300]"
											:title="`Edit ${item.title}`"
										>
											<img src="/assets/icons/edit.svg" alt="Edit" class="h-4 w-4 object-contain" />
										</button>

										<!-- Delete Item Button -->
										<button
											type="button"
											@click="confirmDeleteItem(item, $event)"
											class="flex h-7 w-7 items-center justify-center rounded-[6px] transition hover:opacity-85 active:scale-95 focus:outline-none cursor-pointer bg-[#ff9ca1] text-[#ff2f35]"
											:title="`Hapus ${item.title}`"
										>
											<img src="/assets/icons/delete.svg" alt="Delete" class="h-4 w-4 object-contain" />
										</button>
									</div>
								</div>
							</div>

							<!-- Add New Item Card Button [+] -->
							<button
								type="button"
								@click="openAddModal"
								:style="{ borderRadius: '10px' }"
								class="flex w-full items-center justify-center rounded-[10px] border border-[#d6e0ee] bg-white py-3.5 text-[#183669] shadow-xs transition hover:border-[#183669] hover:bg-slate-50 active:scale-98 focus:outline-none cursor-pointer"
								title="Tambah Materi / Tugas"
							>
								<svg class="h-6 w-6 text-[#183669] stroke-[3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
								</svg>
							</button>
						</div>

					</div>
				</div>
			</div>
		</div>

		<!-- ================= MODAL: TAMBAH MATERI / TASK ================= -->
		<ModalFormMateriTask
			:show="isAddModalOpen"
			:month-name="currentMonth?.name || 'Bulan'"
			@close="isAddModalOpen = false"
			@submit="handleSaveNewItem"
		/>

		<!-- ================= MODAL: DELETE MONTH CONFIRMATION ================= -->
		<Teleport to="body">
			<div
				v-if="isDeleteMonthModalOpen"
				class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overscroll-contain bg-black/50 p-4 backdrop-blur-xs"
				@click.self="isDeleteMonthModalOpen = false"
			>
				<div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl text-center font-poppins">
					<div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-600 mb-4">
						<svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
							<path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
						</svg>
					</div>

					<h3 class="text-lg font-bold text-[#17334F]">
						Hapus {{ monthToDelete?.name }}?
					</h3>
					<p class="mt-2 font-inter text-sm text-[#64748b]">
						Semua materi dan tugas di dalam tab <span class="font-bold text-[#17334F]">"{{ monthToDelete?.name }}"</span> akan ikut terhapus.
					</p>

					<div class="mt-6 flex items-center justify-center gap-3">
						<button
							type="button"
							@click="isDeleteMonthModalOpen = false"
							class="rounded-lg border border-[#d6e0ee] px-4 py-2.5 text-sm font-medium text-[#475569] hover:bg-slate-50 transition"
						>
							Batal
						</button>
						<button
							type="button"
							@click="executeDeleteMonth"
							class="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition shadow-sm"
						>
							Hapus Bulan
						</button>
					</div>
				</div>
			</div>
		</Teleport>

		<!-- ================= MODAL: DELETE ITEM CONFIRMATION ================= -->
		<Teleport to="body">
			<div
				v-if="isDeleteItemModalOpen"
				class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overscroll-contain bg-black/50 p-4 backdrop-blur-xs"
				@click.self="isDeleteItemModalOpen = false"
			>
				<div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl text-center font-poppins">
					<div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-600 mb-4">
						<svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
							<path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
						</svg>
					</div>

					<h3 class="text-lg font-bold text-[#17334F]">
						Hapus {{ itemToDelete?.title }}?
					</h3>
					<p class="mt-2 font-inter text-sm text-[#64748b]">
						Apakah Anda yakin ingin menghapus <span class="font-bold text-[#17334F]">"{{ itemToDelete?.title }}"</span>? Tindakan ini tidak dapat dibatalkan.
					</p>

					<div class="mt-6 flex items-center justify-center gap-3">
						<button
							type="button"
							@click="isDeleteItemModalOpen = false"
							class="rounded-lg border border-[#d6e0ee] px-4 py-2.5 text-sm font-medium text-[#475569] hover:bg-slate-50 transition"
						>
							Batal
						</button>
						<button
							type="button"
							@click="executeDeleteItem"
							class="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition shadow-sm"
						>
							Hapus Item
						</button>
					</div>
				</div>
			</div>
		</Teleport>

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
