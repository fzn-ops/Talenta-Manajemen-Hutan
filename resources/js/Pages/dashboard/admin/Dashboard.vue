<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/dashboard/AdminLayout.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import RejectModal from '@/Components/dashboard/RejectModal.vue';
import ApproveModal from '@/Components/dashboard/ApproveModal.vue';
import PreviewButtonTable from '@/Components/dashboard/PreviewButtonTable.vue';
import AcceptButton from '@/Components/dashboard/admin/AcceptButton.vue';
import RejectButton from '@/Components/dashboard/admin/RejectButton.vue';
import DeleteButtonTable from '@/Components/dashboard/DeleteButtonTable.vue';
import DeleteModal from '@/Components/dashboard/DeleteModal.vue';

// Detail Modals
import ModalFormPersetujuanAktivitas from '@/Components/dashboard/admin/ModalFormPersetujuanAktivitas.vue';
import ModalFormPendaftaranMahasiswa from '@/Components/dashboard/admin/ModalFormPendaftaranMahasiswa.vue';
import ModalFormHasilAktivitas from '@/Components/dashboard/admin/ModalFormHasilAktivitas.vue';

// Chart.js registration
import { Pie } from 'vue-chartjs';
import {
	Chart as ChartJS,
	ArcElement,
	Tooltip,
	Legend,
} from 'chart.js';
import ChartDataLabels from 'chartjs-plugin-datalabels';

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
	participantDistribution: {
		type: Array,
		default: () => [
			{ category: 'Professional', count: 510, color: '#7c72ff' },
			{ category: 'Bisnis', count: 270, color: '#ff8b85' },
			{ category: 'Birokrat', count: 120, color: '#38bdf8' },
			{ category: 'Akademisi', count: 100, color: '#fb923c' },
		],
	},
	pengajuanAktivitas: {
		type: Array,
		default: () => [],
	},
	pendaftaranAktivitas: {
		type: Array,
		default: () => [],
	},
	hasilAktivitas: {
		type: Array,
		default: () => [],
	},
});

const isLoading = ref(true);
const isDesktop = ref(false);

const updateScreenSize = () => {
	if (typeof window !== 'undefined') {
		isDesktop.value = window.innerWidth >= 1024;
	}
};

onMounted(() => {
	setTimeout(() => {
		isLoading.value = false;
	}, 250);
	updateScreenSize();
	window.addEventListener('resize', updateScreenSize, { passive: true });
});

onUnmounted(() => {
	if (typeof window !== 'undefined') {
		window.removeEventListener('resize', updateScreenSize);
	}
});

// Toast State
const toast = ref({
	show: false,
	type: 'success',
	title: '',
	message: '',
});

const showToast = (type, title, message) => {
	toast.value = { show: true, type, title, message };
	setTimeout(() => {
		toast.value.show = false;
	}, 3500);
};

// Flash messages from Laravel
const page = usePage();
watch(() => page.props.flash?.success, (msg) => {
	if (msg) showToast('success', 'Berhasil', msg);
}, { immediate: true });
watch(() => page.props.flash?.error, (msg) => {
	if (msg) showToast('error', 'Gagal', msg);
}, { immediate: true });

// ==============================
// 1. PARTICIPANT STATISTICS & SLIDER
// ==============================
const participantStats = computed(() => props.participantDistribution);
const totalParticipants = computed(() => {
	return participantStats.value.reduce((acc, curr) => acc + curr.count, 0);
});

const activeSlideIndex = ref(0);
const isDragging = ref(false);
const dragStartX = ref(0);
const dragStartY = ref(0);
const dragOffset = ref(0);
let isHorizontalDrag = null;

const startDrag = (clientX, clientY) => {
	if (isDesktop.value) return;
	isDragging.value = true;
	dragStartX.value = clientX;
	dragStartY.value = clientY;
	dragOffset.value = 0;
	isHorizontalDrag = null;
};

const moveDrag = (clientX, clientY, e) => {
	if (!isDragging.value || isDesktop.value) return;
	const deltaX = clientX - dragStartX.value;
	const deltaY = clientY - dragStartY.value;

	if (isHorizontalDrag === null && (Math.abs(deltaX) > 6 || Math.abs(deltaY) > 6)) {
		isHorizontalDrag = Math.abs(deltaX) > Math.abs(deltaY);
	}

	if (isHorizontalDrag === false) {
		isDragging.value = false;
		dragOffset.value = 0;
		return;
	}

	if (isHorizontalDrag === true) {
		if (e && e.cancelable && e.preventDefault) {
			e.preventDefault();
		}
		if ((activeSlideIndex.value === 0 && deltaX > 0) || (activeSlideIndex.value === 1 && deltaX < 0)) {
			dragOffset.value = deltaX * 0.28;
		} else {
			dragOffset.value = deltaX;
		}
	}
};

const endDrag = () => {
	if (!isDragging.value) return;
	isDragging.value = false;
	const threshold = 40;
	if (dragOffset.value < -threshold && activeSlideIndex.value < 1) {
		activeSlideIndex.value = 1;
	} else if (dragOffset.value > threshold && activeSlideIndex.value > 0) {
		activeSlideIndex.value = 0;
	}
	dragOffset.value = 0;
	isHorizontalDrag = null;
};

const handleTouchStart = (e) => {
	if (e.touches && e.touches.length === 1) {
		startDrag(e.touches[0].clientX, e.touches[0].clientY);
	}
};
const handleTouchMove = (e) => {
	if (e.touches && e.touches.length === 1) {
		moveDrag(e.touches[0].clientX, e.touches[0].clientY, e);
	}
};
const handleTouchEnd = () => {
	endDrag();
};

const handleMouseDown = (e) => {
	if (e.button !== 0) return;
	startDrag(e.clientX, e.clientY);
};
const handleMouseMove = (e) => {
	moveDrag(e.clientX, e.clientY, e);
};
const handleMouseUp = () => {
	endDrag();
};
const handleMouseLeave = () => {
	if (isDragging.value) {
		endDrag();
	}
};

const goToSlide = (index) => {
	activeSlideIndex.value = index;
	dragOffset.value = 0;
};

const mobileTrackStyle = computed(() => {
	if (isDesktop.value) return {};
	if (isDragging.value) {
		return { transform: `translateX(calc(-${activeSlideIndex.value * 100}% + ${dragOffset.value}px))` };
	}
	return { transform: `translateX(-${activeSlideIndex.value * 100}%)` };
});

const pieChartData = computed(() => ({
	labels: participantStats.value.map((s) => s.category),
	datasets: [
		{
			data: participantStats.value.map((s) => s.count),
			backgroundColor: participantStats.value.map((s) => s.color),
			borderColor: '#ffffff',
			borderWidth: 2,
			hoverOffset: 6,
		},
	],
}));

const pieChartOptions = {
	responsive: true,
	maintainAspectRatio: false,
	layout: { padding: 12 },
	animation: { duration: 1200, easing: 'easeOutQuart' },
	plugins: {
		legend: { display: false },
		tooltip: {
			backgroundColor: 'rgba(15, 23, 42, 0.95)',
			padding: { top: 8, bottom: 8, left: 12, right: 12 },
			cornerRadius: 8,
			titleFont: { family: 'Poppins', size: 13, weight: 'bold' },
			bodyFont: { family: 'Inter', size: 12 },
			callbacks: {
				label(context) {
					const value = context.parsed;
					const percentage = ((value / totalParticipants.value) * 100).toFixed(1);
					return ` ${context.label}: ${value} orang (${percentage}%)`;
				},
			},
		},
		datalabels: {
			color: '#ffffff',
			font: { family: 'Poppins', weight: 'bold', size: 11 },
			formatter(value) {
				const pct = ((value / totalParticipants.value) * 100).toFixed(0);
				return Number(pct) >= 6 ? `${pct}%` : '';
			},
		},
	},
};

// ==============================
// 2. HELPER FORMATTER & MEDIA
// ==============================
const formatDate = (dateStr) => {
	if (!dateStr) return '-';
	try {
		const options = { day: 'numeric', month: 'long', year: 'numeric' };
		return new Date(dateStr).toLocaleDateString('id-ID', options);
	} catch (e) {
		return dateStr;
	}
};

const getCategories = (kategori) => {
	if (!kategori) return [];
	if (Array.isArray(kategori)) return kategori;
	return String(kategori).split(',').map((s) => s.trim()).filter(Boolean);
};

