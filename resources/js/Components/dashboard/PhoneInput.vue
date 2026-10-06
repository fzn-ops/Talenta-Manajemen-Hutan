<script setup>
import { computed, h, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
	modelValue: {
		type: [String, Number],
		default: '',
	},
	placeholder: {
		type: String,
		default: '',
	},
	readonly: {
		type: Boolean,
		default: false,
	},
	disabled: {
		type: Boolean,
		default: false,
	},
	hasError: {
		type: Boolean,
		default: false,
	},
	heightClass: {
		type: String,
		default: 'h-[46px]',
	},
	placement: {
		type: String,
		default: 'auto', // 'auto' | 'top' | 'bottom'
	},
});

const emit = defineEmits(['update:modelValue', 'blur', 'focus']);

// Daftar kode negara umum dengan nama (Indonesia default + negara-negara Asia mainstream & global)
const countries = [
	// Asia Tenggara (ASEAN)
	{ code: 'ID', name: 'Indonesia', dial_code: '+62', placeholder: '812-3456-7890' },
	{ code: 'MY', name: 'Malaysia', dial_code: '+60', placeholder: '12-345-6789' },
	{ code: 'SG', name: 'Singapura (Singapore)', dial_code: '+65', placeholder: '8123-4567' },
	{ code: 'BN', name: 'Brunei Darussalam', dial_code: '+673', placeholder: '812-3456' },
	{ code: 'TH', name: 'Thailand', dial_code: '+66', placeholder: '81-234-5678' },
	{ code: 'PH', name: 'Filipina (Philippines)', dial_code: '+63', placeholder: '912-345-6789' },
	{ code: 'VN', name: 'Vietnam', dial_code: '+84', placeholder: '91-234-5678' },
	{ code: 'KH', name: 'Kamboja (Cambodia)', dial_code: '+855', placeholder: '12-345-678' },
	{ code: 'LA', name: 'Laos', dial_code: '+856', placeholder: '20-2345-6789' },
	{ code: 'MM', name: 'Myanmar', dial_code: '+95', placeholder: '9-234-5678' },
	{ code: 'TL', name: 'Timor-Leste', dial_code: '+670', placeholder: '7712-3456' },

	// Asia Selatan
	{ code: 'BD', name: 'Bangladesh', dial_code: '+880', placeholder: '1712-345678' },
	{ code: 'IN', name: 'India', dial_code: '+91', placeholder: '98765-43210' },
	{ code: 'PK', name: 'Pakistan', dial_code: '+92', placeholder: '301-2345678' },
	{ code: 'LK', name: 'Sri Lanka', dial_code: '+94', placeholder: '71-234-5678' },
	{ code: 'NP', name: 'Nepal', dial_code: '+977', placeholder: '984-1234567' },
	{ code: 'MV', name: 'Maladewa (Maldives)', dial_code: '+960', placeholder: '771-2345' },
	{ code: 'AF', name: 'Afghanistan', dial_code: '+93', placeholder: '70-123-4567' },

	// Asia Timur
	{ code: 'JP', name: 'Jepang (Japan)', dial_code: '+81', placeholder: '90-1234-5678' },
	{ code: 'KR', name: 'Korea Selatan (South Korea)', dial_code: '+82', placeholder: '10-1234-5678' },
	{ code: 'CN', name: 'Tiongkok (China)', dial_code: '+86', placeholder: '138-0013-8000' },
	{ code: 'TW', name: 'Taiwan', dial_code: '+886', placeholder: '912-345-678' },
	{ code: 'HK', name: 'Hong Kong', dial_code: '+852', placeholder: '9123-4567' },
	{ code: 'MO', name: 'Makau (Macau)', dial_code: '+853', placeholder: '6123-4567' },
	{ code: 'MN', name: 'Mongolia', dial_code: '+976', placeholder: '8812-3456' },

	// Timur Tengah & Asia Barat
	{ code: 'SA', name: 'Arab Saudi (Saudi Arabia)', dial_code: '+966', placeholder: '50-123-4567' },
	{ code: 'AE', name: 'Uni Emirat Arab (UAE)', dial_code: '+971', placeholder: '50-123-4567' },
	{ code: 'QA', name: 'Qatar', dial_code: '+974', placeholder: '3312-3456' },
	{ code: 'KW', name: 'Kuwait', dial_code: '+965', placeholder: '9123-4567' },
	{ code: 'BH', name: 'Bahrain', dial_code: '+973', placeholder: '3912-3456' },
	{ code: 'OM', name: 'Oman', dial_code: '+968', placeholder: '9123-4567' },
	{ code: 'YE', name: 'Yaman (Yemen)', dial_code: '+967', placeholder: '71-234-5678' },
	{ code: 'JO', name: 'Yordania (Jordan)', dial_code: '+962', placeholder: '7-9012-3456' },
	{ code: 'LB', name: 'Lebanon', dial_code: '+961', placeholder: '70-123-456' },
	{ code: 'IQ', name: 'Irak (Iraq)', dial_code: '+964', placeholder: '790-123-4567' },
	{ code: 'IR', name: 'Iran', dial_code: '+98', placeholder: '912-345-6789' },
	{ code: 'PS', name: 'Palestina (Palestine)', dial_code: '+970', placeholder: '59-912-3456' },
	{ code: 'SY', name: 'Suriah (Syria)', dial_code: '+963', placeholder: '944-123-456' },
	{ code: 'TR', name: 'Turki (Turkey)', dial_code: '+90', placeholder: '532-123-4567' },

	// Asia Tengah
	{ code: 'UZ', name: 'Uzbekistan', dial_code: '+998', placeholder: '90-123-4567' },
	{ code: 'KZ', name: 'Kazakhstan', dial_code: '+7', placeholder: '701-234-5678' },

	// Negara Populer Lainnya
	{ code: 'AU', name: 'Australia', dial_code: '+61', placeholder: '412-345-678' },
	{ code: 'GB', name: 'Inggris (United Kingdom)', dial_code: '+44', placeholder: '7123-456789' },
	{ code: 'US', name: 'Amerika Serikat (USA)', dial_code: '+1', placeholder: '202-555-0123' },
	{ code: 'DE', name: 'Jerman (Germany)', dial_code: '+49', placeholder: '151-23456789' },
	{ code: 'NL', name: 'Belanda (Netherlands)', dial_code: '+31', placeholder: '6-12345678' },
	{ code: 'EG', name: 'Mesir (Egypt)', dial_code: '+20', placeholder: '10-1234-5678' },
];

