<template>
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
            <div class="flex w-full flex-col gap-6 lg:max-w-[320px] lg:flex-none">
                <ProfileCard
                    :user="user"
                    @edit-profile="openProfileModal"
                />
                <div class="rounded-2xl border border-border bg-panel p-4 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="grid h-9 w-9 place-items-center rounded-lg bg-sky-100 text-sky-600">
                            <i class="fa fa-calendar-check"></i>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-semibold text-ink">Today's Presence</div>
                            <div class="text-xs text-muted" v-if="todayPresence.checkin">
                                Checked in at {{ todayPresence.checkin }}
                                <span v-if="todayPresence.checkout">- {{ todayPresence.checkout }}</span>
                            </div>
                            <div class="text-xs text-muted" v-else>No presence recorded today.</div>
                        </div>
                    </div>
                    <button class="mt-3 w-full rounded-xl bg-primary px-3 py-2 text-xs font-semibold text-white" type="button">
                        Check In
                    </button>
                </div>
                <QuickLinkCard
                    title="Curriculum & SOP"
                    subtitle="Access official documents"
                    link-url="https://drive.google.com/drive/folders/1LoCN_taJgB37eHhdc9HY1IplL8N5jjBV?usp=sharing"
                />
            </div>

            <div class="flex min-w-0 flex-1 flex-col gap-6">
                <AgendaCard
                    :schedules="schedules"
                    :loading="loadingSchedule"
                    :date="agendaDate"
                    @date-change="handleAgendaDateChange"
                    @open-presence="openPresenceModal"
                />
            </div>
        </div>
        <Modal
            :open="presenceModalOpen"
            title="Agenda Details"
            eyebrow="Check In"
            size="md"
            @close="closePresenceModal"
        >
            <div v-if="selectedSchedule" class="grid gap-3 text-sm">
                <div class="rounded-xl border border-border bg-white px-4 py-3">
                    <div class="font-semibold text-ink">{{ selectedSchedule.name }}</div>
                    <div class="text-xs text-muted">
                        <span v-if="selectedSchedule.speaker"><b>{{ selectedSchedule.speaker }}:</b> </span>
                        <span>{{ selectedSchedule.title }}</span>
                    </div>
                </div>
                <div v-if="selectedSchedule.start_date || selectedSchedule.end_date" class="text-xs text-muted">
                    <span v-if="selectedSchedule.start_date">Start: {{ formatDateTime(selectedSchedule.start_date) }}</span>
                    <span v-if="selectedSchedule.end_date"> • End: {{ formatDateTime(selectedSchedule.end_date) }}</span>
                </div>
                <div v-if="isLatePresence" class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
                    A late check-in will send a notification to the agenda creator.
                </div>
                <div v-if="selectedSchedule.absence" class="text-xs text-emerald-600">
                    You checked in at {{ formatDateTime(selectedSchedule.absence.created_at) }}
                </div>
                <div v-if="presenceError" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ presenceError }}
                </div>
            </div>
            <template #footer>
                <button
                    class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                    type="button"
                    @click="closePresenceModal"
                >
                    Cancel
                </button>
                <button
                    class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white"
                    type="button"
                    :disabled="presenceSubmitting || !selectedSchedule"
                    @click="submitPresence(selectedSchedule && selectedSchedule.id)"
                >
                    {{ presenceSubmitting ? 'Saving...' : 'Check In' }}
                </button>
            </template>
        </Modal>
        <Modal
            :open="profileModalOpen"
            title="Edit Profile"
            eyebrow="Account"
            size="md"
            @close="closeProfileModal"
        >
            <div class="grid gap-4 text-sm">
                <label class="grid gap-2">
                    <span class="text-muted">Profile Photo</span>
                    <input
                        type="file"
                        accept="image/*"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        @change="getImage"
                    />
                </label>
                <div v-if="dataRaw.image_url" class="flex items-center gap-3">
                    <img
                        :src="dataRaw.image_url"
                        alt="Profile preview"
                        class="h-14 w-14 rounded-full border border-border object-cover"
                    />
                    <div class="text-xs text-muted">
                        {{ uploadingImage ? 'Uploading image...' : 'Image ready' }}
                    </div>
                </div>
                <label class="grid gap-2">
                    <span class="text-muted">Name</span>
                    <input
                        v-model="user.name"
                        type="text"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2">
                    <span class="text-muted">Email</span>
                    <input
                        v-model="user.email"
                        type="email"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2">
                    <span class="text-muted">Phone</span>
                    <input
                        v-model="user.phone"
                        type="text"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2">
                    <span class="text-muted">Address</span>
                    <textarea
                        v-model="user.address"
                        rows="3"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    ></textarea>
                </label>
                <label class="grid gap-2">
                    <span class="text-muted">New Password</span>
                    <input
                        v-model="user.password"
                        type="password"
                        autocomplete="new-password"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2">
                    <span class="text-muted">Confirm Password</span>
                    <input
                        v-model="user.password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <div v-if="profileError" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ profileError }}
                </div>
            </div>
            <template #footer>
                <button
                    class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                    type="button"
                    @click="closeProfileModal"
                >
                    Cancel
                </button>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="button"
                    :disabled="updatingProfile || uploadingImage"
                    @click="updateProfile"
                >
                    {{ updatingProfile ? 'Saving...' : 'Save' }}
                </button>
            </template>
        </Modal>
    </div>