const getImages = (item) => {
	if (!item) return [];
	const images = item.gambar_pengajuan_aktivitas || (item.gambar ? [item.gambar] : []);
	if (!Array.isArray(images)) return [images].filter(Boolean);
	return images.filter(Boolean);
};

const getBuktiPendaftaranList = (item) => {
	if (!item) return [];
	const bukti = item.bukti_pendaftaran || item.gambar || item.file;
	if (!bukti) return [];
	if (Array.isArray(bukti)) return bukti.filter(Boolean);
	return [bukti];
};

const getHasilBuktiList = (item) => {
	if (!item) return [];
	const bukti = item.bukti || item.gambar || item.media || item.file;
	if (!bukti) return [];
	if (Array.isArray(bukti)) return bukti.filter(Boolean);
	return [bukti];
};

const getImageSrc = (val) => {
	if (!val) return `https://picsum.photos/seed/talenta/800/600`;
	if (typeof val === 'object' && val.gambar) val = val.gambar;
	if (typeof val === 'object' && val.url) return val.url;
	if (typeof val === 'object' && val.file) return URL.createObjectURL(val.file);
	if (typeof val === 'string') {
		if (val.startsWith('http://') || val.startsWith('https://') || val.startsWith('blob:') || val.startsWith('data:')) return val;
		if (val.startsWith('/storage/')) return val;
		if (val.startsWith('storage/')) return '/' + val;
		if (val.startsWith('/')) return val;
		return '/storage/' + val;
	}
	return `https://picsum.photos/seed/talenta/800/600`;
};

const showImageModal = ref(false);
const selectedImage = ref('');
const activeImageList = ref([]);
const activeImageIndex = ref(0);
const activeImageType = ref('Gambar');

const openImage = (target, type = 'Gambar', index = 0) => {
	if (!target) return;
	activeImageType.value = type === 'BuktiRegistrasi' || type === 'Hasil' ? 'Bukti' : 'Gambar';
	let list = [];
	if (type === 'Gambar') {
		list = getImages(target);
	} else if (type === 'BuktiRegistrasi') {
		list = getBuktiPendaftaranList(target);
	} else if (type === 'Hasil') {
		list = getHasilBuktiList(target);
	} else {
		list = getImages(target);
	}

	if (list.length > 0) {
		activeImageList.value = list;
		activeImageIndex.value = index >= 0 && index < list.length ? index : 0;
		selectedImage.value = getImageSrc(list[activeImageIndex.value]);
		showImageModal.value = true;
		return;
	}
	selectedImage.value = getImageSrc(target);
	activeImageList.value = [target];
	activeImageIndex.value = 0;
	showImageModal.value = true;
};

const prevImage = () => {
	if (activeImageList.value.length <= 1) return;
	activeImageIndex.value = (activeImageIndex.value - 1 + activeImageList.value.length) % activeImageList.value.length;
	selectedImage.value = getImageSrc(activeImageList.value[activeImageIndex.value]);
};

const nextImage = () => {
	if (activeImageList.value.length <= 1) return;
	activeImageIndex.value = (activeImageIndex.value + 1) % activeImageList.value.length;
	selectedImage.value = getImageSrc(activeImageList.value[activeImageIndex.value]);
};

// Touch swipe support on modal (mobile)
const modalTouchStartX = ref(0);
const modalTouchEndX = ref(0);

const handleModalTouchStart = (e) => {
	if (!e.changedTouches || e.changedTouches.length === 0) return;
	modalTouchStartX.value = e.changedTouches[0].clientX;
};

const handleModalTouchEnd = (e) => {
	if (!e.changedTouches || e.changedTouches.length === 0) return;
	modalTouchEndX.value = e.changedTouches[0].clientX;
	const diff = modalTouchEndX.value - modalTouchStartX.value;
	if (Math.abs(diff) > 40 && activeImageList.value.length > 1) {
		if (diff > 0) {
			prevImage();
		} else {
			nextImage();
		}
	}
};

const onImageError = (e) => {
	const seed = String(selectedImage.value || 'talenta').replace(/[^a-zA-Z0-9]/g, '');
	e.target.src = `https://picsum.photos/seed/${seed || 'talenta'}/800/600`;
};

// Hover state for navigation arrows & indicator on desktop
const isHoveringNav = ref(false);

// Keyboard navigation (Esc to close, Arrow keys to slide)
const handleModalKeyDown = (e) => {
	if (!showImageModal.value) return;
	if (e.key === 'Escape') {
		showImageModal.value = false;
	} else if (e.key === 'ArrowLeft') {
		prevImage();
	} else if (e.key === 'ArrowRight') {
		nextImage();
	}
};

watch(showImageModal, (isOpen) => {
	if (isOpen) {
		window.addEventListener('keydown', handleModalKeyDown);
	} else {
		window.removeEventListener('keydown', handleModalKeyDown);
		isHoveringNav.value = false;
	}
});

onUnmounted(() => {
	window.removeEventListener('keydown', handleModalKeyDown);
});

// ==============================
// ==============================
// 3. SORTING & LOADING STATE UNTUK 3 TABEL (5 TERATAS)
// ==============================
const isLoadingTable1 = ref(false);
const isLoadingTable2 = ref(false);
const isLoadingTable3 = ref(false);

const isTable1Loading = computed(() => isLoadingTable1.value || (isActionLoading.value && approveType.value === 'pengajuan') || (isActionLoading.value && rejectType.value === 'pengajuan'));
const isTable2Loading = computed(() => isLoadingTable2.value || (isActionLoading.value && approveType.value === 'pendaftaran') || (isActionLoading.value && rejectType.value === 'pendaftaran'));
const isTable3Loading = computed(() => isLoadingTable3.value || (isActionLoading.value && approveType.value === 'hasil') || (isActionLoading.value && rejectType.value === 'hasil'));

// Table Columns Definitions
const columnsPengajuan = [
	{ key: 'judul', label: 'Judul', sortable: true, width: 'w-[28%] min-w-[220px]' },
	{ key: 'kategori', label: 'Kategori', sortable: false, width: 'w-[24%] min-w-[210px]' },
	{ key: 'deadline', label: 'Deadline', sortable: true, width: 'w-[16%] min-w-[140px]' },
	{ key: 'gambar', label: 'Gambar', sortable: false, width: 'w-[14%] min-w-[120px]' },
	{ key: 'status', label: 'Status', sortable: false, width: 'w-[18%] min-w-[130px]' },
	{ key: 'action', label: 'Aksi', sortable: false, width: 'w-[170px] min-w-[170px] max-w-[170px]' },
];

const columnsPendaftaran = [
	{ key: 'nama_tim', label: 'Nama Tim', sortable: true, width: 'w-[24%] min-w-[180px]' },
	{ key: 'peserta', label: 'Nama Peserta', sortable: true, width: 'w-[36%] min-w-[240px]' },
	{ key: 'bukti', label: 'Bukti Registrasi', sortable: false, width: 'w-[21%] min-w-[160px]' },
	{ key: 'status', label: 'Status', sortable: false, width: 'w-[19%] min-w-[130px]' },
	{ key: 'action', label: 'Aksi', sortable: false, width: 'w-[170px] min-w-[170px] max-w-[170px]' },
];

const columnsHasil = [
	{ key: 'nama_mahasiswa', label: 'Nama Mahasiswa', sortable: true, width: 'w-[21%] min-w-[170px]' },
	{ key: 'nim', label: 'NIM', sortable: true, width: 'w-[13%] min-w-[110px]' },
	{ key: 'nama_kegiatan', label: 'Nama Kegiatan', sortable: true, width: 'w-[25%] min-w-[200px]' },
	{ key: 'jenis_roadmap', label: 'Jenis Roadmap', sortable: false, width: 'w-[16%] min-w-[140px]' },
	{ key: 'bukti', label: 'Bukti', sortable: false, width: 'w-[12%] min-w-[110px]' },
	{ key: 'status', label: 'Status', sortable: false, width: 'w-[13%] min-w-[120px]' },
	{ key: 'action', label: 'Aksi', sortable: false, width: 'w-[170px] min-w-[170px] max-w-[170px]' },
];