// SVG Flags renderer agar tampil tajam dan berwarna di semua OS (termasuk Windows)
const flagSvgMap = {
	ID: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('path', { fill: '#E70011', d: 'M0 0h640v240H0z' }),
			h('path', { fill: '#FFFFFF', d: 'M0 240h640v240H0z' }),
		]),
	MY: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('path', { fill: '#CC0000', d: 'M0 0h640v480H0z' }),
			h('path', { stroke: '#fff', 'stroke-width': '34', d: 'M0 51h640M0 119h640M0 187h640M0 255h640M0 323h640M0 391h640M0 459h640' }),
			h('rect', { width: '320', height: '272', fill: '#000066' }),
			h('circle', { cx: '160', cy: '136', r: '80', fill: '#FFCC00' }),
			h('circle', { cx: '180', cy: '136', r: '68', fill: '#000066' }),
		]),
	SG: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('path', { fill: '#ED2939', d: 'M0 0h640v240H0z' }),
			h('path', { fill: '#FFFFFF', d: 'M0 240h640v240H0z' }),
			h('circle', { cx: '150', cy: '120', r: '60', fill: '#FFFFFF' }),
			h('circle', { cx: '168', cy: '120', r: '60', fill: '#ED2939' }),
		]),
	BN: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#F7E017' }),
			h('polygon', { points: '0,0 640,360 640,480 0,120', fill: '#FFFFFF' }),
			h('polygon', { points: '0,60 640,420 640,480 0,120', fill: '#000000' }),
		]),
	TH: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#A51931' }),
			h('rect', { y: '80', width: '640', height: '320', fill: '#F4F5F8' }),
			h('rect', { y: '160', width: '640', height: '160', fill: '#2D2A4A' }),
		]),
	PH: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '240', fill: '#0038A8' }),
			h('rect', { y: '240', width: '640', height: '240', fill: '#CE1126' }),
			h('polygon', { points: '0,0 280,240 0,480', fill: '#FFFFFF' }),
		]),
	VN: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#DA251D' }),
			h('polygon', { points: '320,120 355,230 470,230 378,298 412,410 320,342 228,410 262,298 170,230 285,230', fill: '#FFFF00' }),
		]),
	KH: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#032EA1' }),
			h('rect', { y: '120', width: '640', height: '240', fill: '#E00025' }),
			h('polygon', { points: '320,170 300,290 340,290', fill: '#FFFFFF' }),
			h('rect', { x: '270', y: '220', width: '100', height: '70', fill: '#FFFFFF' }),
		]),
	LA: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#CE1126' }),
			h('rect', { y: '120', width: '640', height: '240', fill: '#002868' }),
			h('circle', { cx: '320', cy: '240', r: '80', fill: '#FFFFFF' }),
		]),
	MM: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '160', fill: '#FECB00' }),
			h('rect', { y: '160', width: '640', height: '160', fill: '#34B233' }),
			h('rect', { y: '320', width: '640', height: '160', fill: '#EA2839' }),
			h('polygon', { points: '320,130 350,225 450,225 368,285 400,380 320,320 240,380 272,285 190,225 290,225', fill: '#FFFFFF' }),
		]),
	TL: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#DC241F' }),
			h('polygon', { points: '0,0 320,240 0,480', fill: '#FFC72C' }),
			h('polygon', { points: '0,0 200,240 0,480', fill: '#000000' }),
			h('polygon', { points: '70,210 80,240 110,240 85,260 95,290 70,270 45,290 55,260 30,240 60,240', fill: '#FFFFFF' }),
		]),
	BD: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#006A4E' }),
			h('circle', { cx: '280', cy: '240', r: '140', fill: '#F42A41' }),
		]),
	IN: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '160', fill: '#FF9933' }),
			h('rect', { y: '160', width: '640', height: '160', fill: '#FFFFFF' }),
			h('rect', { y: '320', width: '640', height: '160', fill: '#138808' }),
			h('circle', { cx: '320', cy: '240', r: '50', fill: '#000080' }),
		]),
	PK: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#01411C' }),
			h('rect', { width: '160', height: '480', fill: '#FFFFFF' }),
			h('circle', { cx: '410', cy: '240', r: '110', fill: '#FFFFFF' }),
			h('circle', { cx: '440', cy: '220', r: '100', fill: '#01411C' }),
			h('polygon', { points: '450,160 460,190 490,190 465,210 475,240 450,220 425,240 435,210 410,190 440,190', fill: '#FFFFFF' }),
		]),
	LK: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#FFBE29' }),
			h('rect', { x: '30', y: '30', width: '70', height: '420', fill: '#00534E' }),
			h('rect', { x: '110', y: '30', width: '70', height: '420', fill: '#EB7400' }),
			h('rect', { x: '200', y: '30', width: '410', height: '420', fill: '#8D153A' }),
		]),
	NP: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#FFFFFF' }),
			h('polygon', { points: '60,20 380,220 180,220 400,460 60,460', fill: '#003893' }),
			h('polygon', { points: '80,50 340,210 160,210 360,440 80,440', fill: '#DC143C' }),
			h('circle', { cx: '180', cy: '340', r: '40', fill: '#FFFFFF' }),
		]),
	MV: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#D21034' }),
			h('rect', { x: '100', y: '80', width: '440', height: '320', fill: '#007E3A' }),
			h('circle', { cx: '340', cy: '240', r: '75', fill: '#FFFFFF' }),
			h('circle', { cx: '370', cy: '240', r: '75', fill: '#007E3A' }),
		]),
	AF: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '213', height: '480', fill: '#000000' }),
			h('rect', { x: '213', width: '214', height: '480', fill: '#D32011' }),
			h('rect', { x: '427', width: '213', height: '480', fill: '#007A3D' }),
		]),
	JP: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#FFFFFF' }),
			h('circle', { cx: '320', cy: '240', r: '144', fill: '#BC002D' }),
		]),
	KR: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#FFFFFF' }),
			h('circle', { cx: '320', cy: '240', r: '100', fill: '#CD2E3A' }),
			h('path', { d: 'M220 240a100 100 0 0 0 200 0c0 55-45 100-100 100s-100-45-100-100z', fill: '#0047A0' }),
		]),
	CN: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#DE2910' }),
			h('circle', { cx: '110', cy: '110', r: '40', fill: '#FFDE00' }),
		]),
	TW: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#FE0000' }),
			h('rect', { width: '320', height: '240', fill: '#000095' }),
			h('circle', { cx: '160', cy: '120', r: '50', fill: '#FFFFFF' }),
		]),
	HK: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#DE2910' }),
			h('circle', { cx: '320', cy: '240', r: '80', fill: '#FFFFFF' }),
			h('circle', { cx: '320', cy: '240', r: '50', fill: '#DE2910' }),
		]),
	MO: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#007B5F' }),
			h('circle', { cx: '320', cy: '240', r: '60', fill: '#FFFFFF' }),
		]),
	MN: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '213', height: '480', fill: '#E4002B' }),
			h('rect', { x: '213', width: '214', height: '480', fill: '#0033A0' }),
			h('rect', { x: '427', width: '213', height: '480', fill: '#E4002B' }),
			h('circle', { cx: '106', cy: '240', r: '30', fill: '#FFD100' }),
		]),
	SA: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#006C35' }),
			h('rect', { x: '180', y: '330', width: '280', height: '20', rx: '10', fill: '#FFFFFF' }),
			h('text', { x: '50%', y: '50%', 'text-anchor': 'middle', fill: '#fff', 'font-size': '110', 'font-weight': 'bold', 'font-family': 'sans-serif' }, 'SA'),
		]),
	AE: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '160', fill: '#00732F' }),
			h('rect', { y: '160', width: '640', height: '160', fill: '#FFFFFF' }),
			h('rect', { y: '320', width: '640', height: '160', fill: '#000000' }),
			h('rect', { width: '160', height: '480', fill: '#FF0000' }),
		]),
	QA: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#8D1B3D' }),
			h('polygon', { points: '0,0 180,0 220,53 180,106 220,160 180,213 220,266 180,320 220,373 180,426 220,480 0,480', fill: '#FFFFFF' }),
		]),
	KW: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '160', fill: '#007A3D' }),
			h('rect', { y: '160', width: '640', height: '160', fill: '#FFFFFF' }),
			h('rect', { y: '320', width: '640', height: '160', fill: '#CE1126' }),
			h('polygon', { points: '0,0 160,160 160,320 0,480', fill: '#000000' }),
		]),
	BH: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#CE1126' }),
			h('polygon', { points: '0,0 160,0 200,48 160,96 200,144 160,192 200,240 160,288 200,336 160,384 200,432 160,480 0,480', fill: '#FFFFFF' }),
		]),
	OM: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '160', fill: '#FFFFFF' }),
			h('rect', { y: '160', width: '640', height: '160', fill: '#DB161B' }),
			h('rect', { y: '320', width: '640', height: '160', fill: '#008000' }),
			h('rect', { width: '160', height: '480', fill: '#DB161B' }),
		]),
	YE: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '160', fill: '#CE1126' }),
			h('rect', { y: '160', width: '640', height: '160', fill: '#FFFFFF' }),
			h('rect', { y: '320', width: '640', height: '160', fill: '#000000' }),
		]),
	JO: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '160', fill: '#000000' }),
			h('rect', { y: '160', width: '640', height: '160', fill: '#FFFFFF' }),
			h('rect', { y: '320', width: '640', height: '160', fill: '#007A3D' }),
			h('polygon', { points: '0,0 240,240 0,480', fill: '#CE1126' }),
			h('circle', { cx: '80', cy: '240', r: '20', fill: '#FFFFFF' }),
		]),
	LB: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '120', fill: '#ED1C24' }),
			h('rect', { y: '120', width: '640', height: '240', fill: '#FFFFFF' }),
			h('rect', { y: '360', width: '640', height: '120', fill: '#ED1C24' }),
			h('polygon', { points: '320,150 250,330 390,330', fill: '#00A651' }),
		]),
	IQ: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '160', fill: '#CE1126' }),
			h('rect', { y: '160', width: '640', height: '160', fill: '#FFFFFF' }),
			h('rect', { y: '320', width: '640', height: '160', fill: '#000000' }),
			h('text', { x: '50%', y: '56%', 'text-anchor': 'middle', fill: '#007A3D', 'font-size': '65', 'font-weight': 'bold', 'font-family': 'sans-serif' }, 'ALLAH'),
		]),
	IR: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '160', fill: '#239F40' }),
			h('rect', { y: '160', width: '640', height: '160', fill: '#FFFFFF' }),
			h('rect', { y: '320', width: '640', height: '160', fill: '#DA0000' }),
			h('circle', { cx: '320', cy: '240', r: '40', fill: '#DA0000' }),
		]),
	PS: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '160', fill: '#000000' }),
			h('rect', { y: '160', width: '640', height: '160', fill: '#FFFFFF' }),
			h('rect', { y: '320', width: '640', height: '160', fill: '#007A3D' }),
			h('polygon', { points: '0,0 240,240 0,480', fill: '#E4312B' }),
		]),
	SY: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '160', fill: '#CE1126' }),
			h('rect', { y: '160', width: '640', height: '160', fill: '#FFFFFF' }),
			h('rect', { y: '320', width: '640', height: '160', fill: '#000000' }),
			h('circle', { cx: '230', cy: '240', r: '25', fill: '#007A3D' }),
			h('circle', { cx: '410', cy: '240', r: '25', fill: '#007A3D' }),
		]),
	TR: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#E30A17' }),
			h('circle', { cx: '280', cy: '240', r: '120', fill: '#FFFFFF' }),
			h('circle', { cx: '310', cy: '240', r: '96', fill: '#E30A17' }),
		]),
	UZ: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '160', fill: '#0099B5' }),
			h('rect', { y: '160', width: '640', height: '160', fill: '#FFFFFF' }),
			h('rect', { y: '320', width: '640', height: '160', fill: '#1EB53A' }),
			h('circle', { cx: '120', cy: '80', r: '40', fill: '#FFFFFF' }),
			h('circle', { cx: '135', cy: '80', r: '35', fill: '#0099B5' }),
		]),
	KZ: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#00AFCA' }),
			h('circle', { cx: '320', cy: '200', r: '60', fill: '#FEC50C' }),
		]),
	AU: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#00008B' }),
			h('rect', { width: '320', height: '240', fill: '#012169' }),
			h('path', { stroke: '#fff', 'stroke-width': '40', d: 'M0 0l320 240M320 0L0 240' }),
			h('path', { stroke: '#C8102E', 'stroke-width': '24', d: 'M0 0l320 240M320 0L0 240' }),
			h('path', { stroke: '#fff', 'stroke-width': '60', d: 'M160 0v240M0 120h320' }),
			h('path', { stroke: '#C8102E', 'stroke-width': '36', d: 'M160 0v240M0 120h320' }),
		]),
	GB: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#012169' }),
			h('path', { stroke: '#fff', 'stroke-width': '80', d: 'M0 0l640 480M640 0L0 480' }),
			h('path', { stroke: '#C8102E', 'stroke-width': '48', d: 'M0 0l640 480M640 0L0 480' }),
			h('path', { stroke: '#fff', 'stroke-width': '120', d: 'M320 0v480M0 240h640' }),
			h('path', { stroke: '#C8102E', 'stroke-width': '72', d: 'M320 0v480M0 240h640' }),
		]),
	US: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#B22234' }),
			h('path', { stroke: '#fff', 'stroke-width': '37', d: 'M0 55h640M0 129h640M0 203h640M0 277h640M0 351h640M0 425h640' }),
			h('rect', { width: '260', height: '260', fill: '#3C3B6E' }),
		]),
	DE: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '160', fill: '#000000' }),
			h('rect', { y: '160', width: '640', height: '160', fill: '#DD0000' }),
			h('rect', { y: '320', width: '640', height: '160', fill: '#FFCE00' }),
		]),
	NL: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '160', fill: '#AE1C28' }),
			h('rect', { y: '160', width: '640', height: '160', fill: '#FFFFFF' }),
			h('rect', { y: '320', width: '640', height: '160', fill: '#21468B' }),
		]),
	EG: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '160', fill: '#CE1126' }),
			h('rect', { y: '160', width: '640', height: '160', fill: '#FFFFFF' }),
			h('rect', { y: '320', width: '640', height: '160', fill: '#000000' }),
			h('circle', { cx: '320', cy: '240', r: '35', fill: '#C09300' }),
		]),
};

