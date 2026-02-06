<template>
    <div class="grid gap-6">
        <section class="rounded-2xl border border-border bg-panel p-6 shadow-sm">
            <Loading :active="loading" :is-full-page="false" />

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-lg font-semibold text-ink">Presensi Harian</h1>
                    <p class="mt-1 text-sm text-muted">Presensi Harian Residen Kardiologi.</p>
                </div>
                <button
                    class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                    type="button"
                    @click="fetchAvailability"
                >
                    Refresh
                </button>
            </div>

            <div v-if="errorMessage" class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                {{ errorMessage }}
            </div>
            <div v-if="infoMessage" class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
                {{ infoMessage }}
            </div>

            <div class="mt-6 grid gap-4 lg:grid-cols-[1.2fr_1fr]">
                <div class="rounded-2xl border border-border bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs uppercase tracking-[0.2em] text-muted">Presensi Harian</div>
                            <div class="mt-2 text-lg font-semibold text-ink">{{ studentName }}</div>
                            <div class="text-sm text-muted">{{ todayLabel }}</div>
                        </div>
                        <span
                            class="rounded-full border px-3 py-1 text-xs font-semibold"
                            :class="statusBadgeClass"
                        >
                            {{ statusBadge }}
                        </span>
                    </div>

                    <div class="mt-5 grid gap-3">
                        <label class="grid gap-2 text-sm">
                            <span class="text-muted">Catatan</span>
                            <input
                                v-model="note"
                                type="text"
                                placeholder="Tambahkan catatan..."
                                class="w-full rounded-xl border border-border bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                            />
                        </label>

                        <div class="grid gap-2">
                            <span class="text-xs text-muted">Foto</span>
                            <input
                                ref="photoInput"
                                type="file"
                                accept="image/*"
                                capture="environment"
                                class="hidden"
                                @change="handlePhotoChange"
                            />
                            <button
                                class="flex w-full items-center justify-between rounded-xl border border-border bg-white px-4 py-3 text-sm font-semibold text-ink transition hover:bg-slate-50"
                                type="button"
                                :disabled="uploadingPhoto"
                                @click="triggerPhoto"
                            >
                                <span class="flex items-center gap-2">
                                    <Icon icon="mdi:camera" class="h-5 w-5 text-muted" />
                                    {{ uploadingPhoto ? 'Mengunggah foto...' : 'Ambil Foto' }}
                                </span>
                                <span class="text-xs text-muted">{{ photoName || 'Belum dipilih' }}</span>
                            </button>
                            <div v-if="photoUrl" class="mt-3 flex items-center gap-3">
                                <img
                                    :src="photoUrl"
                                    alt="Preview"
                                    class="h-20 w-20 rounded-xl border border-border object-cover"
                                />
                                <div class="text-xs text-emerald-600">Foto siap digunakan.</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-2" v-if="available && !completed">
                        <button
                            v-if="isCheckedIn"
                            class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white"
                            type="button"
                            :disabled="submitting"
                            @click="submitPresence('on', 'Mau pulang? Salim dulu..')"
                        >
                            Pulang
                        </button>

                        <template v-else>
                            <button
                                class="w-full rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white"
                                type="button"
                                :disabled="submitting"
                                @click="submitPresence('off', 'Izin?')"
                            >
                                Izin
                            </button>
                            <button
                                class="w-full rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white"
                                type="button"
                                :disabled="submitting"
                                @click="submitPresence('out', 'Dinas Luar?')"
                            >
                                Dinas Luar
                            </button>
                            <button
                                class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white"
                                type="button"
                                :disabled="submitting"
                                @click="submitPresence('on', 'Masuk?')"
                            >
                                Masuk
                            </button>
                        </template>
                    </div>

                    <div class="mt-4 text-xs text-muted" v-if="submitting">
                        Proses pemeriksaan dan kompresi data..
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-ink">Lokasi</div>
                    <div class="mt-2 text-xs text-muted">
                        <span>{{ latLabel }}</span>
                        <span>{{ lngLabel }}</span>
                    </div>
                    <div class="mt-2 text-sm text-ink">
                        <span v-if="distanceLabel">{{ distanceLabel }}</span>
                        <span v-if="distanceFail" class="text-rose-600" v-html="distanceFail"></span>
                    </div>
                    <button
                        class="mt-4 w-full rounded-xl border border-border px-4 py-2 text-sm text-muted"
                        type="button"
                        @click="getLocation"
                    >
                        Re-Position
                    </button>

                    <div class="mt-6 rounded-xl border border-border bg-slate-50 px-4 py-3 text-xs text-muted">
                        <div class="font-semibold text-ink">Status Perangkat</div>
                        <div class="mt-1">Platform: {{ platform || '-' }}</div>
                        <div v-if="completed" class="mt-1 text-emerald-600">Presensi selesai hari ini.</div>
                        <div v-else-if="!available" class="mt-1 text-rose-600">Presensi belum tersedia.</div>
                        <div v-else class="mt-1 text-sky-600">Presensi tersedia.</div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Repository from '../../repository';