// Tabel 1: Pengajuan Aktivitas
const sortKey1 = ref('id');
const sortDirection1 = ref('asc');
const toggleSort1 = (key) => {
	if (isTable1Loading.value) return;
	isLoadingTable1.value = true;
	if (sortKey1.value === key) {
		sortDirection1.value = sortDirection1.value === 'asc' ? 'desc' : 'asc';
	} else {
		sortKey1.value = key;
		sortDirection1.value = 'asc';
	}
	setTimeout(() => {
		isLoadingTable1.value = false;
	}, 200);
};
const sortedPengajuan = computed(() => {
	const data = [...(props.pengajuanAktivitas || [])];
	if (!sortKey1.value) return data.slice(0, 5);
	return data.sort((a, b) => {
		let valA = a[sortKey1.value];
		let valB = b[sortKey1.value];
		if (sortKey1.value === 'id') {
			valA = Number(valA) || 0;
			valB = Number(valB) || 0;
		} else if (sortKey1.value === 'deadline') {
			valA = valA ? new Date(valA).getTime() : 0;
			valB = valB ? new Date(valB).getTime() : 0;
		} else if (sortKey1.value === 'kategori') {
			valA = Array.isArray(a.kategori) ? a.kategori.join(', ') : (a.kategori || '');
			valB = Array.isArray(b.kategori) ? b.kategori.join(', ') : (b.kategori || '');
		}
		if (typeof valA === 'string') valA = valA.toLowerCase();
		if (typeof valB === 'string') valB = valB.toLowerCase();
		if (valA < valB) return sortDirection1.value === 'asc' ? -1 : 1;
		if (valA > valB) return sortDirection1.value === 'asc' ? 1 : -1;
		return 0;
	}).slice(0, 5);
});

// Tabel 2: Pendaftaran Aktivitas
const sortKey2 = ref('id');
const sortDirection2 = ref('asc');
const toggleSort2 = (key) => {
	if (isTable2Loading.value) return;
	isLoadingTable2.value = true;
	if (sortKey2.value === key) {
		sortDirection2.value = sortDirection2.value === 'asc' ? 'desc' : 'asc';
	} else {
		sortKey2.value = key;
		sortDirection2.value = 'asc';
	}
	setTimeout(() => {
		isLoadingTable2.value = false;
	}, 200);
};
const sortedPendaftaran = computed(() => {
	const data = [...(props.pendaftaranAktivitas || [])];
	if (!sortKey2.value) return data.slice(0, 5);
	return data.sort((a, b) => {
		let valA = a[sortKey2.value];
		let valB = b[sortKey2.value];
		if (sortKey2.value === 'id') {
			valA = Number(valA) || 0;
			valB = Number(valB) || 0;
		} else if (sortKey2.value === 'peserta') {
			valA = Array.isArray(a.peserta) ? a.peserta.map((p) => p.nama).join(', ') : (a.namaPeserta || '');
			valB = Array.isArray(b.peserta) ? b.peserta.map((p) => p.nama).join(', ') : (b.namaPeserta || '');
		}
		if (typeof valA === 'string') valA = valA.toLowerCase();
		if (typeof valB === 'string') valB = valB.toLowerCase();
		if (valA < valB) return sortDirection2.value === 'asc' ? -1 : 1;
		if (valA > valB) return sortDirection2.value === 'asc' ? 1 : -1;
		return 0;
	}).slice(0, 5);
});

// Tabel 3: Hasil Aktivitas
const sortKey3 = ref('id');
const sortDirection3 = ref('asc');
const toggleSort3 = (key) => {
	if (isTable3Loading.value) return;
	isLoadingTable3.value = true;
	if (sortKey3.value === key) {
		sortDirection3.value = sortDirection3.value === 'asc' ? 'desc' : 'asc';
	} else {
		sortKey3.value = key;
		sortDirection3.value = 'asc';
	}
	setTimeout(() => {
		isLoadingTable3.value = false;
	}, 200);
};
const sortedHasil = computed(() => {
	const data = [...(props.hasilAktivitas || [])];
	if (!sortKey3.value) return data.slice(0, 5);
	return data.sort((a, b) => {
		let valA = a[sortKey3.value];
		let valB = b[sortKey3.value];
		if (sortKey3.value === 'id') {
			valA = Number(valA) || 0;
			valB = Number(valB) || 0;
		} else if (sortKey3.value === 'nama_mahasiswa') {
			valA = a.user?.nama || a.nama_mahasiswa || '';
			valB = b.user?.nama || b.nama_mahasiswa || '';
		} else if (sortKey3.value === 'nim') {
			valA = a.user?.nim || a.nim || '';
			valB = b.user?.nim || b.nim || '';
		} else if (sortKey3.value === 'nama_kegiatan') {
			valA = a.pendaftaran_aktivitas?.activity?.judul || a.materi_roadmap?.judul || a.nama_kegiatan || '';
			valB = b.pendaftaran_aktivitas?.activity?.judul || b.materi_roadmap?.judul || b.nama_kegiatan || '';
		} else if (sortKey3.value === 'jenis_roadmap') {
			valA = a.materi_roadmap?.bulan_roadmap?.roadmap?.judul || a.jenis_roadmap || '';
			valB = b.materi_roadmap?.bulan_roadmap?.roadmap?.judul || b.jenis_roadmap || '';
		}
		if (typeof valA === 'string') valA = valA.toLowerCase();
		if (typeof valB === 'string') valB = valB.toLowerCase();
		if (valA < valB) return sortDirection3.value === 'asc' ? -1 : 1;
		if (valA > valB) return sortDirection3.value === 'asc' ? 1 : -1;
		return 0;
	}).slice(0, 5);
});

// ==============================
// 4. MODALS & ACTIONS LOGIC
// ==============================
const isActionLoading = ref(false);

// Approve Modal
const showConfirmApproveModal = ref(false);
const itemToApprove = ref(null);
const approveType = ref(''); // 'pengajuan' | 'pendaftaran' | 'hasil'

// Reject Modal
const showRejectModal = ref(false);
const itemToReject = ref(null);
const rejectType = ref(''); // 'pengajuan' | 'pendaftaran' | 'hasil'

// Detail Form Modals
const selectedPengajuan = ref(null);
const showDetailPengajuan = ref(false);

const selectedPendaftaran = ref(null);
const showDetailPendaftaran = ref(false);

const selectedHasil = ref(null);
const showDetailHasil = ref(false);

// Handlers for Table 1 (Pengajuan)
const openDetailPengajuan = (item) => {
	selectedPengajuan.value = item;
	showDetailPengajuan.value = true;
};
const promptApprovePengajuan = (item) => {
	const target = item || selectedPengajuan.value;
	if (!target) return;
	if (String(target.status).toLowerCase() === 'disetujui') {
		showToast('error', 'Peringatan', 'Aktivitas ini sudah disetujui sebelumnya.');
		return;
	}
	itemToApprove.value = target;
	approveType.value = 'pengajuan';
	showConfirmApproveModal.value = true;
};
const openRejectPengajuan = (item) => {
	itemToReject.value = item || selectedPengajuan.value;
	rejectType.value = 'pengajuan';
	showRejectModal.value = true;
};

// Handlers for Table 2 (Pendaftaran)
const openDetailPendaftaran = (item) => {
	selectedPendaftaran.value = item;
	showDetailPendaftaran.value = true;
};
const promptApprovePendaftaran = (item) => {
	const target = item || selectedPendaftaran.value;
	if (!target) return;
	if (String(target.status).toLowerCase() === 'disetujui') {
		showToast('error', 'Peringatan', 'Pendaftaran ini sudah disetujui sebelumnya.');
		return;
	}
	itemToApprove.value = target;
	approveType.value = 'pendaftaran';
	showConfirmApproveModal.value = true;
};
const openRejectPendaftaran = (item) => {
	itemToReject.value = item || selectedPendaftaran.value;
	rejectType.value = 'pendaftaran';
	showRejectModal.value = true;
};

// Handlers for Table 3 (Hasil)
const openDetailHasil = (item) => {
	selectedHasil.value = item;
	showDetailHasil.value = true;
};
const promptApproveHasil = (item) => {
	const target = item || selectedHasil.value;
	if (!target) return;
	if (String(target.status).toLowerCase() === 'disetujui') {
		showToast('error', 'Peringatan', 'Hasil aktivitas ini sudah disetujui sebelumnya.');
		return;
	}
	itemToApprove.value = target;
	approveType.value = 'hasil';
	showConfirmApproveModal.value = true;
};
const openRejectHasil = (item) => {
	itemToReject.value = item || selectedHasil.value;
	rejectType.value = 'hasil';
	showRejectModal.value = true;
};

// Computed for ApproveModal text
const approveModalTitle = computed(() => {
	if (approveType.value === 'pengajuan') return 'Setujui Pengajuan Aktivitas?';
	if (approveType.value === 'pendaftaran') return 'Setujui Pendaftaran Aktivitas?';
	return 'Setujui Hasil Aktivitas?';
});