const getFlagComponent = (code) => {
	return flagSvgMap[code] || flagSvgMap.ID;
};

const selectedCountry = ref(countries[0]); // Default: Indonesia (+62)
const nationalNumber = ref('');
const isDropdownOpen = ref(false);
const isDropUp = ref(false);
const searchQuery = ref('');
const dropdownRef = ref(null);
const searchInputRef = ref(null);
const phoneInputRef = ref(null);

// Format nomor telepon otomatis dengan pemisah: 3 digit - 4 digit - sisa digit
const formatPhoneNumber = (digits) => {
	if (!digits) return '';
	if (digits.length <= 3) {
		return digits;
	}
	if (digits.length <= 7) {
		return `${digits.slice(0, 3)}-${digits.slice(3)}`;
	}
	return `${digits.slice(0, 3)}-${digits.slice(3, 7)}-${digits.slice(7, 13)}`;
};

// Parse input string saat pertama kali load atau saat prop modelValue berubah
const parsePhoneNumber = (val) => {
	if (!val || val === '-') {
		nationalNumber.value = '';
		return;
	}

	const str = String(val).trim();

	// Cek apakah string diawali kode negara (+XX)
	let matchedCountry = null;
	const sorted = [...countries].sort((a, b) => b.dial_code.length - a.dial_code.length);
	for (const c of sorted) {
		if (str.startsWith(c.dial_code)) {
			matchedCountry = c;
			break;
		}
	}

	let digits = '';
	if (matchedCountry) {
		selectedCountry.value = matchedCountry;
		digits = str.slice(matchedCountry.dial_code.length).replace(/\D/g, '');
	} else if (str.startsWith('0')) {
		selectedCountry.value = countries[0];
		digits = str.replace(/\D/g, '').replace(/^0+/, '');
	} else if (str.startsWith('62')) {
		selectedCountry.value = countries[0];
		digits = str.slice(2).replace(/\D/g, '');
	} else {
		selectedCountry.value = countries[0];
		digits = str.replace(/\D/g, '');
	}

	if (digits.startsWith('0')) {
		digits = digits.replace(/^0+/, '');
	}
	if (digits.length > 13) {
		digits = digits.slice(0, 13);
	}

	nationalNumber.value = formatPhoneNumber(digits);
};

