import './bootstrap';
import '@fortawesome/fontawesome-free/css/all.min.css';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

// ---- Token (docs/26) — Chart.js default-nya terang, wajib ditimpa (landmine #8)
const TOKEN = {
    fgMuted: '#7a9488',
    grid: 'rgba(30,48,41,0.5)',
    font: 'Plus Jakarta Sans',
    palet: ['#00e68a', '#00b4d8', '#ffb347', '#ff5c5c', '#a78bfa', '#f472b6', '#34d399', '#fbbf24', '#60a5fa', '#fb923c', '#f87171', '#4ade80', '#38bdf8', '#c084fc', '#facc15'],
};
Chart.defaults.color = TOKEN.fgMuted;
Chart.defaults.borderColor = TOKEN.grid;
Chart.defaults.font.family = TOKEN.font;

// ---- Format rupiah (identik fR/fRs prototipe)
export function rupiah(a) {
    if (!a) return 'Rp 0';
    return 'Rp ' + Math.round(a).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}
export function rupiahSingkat(a) {
    if (a >= 1e9) return 'Rp ' + (a / 1e9).toFixed(1) + ' M';
    if (a >= 1e6) return 'Rp ' + (a / 1e6).toFixed(1) + ' Jt';
    if (a >= 1e3) return 'Rp ' + (a / 1e3).toFixed(0) + ' Rb';
    return 'Rp ' + a;
}

// ---- Modal (x-modal): buka/tutup + Escape
Alpine.data('modal', (terbukaAwal = false) => ({
    terbuka: terbukaAwal,
    buka() { this.terbuka = true; },
    tutup() { this.terbuka = false; },
}));

// ---- Toast (x-toast): hilang 3 detik dengan animasi toastOut prototipe
Alpine.data('toast', () => ({
    tampil: true,
    keluar: false,
    init() {
        setTimeout(() => {
            this.keluar = true;
            setTimeout(() => { this.tampil = false; }, 300);
        }, 3000);
    },
}));

// ---- Toggle mata pada input sandi (toggleAuthPw prototipe)
Alpine.data('toggleSandi', () => ({
    terlihat: false,
    get tipe() { return this.terlihat ? 'text' : 'password'; },
    balik() { this.terlihat = !this.terlihat; },
}));

// ---- Meter kekuatan sandi (updateStrength prototipe) — nilai warna = token docs/26
Alpine.data('kekuatanSandi', () => ({
    sandi: '',
    get tingkat() {
        const pw = this.sandi; let s = 0;
        if (pw.length >= 6) s++;
        if (pw.length >= 10) s++;
        if (/[A-Z]/.test(pw)) s++;
        if (/[0-9]/.test(pw)) s++;
        if (/[^A-Za-z0-9]/.test(pw)) s++;
        const l = [
            { w: '0%', c: 'transparent', t: '' },
            { w: '20%', c: '#ff5c5c', t: 'Sangat lemah' },
            { w: '40%', c: '#ff5c5c', t: 'Lemah' },
            { w: '60%', c: '#ffb347', t: 'Cukup' },
            { w: '80%', c: '#00e68a', t: 'Kuat' },
            { w: '100%', c: '#00e68a', t: 'Sangat kuat' },
        ];
        return l[s] || l[0];
    },
}));

// ---- Modal konfirmasi sandi (x-modal-konfirmasi-sandi): 3 gagal → tutup
// Memanggil POST /konfirmasi-sandi (docs/21); 403 = sandi salah.
Alpine.data('konfirmasiSandi', (url) => ({
    terbuka: false,
    sandi: '',
    percobaan: 0,
    salah: false,
    memproses: false,
    goyang: false,
    aksi: null,
    buka(aksi) {
        this.aksi = aksi;
        this.sandi = '';
        this.percobaan = 0;
        this.salah = false;
        this.terbuka = true;
        this.$nextTick(() => this.$refs.sandi?.focus());
    },
    tutup() { this.terbuka = false; },
    async konfirmasi() {
        if (!this.sandi) { this.salah = true; return; }
        this.memproses = true;
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify({ password: this.sandi }),
            });
            if (res.ok) {
                this.terbuka = false;
                if (typeof this.aksi === 'function') this.aksi();
                else if (this.aksi instanceof HTMLFormElement) this.aksi.submit();
                return;
            }
            this.percobaan++;
            this.salah = true;
            this.goyang = true;
            setTimeout(() => { this.goyang = false; }, 400);
            if (this.percobaan >= 3) this.terbuka = false;
        } finally {
            this.memproses = false;
        }
    },
}));

// ---- Partikel layar login (createParticles prototipe: 30 titik)
Alpine.data('partikel', (jumlah = 30) => ({
    init() {
        for (let i = 0; i < jumlah; i++) {
            const s = document.createElement('span');
            const ukuran = (2 + Math.random() * 4) + 'px';
            s.style.left = Math.random() * 100 + '%';
            s.style.bottom = '-10px';
            s.style.width = ukuran;
            s.style.height = ukuran;
            s.style.animationDuration = (6 + Math.random() * 10) + 's';
            s.style.animationDelay = (Math.random() * 8) + 's';
            this.$el.appendChild(s);
        }
    },
}));