const approveModalMessage = computed(() => {
	if (!itemToApprove.value) return '';
	if (approveType.value === 'pengajuan') {
		return `Apakah Anda yakin ingin menyetujui pengajuan "${itemToApprove.value.judul}"?`;
	}
	if (approveType.value === 'pendaftaran') {
		return `Apakah Anda yakin ingin menyetujui pendaftaran dari tim "${itemToApprove.value.nama_tim || itemToApprove.value.namaTim || 'Tim'}"?`;
	}
	return `Apakah Anda yakin ingin menyetujui hasil kegiatan mahasiswa "${itemToApprove.value.nama_mahasiswa || itemToApprove.value.namaMahasiswa}" untuk kegiatan "${itemToApprove.value.nama_kegiatan || itemToApprove.value.namaAktivitas}"?`;
});

const approveModalInfo = computed(() => {
	if (approveType.value === 'pengajuan') {
		return 'Aktivitas ini akan langsung otomatis dibuat dan <strong>diterbitkan ke List Aktivitas</strong>.';
	}
	if (approveType.value === 'pendaftaran') {
		return 'Tim mahasiswa ini akan berstatus <strong>Disetujui</strong> untuk mengikuti aktivitas.';
	}
	return 'Status hasil aktivitas mahasiswa ini akan diperbarui menjadi <strong>Disetujui</strong>.';
});

// Execute Approval
const executeApprove = () => {
	if (!itemToApprove.value) return;
	const target = itemToApprove.value;
	const type = approveType.value;

	showConfirmApproveModal.value = false;
	showDetailPengajuan.value = false;
	showDetailPendaftaran.value = false;
	showDetailHasil.value = false;
	isActionLoading.value = true;

	if (type === 'pengajuan') {
		router.post(`/admin/aktivitas/persetujuan/${target.id}/approve`, {}, {
			preserveScroll: true,
			onSuccess: () => {
				itemToApprove.value = null;
			},
			onError: () => {
				showToast('error', 'Gagal', 'Gagal menyetujui aktivitas.');
			},
			onFinish: () => {
				isActionLoading.value = false;
			},
		});
	} else if (type === 'pendaftaran') {
		router.patch(`/admin/aktivitas/bukti-pendaftaran/${target.id}/approve`, {}, {
			preserveScroll: true,
			onSuccess: () => {
				itemToApprove.value = null;
			},
			onError: () => {
				showToast('error', 'Gagal', 'Gagal menyetujui pendaftaran aktivitas.');
			},
			onFinish: () => {
				isActionLoading.value = false;
			},
		});
	} else if (type === 'hasil') {
		router.post(`/admin/aktivitas/hasil/${target.id}/approve`, {}, {
			preserveScroll: true,
			onSuccess: () => {
				itemToApprove.value = null;
			},
			onError: () => {
				showToast('error', 'Gagal', 'Gagal menyetujui hasil aktivitas.');
			},
			onFinish: () => {
				isActionLoading.value = false;
			},
		});
	}
};

// Execute Reject
const handleReject = (reason) => {
	if (!itemToReject.value) return;
	const target = itemToReject.value;
	const type = rejectType.value;

	showRejectModal.value = false;
	showDetailPengajuan.value = false;
	showDetailPendaftaran.value = false;
	showDetailHasil.value = false;
	isActionLoading.value = true;

	if (type === 'pengajuan') {
		router.post(`/admin/aktivitas/persetujuan/${target.id}/reject`, {
			catatan: reason || '',
		}, {
			preserveScroll: true,
			onError: () => {
				showToast('error', 'Gagal', 'Gagal menolak aktivitas.');
			},
			onFinish: () => {
				isActionLoading.value = false;
			},
		});
	} else if (type === 'pendaftaran') {
		router.patch(`/admin/aktivitas/bukti-pendaftaran/${target.id}/reject`, {
			catatan_penolakan: reason || '',
		}, {
			preserveScroll: true,
			onError: () => {
				showToast('error', 'Gagal', 'Gagal menolak pendaftaran aktivitas.');
			},
			onFinish: () => {
				isActionLoading.value = false;
			},
		});
	} else if (type === 'hasil') {
		router.post(`/admin/aktivitas/hasil/${target.id}/reject`, {
			catatan: reason || '',
		}, {
			preserveScroll: true,
			onError: () => {
				showToast('error', 'Gagal', 'Gagal menolak hasil aktivitas.');
			},
			onFinish: () => {
				isActionLoading.value = false;
			},
		});
	}
};

// ==============================
// 5. DELETE MODAL & ACTIONS
// ==============================
const showDeleteModal = ref(false);
const itemToDelete = ref(null);
const deleteType = ref('pengajuan'); // 'pengajuan' | 'pendaftaran' | 'hasil'

const confirmDeletePengajuan = (item) => {
	itemToDelete.value = item;
	deleteType.value = 'pengajuan';
	showDeleteModal.value = true;
};

const confirmDeletePendaftaran = (item) => {
	itemToDelete.value = item;
	deleteType.value = 'pendaftaran';
	showDeleteModal.value = true;
};

const confirmDeleteHasil = (item) => {
	itemToDelete.value = item;
	deleteType.value = 'hasil';
	showDeleteModal.value = true;
};

const executeDelete = () => {
	if (!itemToDelete.value) return;
	const target = itemToDelete.value;
	const type = deleteType.value;
	isActionLoading.value = true;

	let url = '';
	if (type === 'pengajuan') {
		url = `/admin/aktivitas/persetujuan/${target.id}`;
	} else if (type === 'pendaftaran') {
		url = `/admin/aktivitas/bukti-pendaftaran/${target.id}`;
	} else if (type === 'hasil') {
		url = `/admin/aktivitas/hasil/${target.id}`;
	}

	router.delete(url, {
		preserveScroll: true,
		onSuccess: () => {
			showDeleteModal.value = false;
			itemToDelete.value = null;
		},
		onError: () => {
			showToast('error', 'Gagal', 'Terjadi kesalahan saat menghapus data.');
		},
		onFinish: () => {
			isActionLoading.value = false;
		},
	});
};
</script>