watch(
	() => props.modelValue,
	(newVal) => {
		const currentFull = nationalNumber.value ? `${selectedCountry.value.dial_code} ${nationalNumber.value}` : '';
		if (String(newVal).replace(/[\s-]/g, '') !== currentFull.replace(/[\s-]/g, '')) {
			parsePhoneNumber(newVal);
		}
	},
	{ immediate: true }
);

// Filter pencarian negara
const filteredCountries = computed(() => {
	const q = searchQuery.value.toLowerCase().trim();
	if (!q) return countries;
	return countries.filter(
		(c) =>
			c.name.toLowerCase().includes(q) ||
			c.dial_code.includes(q) ||
			c.code.toLowerCase().includes(q)
	);
});

// Hitung posisi buka dropdown (ke atas jika ruang bawah tidak cukup atau jika placement='top')
const calculatePlacement = () => {
	if (props.placement === 'top') {
		isDropUp.value = true;
		return;
	}
	if (props.placement === 'bottom') {
		isDropUp.value = false;
		return;
	}

	// Mode auto: cek sisa ruang dari elemen ke batas bawah modal / container terdekat
	if (dropdownRef.value) {
		const rect = dropdownRef.value.getBoundingClientRect();
		let containerBottom = window.innerHeight;

		// Cari ancestor yang memiliki scroll/modal wrapper
		let parent = dropdownRef.value.parentElement;
		while (parent && parent !== document.body) {
			const style = window.getComputedStyle(parent);
			const overflowY = style.overflowY || style.overflow;
			if (overflowY === 'auto' || overflowY === 'scroll' || overflowY === 'hidden') {
				const parentRect = parent.getBoundingClientRect();
				containerBottom = Math.min(containerBottom, parentRect.bottom);
				break;
			}
			parent = parent.parentElement;
		}

		const spaceBelow = containerBottom - rect.bottom;
		const dropdownHeight = 270;

		// Jika ruang di bawah kurang dari 270px, otomatis buka ke atas
		isDropUp.value = spaceBelow < dropdownHeight;
	}
};