// ---- Grafik Pagu vs Realisasi (bar) — konfigurasi identik prototipe
Alpine.data('grafikBar', (data) => ({
    grafik: null,
    init() {
        this.grafik = new Chart(this.$refs.kanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    { label: 'Pagu', data: data.pagu, backgroundColor: 'rgba(0,230,138,0.25)', borderColor: '#00e68a', borderWidth: 1, borderRadius: 6 },
                    { label: 'Realisasi', data: data.realisasi, backgroundColor: 'rgba(255,92,92,0.25)', borderColor: '#ff5c5c', borderWidth: 1, borderRadius: 6 },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: TOKEN.fgMuted, font: { size: 11, family: TOKEN.font } } },
                    tooltip: { callbacks: { label: (c) => c.dataset.label + ': ' + rupiah(c.parsed.y) } },
                },
                scales: {
                    x: { ticks: { color: TOKEN.fgMuted, font: { size: 10 } }, grid: { color: TOKEN.grid } },
                    y: { ticks: { color: TOKEN.fgMuted, font: { size: 10 }, callback: (v) => rupiahSingkat(v) }, grid: { color: TOKEN.grid } },
                },
            },
        });
    },
    destroy() { this.grafik?.destroy(); },
}));

// ---- Grafik Distribusi Pagu (doughnut) — konfigurasi identik prototipe
Alpine.data('grafikDoughnut', (data) => ({
    grafik: null,
    init() {
        const warna = TOKEN.palet.slice(0, data.labels.length);
        this.grafik = new Chart(this.$refs.kanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{ data: data.data, backgroundColor: warna.map((c) => c + '44'), borderColor: warna, borderWidth: 2, hoverOffset: 8 }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '60%',
                plugins: {
                    legend: { position: 'bottom', labels: { color: TOKEN.fgMuted, font: { size: 10, family: TOKEN.font }, padding: 12, usePointStyle: true, pointStyleWidth: 10 } },
                    tooltip: { callbacks: { label: (c) => c.label + ': ' + rupiah(c.parsed) } },
                },
            },
        });
    },
    destroy() { this.grafik?.destroy(); },
}));

// ---- Grafik garis tren serapan (F11): realisasi, target, tahun lalu — % kumulatif per bulan
Alpine.data('grafikGaris', (data) => ({
    grafik: null,
    init() {
        const seri = [
            { label: 'Realisasi ' + data.tahun, data: data.realisasi, borderColor: '#00e68a', backgroundColor: 'rgba(0,230,138,0.12)', fill: true, tension: 0.3, borderWidth: 2 },
            { label: 'Target ' + data.tahun, data: data.target, borderColor: '#00b4d8', borderDash: [6, 4], tension: 0.3, borderWidth: 2, pointRadius: 2 },
        ];
        if (data.tahunLalu) seri.push({ label: 'Realisasi ' + data.tahunLalu, data: data.lalu, borderColor: '#ffb347', tension: 0.3, borderWidth: 2, pointRadius: 2 });
        this.grafik = new Chart(this.$refs.kanvas.getContext('2d'), {
            type: 'line',
            data: { labels: data.labels, datasets: seri },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: TOKEN.fgMuted, font: { size: 11, family: TOKEN.font } } },
                    tooltip: { callbacks: { label: (c) => c.dataset.label + ': ' + c.parsed.y + '%' } },
                },
                scales: {
                    x: { ticks: { color: TOKEN.fgMuted, font: { size: 10 } }, grid: { color: TOKEN.grid } },
                    y: { min: 0, suggestedMax: 100, ticks: { color: TOKEN.fgMuted, font: { size: 10 }, callback: (v) => v + '%' }, grid: { color: TOKEN.grid } },
                },
            },
        });
    },
    destroy() { this.grafik?.destroy(); },
}));

// ---- Form anggaran (layar Anggaran): satu state utk modal Program/Kegiatan/Sub Kegiatan
// detail: { jenis: 'program'|'kegiatan'|'sub', mode: 'tambah'|'ubah', id, induk, nilai: {...} }
Alpine.data('anggaranForm', (awal = null) => ({
    jenis: null, mode: 'tambah', id: null, induk: null,
    nilai: { kode: '', nama: '', pagu: '', bidang_id: '', sumber_dana_id: '', pptk: '', urutan: 0 },
    init() {
        if (awal && awal.jenis) {
            this.buka(awal);
            this.$nextTick(() => this.$dispatch('buka-modal', 'form-' + awal.jenis));
        }
    },
    buka(d) {
        this.jenis = d.jenis; this.mode = d.mode; this.id = d.id ?? null; this.induk = d.induk ?? null;
        this.nilai = Object.assign({ kode: '', nama: '', pagu: '', bidang_id: '', sumber_dana_id: '', pptk: '', urutan: 0 }, d.nilai || {});
    },
    bukaModal(d) {
        this.buka(d);
        this.$dispatch('buka-modal', 'form-' + d.jenis);
    },
    // ubah/hapus butuh konfirmasi sandi dulu (docs/26)
    ubah(d) {
        this.$dispatch('buka-konfirmasi', { id: 'anggaran', aksi: () => this.bukaModal(Object.assign({ mode: 'ubah' }, d)) });
    },
    hapus(formId) {
        const form = document.getElementById(formId);
        this.$dispatch('buka-konfirmasi', { id: 'anggaran', aksi: form });
    },
    action(base) {
        return this.mode === 'ubah' ? base + '/' + this.id : base;
    },
    get judul() {
        const n = { program: 'Program', kegiatan: 'Kegiatan', sub: 'Sub Kegiatan' }[this.jenis] || '';
        return (this.mode === 'ubah' ? 'Ubah ' : 'Tambah ') + n;
    },
}));

window.Alpine = Alpine;
Alpine.start();
