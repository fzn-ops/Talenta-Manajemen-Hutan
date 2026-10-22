<script setup>
import AdminLayout from '@/Layouts/dashboard/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch, onMounted, onUnmounted } from 'vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import ModalFormMateriTask from '@/Components/dashboard/admin/ModalFormMateriTask.vue';
import EditButtonTable from '@/Components/dashboard/EditButtonTable.vue';
import RoadmapCardBuilder from '@/Components/dashboard/admin/RoadmapCardBuilder.vue';
import RoadmapMaterialNavigation from '@/Components/dashboard/admin/RoadmapMaterialNavigation.vue';
import DeleteModal from '@/Components/dashboard/DeleteModal.vue';

const props = defineProps({
	roadmap: {
		type: Object,
		required: true,
	},
	months: {
		type: Array,
		default: () => [],
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

const currentRoadmap = computed(() => {
	return props.roadmap || {
		id: 1,
		category: 'Roadmap',
		title: 'Roadmap',
		subTitle: 'Yuk Pelajari Roadmap Yang Telah Kamu Ikuti!',
	};
});

// Months Data Structure with Materials & Tasks
const months = ref(props.months || []);

// Active Tab & Selection State
const activeMonthId = ref(props.months?.[0]?.id || 1);
const selectedItemId = ref(props.months?.[0]?.items?.[0]?.id || null);

// Keep months in sync with props
watch(
	() => props.months,
	(newMonths) => {
		months.value = newMonths || [];
		if (!months.value.some((m) => m.id === activeMonthId.value)) {
			activeMonthId.value = months.value[0]?.id || null;
			selectedItemId.value = months.value[0]?.items?.[0]?.id || null;
		} else {
			const curM = months.value.find((m) => m.id === activeMonthId.value);
			if (curM && !curM.items.some((i) => i.id === selectedItemId.value)) {
				selectedItemId.value = curM.items[0]?.id || null;
			}
		}
	},
	{ deep: true }
);

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

const handleAddCard = async (type) => {
	if (!hasCardType(type)) {
		editCards.value.push({ id: Date.now().toString(), type, content: '', fileName: '' });
		showAddCardDropdown.value = false;
		
		await nextTick();
		const mainEl = document.querySelector('main');
		if (mainEl) {
			mainEl.scrollTo({ top: mainEl.scrollHeight, behavior: 'smooth' });
		}
	}
};

// Initial State for dirty checking
const initialTopic = ref('');
const initialCardsString = ref('');

// Helper to reliably render base64 PDF in iframe/object by converting to Blob URL
const blobUrlCache = new Map();
const getPdfUrl = (dataUrl) => {
	if (!dataUrl) return '';
	if (dataUrl.startsWith('http') || dataUrl.startsWith('/')) return dataUrl;
	
	if (dataUrl.startsWith('data:application/pdf;base64,')) {
		if (blobUrlCache.has(dataUrl)) {
			return blobUrlCache.get(dataUrl);
		}
		try {
			const parts = dataUrl.split(',');
			const bstr = atob(parts[1]);
			let n = bstr.length;
			const u8arr = new Uint8Array(n);
			while(n--) {
				u8arr[n] = bstr.charCodeAt(n);
			}
			const blob = new Blob([u8arr], { type: 'application/pdf' });
			const url = URL.createObjectURL(blob);
			blobUrlCache.set(dataUrl, url);
			return url;
		} catch (e) {
			console.error('Failed to convert base64 to Blob URL', e);
			return dataUrl;
		}
	}
	return dataUrl;
};

const isSaving = ref(false);

const stripError = (cards) => {
	return cards.map(c => ({
		id: c.id,
		type: c.type,
		content: c.content,
		fileName: c.fileName || '',
		isFixed: Boolean(c.isFixed),
		minTasks: c.type === 'task_submission' ? (Math.max(1, parseInt(c.minTasks) || 1)) : undefined,
	}));
};

const isModified = computed(() => {
	if (editTopic.value !== initialTopic.value) return true;
	return JSON.stringify(stripError(editCards.value)) !== initialCardsString.value;
});

// Sync editor state with selected item
const syncEditorState = () => {
	isEditingContent.value = false;
	if (currentItem.value) {
		editTopic.value = currentItem.value.judul || currentItem.value.topic || '';
		
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

		// Enforce task_submission card for Tugas
		if (currentItem.value.type === 'Tugas') {
			const targetMinTasks = (currentItem.value.jumlah_aktivitas || currentItem.value.maksimal_tugas) > 0 ? (currentItem.value.jumlah_aktivitas || currentItem.value.maksimal_tugas) : 1;
			const hasTaskSub = editCards.value.some(c => c.type === 'task_submission');
			if (!hasTaskSub) {
				editCards.value.push({
					id: Date.now() + 'ts',
					type: 'task_submission',
					content: `Ikuti Kegiatan Terkait ${currentItem.value.judul || currentItem.value.topic || 'Tugas'}`,
					fileName: '',
					isFixed: true, // Cannot be deleted
					minTasks: targetMinTasks,
				});
			} else {
				const taskSub = editCards.value.find(c => c.type === 'task_submission');
				if (taskSub) {
					taskSub.isFixed = true;
					if (taskSub.minTasks === undefined || taskSub.minTasks === null || taskSub.minTasks <= 0) {
						taskSub.minTasks = targetMinTasks;
					}
				}
			}
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

const isMobileNavOpen = ref(false);

watch(
	[activeMonthId, selectedItemId],
	() => {
		syncEditorState();
		isMobileNavOpen.value = false;
	},
	{ immediate: true }
);

// Switch active month
const selectMonth = (monthId) => {
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

// --- Dynamic Top-Right Corner Logic ---
const tabsContainerRef = ref(null);
const isTopRightRounded = ref(true);
let tabsResizeObserver = null;

const checkTopRightCorner = () => {
	if (!tabsContainerRef.value) return;
	const container = tabsContainerRef.value;
	const children = container.children;
	if (children.length === 0) return;
	
	const lastChild = children[children.length - 1];
	const totalWidth = lastChild.offsetLeft + lastChild.offsetWidth;
	
	// If the tabs occupy less width than the container, we have empty space on the right
	isTopRightRounded.value = totalWidth < container.clientWidth - 5;
};

onMounted(() => {
	if (tabsContainerRef.value) {
		tabsResizeObserver = new ResizeObserver(() => checkTopRightCorner());
		tabsResizeObserver.observe(tabsContainerRef.value);
	}
	setTimeout(() => checkTopRightCorner(), 100);
});

onUnmounted(() => {
	if (tabsResizeObserver && tabsContainerRef.value) {
		tabsResizeObserver.unobserve(tabsContainerRef.value);
	}
});

watch(() => months.value.length, () => {
	nextTick(() => checkTopRightCorner());
});

const getTaskMinRequirement = (item) => {
	if (!item || item.type !== 'Tugas') return 1;
	if (item.cards) {
		const tCard = item.cards.find(c => c.type === 'task_submission');
		if (tCard && tCard.minTasks) return Math.max(1, parseInt(tCard.minTasks) || 1);
	}
	return Math.max(1, parseInt(item.jumlah_aktivitas ?? item.maksimal_tugas) || 1);
};


const getTaskInstructionText = (item) => {
	if (!item || item.type !== 'Tugas') return '';
	if (item.cards) {
		const tCard = item.cards.find(c => c.type === 'task_submission');
		if (tCard && tCard.content) return tCard.content;
	}
	return `Ikuti Kegiatan Terkait ${item.judul || item.topic || 'Tugas'}`;
};

// Save Item Content
const handleSaveItem = () => {
	if (!currentItem.value || isSaving.value) return;

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
		else if (c.type === 'task_submission') {
			isEmpty = !c.content || c.content.trim() === '' || !c.minTasks || parseInt(c.minTasks) < 1;
		}
		
		c.hasError = isEmpty;
		if (isEmpty) hasEmpty = true;
	});

	if (hasEmpty) {
		showToast('error', 'Gagal Disimpan', 'Terdapat konten materi yang belum terisi dengan sempurna (pastikan judul dan jumlah tugas minimal terisi valid).');
		return;
	}

	isSaving.value = true;
	router.put(`/admin/roadmap/materi/${currentItem.value.id}`, {
		judul: editTopic.value.trim(),
		topic: editTopic.value.trim(),
		cards: stripError(editCards.value),
	}, {
		preserveScroll: true,
		onSuccess: () => {
			isSaving.value = false;
			isEditingContent.value = false;
			showToast('success', 'Berhasil Disimpan', `Konten "${currentItem.value.title}" berhasil diperbarui.`);
		},
		onError: (err) => {
			isSaving.value = false;
			const msg = Object.values(err)[0] || 'Gagal menyimpan konten.';
			showToast('error', 'Gagal Disimpan', msg);
		},
	});
};

// Reset/Cancel Item Changes
const handleCancelEdit = () => {
	syncEditorState();
	isEditingContent.value = false;
};

const topicTextareaRef = ref(null);

const adjustTopicTextarea = (e) => {
	const el = e?.target || topicTextareaRef.value;
	if (!el) return;
	el.style.height = 'auto';
	el.style.height = el.scrollHeight + 'px';
};

const startEditContent = async () => {
	isEditingContent.value = true;
	await nextTick();
	adjustTopicTextarea();
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

// Open external links in rich text safely in a new tab
const handleRichTextClick = (e) => {
	const link = e.target.closest('a');
	if (!link) return;
	const href = link.getAttribute('href');
	if (href && (href.startsWith('http://') || href.startsWith('https://') || href.startsWith('//'))) {
		e.preventDefault();
		window.open(href, '_blank', 'noopener,noreferrer');
	}
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

	router.post(`/admin/roadmap/${props.roadmap.id}/bulan`, {}, {
		preserveScroll: true,
		onSuccess: (page) => {
			const newMonthId = page.props.flash?.newMonthId;
			if (newMonthId) {
				activeMonthId.value = newMonthId;
			} else if (months.value.length > 0) {
				const lastMonth = months.value[months.value.length - 1];
				activeMonthId.value = lastMonth.id;
			}
			showToast('success', 'Bulan Ditambahkan', 'Tab Bulan berhasil ditambahkan.');
		},
		onError: (err) => {
			const msg = Object.values(err)[0] || 'Gagal menambahkan bulan.';
			showToast('error', 'Gagal', msg);
		},
	});
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

	router.delete(`/admin/roadmap/${props.roadmap.id}/bulan/${deletedId}`, {
		preserveScroll: true,
		onSuccess: () => {
			isDeleteMonthModalOpen.value = false;
			monthToDelete.value = null;
			showToast('success', 'Bulan Dihapus', 'Bulan berhasil dihapus dari roadmap.');
		},
		onError: (err) => {
			isDeleteMonthModalOpen.value = false;
			const msg = Object.values(err)[0] || 'Gagal menghapus bulan.';
			showToast('error', 'Gagal', msg);
		},
	});
};

// Add Item (Materi / Tugas) Modal
const isAddModalOpen = ref(false);

const openAddModal = () => {
	isAddModalOpen.value = true;
};

const handleSaveNewItem = (itemData) => {
	if (!currentMonth.value) return;

	router.post(`/admin/roadmap/${props.roadmap.id}/bulan/${currentMonth.value.id}/materi`, {
		type: itemData.type,
		title: itemData.title,
	}, {
		preserveScroll: true,
		onSuccess: (page) => {
			const newItemId = page.props.flash?.newItemId;
			if (newItemId) {
				selectedItemId.value = newItemId;
			}
			isAddModalOpen.value = false;
			showToast('success', 'Item Ditambahkan', `"${itemData.title}" berhasil ditambahkan.`);
		},
		onError: (err) => {
			const msg = Object.values(err)[0] || 'Gagal menambahkan item.';
			showToast('error', 'Gagal', msg);
		},
	});
};

// Handle Item Title Update from Sidebar
const updateItemTitle = (item, newTitle) => {
	router.patch(`/admin/roadmap/materi/${item.id}/title`, {
		title: newTitle,
	}, {
		preserveScroll: true,
		onSuccess: () => {
			item.title = newTitle;
			item.topik = newTitle;
			showToast('success', 'Judul Diperbarui', `Nama item diubah menjadi "${newTitle}".`);
		},
		onError: (err) => {
			const msg = Object.values(err)[0] || 'Gagal memperbarui judul.';
			showToast('error', 'Gagal', msg);
		},
	});
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

	router.delete(`/admin/roadmap/materi/${deletedId}`, {
		preserveScroll: true,
		onSuccess: () => {
			if (selectedItemId.value === deletedId) {
				const remaining = currentMonth.value.items.filter((i) => i.id !== deletedId);
				selectedItemId.value = remaining[0]?.id || null;
			}
			isDeleteItemModalOpen.value = false;
			itemToDelete.value = null;
			showToast('success', 'Item Dihapus', 'Item berhasil dihapus dari daftar.');
		},
		onError: (err) => {
			isDeleteItemModalOpen.value = false;
			const msg = Object.values(err)[0] || 'Gagal menghapus item.';
			showToast('error', 'Gagal', msg);
		},
	});
};
</script>

<template>
	<Head :title="`Detail ${currentRoadmap.title} - Admin`" />

	<AdminLayout>
		<div class="flex flex-col gap-6 min-h-[calc(100vh-80px)] mx-auto w-full max-w-[1520px] px-4 pt-8 sm:pt-[52px] pb-24 sm:pb-20 font-poppins sm:px-6 lg:px-8">
			<!-- Top Breadcrumb -->
			<nav class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-[#183669]">
				<Link href="/admin/roadmap" class="hover:underline text-[#183669] transition-colors">
					Daftar Roadmap
				</Link>
				<span class="text-[#8ca1b9]">/</span>
				<span class="text-[#7188a3] font-medium truncate max-w-sm">{{ currentRoadmap.title }}</span>
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

			<div class="mt-6 w-full max-w-full overflow-visible">
				<!-- Folder Header Tabs Row (Seamless 0 gap between tabs and + button, horizontal scroll for many tabs) -->
				<div 
					ref="tabsContainerRef"
					class="flex items-end select-none relative z-10 -mb-[1px] overflow-x-auto scrollbar-hide w-full" 
					style="scrollbar-width: none; -ms-overflow-style: none;"
				>
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

				<!-- Main White Card Body (Top right corner dynamic; bottom corners 10px rounded) -->
				<div
					:class="[
						'border border-[#d6e0ee] bg-white p-5 sm:p-7 lg:p-9 shadow-xs font-poppins relative z-0 min-h-[400px]',
						isTopRightRounded ? 'rounded-tr-[10px]' : 'rounded-tr-none',
						'rounded-b-[10px] rounded-tl-none'
					]"
				>
					<div class="flex flex-col md:flex-row gap-6 lg:gap-8 items-start">
						
						<!-- ================= LEFT COLUMN: CONTENT & MEDIA (70%) ================= -->
						<div class="w-full md:w-[62%] lg:w-[68%] xl:w-[70%] space-y-5">
							
							<!-- READ-ONLY VIEW -->
							<template v-if="!isEditingContent">
								<!-- Title & Edit Button -->
								<div class="flex items-start gap-4">
									<h2 class="flex-1 min-w-0 font-poppins text-[22px] sm:text-[26px] font-extrabold text-[#17334F] leading-tight break-words">
										{{ currentItem?.judul || currentItem?.topic || 'Belum ada judul' }}
									</h2>
									<div class="shrink-0 pt-0.5">
										<button
											type="button"
											@click="startEditContent"
											class="inline-flex items-center gap-1.5 rounded-[8px] bg-[#ffd56a] hover:bg-[#ffcc54] active:scale-95 px-3.5 py-1.5 font-poppins text-[13px] font-bold text-[#8a5b00] shadow-xs transition-all cursor-pointer"
											title="Edit Konten Materi"
										>
											<svg
												width="15"
												height="15"
												viewBox="0 0 19 19"
												fill="none"
												xmlns="http://www.w3.org/2000/svg"
												class="h-3.5 w-3.5 shrink-0"
												aria-hidden="true"
											>
												<path
													d="M4.92119 4.92074C4.92119 5.18177 4.81749 5.43211 4.63291 5.61669C4.44833 5.80126 4.19798 5.90496 3.93695 5.90496H2.95271C2.69168 5.90496 2.44133 6.00865 2.25675 6.19323C2.07217 6.3778 1.96847 6.62814 1.96847 6.88917V15.7471C1.96847 16.0082 2.07217 16.2585 2.25675 16.4431C2.44133 16.6277 2.69168 16.7313 2.95271 16.7313H11.8108C12.0719 16.7313 12.3222 16.6277 12.5068 16.4431C12.6914 16.2585 12.7951 16.0082 12.7951 15.7471V14.7629C12.7951 14.5019 12.8988 14.2515 13.0834 14.067C13.2679 13.8824 13.5183 13.7787 13.7793 13.7787C14.0404 13.7787 14.2907 13.8824 14.4753 14.067C14.6599 14.2515 14.7636 14.5019 14.7636 14.7629V15.7471C14.7636 16.5302 14.4525 17.2812 13.8987 17.835C13.345 18.3887 12.594 18.6998 11.8108 18.6998H2.95271C2.1696 18.6998 1.41857 18.3887 0.864829 17.835C0.311088 17.2812 0 16.5302 0 15.7471V6.88917C0 6.10608 0.311088 5.35506 0.864829 4.80134C1.41857 4.24761 2.1696 3.93652 2.95271 3.93652H3.93695C4.19798 3.93652 4.44833 4.04022 4.63291 4.22479C4.81749 4.40937 4.92119 4.65971 4.92119 4.92074Z"
													fill="#8a5b00"
												/>
												<path
													d="M11.413 2.96342L15.7358 7.2861L9.55481 13.4896C9.4634 13.5813 9.3548 13.6541 9.23522 13.7037C9.11564 13.7534 8.98744 13.779 8.85797 13.779H5.90526C5.64422 13.779 5.39388 13.6753 5.2093 13.4907C5.02472 13.3061 4.92102 13.0558 4.92102 12.7948V9.84211C4.92105 9.71264 4.94662 9.58444 4.99628 9.46487C5.04593 9.34529 5.11869 9.23669 5.21039 9.14528L11.413 2.96342ZM17.8067 0.893608C18.3495 1.43606 18.6678 2.16332 18.6979 2.93014C18.728 3.69697 18.4677 4.44693 17.9691 5.03027L17.8076 5.20743L17.1256 5.89048L12.8077 1.57272L13.4918 0.893608C14.064 0.32144 14.84 0 15.6492 0C16.4584 0 17.2345 0.32144 17.8067 0.893608Z"
													fill="#8a5b00"
												/>
											</svg>
											<span>Edit</span>
										</button>
									</div>
								</div>

								<!-- Media & Content Display (Read Only) -->
								<div v-if="currentItem?.cards && currentItem.cards.length > 0" class="flex flex-col gap-6 sm:gap-7 mt-6">
									<div v-for="card in currentItem.cards" :key="card.id" class="w-full">
										<!-- IMAGE -->
										<div v-if="card.type === 'image' && card.content" class="w-full aspect-video rounded-[10px] overflow-hidden border border-[#d6e0ee] shadow-sm bg-slate-50">
											<img :src="card.content" class="w-full h-full object-cover" />
										</div>
										
										<!-- PDF -->
										<div v-else-if="card.type === 'pdf' && card.fileName" class="w-full flex flex-col rounded-[10px] border border-[#d6e0ee] shadow-sm bg-white overflow-hidden">
											<!-- Header: Icon, Name, Download -->
											<div class="flex items-center justify-between bg-[#fafcff] px-4 py-3 border-b border-[#d6e0ee]">
												<div class="flex items-center gap-3 min-w-0">
													<svg class="h-7 w-7 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
														<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
													</svg>
													<div class="flex-1 min-w-0">
														<p class="font-poppins font-bold text-[#183669] truncate text-[13px] sm:text-[14px]">{{ card.fileName }}</p>
													</div>
												</div>
												<a :href="getPdfUrl(card.content)" target="_blank" :download="card.fileName" class="shrink-0 rounded-[6px] bg-[#183669] px-3 py-1.5 sm:px-4 sm:py-2 text-[11px] sm:text-[12px] font-bold text-white hover:bg-[#122b54] transition shadow-sm whitespace-nowrap flex items-center gap-1.5">
													<svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
													Unduh
												</a>
											</div>
											<!-- Browser Native PDF Viewer -->
											<div class="w-full h-[450px] sm:h-[550px] md:h-[650px] bg-slate-50 relative">
												<object :data="getPdfUrl(card.content)" type="application/pdf" class="w-full h-full">
													<iframe :src="getPdfUrl(card.content)" class="w-full h-full border-none" title="PDF Viewer">
														<p class="p-4 text-[13px] text-center text-slate-500">Browser Anda tidak mendukung preview PDF. Silakan klik tombol Unduh.</p>
													</iframe>
												</object>
											</div>
										</div>
										
										<!-- TEXT -->
										<div 
											v-else-if="card.type === 'text'" 
											class="w-full rendered-rich-text text-left font-inter text-[13.5px] sm:text-[14.5px] text-[#334155] leading-relaxed break-words" 
											@click="handleRichTextClick" 
											v-html="card.content"
										></div>
										
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
								<div v-else-if="currentItem?.description || currentItem?.file">
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
									<div 
										class="font-inter text-[13.5px] sm:text-[14.5px] leading-relaxed text-[#334155] text-left rendered-rich-text break-words mt-6"
										@click="handleRichTextClick"
										v-html="currentItem?.description"
									></div>
								</div>
								
								<!-- Empty State Guide -->
								<div v-else class="mt-8 flex flex-col items-center justify-center p-10 bg-[#f8fafc] border-2 border-dashed border-[#d6e0ee] rounded-[10px] text-center">
									<h3 class="font-poppins text-[16px] sm:text-[18px] font-bold text-[#17334F] mb-2">Konten Materi Kosong</h3>
									<p class="font-inter text-[13px] sm:text-[14px] text-[#64748b] max-w-sm mx-auto leading-relaxed">
										Silakan klik tombol <strong class="text-[#183669] font-bold">Edit Konten</strong> di atas untuk mulai menambahkan teks, gambar, video YouTube, atau PDF.
									</p>
								</div>
								
								<!-- Task Information & Instruction Preview (Admin View) -->
								<div v-if="currentItem?.type === 'Tugas'" class="mt-8 border-t border-[#d6e0ee] pt-6">
									<div class="rounded-[10px] border border-[#d6e0ee] bg-[#fafcff] p-5 sm:p-6 shadow-xs transition-all">
										<div class="flex items-start gap-4">
											<!-- Icon Box -->
											<div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-[8px] bg-[#e8eff8] text-[#183669] border border-[#d6e0ee]">
												<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
													<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
												</svg>
											</div>

											<!-- Content Details -->
											<div class="flex-1 min-w-0">
												<div class="flex flex-wrap items-center gap-2 mb-1.5">
													<span class="font-poppins text-[11px] font-bold uppercase tracking-wider text-[#7188a3]">
														Instruksi Pengumpulan Tugas
													</span>
													<span class="inline-flex items-center gap-1 rounded-full bg-[#183669]/10 px-2.5 py-0.5 font-poppins text-[11.5px] font-bold text-[#183669]">
														<svg class="h-3.5 w-3.5 text-[#183669]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
															<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
														</svg>
														Wajib: {{ getTaskMinRequirement(currentItem) }} Kegiatan
													</span>
												</div>

												<h3 class="font-poppins text-[16px] sm:text-[17px] font-bold text-[#17334F] leading-snug">
													{{ getTaskInstructionText(currentItem) }}
												</h3>

												<p class="font-inter text-[13px] text-[#5a718d] leading-relaxed mt-2.5">
													Mahasiswa diwajibkan untuk mengumpulkan tepat <strong class="text-[#183669] font-bold">{{ getTaskMinRequirement(currentItem) }} kegiatan</strong> (tidak kurang dan tidak lebih) yang relevan melalui formulir tugas pada halaman mahasiswa.
												</p>

												<div class="mt-4 flex items-center gap-2 text-[12px] text-[#7188a3] font-inter border-t border-[#e2eaf4] pt-3">
													<svg class="h-4 w-4 text-[#8ca1b9] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
														<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
													</svg>
													<span>Untuk mengubah judul instruksi atau jumlah minimal kegiatan, klik tombol <strong class="text-[#183669]">Edit</strong> di atas.</span>
												</div>
											</div>
										</div>
									</div>
								</div>
							</template>

							<!-- EDIT MODE -->
							<template v-else>
								<!-- 1. Topic Title Field -->
								<div class="flex items-start gap-3 relative">
									<!-- Editable Dashed Input Box -->
									<div
										:style="{ borderRadius: '10px' }"
										class="flex-1 flex items-start gap-2 rounded-[10px] border-2 border-dashed border-[#183669] bg-white pl-4 pr-3 py-2.5 transition-colors focus-within:bg-[#fafcff]"
									>
										<textarea
											ref="topicTextareaRef"
											v-model="editTopic"
											rows="1"
											maxlength="150"
											placeholder="Cara Mendapatkan Return Usaha 100% dalam 1 Bulan"
											@input="adjustTopicTextarea"
											class="w-full border-none bg-transparent p-0 font-poppins text-[15px] sm:text-[16px] font-bold text-[#183669] placeholder-[#94a3b8] focus:outline-none focus:ring-0 resize-none overflow-hidden leading-[1.4]"
										></textarea>
										<span class="text-[10px] sm:text-[11px] font-semibold text-[#8ca1b9] shrink-0 bg-slate-50 px-1.5 py-0.5 rounded border border-slate-200 mt-0.5">
											{{ editTopic.length }}/150
										</span>
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
										:disabled="!isModified || isSaving"
										:class="[
											'flex items-center gap-2 rounded-[8px] px-6 py-2.5 text-[13px] font-semibold shadow-sm transition focus:outline-none',
											isModified && !isSaving
												? 'bg-[#183669] text-white hover:bg-[#122b54] cursor-pointer' 
												: 'bg-slate-100 text-slate-400 cursor-not-allowed border border-slate-200'
										]"
									>
										<svg v-if="isSaving" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
											<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
											<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
										</svg>
										<svg v-else class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
											<path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7l-4-4zm-7 16a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm3-10H6V5h9v4z"/>
										</svg>
										{{ isSaving ? 'Menyimpan...' : 'Simpan Materi' }}
									</button>
								</div>
							</template>
						</div>

						<!-- ================= RIGHT COLUMN: MATERI & TUGAS LIST (30%) (DESKTOP / TABLET) ================= -->
						<div class="hidden md:block w-full md:w-[38%] lg:w-[32%] xl:w-[30%] sticky top-6 self-start space-y-3">
							<RoadmapMaterialNavigation
								:items="currentMonth?.items || []"
								v-model:selectedItemId="selectedItemId"
								@update-item-title="updateItemTitle"
								@delete-item="confirmDeleteItem"
								@add-item="openAddModal"
							/>
						</div>

					</div>
				</div>
			</div>
		</div>

		<!-- ================= MOBILE BOTTOM NAVIGATION BAR ================= -->
		<div 
			v-if="currentMonth" 
			class="md:hidden fixed bottom-0 inset-x-0 z-30 bg-white border-t border-[#d6e0ee] shadow-[0_-8px_20px_-3px_rgba(0,0,0,0.08)] p-3 px-4 flex items-center justify-between"
		>
			<div class="flex flex-col mr-3 flex-1 min-w-0">
				<span class="text-[10px] font-bold text-[#8ca1b9] uppercase tracking-wider mb-0.5">Materi Saat Ini</span>
				<span class="text-[13px] font-extrabold text-[#17334F] line-clamp-1 truncate">{{ currentItem?.title || 'Belum ada' }}</span>
			</div>
			<button
				type="button"
				@click="isMobileNavOpen = true"
				class="flex shrink-0 items-center gap-2 bg-[#183669] text-white px-5 py-2.5 rounded-full text-[12px] font-bold active:scale-95 transition hover:bg-[#122b54] cursor-pointer shadow-sm"
			>
				<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
				</svg>
				Daftar Materi
			</button>
		</div>

		<!-- ================= MODAL: MOBILE NAVIGATION DRAWER ================= -->
		<Teleport to="body">
			<!-- Overlay with identical animation and background as SidebarAdmin -->
			<Transition
				enter-active-class="ease-out duration-300"
				enter-from-class="opacity-0"
				enter-to-class="opacity-100"
				leave-active-class="ease-in duration-200"
				leave-from-class="opacity-100"
				leave-to-class="opacity-0"
			>
				<div
					v-if="isMobileNavOpen"
					class="fixed inset-0 z-[60] bg-[#102653]/35 backdrop-blur-xs cursor-pointer md:hidden"
					aria-hidden="true"
					@click="isMobileNavOpen = false"
				></div>
			</Transition>

			<!-- Drawer Panel sliding from right -->
			<Transition
				enter-active-class="transform transition ease-out duration-300"
				enter-from-class="translate-x-full"
				enter-to-class="translate-x-0"
				leave-active-class="transform transition ease-in duration-200"
				leave-from-class="translate-x-0"
				leave-to-class="translate-x-full"
			>
				<div
					v-if="isMobileNavOpen"
					class="fixed top-0 right-0 z-[61] h-full w-[85%] max-w-[350px] bg-white shadow-2xl flex flex-col md:hidden font-poppins"
				>
					<!-- Drawer Header -->
					<div class="flex items-center justify-between p-4 sm:p-5 bg-white border-b border-[#d6e0ee] shrink-0">
						<div>
							<h3 class="font-extrabold text-[#17334F] text-[17px] sm:text-[18px]">Daftar Materi</h3>
							<p class="text-[12px] text-[#64748b] mt-0.5">Navigasi bulan ini</p>
						</div>
						<button 
							type="button"
							@click="isMobileNavOpen = false" 
							class="p-2 rounded-[8px] hover:bg-slate-100 text-[#64748b] transition active:bg-slate-200 cursor-pointer"
						>
							<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
							</svg>
						</button>
					</div>
					<!-- Drawer Body -->
					<div class="flex-1 overflow-y-auto p-4 sm:p-5 bg-[#fafcff] space-y-2">
						<RoadmapMaterialNavigation
							:items="currentMonth?.items || []"
							v-model:selectedItemId="selectedItemId"
							@update-item-title="updateItemTitle"
							@delete-item="confirmDeleteItem"
							@add-item="openAddModal"
							@update:selectedItemId="isMobileNavOpen = false"
						/>
					</div>
				</div>
			</Transition>
		</Teleport>

		<!-- ================= MODAL: TAMBAH MATERI / TASK ================= -->
		<ModalFormMateriTask
			:show="isAddModalOpen"
			:month-name="currentMonth?.name || 'Bulan'"
			@close="isAddModalOpen = false"
			@submit="handleSaveNewItem"
		/>

		<!-- ================= MODAL: DELETE MONTH CONFIRMATION ================= -->
		<DeleteModal
			:show="isDeleteMonthModalOpen"
			@close="isDeleteMonthModalOpen = false"
			@confirm="executeDeleteMonth"
		>
			<template #title>
				Hapus {{ monthToDelete?.name }}?
			</template>
			<template #message>
				Semua materi dan tugas di dalam tab <span class="font-bold text-[#17334F]">"{{ monthToDelete?.name }}"</span> akan ikut terhapus.
			</template>
			<template #confirm-text>
				Hapus Bulan
			</template>
		</DeleteModal>

		<!-- ================= MODAL: DELETE ITEM CONFIRMATION ================= -->
		<DeleteModal
			:show="isDeleteItemModalOpen"
			@close="isDeleteItemModalOpen = false"
			@confirm="executeDeleteItem"
		>
			<template #title>
				Hapus {{ itemToDelete?.title }}?
			</template>
			<template #message>
				Apakah Anda yakin ingin menghapus <span class="font-bold text-[#17334F]">"{{ itemToDelete?.title }}"</span>? Tindakan ini tidak dapat dibatalkan.
			</template>
			<template #confirm-text>
				Hapus Item
			</template>
		</DeleteModal>

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

<style>
/* Rich Text Render Styling */
.rendered-rich-text {
	word-break: break-word;
	color: #334155;
	font-family: 'Inter', sans-serif;
	line-height: 1.7;
}

.rendered-rich-text p {
	margin-top: 0.5rem !important;
	margin-bottom: 0.5rem !important;
	line-height: 1.7 !important;
}

.rendered-rich-text p:first-child {
	margin-top: 0 !important;
}

.rendered-rich-text p:last-child {
	margin-bottom: 0 !important;
}

.rendered-rich-text h1 {
	display: block !important;
	font-size: 1.45rem !important;
	font-weight: 700 !important;
	color: #17334F !important;
	margin-top: 1.25rem !important;
	margin-bottom: 0.5rem !important;
	line-height: 1.3 !important;
}

.rendered-rich-text h2 {
	display: block !important;
	font-size: 1.25rem !important;
	font-weight: 700 !important;
	color: #17334F !important;
	margin-top: 1rem !important;
	margin-bottom: 0.4rem !important;
	line-height: 1.35 !important;
}

.rendered-rich-text h3 {
	display: block !important;
	font-size: 1.1rem !important;
	font-weight: 600 !important;
	color: #17334F !important;
	margin-top: 0.85rem !important;
	margin-bottom: 0.35rem !important;
	line-height: 1.4 !important;
}

.rendered-rich-text b,
.rendered-rich-text strong {
	font-weight: 700 !important;
	color: #0f2347 !important;
}

.rendered-rich-text i,
.rendered-rich-text em {
	font-style: italic !important;
}

.rendered-rich-text u {
	text-decoration: underline !important;
}

.rendered-rich-text s,
.rendered-rich-text strike {
	text-decoration: line-through !important;
}

.rendered-rich-text ul {
	display: block !important;
	list-style-type: disc !important;
	padding-left: 1.6rem !important;
	margin-top: 0.5rem !important;
	margin-bottom: 0.5rem !important;
}

.rendered-rich-text ol {
	display: block !important;
	list-style-type: decimal !important;
	padding-left: 1.6rem !important;
	margin-top: 0.5rem !important;
	margin-bottom: 0.5rem !important;
}

.rendered-rich-text li {
	display: list-item !important;
	margin-top: 0.25rem !important;
	margin-bottom: 0.25rem !important;
	line-height: 1.65 !important;
}

.rendered-rich-text a {
	color: #2563eb !important;
	text-decoration: underline !important;
	font-weight: 600 !important;
	cursor: pointer !important;
	transition: color 0.15s ease, opacity 0.15s ease !important;
}

.rendered-rich-text a:hover {
	color: #1d4ed8 !important;
	opacity: 0.9 !important;
}

.rendered-rich-text blockquote {
	display: block !important;
	border-left: 4px solid #183669 !important;
	background: #f8fafc !important;
	padding: 0.6rem 1rem !important;
	margin: 0.75rem 0 !important;
	color: #475569 !important;
	font-style: italic !important;
	border-radius: 0 8px 8px 0 !important;
}

.rendered-rich-text code {
	background: #f1f5f9 !important;
	color: #e05252 !important;
	padding: 0.2rem 0.4rem !important;
	border-radius: 4px !important;
	font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
	font-size: 0.875em !important;
}

.rendered-rich-text hr {
	border: 0 !important;
	height: 1px !important;
	background: #d6e0ee !important;
	margin: 1.25rem 0 !important;
}
</style>