const toggleDropdown = () => {
	if (props.readonly || props.disabled) return;
	const willOpen = !isDropdownOpen.value;
	if (willOpen) {
		calculatePlacement();
	}
	isDropdownOpen.value = willOpen;
	if (isDropdownOpen.value) {
		searchQuery.value = '';
		nextTick(() => {
			searchInputRef.value?.focus();
		});
	}
};

const selectCountry = (c) => {
	selectedCountry.value = c;
	isDropdownOpen.value = false;
	searchQuery.value = '';
	emitValue();
	nextTick(() => {
		phoneInputRef.value?.focus();
	});
};

// Hanya izinkan angka saat tombol ditekan (angka 0-9)
const onKeyPress = (e) => {
	if (props.readonly || props.disabled) return;
	const char = String.fromCharCode(e.which || e.keyCode);
	if (!/^[0-9]$/.test(char)) {
		e.preventDefault();
	}
};

// Handle tombol Backspace agar nyaman saat melewati tanda minus '-'
const onKeyDown = (e) => {
	if (props.readonly || props.disabled) return;

	if (e.key === 'Backspace') {
		const input = e.target;
		const selStart = input.selectionStart;
		const selEnd = input.selectionEnd;

		// Jika kursor tunggal tepat setelah karakter '-'
		if (selStart === selEnd && selStart > 0 && input.value[selStart - 1] === '-') {
			e.preventDefault();
			const val = input.value;
			const beforeMinus = val.slice(0, selStart - 2);
			const afterMinus = val.slice(selStart);
			let digits = (beforeMinus + afterMinus).replace(/\D/g, '');
			if (digits.startsWith('0')) digits = digits.replace(/^0+/, '');
			const formatted = formatPhoneNumber(digits);
			nationalNumber.value = formatted;
			if (phoneInputRef.value) {
				phoneInputRef.value.value = formatted;
			}
			emitValue();
			nextTick(() => {
				const newPos = Math.max(0, selStart - 2);
				input.setSelectionRange(newPos, newPos);
			});
		}
	}
};

