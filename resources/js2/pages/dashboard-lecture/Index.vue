<template>
    <div class="grid gap-6">
        <div class="grid gap-6 lg:grid-cols-[360px_1fr]">
            <div class="grid gap-6">
                <ProfileCard
                    :user="user"
                    :info-cards="dataRaw.info_cards"
                    :schedules-count="schedules.length"
                    :score-pending="scorePending"
                    @edit-profile="openProfileModal"
                />
                <QuickLinkCard
                    title="Kurikulum & Standar Operasional"
                    subtitle="Akses dokumen resmi"
                    link-url="https://drive.google.com/drive/folders/1LoCN_taJgB37eHhdc9HY1IplL8N5jjBV?usp=sharing"
                />
                <AgendaCard
                    :schedules="schedules"
                />
            </div>

            <div class="grid gap-6">
                <ScoringCard
                    :items="dataContent"
                    @open-file="openFileModal"
                />
                <ExamScoringCard
                    v-if="dataContentAll.length"
                    :items="dataContentAll"
                    @open-file="openFileModal"
                />
            </div>
        </div>

        <FileDetailModal
            :open="detailModalOpen"
            :file="detailFile"
            @close="detailModalOpen = false"
        />
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
import ProfileCard from './ProfileCard.vue';
import QuickLinkCard from './QuickLinkCard.vue';
import AgendaCard from './AgendaCard.vue';
import ScoringCard from './ScoringCard.vue';
import ExamScoringCard from './ExamScoringCard.vue';
import FileDetailModal from './FileDetailModal.vue';
import ProfileModal from './ProfileModal.vue';

export default {
    components: {
        ProfileCard,
        QuickLinkCard,
        AgendaCard,
        ScoringCard,
        ExamScoringCard,
        FileDetailModal,
        ProfileModal,
    },
    data() {
        return {
            dataContent: [],
            dataContentAll: [],
            schedules: [],
            detailFile: {},
            detailModalOpen: false,
            profileModalOpen: false,
            updatingProfile: false,
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
    computed: {
        scorePending() {
            if (!Array.isArray(this.dataContent)) {
                return 0;
            }
            return this.dataContent.filter((item) => item && item.score == null).length;
        },
    },
    created() {
        this.loadData();
        this.loadDataAll();
        this.loadSchedule();
        this.loadUser();
    },
    methods: {
        loadData() {
            return Repository.get('/cmsd/get-open-stase-task')
                .then((response) => {
                    const data = response && response.data ? response.data : [];
                    this.dataContent = Array.isArray(data) ? data : [];
                })
                .catch(() => {
                    this.dataContent = [];
                });
        },
        loadDataAll() {
            return Repository.get('/cmsd/get-open-stase-task-all')
                .then((response) => {
                    const data = response && response.data ? response.data : [];
                    this.dataContentAll = Array.isArray(data) ? data : [];
                })
                .catch(() => {
                    this.dataContentAll = [];
                });
        },
        loadSchedule() {
            return Repository.get('/cmsd/get-schedule')
                .then((response) => {
                    const data = response && response.data ? response.data : [];
                    this.schedules = Array.isArray(data) ? data : [];
                })
                .catch(() => {
                    this.schedules = [];
                });
        },
        loadUser() {
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
                    this.dataRaw.image_url = profile.image ? `/storage/${profile.image}` : '';
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
                });
        },
        openFileModal(file) {
            this.detailFile = file || {};
            this.detailModalOpen = true;
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
                })
                .finally(() => {
                    this.updatingProfile = false;
                });
        },
        getImage(event) {
            const file = event && event.target ? event.target.files[0] : null;
            if (!file) {
                return;
            }

            if (file.size > 4200000) {
                this.user.image = '';
                return;
            }

            const reader = new FileReader();
            reader.onloadend = () => {
                this.user.image = reader.result;
            };
            this.dataRaw.image_url = URL.createObjectURL(file);
            reader.readAsDataURL(file);
        },
    },
};
</script>