</template>

<script>
import Repository from '../../repository';
import { getApps, initializeApp } from 'firebase/app';
import { getDownloadURL, getStorage, ref, uploadBytes } from 'firebase/storage';
import { useFirebaseConfigStore } from '../../stores/firebaseConfig';
import ProfileCard from './ProfileCard.vue';
import QuickLinkCard from './QuickLinkCard.vue';
import AgendaCard from './AgendaCard.vue';
import Modal from '../../components/Modal.vue';

export default {
    components: {
        ProfileCard,
        QuickLinkCard,
        AgendaCard,
        Modal,
    },
    computed: {
        isLatePresence() {
            if (!this.selectedSchedule || !this.selectedSchedule.end_date) {
                return false;
            }
            if (this.selectedSchedule.absence) {
                return false;
            }
            return this.isAfterDate(this.selectedSchedule.end_date);
        },
    },
    data() {
        return {
            user: {
                name: 'Resident Student',
                email: 'student@example.com',
                image: '',
                phone: '',
                address: '',
                password: '',
                password_confirmation: '',
            },
            loadingProfile: false,
            uploadingImage: false,
            firebaseConfigStore: null,
            todayPresence: {
                checkin: '08:12',
                checkout: '16:45',
            },
            schedules: [],
            loadingSchedule: false,
            agendaDate: '',
            presenceModalOpen: false,
            presenceSubmitting: false,
            presenceError: '',
            selectedSchedule: null,
            profileModalOpen: false,
            updatingProfile: false,
            profileError: '',
            dataRaw: {
                image_url: '',
            },
        };
    },
    created() {
        this.agendaDate = this.getDateString(new Date());
        this.loadSchedule();
        this.firebaseConfigStore = useFirebaseConfigStore();
        this.loadUser();
    },
    methods: {
        loadUser() {
            this.loadingProfile = true;
            return Repository.get('/api/student-profile')
                .then((response) => {
                    const payload = response && response.data && response.data.result
                        ? response.data.result
                        : {};
                    const student = payload.student || {};
                    const profile = payload.profile || {};
                    let image = profile.image || '';
                    if (profile.image_link) {
                        image = profile.image_link;
                    } else if (image && !image.startsWith('http')) {
                        image = `/storage/${image}`;
                    }
                    this.user = {
                        ...this.user,
                        ...student,
                        image,
                        phone: profile.phone || '',
                        address: profile.address || '',
                    };
                    this.dataRaw.image_url = image || '';
                })
                .catch(() => {
                    this.user = {
                        name: '',
                        email: '',
                        image: '',
                        phone: '',
                        address: '',
                        password: '',
                        password_confirmation: '',
                    };
                    this.dataRaw.image_url = '';
                })
                .finally(() => {
                    this.loadingProfile = false;
                });
        },
        isAfterDate(value) {
            if (!value) {
                return false;
            }
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return false;
            }
            return Date.now() > date.getTime();
        },
        getDateString(date) {
            const year = date.getFullYear();
            const month = `${date.getMonth() + 1}`.padStart(2, '0');
            const day = `${date.getDate()}`.padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        loadSchedule(date = this.agendaDate) {
            this.loadingSchedule = true;
            return Repository.get('/api/activities-today', {
                params: {
                    date,
                },
            })
                .then((response) => {
                    const data = response && response.data ? response.data.result : [];
                    this.schedules = Array.isArray(data) ? data : [];
                })
                .catch(() => {
                    this.schedules = [];
                })
                .finally(() => {
                    this.loadingSchedule = false;
                });
        },
        handleAgendaDateChange(value) {
            this.agendaDate = value;
            this.loadSchedule(value);
        },
        openPresenceModal(schedule) {
            this.selectedSchedule = schedule || null;
            this.presenceError = '';
            this.presenceModalOpen = true;
        },
        closePresenceModal() {
            this.presenceModalOpen = false;
            this.presenceError = '';
            this.presenceSubmitting = false;
            this.selectedSchedule = null;
        },
        openProfileModal() {
            this.profileModalOpen = true;
            this.profileError = '';
        },
        closeProfileModal() {
            this.profileModalOpen = false;
            this.profileError = '';
            this.updatingProfile = false;
            this.user.password = '';
            this.user.password_confirmation = '';
        },
        updateProfile() {
            if (this.updatingProfile) {
                return;
            }
            this.updatingProfile = true;
            this.profileError = '';
            const payload = {
                email: this.user.email,
                name: this.user.name,
                password: this.user.password,
                password_confirmation: this.user.password_confirmation,
                image: this.user.image,
                phone: this.user.phone,
                address: this.user.address,
            };
            return Repository.patch('/api/student-profile', payload)
                .then(() => {
                    this.closeProfileModal();
                    this.loadUser();
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to update profile.';
                    this.profileError = message;
                })
                .finally(() => {
                    this.updatingProfile = false;
                });
        },
        async getImage(event) {
            const file = event && event.target ? event.target.files[0] : null;
            if (!file) {
                return;
            }

            if (file.size > 4200000) {
                this.user.image = '';
                this.dataRaw.image_url = '';
                return;
            }

            this.uploadingImage = true;
            try {
                const config = await this.firebaseConfigStore.fetchConfig();
                if (!config) {
                    return;
                }

                if (!getApps().length) {
                    initializeApp(config);
                }

                const storage = getStorage();
                const prefix = 'KardiologiFkkmk/Student/Profile';
                const safeName = `${Date.now()}-${file.name}`.replace(/\s+/g, '-');
                const storageRef = ref(storage, `${prefix}/${safeName}`);
                await uploadBytes(storageRef, file);
                const url = await getDownloadURL(storageRef);
                this.user.image = url;
                this.dataRaw.image_url = url;
            } finally {
                this.uploadingImage = false;
            }
        },
        submitPresence(activityId) {
            if (!activityId || this.presenceSubmitting) {
                return;
            }
            this.presenceSubmitting = true;
            this.presenceError = '';
            return Repository.post(`/api/activity-presence/${activityId}`)
                .then((response) => {
                    const presence = response && response.data ? response.data.result : null;
                    if (presence) {
                        this.selectedSchedule.absence = presence;
                        const index = this.schedules.findIndex((item) => item && item.id === this.selectedSchedule.id);
                        if (index !== -1) {
                            this.schedules[index].absence = presence;
                        }
                    }
                    this.presenceModalOpen = false;
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to check in.';
                    this.presenceError = message;
                })
                .finally(() => {
                    this.presenceSubmitting = false;
                });
        },
        formatDateTime(value) {
            if (!value) {
                return '';
            }
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return value;
            }
            return date.toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
            });
        },
    },
};
</script>