// Saat user paste teks
const onPaste = (e) => {
	if (props.readonly || props.disabled) return;
	e.preventDefault();
	const pasted = (e.clipboardData || window.clipboardData)?.getData('text') || '';
	if (!pasted) return;

	const trimmed = pasted.trim();
	let matched = null;
	const sorted = [...countries].sort((a, b) => b.dial_code.length - a.dial_code.length);
	for (const c of sorted) {
		if (trimmed.startsWith(c.dial_code)) {
			matched = c;
			break;
		}
	}

	let digits = '';
	if (matched) {
		selectedCountry.value = matched;
		digits = trimmed.slice(matched.dial_code.length).replace(/\D/g, '');
	} else {
		digits = trimmed.replace(/\D/g, '');
		if (digits.startsWith('62')) {
			selectedCountry.value = countries[0];
			digits = digits.slice(2);
		}
	}

	if (digits.startsWith('0')) {
		digits = digits.replace(/^0+/, '');
	}
	if (digits.length > 13) {
		digits = digits.slice(0, 13);
	}

	const formatted = formatPhoneNumber(digits);
	nationalNumber.value = formatted;
	if (phoneInputRef.value) {
		phoneInputRef.value.value = formatted;
	}
	emitValue();
};

// Saat user mengetik
const onInput = (e) => {
	let val = e.target.value;
	let digits = val.replace(/\D/g, '');

	// Hapus angka 0 di awal jika diketik
	if (digits.startsWith('0')) {
		digits = digits.replace(/^0+/, '');
	}

	// Maksimal 13 digit
	if (digits.length > 13) {
		digits = digits.slice(0, 13);
	}

	const formatted = formatPhoneNumber(digits);
	nationalNumber.value = formatted;
	if (phoneInputRef.value) {
		phoneInputRef.value.value = formatted;
	}
	emitValue();
};

