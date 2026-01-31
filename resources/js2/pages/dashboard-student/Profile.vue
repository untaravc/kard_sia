<template>
    <div class="mx-auto flex w-full max-w-md flex-col items-center gap-6">
        <div class="w-full rounded-2xl border border-border bg-panel shadow-sm">
            <div class="relative h-24 rounded-t-2xl bg-gradient-to-br from-cyan-500 to-emerald-400">
                <div class="absolute left-5 top-12">
                    <div
                        v-if="user.image"
                        class="h-16 w-16 rounded-full border-2 border-white bg-cover bg-center"
                        :style="'background-image: url(' + user.image + ')'"
                    ></div>
                    <div
                        v-else
                        class="h-16 w-16 rounded-full border-2 border-white bg-cover bg-center"
                        :style="'background-image: url(/assets/images/dr_default.jpeg)'"
                    ></div>
                </div>
            </div>
            <div class="px-5 pb-5 pt-10">
                <div class="min-w-0">
                    <div class="text-base font-semibold text-ink break-words">{{ user.name }}</div>
                    <div class="text-xs text-muted break-words">{{ user.email }}</div>
                </div>

                <div class="mt-4 rounded-2xl bg-slate-50 p-4">
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Profile Details</div>
                    <div class="mt-3 grid gap-2 text-xs text-muted">
                        <div class="flex items-center justify-between gap-3">
                            <span>Email</span>
                            <span class="font-medium text-ink">{{ user.email || '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Phone</span>
                            <span class="font-medium text-ink">{{ user.phone || '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Address</span>
                            <span class="font-medium text-ink text-right">{{ user.address || '-' }}</span>
                        </div>
                    </div>
                </div>
                <button
                    class="mt-4 w-full rounded-xl border border-border px-3 py-2 text-xs font-semibold text-ink"
                    type="button"
                    @click="openProfileModal"
                >
                    Edit Profile
                </button>
                <router-link
                    class="mt-3 block w-full rounded-xl border border-border px-3 py-2 text-center text-xs font-semibold text-ink"
                    to="/cblu/release-note"
                >
                    Release Notes
                </router-link>
            </div>
        </div>
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
import Modal from '../../components/Modal.vue';
import { uploadFirebaseFile } from '../../upload';

export default {
    components: {
        Modal,
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
            profileModalOpen: false,
            updatingProfile: false,
            profileError: '',
            dataRaw: {
                image_url: '',
            },
        };
    },
    created() {
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
                const prefix = 'KardiologiFkkmk/Student/Profile';
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
