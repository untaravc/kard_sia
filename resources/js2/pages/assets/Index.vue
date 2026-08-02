<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Asset Management</div>
                <h1 class="text-2xl font-semibold text-ink">Assets</h1>
            </div>
            <button
                class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                type="button"
                @click="openCreate"
            >
                Add Asset
            </button>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="text-xs text-muted">Keyword</label>
                    <input
                        v-model.trim="filters.keyword"
                        type="text"
                        placeholder="Search number, name, brand..."
                        @keyup.enter="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="flex-1 min-w-[180px]">
                    <label class="text-xs text-muted">Owner</label>
                    <select
                        v-model="filters.owner"
                        @change="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">All</option>
                        <option v-for="option in ownerOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
                <div class="flex-1 min-w-[180px]">
                    <label class="text-xs text-muted">Category</label>
                    <select
                        v-model="filters.category"
                        @change="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">All</option>
                        <option v-for="option in categoryOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
                <div class="flex-1 min-w-[160px]">
                    <label class="text-xs text-muted">Status</label>
                    <select
                        v-model="filters.status"
                        @change="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">All</option>
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button
                        class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                        type="button"
                        @click="applyFilter"
                    >
                        Search
                    </button>
                    <button
                        class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                        type="button"
                        @click="resetFilter"
                    >
                        Reset
                    </button>
                </div>
            </div>
        </section>

        <section class="relative rounded-2xl border border-border bg-panel">
            <Loading :active="loading" :is-full-page="false" />
            <div class="flex items-center justify-between border-b border-border px-5 py-4">
                <div class="font-semibold">Assets</div>
                <div class="text-xs text-muted" v-if="pagination.total">
                    {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }}
                </div>
            </div>
            <div
                v-if="errorMessage && !modalOpen"
                class="border-b border-rose-100 bg-rose-50 px-5 py-3 text-xs text-rose-600"
            >
                {{ errorMessage }}
            </div>
            <div class="divide-y divide-border">
                <div v-if="!loading && assets.length === 0" class="px-5 py-6 text-sm text-muted">
                    No assets found.
                </div>
                <div
                    v-for="(asset, index) in assets"
                    :key="asset.id"
                    class="flex flex-wrap items-center gap-3 px-5 py-4"
                >
                    <div class="w-8 text-sm font-semibold text-muted">
                        {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                    </div>
                    <div class="h-12 w-12 shrink-0 overflow-hidden rounded-lg border border-border bg-surface">
                        <img
                            v-if="asset.photo_url"
                            :src="asset.photo_url"
                            class="h-full w-full cursor-pointer object-cover"
                            @click="openImage(asset.photo_url)"
                        />
                        <div v-else class="flex h-full w-full items-center justify-center text-[10px] text-muted">
                            No img
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <div class="font-semibold text-ink">{{ asset.name }}</div>
                            <span v-if="asset.number" class="text-xs text-muted">#{{ asset.number }}</span>
                            <span v-if="asset.status" class="text-xs text-muted">• {{ labelFor(statusOptions, asset.status) }}</span>
                        </div>
                        <div class="text-xs text-muted">
                            <span v-if="asset.category">{{ labelFor(categoryOptions, asset.category) }}</span>
                            <span v-if="asset.owner">• {{ labelFor(ownerOptions, asset.owner) }}</span>
                            <span v-if="asset.brand">• {{ asset.brand }}</span>
                            <span v-if="asset.location">• {{ asset.location }}</span>
                            <span v-if="asset.price">• {{ formatPrice(asset.price) }}</span>
                        </div>
                    </div>
                    <div class="relative action-dropdown">
                        <button
                            class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                            type="button"
                            @click.stop="toggleActionMenu(asset.id)"
                        >
                            Actions
                        </button>
                        <div
                            v-if="actionMenuOpenId === asset.id"
                            class="absolute right-0 z-10 mt-2 w-36 rounded-xl border border-border bg-white p-1 shadow-lg"
                        >
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                type="button"
                                @click="handleAction('detail', asset)"
                            >
                                Detail
                            </button>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                type="button"
                                @click="handleAction('addLog', asset)"
                            >
                                Tambah Riwayat
                            </button>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                type="button"
                                @click="handleAction('edit', asset)"
                            >
                                Edit
                            </button>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-rose-600 hover:bg-rose-50"
                                type="button"
                                @click="handleAction('delete', asset)"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between border-t border-border px-5 py-4 text-xs text-muted">
                <button
                    class="rounded-lg border border-border px-3 py-1.5"
                    type="button"
                    :disabled="pagination.current_page <= 1"
                    @click="changePage(pagination.current_page - 1)"
                >
                    Prev
                </button>
                <div>Page {{ pagination.current_page || 1 }} / {{ pagination.last_page || 1 }}</div>
                <button
                    class="rounded-lg border border-border px-3 py-1.5"
                    type="button"
                    :disabled="pagination.current_page >= pagination.last_page"
                    @click="changePage(pagination.current_page + 1)"
                >
                    Next
                </button>
            </div>
        </section>

        <Modal
            :open="modalOpen"
            :title="editMode ? 'Edit Asset' : 'Create Asset'"
            :eyebrow="editMode ? 'Update asset' : 'New asset'"
            size="lg"
            @close="closeModal"
        >
            <form class="grid gap-4 md:grid-cols-2" @submit.prevent="submitForm">
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Number</span>
                    <input
                        v-model.trim="form.number"
                        type="text"
                        disabled
                        placeholder="Auto-generated"
                        class="w-full cursor-not-allowed rounded-xl border border-border bg-slate-100 px-3 py-2 text-sm text-muted focus:outline-none"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Name</span>
                    <input
                        v-model.trim="form.name"
                        type="text"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Owner</span>
                    <select
                        v-model="form.owner"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">-</option>
                        <option v-for="option in ownerOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Category</span>
                    <select
                        v-model="form.category"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">-</option>
                        <option v-for="option in categoryOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Status</span>
                    <select
                        v-model="form.status"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">-</option>
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Brand</span>
                    <input
                        v-model.trim="form.brand"
                        type="text"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Supplier</span>
                    <input
                        v-model.trim="form.supplier"
                        type="text"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Price</span>
                    <money
                        v-model="form.price"
                        v-bind="moneyConfig"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Estimate Month</span>
                    <input
                        v-model.number="form.estimate_month"
                        type="number"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Purchase Date</span>
                    <input
                        v-model="form.purchase_date"
                        type="date"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Warranty Until</span>
                    <input
                        v-model="form.warranty_until"
                        type="date"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Location</span>
                    <input
                        v-model.trim="form.location"
                        type="text"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Study Program</span>
                    <select
                        v-model="form.study_program_code"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">-</option>
                        <option v-for="option in studyPrograms" :key="option.id" :value="option.code">
                            {{ option.name }}
                        </option>
                    </select>
                </label>
                <div class="grid gap-2 text-sm md:col-span-2">
                    <span class="text-muted">Photo</span>
                    <input
                        v-if="!form.photo_url"
                        ref="photoInput"
                        type="file"
                        accept="image/jpg,image/jpeg,image/png"
                        :disabled="photoUploading"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm"
                        @change="uploadPhoto"
                    />
                    <div class="text-xs text-muted" v-if="!form.photo_url">
                        File type: jpg, jpeg, png. Max size: 2MB.
                    </div>
                    <div class="mt-1 flex items-center gap-4">
                        <div class="flex h-32 w-32 items-center justify-center overflow-hidden rounded-xl border border-border bg-surface">
                            <img v-if="form.photo_url" :src="form.photo_url" class="h-full w-full object-contain" />
                            <div v-else class="text-xs text-muted">No photo</div>
                        </div>
                        <div class="text-xs text-muted" v-if="photoUploading">Uploading...</div>
                        <button
                            v-if="form.photo_url && !photoUploading"
                            type="button"
                            class="rounded-xl border border-rose-200 px-3 py-1.5 text-xs text-rose-600 hover:bg-rose-50"
                            @click="confirmErasePhoto"
                        >
                            Erase
                        </button>
                    </div>
                    <span v-if="photoError" class="text-xs text-rose-600">{{ photoError }}</span>
                </div>
                <label class="grid gap-2 text-sm md:col-span-2">
                    <span class="text-muted">Description</span>
                    <textarea
                        v-model.trim="form.description"
                        rows="3"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    ></textarea>
                </label>
                <div v-if="errorMessage" class="md:col-span-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ errorMessage }}
                </div>
                <button
                    class="md:col-span-2 rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
                    type="submit"
                    :disabled="submitting || photoUploading"
                >
                    {{ submitting ? 'Saving...' : editMode ? 'Update Asset' : 'Create Asset' }}
                </button>
            </form>
        </Modal>

        <Modal
            :open="erasePhotoModalOpen"
            title="Erase Photo"
            eyebrow="Confirmation"
            size="sm"
            @close="erasePhotoModalOpen = false"
        >
            <div class="grid gap-4">
                <p class="text-sm text-muted">
                    Are you sure you want to remove this photo? This cannot be undone.
                </p>
                <div class="flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                        @click="erasePhotoModalOpen = false"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-medium text-white"
                        @click="erasePhoto"
                    >
                        Erase
                    </button>
                </div>
            </div>
        </Modal>

        <Modal
            :open="imageModalOpen"
            title="Asset Photo"
            eyebrow="Preview"
            size="lg"
            @close="closeImage"
        >
            <div class="flex items-center justify-center">
                <img v-if="imageModalUrl" :src="imageModalUrl" class="max-h-[70vh] w-auto rounded-xl object-contain" />
            </div>
        </Modal>

        <Modal
            :open="logModalOpen"
            title="Tambah Riwayat"
            :eyebrow="logAsset ? logAsset.name : 'Asset'"
            size="md"
            @close="closeLogModal"
        >
            <form class="grid gap-4" @submit.prevent="submitLog">
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Status</span>
                    <select
                        v-model="logForm.status"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">-</option>
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Location</span>
                    <input
                        v-model.trim="logForm.location"
                        type="text"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Note</span>
                    <textarea
                        v-model.trim="logForm.note"
                        rows="3"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    ></textarea>
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Study Program</span>
                    <select
                        v-model="logForm.study_program_code"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">-</option>
                        <option v-for="option in studyPrograms" :key="option.id" :value="option.code">
                            {{ option.name }}
                        </option>
                    </select>
                </label>
                <div class="grid gap-2 text-sm">
                    <span class="text-muted">Photos</span>
                    <div class="flex flex-wrap items-center gap-2">
                        <input
                            type="file"
                            accept="image/jpg,image/jpeg,image/png"
                            multiple
                            :disabled="logPhotoUploading"
                            class="flex-1 rounded-xl border border-border bg-white px-3 py-2 text-sm"
                            @change="uploadLogPhotos"
                        />
                        <button
                            v-if="isMobile"
                            type="button"
                            :disabled="logPhotoUploading"
                            class="rounded-xl border border-border px-3 py-2 text-sm text-muted disabled:opacity-60"
                            @click="triggerLogCamera"
                        >
                            📷 Camera
                        </button>
                        <input
                            ref="logCameraInput"
                            type="file"
                            accept="image/*"
                            capture="environment"
                            class="hidden"
                            @change="uploadLogPhotos"
                        />
                    </div>
                    <div class="text-xs text-muted">File type: jpg, jpeg, png. Max size: 2MB each.</div>
                    <div class="text-xs text-muted" v-if="logPhotoUploading">Uploading...</div>
                    <div v-if="logForm.photo_urls.length" class="mt-1 flex flex-wrap gap-3">
                        <div
                            v-for="(url, index) in logForm.photo_urls"
                            :key="index"
                            class="relative h-20 w-20 overflow-hidden rounded-xl border border-border bg-surface"
                        >
                            <img :src="url" class="h-full w-full cursor-pointer object-cover" @click="openImage(url)" />
                            <button
                                type="button"
                                class="absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-rose-600 text-xs leading-none text-white"
                                @click="removeLogPhoto(index)"
                            >
                                ×
                            </button>
                        </div>
                    </div>
                </div>
                <div v-if="logError" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ logError }}
                </div>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
                    type="submit"
                    :disabled="logSubmitting || logPhotoUploading"
                >
                    {{ logSubmitting ? 'Saving...' : 'Simpan Riwayat' }}
                </button>
            </form>
        </Modal>

        <Modal
            :open="detailModalOpen"
            title="Detail Riwayat"
            :eyebrow="detailAsset ? detailAsset.name : 'Asset'"
            size="lg"
            @close="closeDetail"
        >
            <div class="relative grid gap-5">
                <Loading :active="detailLoading" :is-full-page="false" />
                <div v-if="detailAsset" class="grid gap-4 sm:grid-cols-[8rem,1fr]">
                    <div class="h-32 w-32 overflow-hidden rounded-xl border border-border bg-surface">
                        <img
                            v-if="detailAsset.photo_url"
                            :src="detailAsset.photo_url"
                            class="h-full w-full cursor-pointer object-cover"
                            @click="openImage(detailAsset.photo_url)"
                        />
                        <div v-else class="flex h-full w-full items-center justify-center text-xs text-muted">
                            No photo
                        </div>
                    </div>
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm sm:grid-cols-3">
                        <div>
                            <dt class="text-xs text-muted">Number</dt>
                            <dd class="text-ink">{{ detailAsset.number || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted">Name</dt>
                            <dd class="text-ink">{{ detailAsset.name || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted">Owner</dt>
                            <dd class="text-ink">{{ labelFor(ownerOptions, detailAsset.owner) || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted">Category</dt>
                            <dd class="text-ink">{{ labelFor(categoryOptions, detailAsset.category) || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted">Status</dt>
                            <dd class="text-ink">{{ labelFor(statusOptions, detailAsset.status) || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted">Brand</dt>
                            <dd class="text-ink">{{ detailAsset.brand || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted">Supplier</dt>
                            <dd class="text-ink">{{ detailAsset.supplier || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted">Price</dt>
                            <dd class="text-ink">{{ detailAsset.price ? formatPrice(detailAsset.price) : '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted">Estimate Month</dt>
                            <dd class="text-ink">{{ detailAsset.estimate_month || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted">Purchase Date</dt>
                            <dd class="text-ink">{{ detailAsset.purchase_date ? formatDate(detailAsset.purchase_date) : '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted">Warranty Until</dt>
                            <dd class="text-ink">{{ detailAsset.warranty_until ? formatDate(detailAsset.warranty_until) : '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted">Location</dt>
                            <dd class="text-ink">{{ detailAsset.location || '-' }}</dd>
                        </div>
                        <div class="col-span-2 sm:col-span-3">
                            <dt class="text-xs text-muted">Description</dt>
                            <dd class="text-ink whitespace-pre-line">{{ detailAsset.description || '-' }}</dd>
                        </div>
                    </dl>
                </div>

                <div>
                    <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted">Riwayat</div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-border text-xs uppercase tracking-wide text-muted">
                                    <th class="px-3 py-2">#</th>
                                    <th class="px-3 py-2">Tanggal</th>
                                    <th class="px-3 py-2">Status</th>
                                    <th class="px-3 py-2">Location</th>
                                    <th class="px-3 py-2">Note</th>
                                </tr>
                            </thead>
                        <tbody>
                            <tr v-if="!detailLoading && detailLogs.length === 0">
                                <td class="px-3 py-4 text-sm text-muted" colspan="5">
                                    Belum ada riwayat.
                                </td>
                            </tr>
                            <tr
                                v-for="(log, index) in detailLogs"
                                :key="log.id"
                                class="border-b border-border align-top"
                            >
                                <td class="px-3 py-2 text-muted">{{ index + 1 }}</td>
                                <td class="px-3 py-2 whitespace-nowrap">{{ formatDateTime(log.created_at) }}</td>
                                <td class="px-3 py-2">{{ labelFor(statusOptions, log.status) }}</td>
                                <td class="px-3 py-2">{{ log.location || '-' }}</td>
                                <td class="px-3 py-2">{{ log.note || '-' }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Modal from '../../components/Modal.vue';
import Repository from '../../repository';
import { uploadFirebaseFile } from '../../upload';
import persistFilters from '../../mixins/persistFilters';

export default {
    components: {
        Loading,
        Modal,
    },
    mixins: [persistFilters('assets')],
    data() {
        return {
            baseUrl: '/api/assets',
            assets: [],
            pagination: {},
            ownerOptions: [],
            categoryOptions: [],
            statusOptions: [],
            studyPrograms: [],
            filters: {
                keyword: '',
                owner: '',
                category: '',
                status: '',
                page: 1,
            },
            form: this.emptyForm(),
            editMode: false,
            modalOpen: false,
            loading: false,
            submitting: false,
            errorMessage: '',
            actionMenuOpenId: null,
            photoUploading: false,
            photoError: '',
            erasePhotoModalOpen: false,
            imageModalOpen: false,
            imageModalUrl: '',
            logModalOpen: false,
            logAsset: null,
            logForm: {
                status: '',
                location: '',
                note: '',
                photo_urls: [],
                study_program_code: '',
            },
            logSubmitting: false,
            logError: '',
            logPhotoUploading: false,
            detailModalOpen: false,
            detailAsset: null,
            detailLogs: [],
            detailLoading: false,
            moneyConfig: {
                prefix: 'Rp ',
                thousands: '.',
                decimal: ',',
                precision: 0,
                masked: false,
            },
        };
    },
    computed: {
        isMobile() {
            if (typeof navigator === 'undefined') {
                return false;
            }
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        },
    },
    created() {
        this.fetchProperties();
        this.fetchStudyPrograms();
        this.fetchAssets();
    },
    mounted() {
        document.addEventListener('click', this.handleDocumentClick);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.handleDocumentClick);
    },
    methods: {
        emptyForm() {
            return {
                id: null,
                number: '',
                owner: '',
                name: '',
                price: 0,
                estimate_month: null,
                supplier: '',
                photo_url: '',
                brand: '',
                purchase_date: '',
                category: '',
                status: '',
                description: '',
                warranty_until: '',
                location: '',
                study_program_code: '',
            };
        },
        fetchStudyPrograms() {
            return Repository.get('/api/study-program-list')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.studyPrograms = Array.isArray(result) ? result : [];
                })
                .catch(() => {
                    this.studyPrograms = [];
                });
        },
        openImage(url) {
            this.imageModalUrl = url;
            this.imageModalOpen = true;
        },
        openDetail(asset) {
            this.detailAsset = asset;
            this.detailLogs = [];
            this.detailModalOpen = true;
            this.fetchLogs(asset.id);
        },
        closeDetail() {
            this.detailModalOpen = false;
            this.detailAsset = null;
            this.detailLogs = [];
        },
        fetchLogs(assetId) {
            this.detailLoading = true;

            return Repository.get(`/api/assets-logs/${assetId}`)
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];
                    this.detailLogs = data;
                })
                .catch(() => {
                    this.detailLogs = [];
                })
                .finally(() => {
                    this.detailLoading = false;
                });
        },
        formatDateTime(value) {
            if (!value) {
                return '-';
            }
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return value;
            }
            return date.toLocaleString('id-ID');
        },
        formatDate(value) {
            if (!value) {
                return '-';
            }
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return value;
            }
            return date.toLocaleDateString('id-ID');
        },
        openAddLog(asset) {
            this.logAsset = asset;
            this.logForm = {
                status: asset.status || '',
                location: asset.location || '',
                note: '',
                photo_urls: [],
                study_program_code: asset.study_program_code || '',
            };
            this.logError = '';
            this.logModalOpen = true;
        },
        closeLogModal() {
            this.logModalOpen = false;
            this.logAsset = null;
            this.logError = '';
        },
        uploadLogPhotos(event) {
            const input = event && event.target ? event.target : null;
            const files = input && input.files ? Array.from(input.files) : [];

            this.logError = '';

            if (!files.length) {
                return;
            }

            const invalid = files.find((file) => this.validatePhoto(file));
            if (invalid) {
                this.logError = this.validatePhoto(invalid);
                if (input) {
                    input.value = '';
                }
                return;
            }

            this.logPhotoUploading = true;
            const uploads = files.map((file) => uploadFirebaseFile({ file, prefix: 'AssetLog/Photo' }));

            Promise.all(uploads)
                .then((urls) => {
                    urls.forEach((url) => {
                        if (url) {
                            this.logForm.photo_urls.push(url);
                        }
                    });
                })
                .catch(() => {
                    this.logError = 'Upload failed.';
                })
                .finally(() => {
                    this.logPhotoUploading = false;
                    if (input) {
                        input.value = '';
                    }
                });
        },
        removeLogPhoto(index) {
            this.logForm.photo_urls.splice(index, 1);
        },
        triggerLogCamera() {
            if (this.$refs.logCameraInput) {
                this.$refs.logCameraInput.click();
            }
        },
        submitLog() {
            if (!this.logAsset) {
                return;
            }

            this.logSubmitting = true;
            this.logError = '';

            return Repository.post(`/api/assets-logs/${this.logAsset.id}`, this.logForm)
                .then(() => {
                    const assetId = this.logAsset.id;
                    this.closeLogModal();
                    this.$showToast('Riwayat berhasil ditambahkan.');
                    if (this.detailModalOpen && this.detailAsset && this.detailAsset.id === assetId) {
                        this.fetchLogs(assetId);
                    }
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Gagal menambahkan riwayat.';
                    this.logError = message;
                })
                .finally(() => {
                    this.logSubmitting = false;
                });
        },
        closeImage() {
            this.imageModalOpen = false;
            this.imageModalUrl = '';
        },
        labelFor(options, value) {
            const match = options.find((option) => option.value === value);
            return match ? match.label : value;
        },
        formatPrice(value) {
            const number = Number(value);
            if (Number.isNaN(number)) {
                return value;
            }
            return number.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' });
        },
        validatePhoto(file) {
            if (!file) {
                return 'No file selected.';
            }
            const allowed = ['image/jpg', 'image/jpeg', 'image/png'];
            if (!allowed.includes(file.type)) {
                return 'File must be jpg, jpeg, or png.';
            }
            if (file.size > 2 * 1024 * 1024) {
                return 'File size must not exceed 2MB.';
            }
            return '';
        },
        uploadPhoto(event) {
            const input = event && event.target ? event.target : null;
            const file = input && input.files && input.files.length ? input.files[0] : null;

            this.photoError = '';

            const validationMessage = this.validatePhoto(file);
            if (validationMessage) {
                this.photoError = validationMessage;
                if (input) {
                    input.value = '';
                }
                return;
            }

            this.photoUploading = true;
            uploadFirebaseFile({ file, prefix: 'Asset/Photo' })
                .then((url) => {
                    if (url) {
                        this.form.photo_url = url;
                    } else {
                        this.photoError = 'Upload failed.';
                    }
                })
                .catch(() => {
                    this.photoError = 'Upload failed.';
                })
                .finally(() => {
                    this.photoUploading = false;
                    if (input) {
                        input.value = '';
                    }
                });
        },
        confirmErasePhoto() {
            this.erasePhotoModalOpen = true;
        },
        erasePhoto() {
            this.form.photo_url = '';
            this.photoError = '';
            this.erasePhotoModalOpen = false;
        },
        fetchProperties() {
            return Repository.get('/api/assets-properties')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    if (!result) {
                        return;
                    }
                    this.ownerOptions = result.owner || [];
                    this.categoryOptions = result.category || [];
                    this.statusOptions = result.status || [];
                })
                .catch(() => {
                    this.ownerOptions = [];
                    this.categoryOptions = [];
                    this.statusOptions = [];
                });
        },
        fetchAssets() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(this.baseUrl, {
                params: this.filters,
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];

                    this.assets = data;
                    this.pagination = result || {};
                })
                .catch(() => {
                    this.assets = [];
                    this.pagination = {};
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        toggleActionMenu(assetId) {
            this.actionMenuOpenId = this.actionMenuOpenId === assetId ? null : assetId;
        },
        closeActionMenu() {
            this.actionMenuOpenId = null;
        },
        handleAction(action, asset) {
            this.closeActionMenu();
            if (action === 'detail') {
                this.openDetail(asset);
                return;
            }
            if (action === 'addLog') {
                this.openAddLog(asset);
                return;
            }
            if (action === 'edit') {
                this.openEdit(asset);
                return;
            }
            if (action === 'delete') {
                this.deleteAsset(asset);
            }
        },
        handleDocumentClick(event) {
            const target = event && event.target ? event.target : null;
            if (!target) {
                return;
            }
            if (target.closest && target.closest('.action-dropdown')) {
                return;
            }
            this.closeActionMenu();
        },
        applyFilter() {
            this.filters.page = 1;
            this.fetchAssets();
        },
        resetFilter() {
            this.filters.keyword = '';
            this.filters.owner = '';
            this.filters.category = '';
            this.filters.status = '';
            this.filters.page = 1;
            this.fetchAssets();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchAssets();
        },
        openCreate() {
            this.editMode = false;
            this.form = this.emptyForm();
            this.errorMessage = '';
            this.photoError = '';
            this.modalOpen = true;
        },
        openEdit(asset) {
            this.editMode = true;
            this.form = {
                id: asset.id,
                number: asset.number || '',
                owner: asset.owner || '',
                name: asset.name || '',
                price: asset.price ?? 0,
                estimate_month: asset.estimate_month ?? null,
                supplier: asset.supplier || '',
                photo_url: asset.photo_url || '',
                brand: asset.brand || '',
                purchase_date: asset.purchase_date ? String(asset.purchase_date).substring(0, 10) : '',
                category: asset.category || '',
                status: asset.status || '',
                description: asset.description || '',
                warranty_until: asset.warranty_until ? String(asset.warranty_until).substring(0, 10) : '',
                location: asset.location || '',
                study_program_code: asset.study_program_code || '',
            };
            this.errorMessage = '';
            this.photoError = '';
            this.modalOpen = true;
        },
        closeModal() {
            this.modalOpen = false;
            this.errorMessage = '';
            this.photoError = '';
            if (!this.editMode) {
                this.form = this.emptyForm();
            }
        },
        submitForm() {
            if (this.editMode) {
                return this.updateAsset();
            }

            return this.createAsset();
        },
        createAsset() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.post(this.baseUrl, this.form)
                .then(() => {
                    this.closeModal();
                    this.fetchAssets();
                    this.$showToast('Asset created successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to create asset.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        updateAsset() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.put(`${this.baseUrl}/${this.form.id}`, this.form)
                .then(() => {
                    this.fetchAssets();
                    this.closeModal();
                    this.$showToast('Asset updated successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to update asset.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        deleteAsset(asset) {
            if (!window.confirm(`Delete asset ${asset.name}?`)) {
                return;
            }

            Repository.delete(`${this.baseUrl}/${asset.id}`)
                .then(() => {
                    this.fetchAssets();
                    this.$showToast('Asset deleted successfully.');
                })
                .catch(() => {
                    this.errorMessage = 'Failed to delete asset.';
                });
        },
    },
};
</script>