const emitValue = () => {
	if (!nationalNumber.value) {
		emit('update:modelValue', '');
		return;
	}
	// Format tersimpan: '+62 895-6228-15861'
	const formatted = `${selectedCountry.value.dial_code} ${nationalNumber.value}`;
	emit('update:modelValue', formatted);
};

const onBlur = (e) => {
	emit('blur', e);
};

const onFocus = (e) => {
	emit('focus', e);
};

// Tutup dropdown jika klik di luar
const handleClickOutside = (e) => {
	if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
		isDropdownOpen.value = false;
	}
};

onMounted(() => {
	document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
	document.removeEventListener('click', handleClickOutside);
});

defineExpose({
	focus: () => phoneInputRef.value?.focus(),
	blur: () => phoneInputRef.value?.blur(),
});
</script>

<template>
	<div
		ref="dropdownRef"
		:class="[
			'phone-container relative flex w-full items-center rounded-[10px] transition-colors duration-150',
			heightClass,
			hasError
				? 'border border-red-400 bg-red-50/20'
				: readonly || disabled
				? 'border border-[#d6e0ee] bg-[#f0f4f9] cursor-not-allowed select-none'
				: 'border border-[#d6e0ee] bg-white hover:border-[#a6b7cb] hover:bg-[#fafcff] focus-within:!border-[#183669] focus-within:!bg-white'
		]"
	>
		<!-- Tombol Pemilih Kode Negara (National Calling Code) -->
		<button
			type="button"
			@mousedown.prevent
			@click.stop="toggleDropdown"
			:disabled="readonly || disabled"
			:tabindex="readonly || disabled ? -1 : 0"
			class="flex h-full shrink-0 items-center gap-2 pl-3.5 pr-2.5 font-inter text-[14px] font-medium text-[#183669] transition hover:bg-slate-100/60 rounded-l-[9px] focus:outline-none disabled:cursor-not-allowed disabled:hover:bg-transparent cursor-pointer"
			:title="`Pilih kode negara (${selectedCountry.name} ${selectedCountry.dial_code})`"
		>
			<!-- Real SVG Flag Icon -->
			<span class="inline-flex items-center shrink-0">
				<component :is="getFlagComponent(selectedCountry.code)" />
			</span>
			<span class="select-none tracking-tight leading-none text-[#183669] font-medium">{{ selectedCountry.dial_code }}</span>
			<svg
				:class="[
					'h-3.5 w-3.5 text-[#7188a3] transition-transform duration-150 shrink-0',
					isDropUp ? (isDropdownOpen ? 'text-[#183669]' : 'rotate-180') : (isDropdownOpen ? 'rotate-180 text-[#183669]' : '')
				]"
				fill="none"
				stroke="currentColor"
				stroke-width="2.2"
				viewBox="0 0 24 24"
			>
				<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
			</svg>
		</button>

		<!-- Garis Pemisah Antara Kode Negara dan Input -->
		<div class="h-5 w-[1px] bg-[#d6e0ee] shrink-0" aria-hidden="true"></div>

		<!-- Input Angka Handphone (Tanpa border dalam, tanpa ring biru tailwind, sejajar sempurna) -->
		<input
			ref="phoneInputRef"
			type="tel"
			inputmode="numeric"
			:value="nationalNumber"
			:readonly="readonly"
			:disabled="disabled"
			:placeholder="placeholder || selectedCountry.placeholder"
			@keypress="onKeyPress"
			@keydown="onKeyDown"
			@paste="onPaste"
			@input="onInput"
			@blur="onBlur"
			@focus="onFocus"
			:class="[
				'phone-raw-input h-full flex-1 border-0 bg-transparent pl-3 font-inter text-[14px] text-[#173a63] placeholder-[#a8bed4] leading-normal',
				$slots.append ? 'pr-11' : 'pr-3',
				readonly || disabled ? 'cursor-not-allowed select-none' : ''
			]"
		/>

		<!-- Slot Tombol Aksi Tambahan (Edit/Gembok: Menyatu di dalam container tanpa pemisah) -->
		<div v-if="$slots.append" class="absolute inset-y-0 right-0 z-10 flex items-center pr-2.5 pointer-events-auto">
			<slot name="append"></slot>
		</div>

		<!-- Popover Dropdown Pilihan Negara (Auto Drop-Up / Drop-Down) -->
		<Transition
			enter-active-class="transition duration-150 ease-out"
			:enter-from-class="isDropUp ? 'transform scale-95 opacity-0 translate-y-1' : 'transform scale-95 opacity-0 -translate-y-1'"
			enter-to-class="transform scale-100 opacity-100 translate-y-0"
			leave-active-class="transition duration-100 ease-in"
			leave-from-class="transform scale-100 opacity-100 translate-y-0"
			:leave-to-class="isDropUp ? 'transform scale-95 opacity-0 translate-y-1' : 'transform scale-95 opacity-0 -translate-y-1'"
		>
			<div
				v-if="isDropdownOpen"
				:class="[
					'absolute left-0 z-50 w-[280px] sm:w-[320px] rounded-[12px] border border-[#d6e0ee] bg-white p-2 shadow-2xl ring-1 ring-black/10',
					isDropUp ? 'bottom-full mb-2' : 'top-full mt-2'
				]"
			>
				<!-- Pencarian Negara -->
				<div class="relative mb-2">
					<span class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-[#7188a3]">
						<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
						</svg>
					</span>
					<input
						ref="searchInputRef"
						v-model="searchQuery"
						type="text"
						placeholder="Cari negara atau kode..."
						class="phone-search-input h-[34px] w-full rounded-[8px] border border-[#d6e0ee] bg-[#f8fafc] pl-8 pr-2.5 font-inter text-[12px] text-[#173a63] placeholder-[#8c9eb5] transition focus:border-[#183669] focus:bg-white focus:outline-none"
					/>
				</div>

				<!-- Daftar Negara -->
				<div class="max-h-[220px] overflow-y-auto divide-y divide-slate-50 pr-0.5">
					<button
						v-for="country in filteredCountries"
						:key="country.code"
						type="button"
						@click.stop="selectCountry(country)"
						:class="[
							'flex w-full items-center justify-between px-2.5 py-2 rounded-[8px] text-left font-inter transition-colors text-[13px] cursor-pointer',
							selectedCountry.code === country.code
								? 'bg-[#183669]/10 font-semibold text-[#183669]'
								: 'text-[#2c4363] hover:bg-[#f1f5f9]'
						]"
					>
						<span class="flex items-center gap-2.5 truncate">
							<component :is="getFlagComponent(country.code)" />
							<span class="truncate">{{ country.name }}</span>
						</span>
						<span class="font-mono text-[12px] text-[#7188a3] ml-2 shrink-0 font-medium">
							{{ country.dial_code }}
						</span>
					</button>

					<div
						v-if="filteredCountries.length === 0"
						class="py-4 text-center font-inter text-[12px] text-[#7188a3]"
					>
						Negara tidak ditemukan
					</div>
				</div>
			</div>
		</Transition>
	</div>
