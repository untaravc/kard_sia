<template>
    <Modal
        :open="open"
        title="Perbarui Profil"
        eyebrow="Profil Dosen"
        size="lg"
        @close="$emit('close')"
    >
        <form class="grid gap-4" @submit.prevent="$emit('submit')">
            <div class="grid gap-2">
                <label class="text-xs font-semibold text-muted">Foto</label>
                <input
                    type="file"
                    class="rounded-xl border border-border bg-white px-3 py-2 text-sm"
                    @change="$emit('file-change', $event)"
                />
                <div class="flex items-center gap-3">
                    <img
                        v-if="imageUrl"
                        :src="imageUrl"
                        alt="Preview"
                        class="h-16 w-16 rounded-xl object-cover"
                    />
                    <img
                        v-else
                        src="/assets/images/dr_default.jpeg"
                        alt="Default"
                        class="h-16 w-16 rounded-xl object-cover"
                    />
                </div>
            </div>
            <div class="grid gap-2">
                <label class="text-xs font-semibold text-muted">Email</label>
                <input
                    v-model.trim="form.email"
                    type="email"
                    class="rounded-xl border border-border bg-white px-3 py-2 text-sm"
                />
            </div>
            <div class="grid gap-2">
                <label class="text-xs font-semibold text-muted">Nama</label>
                <input
                    v-model.trim="form.name"
                    type="text"
                    class="rounded-xl border border-border bg-white px-3 py-2 text-sm"
                />
            </div>
            <div class="grid gap-2">
                <label class="text-xs font-semibold text-muted">Password Baru</label>
                <input
                    v-model="form.password"
                    type="password"
                    class="rounded-xl border border-border bg-white px-3 py-2 text-sm"
                    autocomplete="new-password"
                />
            </div>
            <div class="grid gap-2">
                <label class="text-xs font-semibold text-muted">Konfirmasi Password Baru</label>
                <input
                    v-model="form.password_confirmation"
                    type="password"
                    class="rounded-xl border border-border bg-white px-3 py-2 text-sm"
                />
            </div>
            <div class="grid gap-2">
                <label class="text-xs font-semibold text-muted">No Telepon</label>
                <input
                    v-model.trim="form.phone"
                    type="text"
                    class="rounded-xl border border-border bg-white px-3 py-2 text-sm"
                />
            </div>
            <div class="grid gap-2">
                <label class="text-xs font-semibold text-muted">Alamat</label>
                <textarea
                    v-model.trim="form.address"
                    class="min-h-[90px] rounded-xl border border-border bg-white px-3 py-2 text-sm"
                ></textarea>
            </div>

            <div class="flex items-center justify-end gap-2">
                <button
                    type="button"
                    class="rounded-xl border border-border px-4 py-2 text-xs font-semibold text-muted"
                    @click="$emit('close')"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="rounded-xl bg-emerald-500 px-4 py-2 text-xs font-semibold text-white"
                    :disabled="submitting"
                >
                    <span v-if="submitting">Menyimpan...</span>
                    <span v-else>Simpan</span>
                </button>
            </div>
        </form>
    </Modal>
</template>

<script>
import Modal from '../../components/Modal.vue';

export default {
    components: {
        Modal,
    },
    props: {
        open: {
            type: Boolean,
            default: false,
        },
        form: {
            type: Object,
            required: true,
        },
        imageUrl: {
            type: String,
            default: '',
        },
        submitting: {
            type: Boolean,
            default: false,
        },
    },
};
</script>
