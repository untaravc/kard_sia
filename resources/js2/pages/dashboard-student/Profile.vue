<template>
    <div class="mx-auto flex w-full max-w-md flex-col items-center gap-6">
        <ProfileCard
            class="w-full"
            :user="user"
            @edit-profile="openProfileModal"
        />
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
import Modal from '../../components/Modal.vue';

export default {
    components: {
        ProfileCard,
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
            firebaseConfigStore: null,
            profileModalOpen: false,
            updatingProfile: false,
            profileError: '',
            dataRaw: {
                image_url: '',
            },
        };
    },
    created() {
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
    },
};
</script>