</template>

<style scoped>
/* Reset total dari @tailwindcss/forms pada search input */
.phone-search-input {
	outline: none !important;
	box-shadow: none !important;
	--tw-ring-shadow: none !important;
	--tw-ring-offset-shadow: none !important;
	--tw-ring-color: transparent !important;
	--tw-ring-offset-color: transparent !important;
}

.phone-search-input:focus,
.phone-search-input:focus-visible,
.phone-search-input:active {
	outline: none !important;
	box-shadow: none !important;
	--tw-ring-shadow: none !important;
	--tw-ring-offset-shadow: none !important;
	--tw-ring-color: transparent !important;
	--tw-ring-offset-color: transparent !important;
	border-color: #183669 !important;
}

/* Reset total dari @tailwindcss/forms pada input nomor */
.phone-raw-input {
	border: 0 !important;
	border-width: 0 !important;
	border-style: none !important;
	outline: none !important;
	box-shadow: none !important;
	--tw-ring-shadow: none !important;
	--tw-ring-offset-shadow: none !important;
	--tw-ring-color: transparent !important;
	--tw-ring-offset-color: transparent !important;
	background-color: transparent !important;
	border-radius: 0 !important;
	appearance: none !important;
	-webkit-appearance: none !important;
}

.phone-raw-input:focus,
.phone-raw-input:focus-visible,
.phone-raw-input:focus-within,
.phone-raw-input:active {
	border: 0 !important;
	border-width: 0 !important;
	border-style: none !important;
	outline: none !important;
	box-shadow: none !important;
	--tw-ring-shadow: none !important;
	--tw-ring-offset-shadow: none !important;
	--tw-ring-color: transparent !important;
	--tw-ring-offset-color: transparent !important;
	border-color: transparent !important;
}

.phone-container:focus-within {
	box-shadow: none !important;
	outline: none !important;
	--tw-ring-shadow: none !important;
	--tw-ring-color: transparent !important;
}
</style>