<template>
	<Head title="Dashboard Admin" />

	<AdminLayout>
		<div class="mx-auto w-full max-w-[1520px] px-4 py-6 font-poppins sm:px-6 sm:py-8 lg:px-8 space-y-10 sm:space-y-12 lg:space-y-14 pb-16">
			<!-- Header Section -->
			<div class="space-y-1">
				<h1 class="text-[28px] sm:text-[34px] lg:text-[40px] font-extrabold leading-tight text-[#17334F] tracking-tight">
					Selamat Datang Admin!
				</h1>
				<p class="font-inter text-[13px] sm:text-[14px] font-medium text-[#64748b]">
					Lihat seberapa banyak mahasiswa yang mengikuti aktivitas Talenta!
				</p>
			</div>

			<!-- Distribution Cards Section -->
			<div class="w-full">
				<div class="w-full overflow-hidden lg:overflow-visible select-none lg:select-auto p-1 lg:p-0">
					<div
						class="flex lg:grid lg:grid-cols-2 lg:gap-6 will-change-transform lg:!transform-none lg:!cursor-default"
						:class="isDragging ? 'transition-none cursor-grabbing' : 'transition-transform duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] cursor-grab'"
						:style="mobileTrackStyle"
						@touchstart="handleTouchStart"
						@touchmove="handleTouchMove"
						@touchend="handleTouchEnd"
						@mousedown="handleMouseDown"
						@mousemove="handleMouseMove"
						@mouseup="handleMouseUp"
						@mouseleave="handleMouseLeave"
					>
						<!-- Left Card Slide: Pie Chart -->
						<div class="w-full shrink-0 lg:shrink px-1 lg:px-0">
							<div class="w-full rounded-2xl bg-white p-5 sm:p-6 shadow-xs border border-[#e2e8f0] flex flex-col items-center justify-between min-h-[380px]">
								<h2 class="text-center font-poppins text-[14px] sm:text-[16px] font-bold text-[#17334F] leading-snug px-2">
									Distribusi Peserta Berdasarkan Kategori Talenta
								</h2>

								<div class="relative w-full max-w-[240px] sm:max-w-[260px] h-[210px] sm:h-[250px] my-2 flex items-center justify-center">
									<div v-if="isLoading" class="h-44 w-44 rounded-full bg-slate-100 animate-pulse"></div>
									<Pie
										v-else
										:data="pieChartData"
										:options="pieChartOptions"
										:plugins="[ChartDataLabels]"
										aria-label="Grafik Distribusi Peserta"
									/>
								</div>

								<!-- Legend Badges Below Chart -->
								<div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 text-xs font-semibold text-[#475569] pt-2">
									<div v-for="item in participantStats" :key="item.category" class="flex items-center gap-1.5">
										<span class="h-3 w-3 rounded-full shrink-0" :style="{ backgroundColor: item.color }"></span>
										<span>{{ item.category }}</span>
									</div>
								</div>
							</div>
						</div>

						<!-- Right Card Slide: Real Numbers Table -->
						<div class="w-full shrink-0 lg:shrink px-1 lg:px-0">
							<div class="w-full rounded-2xl bg-white p-5 sm:p-6 shadow-xs border border-[#e2e8f0] flex flex-col justify-between min-h-[380px]">
								<h2 class="text-center font-poppins text-[14px] sm:text-[16px] font-bold text-[#17334F] leading-snug px-2">
									Distribusi Peserta Berdasarkan Kategori Talenta<br />
									<span class="text-[12px] sm:text-[13px] font-medium text-[#64748b]">(Dalam Angka riil)</span>
								</h2>

								<div class="mt-4 flex-1 flex flex-col justify-center px-2 sm:px-10">
									<div class="flex items-center justify-between py-2.5 border-b-2 border-[#17334F]/20 font-bold text-sm sm:text-[15px] text-[#17334F]">
										<span>Kategori</span>
										<span>Jumlah</span>
									</div>
									<div class="divide-y divide-[#f1f5f9] text-sm sm:text-[14px] text-[#334155] font-medium">
										<div v-for="item in participantStats" :key="item.category" class="flex items-center justify-between py-2.5 sm:py-3">
											<span>{{ item.category }}</span>
											<span class="font-semibold text-[#17334F]">{{ item.count }}</span>
										</div>
									</div>
									<div class="flex items-center justify-between py-3.5 border-t-2 border-[#17334F]/20 font-bold text-sm sm:text-[15px] text-[#17334F]">
										<span>Total</span>
										<span>{{ totalParticipants }}</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Dots Pagination Indicator (Mobile only) -->
				<div class="flex items-center justify-center gap-2 lg:hidden mt-4">
					<button
						type="button"
						@click="goToSlide(0)"
						class="h-2 rounded-full transition-all duration-300 cursor-pointer"
						:class="activeSlideIndex === 0 ? 'w-6 bg-[#416f65]' : 'w-2 bg-slate-300 hover:bg-slate-400'"
						aria-label="Slide 1: Grafik"
					></button>
					<button
						type="button"
						@click="goToSlide(1)"
						class="h-2 rounded-full transition-all duration-300 cursor-pointer"
						:class="activeSlideIndex === 1 ? 'w-6 bg-[#416f65]' : 'w-2 bg-slate-300 hover:bg-slate-400'"
						aria-label="Slide 2: Angka Riil"
					></button>
				</div>
			</div>

			<!-- ========================================== -->
			<!-- TABEL 1: PERSETUJUAN AKTIVITAS (5 TERATAS) -->
			<!-- ========================================== -->
			<div>
				<div class="mb-4 flex items-center justify-between gap-3">
					<h2 class="text-[16px] sm:text-[18px] md:text-[20px] font-bold leading-tight sm:leading-none text-[#17334F]">
						Persetujuan Aktivitas
					</h2>
					<Link
						href="/admin/aktivitas/persetujuan"
						class="font-inter text-xs sm:text-sm font-semibold text-[#17334F] hover:text-[#416f65] flex items-center gap-1 transition-colors shrink-0"
					>
						<span>Selengkapnya</span>
						<svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
							<path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
						</svg>
					</Link>
				</div>

				<div :class="['overflow-x-auto lg:overflow-visible rounded-[12px] bg-white shadow-sm ring-1 ring-[#d6e0ee] transition-all', isTable1Loading ? 'cursor-wait' : '']">
					<table class="w-full min-w-[1040px] table-fixed border-separate border-spacing-0 text-sm">
						<thead class="bg-[#416f65]">
							<tr class="h-[48px]">
								<!-- Kolom No -->
								<th class="w-[60px] min-w-[60px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0 rounded-tl-[12px]">
									<button type="button" :disabled="isTable1Loading" @click="toggleSort1('id')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer disabled:cursor-wait disabled:opacity-85" title="Urutkan No">
										<span>No</span>
										<span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
											<svg v-if="sortKey1 === 'id'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection1 === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
											<svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
										</span>
									</button>
								</th>

								<th
									v-for="col in columnsPengajuan"
									:key="col.key"
									:class="[
										col.width,
										'px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 last:border-r-0 lg:border-r-0 last:rounded-tr-[12px]'
									]"
								>
									<button
										v-if="col.sortable"
										type="button"
										:disabled="isTable1Loading"
										@click="toggleSort1(col.key)"
										class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer disabled:cursor-wait disabled:opacity-85"
										:title="`Urutkan ${col.label}`"
									>
										<span>{{ col.label }}</span>
										<span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
											<svg
												v-if="sortKey1 === col.key"
												:class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection1 === 'desc' ? 'rotate-180' : '']"
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
						<tbody :class="['[&_tr:not(:first-child)_td]:border-t [&_tr:not(:first-child)_td]:border-[#d6e0ee] font-inter text-[14px] text-[#435b76] transition-all duration-200', isTable1Loading ? 'opacity-40 blur-[1px] pointer-events-none select-none cursor-wait' : '']">
							<tr
								v-for="(item, idx) in sortedPengajuan"
								:key="`pengajuan-${item.id}-${idx}`"
								class="h-[52px] hover:bg-[#f7f9fd] transition-colors"
							>
								<td class="px-3 py-2.5 text-center font-medium">{{ (sortKey1 === 'id' && sortDirection1 === 'desc') ? sortedPengajuan.length - idx : idx + 1 }}</td>
								<td class="px-3 py-2.5 text-left font-medium text-[#233547] truncate" :title="item.judul">{{ item.judul }}</td>
								<td class="px-3 py-2.5 text-left align-middle relative">
									<div class="flex items-center gap-1.5 flex-nowrap w-full" v-if="getCategories(item.kategori).length > 0">
										<!-- Render first 2 chips -->
										<template v-for="(cat, catIdx) in getCategories(item.kategori).slice(0, 2)" :key="catIdx">
											<span :class="[
												'shrink-0 inline-flex items-center justify-center rounded-full px-2.5 py-0.5 font-inter text-[11px] font-semibold border',
												cat === 'Profesional' || cat === 'Professional' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' :
												cat === 'Bisnis' ? 'bg-amber-50 text-amber-700 border-amber-200' :
												cat === 'Birokrat' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
												cat === 'Akademisi' ? 'bg-purple-50 text-purple-700 border-purple-200' :
												'bg-slate-100 text-slate-700 border-slate-200'
											]">
												{{ cat }}
											</span>
										</template>
										
										<!-- +X Chip for remainder -->
										<div v-if="getCategories(item.kategori).length > 2" class="group relative flex shrink-0">
											<button type="button" class="inline-flex h-5 items-center justify-center rounded-full bg-slate-100 px-1.5 border border-slate-200 text-[10px] font-bold text-slate-600 transition hover:bg-slate-200 focus:outline-none focus:bg-slate-200 cursor-pointer">
												+{{ getCategories(item.kategori).length - 2 }}
											</button>
											<div class="hidden group-hover:block group-focus-within:block absolute left-0 top-full z-[60] mt-1.5 shadow-lg rounded-lg border border-gray-200 bg-white p-2 animate-in fade-in slide-in-from-top-1 duration-200">
												<div class="flex flex-col gap-1.5 min-w-fit whitespace-nowrap">
													<span v-for="(cat, catIdx) in getCategories(item.kategori).slice(2)" :key="catIdx" :class="[
														'inline-flex items-center justify-center rounded-full px-2.5 py-0.5 font-inter text-[11px] font-semibold border',
														cat === 'Profesional' || cat === 'Professional' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' :
														cat === 'Bisnis' ? 'bg-amber-50 text-amber-700 border-amber-200' :
														cat === 'Birokrat' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
														cat === 'Akademisi' ? 'bg-purple-50 text-purple-700 border-purple-200' :
														'bg-slate-100 text-slate-700 border-slate-200'
													]">
														{{ cat }}
													</span>
												</div>
											</div>
										</div>
									</div>
								</td>
								<td class="whitespace-nowrap px-3 py-2.5 text-center">{{ formatDate(item.deadline) }}</td>
								<td class="px-3 py-2.5 text-center">
									<span v-if="getImages(item).length === 0" class="text-gray-400 text-xs italic">-</span>
									<button
										v-else
										type="button"
										class="font-inter text-[14px] font-medium text-[#3b82f6] hover:text-blue-700 hover:underline transition-colors cursor-pointer inline-flex items-center justify-center gap-1 align-middle whitespace-nowrap"
										:title="getImages(item).length > 1 ? `Lihat ${getImages(item).length} Gambar` : 'Lihat Gambar'"
										@click="openImage(item, 'Gambar')"
									>
										<span>Gambar</span>
										<span v-if="getImages(item).length > 1" class="text-xs font-semibold text-[#3b82f6]">({{ getImages(item).length }})</span>
									</button>
								</td>
								<td class="px-3 py-2.5 text-center">
									<div class="flex flex-col items-center justify-center gap-1">
										<div class="mx-auto flex w-fit items-center justify-center gap-1.5 rounded-full px-3 py-1 font-inter text-[11px] font-semibold border"
											:class="{
												'bg-gray-100 text-gray-600 border-gray-200': String(item.status).toLowerCase() === 'menunggu',
												'bg-green-50 text-green-600 border-green-200': String(item.status).toLowerCase() === 'disetujui',
												'bg-red-50 text-red-600 border-red-200': String(item.status).toLowerCase() === 'ditolak'
											}">
											<svg v-if="String(item.status).toLowerCase() === 'menunggu'" class="h-3.5 w-3.5 shrink-0 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" /><polyline points="12 7 12 12 15 15" /></svg>
											<svg v-if="String(item.status).toLowerCase() === 'disetujui'" class="h-3.5 w-3.5 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
											<svg v-if="String(item.status).toLowerCase() === 'ditolak'" class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
											<span class="capitalize">{{ item.status }}</span>
										</div>
										<span
											v-if="item.catatan_penolakan && String(item.status).toLowerCase() === 'menunggu'"
											class="inline-flex items-center gap-1 rounded-full border border-amber-300 bg-amber-50 px-2 py-0.5 font-inter text-[10px] font-semibold text-amber-700 whitespace-nowrap"
											:title="`Catatan penolakan sebelumnya: ${item.catatan_penolakan}`"
										>
											<svg class="h-2.5 w-2.5 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
											Pernah Ditolak
										</span>
									</div>
								</td>
								<td class="w-[170px] min-w-[170px] max-w-[170px] px-2 py-2.5 text-center">
									<div class="flex items-center justify-center gap-1.5 sm:gap-2">
										<AcceptButton
											:disabled="String(item.status).toLowerCase() === 'disetujui'"
											@click="promptApprovePengajuan(item)"
										/>
										<RejectButton
											:disabled="String(item.status).toLowerCase() === 'ditolak' || String(item.status).toLowerCase() === 'disetujui'"
											@click="openRejectPengajuan(item)"
										/>
										<PreviewButtonTable @click="openDetailPengajuan(item)" />
										<DeleteButtonTable @click="confirmDeletePengajuan(item)" />
									</div>
								</td>
							</tr>
							<tr v-if="sortedPengajuan.length === 0">
								<td colspan="7" class="py-8 text-center text-[#64748b]">Belum ada data pengajuan aktivitas.</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<!-- ========================================== -->
			<!-- TABEL 2: PENDAFTARAN AKTIVITAS (5 TERATAS) -->
			<!-- ========================================== -->
			<div>
				<div class="mb-4 flex items-center justify-between gap-3">
					<h2 class="text-[16px] sm:text-[18px] md:text-[20px] font-bold leading-tight sm:leading-none text-[#17334F]">
						Pendaftaran Aktivitas Mahasiswa
					</h2>
					<Link
						href="/admin/aktivitas/bukti-pendaftaran"
						class="font-inter text-xs sm:text-sm font-semibold text-[#17334F] hover:text-[#416f65] flex items-center gap-1 transition-colors shrink-0"
					>
						<span>Selengkapnya</span>
						<svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
							<path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
						</svg>
					</Link>
				</div>

				<div :class="['overflow-x-auto lg:overflow-visible rounded-[12px] bg-white shadow-sm ring-1 ring-[#d6e0ee] transition-all', isTable2Loading ? 'cursor-wait' : '']">
					<table class="w-full min-w-[1040px] table-fixed border-separate border-spacing-0 text-sm">
						<thead class="bg-[#416f65]">
							<tr class="h-[48px]">
								<!-- Kolom No -->
								<th class="w-[60px] min-w-[60px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0 rounded-tl-[12px]">
									<button type="button" :disabled="isTable2Loading" @click="toggleSort2('id')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer disabled:cursor-wait disabled:opacity-85" title="Urutkan No">
										<span>No</span>
										<span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
											<svg v-if="sortKey2 === 'id'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection2 === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
											<svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
										</span>
									</button>
								</th>

								<th
									v-for="col in columnsPendaftaran"
									:key="col.key"
									:class="[
										col.width,
										'px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 last:border-r-0 lg:border-r-0 last:rounded-tr-[12px]'
									]"
								>
									<button
										v-if="col.sortable"
										type="button"
										:disabled="isTable2Loading"
										@click="toggleSort2(col.key)"
										class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer disabled:cursor-wait disabled:opacity-85"
										:title="`Urutkan ${col.label}`"
									>
										<span>{{ col.label }}</span>
										<span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
											<svg
												v-if="sortKey2 === col.key"
												:class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection2 === 'desc' ? 'rotate-180' : '']"
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
						<tbody :class="['[&_tr:not(:first-child)_td]:border-t [&_tr:not(:first-child)_td]:border-[#d6e0ee] font-inter text-[14px] text-[#435b76] transition-all duration-200', isTable2Loading ? 'opacity-40 blur-[1px] pointer-events-none select-none cursor-wait' : '']">
							<tr
								v-for="(item, idx) in sortedPendaftaran"
								:key="`pendaftaran-${item.id}-${idx}`"
								class="h-[52px] hover:bg-[#f7f9fd] transition-colors"
							>
								<td class="px-3 py-2.5 text-center font-medium">{{ (sortKey2 === 'id' && sortDirection2 === 'desc') ? sortedPendaftaran.length - idx : idx + 1 }}</td>
								<td class="px-3 py-2.5 text-left font-medium text-[#233547] truncate" :title="item.nama_tim || item.namaTim">{{ item.nama_tim || item.namaTim }}</td>
								<td class="px-3 py-2.5 text-left truncate" :title="Array.isArray(item.peserta) ? item.peserta.map(p => p.nama).join(', ') : (item.namaPeserta || '-')">
									{{ Array.isArray(item.peserta) ? item.peserta.map(p => p.nama).join(', ') : (item.namaPeserta || '-') }}
								</td>
								<td class="px-3 py-2.5 text-center">
									<span v-if="getBuktiPendaftaranList(item).length === 0" class="text-gray-400 text-xs italic">-</span>
									<button
										v-else
										type="button"
										class="font-inter text-[14px] font-medium text-[#3b82f6] hover:text-blue-700 hover:underline transition-colors cursor-pointer inline-flex items-center justify-center gap-1 align-middle whitespace-nowrap"
										:title="getBuktiPendaftaranList(item).length > 1 ? `Lihat ${getBuktiPendaftaranList(item).length} Bukti` : 'Lihat Bukti'"
										@click="openImage(item, 'BuktiRegistrasi')"
									>
										<span>Bukti</span>
										<span v-if="getBuktiPendaftaranList(item).length > 1" class="text-xs font-semibold text-[#3b82f6]">({{ getBuktiPendaftaranList(item).length }})</span>
									</button>
								</td>
								<td class="px-3 py-2.5 text-center">
									<div class="mx-auto flex w-fit items-center justify-center gap-1.5 rounded-full px-3 py-1 font-inter text-[11px] font-semibold border"
										:class="{
											'bg-gray-100 text-gray-600 border-gray-200': String(item.status).toLowerCase() === 'menunggu',
											'bg-green-50 text-green-600 border-green-200': String(item.status).toLowerCase() === 'disetujui',
											'bg-red-50 text-red-600 border-red-200': String(item.status).toLowerCase() === 'ditolak'
										}">
										<svg v-if="String(item.status).toLowerCase() === 'menunggu'" class="h-3.5 w-3.5 shrink-0 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" /><polyline points="12 7 12 12 15 15" /></svg>
										<svg v-if="String(item.status).toLowerCase() === 'disetujui'" class="h-3.5 w-3.5 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
										<svg v-if="String(item.status).toLowerCase() === 'ditolak'" class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
										<span class="capitalize">{{ item.status }}</span>
									</div>
								</td>
								<td class="w-[170px] min-w-[170px] max-w-[170px] px-2 py-2.5 text-center">
									<div class="flex items-center justify-center gap-1.5 sm:gap-2">
										<AcceptButton
											:disabled="String(item.status).toLowerCase() === 'disetujui'"
											@click="promptApprovePendaftaran(item)"
										/>
										<RejectButton
											:disabled="String(item.status).toLowerCase() === 'ditolak' || String(item.status).toLowerCase() === 'disetujui'"
											@click="openRejectPendaftaran(item)"
										/>
										<PreviewButtonTable @click="openDetailPendaftaran(item)" />
										<DeleteButtonTable @click="confirmDeletePendaftaran(item)" />
									</div>
								</td>
							</tr>
							<tr v-if="sortedPendaftaran.length === 0">
								<td colspan="6" class="py-8 text-center text-[#64748b]">Belum ada data pendaftaran aktivitas.</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<!-- ========================================== -->
			<!-- TABEL 3: HASIL AKTIVITAS (5 TERATAS)       -->
			<!-- ========================================== -->
			<div>
				<div class="mb-4 flex items-center justify-between gap-3">
					<h2 class="text-[16px] sm:text-[18px] md:text-[20px] font-bold leading-tight sm:leading-none text-[#17334F]">
						Hasil Aktivitas Mahasiswa
					</h2>
					<Link
						href="/admin/aktivitas/hasil"
						class="font-inter text-xs sm:text-sm font-semibold text-[#17334F] hover:text-[#416f65] flex items-center gap-1 transition-colors shrink-0"
					>
						<span>Selengkapnya</span>
						<svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
							<path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
						</svg>
					</Link>
				</div>

				<div :class="['overflow-x-auto lg:overflow-visible rounded-[12px] bg-white shadow-sm ring-1 ring-[#d6e0ee] transition-all', isTable3Loading ? 'cursor-wait' : '']">
					<table class="w-full min-w-[1040px] table-fixed border-separate border-spacing-0 text-sm">
						<thead class="bg-[#416f65]">
							<tr class="h-[48px]">
								<!-- Kolom No -->
								<th class="w-[60px] min-w-[60px] px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 lg:border-r-0 rounded-tl-[12px]">
									<button type="button" :disabled="isTable3Loading" @click="toggleSort3('id')" class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer disabled:cursor-wait disabled:opacity-85" title="Urutkan No">
										<span>No</span>
										<span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
											<svg v-if="sortKey3 === 'id'" :class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection3 === 'desc' ? 'rotate-180' : '']" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
											<svg v-else class="h-3.5 w-3.5 opacity-50 transition-opacity group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.69l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
										</span>
									</button>
								</th>

								<th
									v-for="col in columnsHasil"
									:key="col.key"
									:class="[
										col.width,
										'px-2 py-2.5 text-center font-poppins text-[13px] font-semibold text-white select-none border-r border-white/15 last:border-r-0 lg:border-r-0 last:rounded-tr-[12px]'
									]"
								>
									<button
										v-if="col.sortable"
										type="button"
										:disabled="isTable3Loading"
										@click="toggleSort3(col.key)"
										class="group relative inline-flex items-center justify-center mx-auto transition-colors hover:text-white/80 focus:outline-none whitespace-nowrap cursor-pointer disabled:cursor-wait disabled:opacity-85"
										:title="`Urutkan ${col.label}`"
									>
										<span>{{ col.label }}</span>
										<span class="absolute left-full ml-1 top-1/2 -translate-y-1/2 inline-flex shrink-0 items-center text-white/70 group-hover:text-white">
											<svg
												v-if="sortKey3 === col.key"
												:class="['h-3.5 w-3.5 text-white transition-transform duration-200', sortDirection3 === 'desc' ? 'rotate-180' : '']"
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
						<tbody :class="['[&_tr:not(:first-child)_td]:border-t [&_tr:not(:first-child)_td]:border-[#d6e0ee] font-inter text-[14px] text-[#435b76] transition-all duration-200', isTable3Loading ? 'opacity-40 blur-[1px] pointer-events-none select-none cursor-wait' : '']">
							<tr
								v-for="(item, idx) in sortedHasil"
								:key="`hasil-${item.id}-${idx}`"
								class="h-[52px] hover:bg-[#f7f9fd] transition-colors"
							>
								<td class="px-3 py-2.5 text-center font-medium">{{ (sortKey3 === 'id' && sortDirection3 === 'desc') ? sortedHasil.length - idx : idx + 1 }}</td>
								<td class="px-3 py-2.5 text-left font-medium text-[#233547] truncate" :title="item.nama_mahasiswa || item.namaMahasiswa">{{ item.nama_mahasiswa || item.namaMahasiswa }}</td>
								<td class="px-3 py-2.5 text-center font-medium">{{ item.nim }}</td>
								<td class="px-3 py-2.5 text-left truncate" :title="item.nama_kegiatan || item.namaAktivitas">{{ item.nama_kegiatan || item.namaAktivitas }}</td>
								<td class="px-3 py-2.5 text-center">
									<span :class="[
										'shrink-0 inline-flex items-center justify-center rounded-full px-2.5 py-0.5 font-inter text-[11px] font-semibold border',
										(item.jenis_roadmap || item.jenisRoadmap) === 'Professional' || (item.jenis_roadmap || item.jenisRoadmap) === 'Profesional' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' :
										(item.jenis_roadmap || item.jenisRoadmap) === 'Bisnis' ? 'bg-amber-50 text-amber-700 border-amber-200' :
										(item.jenis_roadmap || item.jenisRoadmap) === 'Birokrat' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
										(item.jenis_roadmap || item.jenisRoadmap) === 'Akademisi' ? 'bg-purple-50 text-purple-700 border-purple-200' :
										'bg-slate-100 text-slate-700 border-slate-200'
									]">
										{{ item.jenis_roadmap || item.jenisRoadmap || '-' }}
									</span>
								</td>
								<td class="px-3 py-2.5 text-center">
									<span v-if="getHasilBuktiList(item).length === 0" class="text-gray-400 text-xs italic">-</span>
									<button
										v-else
										type="button"
										class="font-inter text-[14px] font-medium text-[#3b82f6] hover:text-blue-700 hover:underline transition-colors cursor-pointer inline-flex items-center justify-center gap-1 align-middle whitespace-nowrap"
										:title="getHasilBuktiList(item).length > 1 ? `Lihat ${getHasilBuktiList(item).length} Bukti` : 'Lihat Bukti'"
										@click="openImage(item, 'Hasil')"
									>
										<span>Bukti</span>
										<span v-if="getHasilBuktiList(item).length > 1" class="text-xs font-semibold text-[#3b82f6]">({{ getHasilBuktiList(item).length }})</span>
									</button>
								</td>
								<td class="px-3 py-2.5 text-center">
									<div class="mx-auto flex w-fit items-center justify-center gap-1.5 rounded-full px-3 py-1 font-inter text-[11px] font-semibold border"
										:class="{
											'bg-gray-100 text-gray-600 border-gray-200': String(item.status).toLowerCase() === 'menunggu',
											'bg-green-50 text-green-600 border-green-200': String(item.status).toLowerCase() === 'disetujui',
											'bg-red-50 text-red-600 border-red-200': String(item.status).toLowerCase() === 'ditolak'
										}">
										<svg v-if="String(item.status).toLowerCase() === 'menunggu'" class="h-3.5 w-3.5 shrink-0 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" /><polyline points="12 7 12 12 15 15" /></svg>
										<svg v-if="String(item.status).toLowerCase() === 'disetujui'" class="h-3.5 w-3.5 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
										<svg v-if="String(item.status).toLowerCase() === 'ditolak'" class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
										<span class="capitalize">{{ item.status }}</span>
									</div>
								</td>
								<td class="w-[170px] min-w-[170px] max-w-[170px] px-2 py-2.5 text-center">
									<div class="flex items-center justify-center gap-1.5 sm:gap-2">
										<AcceptButton
											:disabled="String(item.status).toLowerCase() === 'disetujui'"
											@click="promptApproveHasil(item)"
										/>
										<RejectButton
											:disabled="String(item.status).toLowerCase() === 'ditolak' || String(item.status).toLowerCase() === 'disetujui'"
											@click="openRejectHasil(item)"
										/>
										<PreviewButtonTable @click="openDetailHasil(item)" />
										<DeleteButtonTable @click="confirmDeleteHasil(item)" />
									</div>
								</td>
							</tr>
							<tr v-if="sortedHasil.length === 0">
								<td colspan="8" class="py-8 text-center text-[#64748b]">Belum ada data hasil aktivitas.</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<!-- ========================================== -->
		<!-- MODALS: PREVIEW & ACTIONS                  -->
		<!-- ========================================== -->

		<!-- Modal Detail Pengajuan Aktivitas -->
		<ModalFormPersetujuanAktivitas
			:show="showDetailPengajuan"
			:data="selectedPengajuan"
			@close="showDetailPengajuan = false"
			@approve="promptApprovePengajuan(selectedPengajuan)"
			@reject="openRejectPengajuan(selectedPengajuan)"
		/>

		<!-- Modal Detail Pendaftaran Aktivitas -->
		<ModalFormPendaftaranMahasiswa
			:show="showDetailPendaftaran"
			:data="selectedPendaftaran"
			@close="showDetailPendaftaran = false"
			@approve="promptApprovePendaftaran(selectedPendaftaran)"
			@reject="openRejectPendaftaran(selectedPendaftaran)"
		/>

		<!-- Modal Detail Hasil Aktivitas -->
		<ModalFormHasilAktivitas
			:show="showDetailHasil"
			:data="selectedHasil"
			@close="showDetailHasil = false"
			@approve="promptApproveHasil(selectedHasil)"
			@reject="openRejectHasil(selectedHasil)"
		/>

		<!-- Modal Konfirmasi Setujui (Dengan penanganan tombol Escape) -->
		<ApproveModal
			:show="showConfirmApproveModal"
			:title="approveModalTitle"
			:info="approveModalInfo"
			:is-loading="isActionLoading"
			@close="showConfirmApproveModal = false"
			@confirm="executeApprove"
		>
			<p class="text-xs sm:text-sm text-[#4d6786] font-inter mb-4 leading-relaxed">
				{{ approveModalMessage }}
			</p>
		</ApproveModal>

		<!-- Modal Penolakan (Dengan penanganan tombol Escape) -->
		<RejectModal
			:show="showRejectModal"
			:is-approved="String(itemToReject?.status).toLowerCase() === 'disetujui'"
			@close="showRejectModal = false"
			@submit="handleReject"
		/>

		<!-- Modal Konfirmasi Hapus -->
		<DeleteModal
			:show="showDeleteModal"
			:is-loading="isActionLoading"
			@close="showDeleteModal = false"
			@confirm="executeDelete"
		>
			<template #title>
				{{ deleteType === 'pengajuan' ? 'Hapus Pengajuan?' : (deleteType === 'pendaftaran' ? 'Hapus Pendaftaran?' : 'Hapus Hasil Aktivitas?') }}
			</template>
			<template #message>
				<span v-if="deleteType === 'pengajuan'">
					Apakah Anda yakin ingin menghapus pengajuan <span class="font-bold text-[#17334F]">"{{ itemToDelete?.judul }}"</span>? Tindakan ini tidak dapat dibatalkan.
				</span>
				<span v-else-if="deleteType === 'pendaftaran'">
					Apakah Anda yakin ingin menghapus data pendaftaran tim <span class="font-bold text-[#17334F]">"{{ itemToDelete?.nama_tim || itemToDelete?.namaTim }}"</span>? Tindakan ini tidak dapat dibatalkan.
				</span>
				<span v-else>
					Apakah Anda yakin ingin menghapus data hasil aktivitas mahasiswa <span class="font-bold text-[#17334F]">"{{ itemToDelete?.nama_mahasiswa || itemToDelete?.namaMahasiswa }}"</span>? Tindakan ini tidak dapat dibatalkan.
				</span>
			</template>
			<template #confirm-text>
				{{ deleteType === 'pengajuan' ? 'Hapus Pengajuan' : (deleteType === 'pendaftaran' ? 'Hapus Pendaftaran' : 'Hapus Hasil') }}
			</template>
		</DeleteModal>

		<!-- Modal Gambar (Lightbox) -->
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
					v-if="showImageModal"
					class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/80 backdrop-blur-md p-4 transition-all"
					@click="showImageModal = false"
				>
					<Transition
						enter-active-class="ease-out duration-200"
						enter-from-class="opacity-0 scale-95"
						enter-to-class="opacity-100 scale-100"
						leave-active-class="ease-in duration-150"
						leave-from-class="opacity-100 scale-100"
						leave-to-class="opacity-0 scale-95"
					>
						<div
							v-if="showImageModal"
							class="relative flex flex-col items-center justify-center bg-transparent max-w-full max-h-full touch-pan-y select-none"
							@click.stop
							@touchstart="handleModalTouchStart"
							@touchend="handleModalTouchEnd"
							@mouseenter="isHoveringNav = true"
							@mouseleave="isHoveringNav = false"
						>
							<!-- Close button -->
							<button
								type="button"
								@click="showImageModal = false"
								class="absolute top-3.5 right-3.5 z-30 flex h-9 w-9 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/85 backdrop-blur-xs shadow-md transition hover:scale-105 active:scale-95 focus:outline-none cursor-pointer"
								title="Tutup Preview"
							>
								<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
								</svg>
							</button>

							<!-- Prev button (slide in from left on image hover) -->
							<button
								v-if="activeImageList.length > 1"
								type="button"
								@click="prevImage"
								:class="[
									'absolute left-3.5 top-1/2 -translate-y-1/2 z-30 hidden sm:flex h-10 w-10 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/85 backdrop-blur-xs shadow-lg transition-all duration-300 ease-out hover:scale-110 active:scale-95 focus:outline-none cursor-pointer',
									isHoveringNav ? 'opacity-100 translate-x-0 pointer-events-auto' : 'opacity-0 -translate-x-6 pointer-events-none'
								]"
								:title="activeImageType + ' Sebelumnya'"
							>
								<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
								</svg>
							</button>

							<!-- Next button (slide in from right on image hover) -->
							<button
								v-if="activeImageList.length > 1"
								type="button"
								@click="nextImage"
								:class="[
									'absolute right-3.5 top-1/2 -translate-y-1/2 z-30 hidden sm:flex h-10 w-10 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/85 backdrop-blur-xs shadow-lg transition-all duration-300 ease-out hover:scale-110 active:scale-95 focus:outline-none cursor-pointer',
									isHoveringNav ? 'opacity-100 translate-x-0 pointer-events-auto' : 'opacity-0 translate-x-6 pointer-events-none'
								]"
								:title="activeImageType + ' Berikutnya'"
							>
								<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
								</svg>
							</button>

							<!-- Image -->
							<img
								:src="selectedImage"
								alt="Zoomed Preview"
								@error="onImageError"
								class="max-h-[82vh] max-w-[88vw] w-auto h-auto min-w-[280px] sm:min-w-[460px] rounded-xl object-contain shadow-2xl"
							/>

							<!-- Bottom Indicator (slide in from bottom on image hover on PC; always visible on mobile) -->
							<div
								v-if="activeImageList.length > 1"
								:class="[
									'absolute bottom-4 left-1/2 -translate-x-1/2 z-30 flex items-center gap-2 rounded-full bg-black/70 px-3.5 py-1.5 backdrop-blur-sm text-xs font-medium text-white shadow-lg transition-all duration-300 ease-out',
									isHoveringNav ? 'opacity-100 translate-y-0 pointer-events-auto' : 'opacity-100 sm:opacity-0 sm:translate-y-6 sm:pointer-events-none'
								]"
							>
								<span>{{ activeImageType }} {{ activeImageIndex + 1 }} dari {{ activeImageList.length }}</span>
								<div class="flex items-center gap-1.5 ml-1">
									<button
										v-for="(_, dotIdx) in activeImageList"
										:key="dotIdx"
										type="button"
										@click="activeImageIndex = dotIdx; selectedImage = getImageSrc(activeImageList[dotIdx])"
										class="h-2 rounded-full transition-all cursor-pointer"
										:class="activeImageIndex === dotIdx ? 'w-4 bg-white' : 'w-2 bg-white/40 hover:bg-white/70'"
										:title="'Buka ' + activeImageType + ' ' + (dotIdx + 1)"
									/>
								</div>
							</div>
						</div>
					</Transition>
				</div>
			</Transition>
		</Teleport>

		<!-- TOAST NOTIFICATION -->
		<ToastNotification
			:show="toast.show"
			:type="toast.type"
			:title="toast.title"
			:message="toast.message"
			@close="toast.show = false"
		/>
	</AdminLayout>
</template>