import { Icon } from '../../icons';
import { uploadFirebaseFile } from '../../upload';

export default {
    components: {
        Loading,
        Icon,
    },
    data() {
        return {
            loading: false,
            submitting: false,
            available: false,
            completed: false,
            platform: '',
            bg: '',
            student: null,
            note: '',
            photoFile: null,
            photoName: '',
            photoUrl: '',
            uploadingPhoto: false,
            lat: null,
            lng: null,
            accuracy: null,
            distance: null,
            distanceDisplay: '',
            distanceFail: '',
            errorMessage: '',
            infoMessage: '',
        };
    },
    computed: {
        statusBadgeClass() {
            if (this.completed) {
                return 'border-emerald-200 bg-emerald-50 text-emerald-700';
            }
            if (this.isCheckedIn) {
                return 'border-amber-200 bg-amber-50 text-amber-700';
            }
            return 'border-sky-200 bg-sky-50 text-sky-700';
        },
        todayLabel() {
            const now = new Date();
            return now.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        },
        studentName() {
            return this.student && this.student.name ? this.student.name : 'Residen';
        },
        isCheckedIn() {
            return !!(this.student && this.student.today_presence);
        },
        statusBadge() {
            if (this.completed) {
                return 'Selesai';
            }
            return this.isCheckedIn ? 'Pulang' : 'Masuk';
        },
        latLabel() {
            if (this.lat === null) {
                return 'Pastikan GPS kamu menyala :)';
            }
            return `${this.lat}, `;
        },
        lngLabel() {
            if (this.lng === null) {
                return '';
            }
            return `${this.lng}`;
        },
        distanceLabel() {
            if (this.distanceDisplay === '') {
                return '';
            }
            return `${this.distanceDisplay} m dari RSS`;
        },
    },
    created() {
        this.fetchAvailability();
    },
    methods: {
        fetchAvailability() {
            this.loading = true;
            this.errorMessage = '';
            this.infoMessage = '';

            return Repository.get('/api/student-daily-check')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.available = result ? !!result.available : false;
                    this.completed = result ? !!result.completed : false;
                    this.platform = result ? result.platform : '';
                    this.bg = result ? result.bg : '';
                    this.student = result ? result.student : null;

                    if (this.completed) {
                        this.infoMessage = 'Presensi hari ini sudah selesai.';
                    } else if (!this.available) {
                        this.infoMessage = 'Presensi tidak tersedia di perangkat ini.';
                    }

                    if (this.available && !this.completed) {
                        this.getLocation();
                    }
                })
                .catch(() => {
                    this.errorMessage = 'Gagal memuat data presensi.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        triggerPhoto() {
            if (this.$refs.photoInput) {
                this.$refs.photoInput.click();
            }
        },
        async handlePhotoChange(event) {
            const file = event && event.target ? event.target.files[0] : null;
            this.photoFile = file || null;
            this.photoName = file ? file.name : '';
            this.photoUrl = '';

            if (!file) {
                return;
            }

            this.uploadingPhoto = true;
            try {
                const compressed = await this.compressImage(file);
                const prefix = 'KardiologiFkkmk/Student/Presence';
                const url = await uploadFirebaseFile({ file: compressed, prefix });
                this.photoUrl = url || '';
            } finally {
                this.uploadingPhoto = false;
            }
        },
        compressImage(file) {
            return new Promise((resolve) => {
                if (!file || !file.type || !file.type.startsWith('image/')) {
                    resolve(file);
                    return;
                }

                const img = new Image();
                img.onload = () => {
                    const maxWidth = 1200;
                    const maxHeight = 1200;
                    let { width, height } = img;

                    const scale = Math.min(1, maxWidth / width, maxHeight / height);
                    width = Math.round(width * scale);
                    height = Math.round(height * scale);

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    if (!ctx) {
                        resolve(file);
                        return;
                    }

                    ctx.drawImage(img, 0, 0, width, height);
                    canvas.toBlob(
                        (blob) => {
                            if (!blob) {
                                resolve(file);
                                return;
                            }
                            const baseName = file.name.replace(/\.[^/.]+$/, '');
                            const webpName = `${baseName}.webp`;
                            const newFile = new File([blob], webpName, { type: 'image/webp' });
                            resolve(newFile);
                        },
                        'image/webp',
                        0.7
                    );
                };
                img.onerror = () => resolve(file);
                img.src = URL.createObjectURL(file);
            });
        },
        submitPresence(status, confirmText) {
            if (this.submitting || !this.available || this.completed || this.uploadingPhoto) {
                return;
            }

            if (confirmText && !window.confirm(confirmText)) {
                return;
            }

            this.submitting = true;
            this.errorMessage = '';
            this.infoMessage = 'Sedang memproses presensi...';

            const finalize = () => this.performSubmit(status);

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (val) => {
                        this.setLocation(val);
                        finalize();
                    },
                    () => {
                        this.infoMessage = 'Gagal mengambil lokasi, melanjutkan submit...';
                        setTimeout(finalize, 1500);
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            } else {
                finalize();
            }
        },
        performSubmit(status) {
            const payload = {
                note: this.note || '',
                status,
                photo_url: this.photoUrl || '',
                lat: this.lat,
                lng: this.lng,
                accuracy: this.accuracy,
                distance: this.distance,
            };

            return Repository.post('/api/student-daily', payload)
                .then(() => {
                    this.note = '';
                    this.photoFile = null;
                    this.photoName = '';
                    this.photoUrl = '';
                    this.$showToast('Presensi tersimpan.');
                    this.fetchAvailability();
                })
                .catch(() => {
                    this.errorMessage = 'Gagal menyimpan presensi.';
                })
                .finally(() => {
                    this.submitting = false;
                    this.infoMessage = '';
                });
        },
        getLocation() {
            if (!navigator.geolocation) {
                this.distanceFail = 'Geolocation tidak tersedia.';
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (val) => {
                    this.setLocation(val);
                },
                () => {
                    this.distanceFail = 'Akurasi GPS rendah, pastikan GPS menyala.';
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        },
        setLocation(val) {
            const currentLat = val.coords.latitude;
            const currentLng = val.coords.longitude;
            const accuracy = val.coords.accuracy;
            const distance = Math.floor(this.calcCrow(-7.7684412, 110.3721119, currentLat, currentLng) * 1000);
            const distanceDisplay = (distance - 200) > 0 ? distance - 200 : 0;

            this.lat = currentLat.toFixed(6);
            this.lng = currentLng.toFixed(6);
            this.accuracy = accuracy;
            this.distance = distance;

            if (accuracy < 500) {
                this.distanceFail = '';
                this.distanceDisplay = distanceDisplay;
            } else {
                this.distanceDisplay = '';
                this.distanceFail = `Akurasi GPS rendah, pastikan GPS menyala <a href="https://www.google.com/maps/@${currentLat},${currentLng},15z" style="color: #fabb00">Periksa disini</a>`;
            }
        },
        calcCrow(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = this.toRad(lat2 - lat1);
            const dLon = this.toRad(lon2 - lon1);
            const rLat1 = this.toRad(lat1);
            const rLat2 = this.toRad(lat2);

            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2)
                + Math.sin(dLon / 2) * Math.sin(dLon / 2) * Math.cos(rLat1) * Math.cos(rLat2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        },
        toRad(value) {
            return value * Math.PI / 180;
        },
    },
};
</script>
