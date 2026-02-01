<template>
    <div class="mx-auto flex w-full max-w-md flex-col items-center gap-6">
        <div class="relative w-full rounded-2xl border border-border bg-panel shadow-sm">
            <Loading :active="loadingProfile" :is-full-page="false" />
            <div class="h-16 rounded-t-2xl bg-gradient-to-br from-indigo-500 via-sky-500 to-emerald-400"></div>
            <div class="px-5 pb-5">
                <div class="-mt-8 flex items-center gap-3">
                    <div
                        class="h-16 w-16 rounded-xl border-2 border-white bg-cover bg-center"
                        :style="profileStyle"
                    ></div>
                    <div class="flex-1">
                        <div class="text-base font-semibold text-ink">{{ user.name }}</div>
                        <div class="text-xs text-muted">{{ user.email }}</div>
                    </div>
                </div>

                <div class="mt-4 grid gap-2">
                    <button
                        class="rounded-xl border border-border px-3 py-2 text-xs font-semibold text-ink"
                        type="button"
                        @click="openProfileModal"
                    >
                        <i class="fa fa-user-edit text-slate-500"></i>
                        Perbarui Profil
                    </button>
                    <router-link
                        class="rounded-xl border border-border px-3 py-2 text-center text-xs font-semibold text-ink"
                        to="/blu/release-note"
                    >
                        Release Notes
                    </router-link>
                </div>

            </div>
        </div>
        <ProfileModal
            :open="profileModalOpen"
            :form="user"
            :image-url="dataRaw.image_url"
            :submitting="updatingProfile"
            @close="closeProfileModal"
            @submit="updateProfile"
            @file-change="getImage"
        />
    </div>
</template>

<script>
import Repository from '../../repository';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import ProfileModal from './ProfileModal.vue';
import { uploadFirebaseFile } from '../../upload';

export default {
    components: {
        Loading,
        ProfileModal,
    },
    data() {
        return {
            profileModalOpen: false,
            updatingProfile: false,
            uploadingImage: false,
            loadingProfile: false,
            dataRaw: {
                image_url: '',
                info_cards: {
                    avg_scoring: 0,
                    scoring: 0,
                    act_lect: 0,
                    log_pending: 0,
                },
            },
            user: {
                id: '',
                name: '',
                email: '',
                password: '',
                password_confirmation: '',
                image: '',
                phone: '',
                address: '',
            },
        };
    },
    created() {
        this.loadUser();
    },
    computed: {
        profileStyle() {
            if (this.user && this.user.image) {
                if (this.user.image.startsWith('http')) {
                    return `background-image: url(${this.user.image})`;
                }
                return `background-image: url(/storage/${this.user.image})`;
            }
            return 'background-image: url(/assets/images/dr_default.jpeg)';
        },
    },
    methods: {
        loadUser() {
            this.loadingProfile = true;
            return Repository.get('/api/lecture-profile')
                .then((response) => {
                    const payload = response && response.data && response.data.result
                        ? response.data.result
                        : {};
                    const lecture = payload.lecture || {};
                    const profile = payload.profile || {};
                    this.user = {
                        ...this.user,
                        ...lecture,
                        address: profile.address || '',
                        phone: profile.phone || '',
                        image: profile.image || '',
                    };
                    if (profile.image && profile.image.startsWith('http')) {
                        this.dataRaw.image_url = profile.image;
                    } else {
                        this.dataRaw.image_url = profile.image ? `/storage/${profile.image}` : '';
                    }
                })
                .catch(() => {
                    this.user = {
                        id: '',
                        name: '',
                        email: '',
                        password: '',
                        password_confirmation: '',
                        image: '',
                        phone: '',
                        address: '',
                    };
                })
                .finally(() => {
                    this.loadingProfile = false;
                });
        },
        openProfileModal() {
            this.profileModalOpen = true;
        },
        closeProfileModal() {
            this.profileModalOpen = false;
        },
        updateProfile() {
            this.updatingProfile = true;

            const payload = {
                email: this.user.email,
                name: this.user.name,
                password: this.user.password,
                password_confirmation: this.user.password_confirmation,
                image: this.user.image,
                phone: this.user.phone,
                address: this.user.address,
            };

            return Repository.patch('/api/lecture-profile', payload)
                .then(() => {
                    this.profileModalOpen = false;
                    this.loadUser();
                    this.user.password = '';
                    this.user.password_confirmation = '';
                    this.$showToast('Profile updated successfully.');
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
                return;
            }

            this.uploadingImage = true;
            try {
                const prefix = 'KardiologiFkkmk/Lecture/Profile';
                const url = await uploadFirebaseFile({ file, prefix });
                if (url) {
                    this.user.image = url;
                    this.dataRaw.image_url = url;
                }
            } finally {
                this.uploadingImage = false;
            }
        },
    },
};
</script>
