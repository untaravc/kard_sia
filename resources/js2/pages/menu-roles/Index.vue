<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Data Master</div>
                <h1 class="text-2xl font-semibold text-ink">Menu Roles</h1>
            </div>
            <button
                class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                type="button"
                :disabled="!selectedRoleId || saving"
                @click="saveUpdate"
            >
                {{ saving ? 'Saving...' : 'Save Update' }}
            </button>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="max-w-xs">
                <label class="text-xs text-muted">Role</label>
                <select
                    v-model="selectedRoleId"
                    class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                >
                    <option :value="null">Select a role</option>
                    <option v-for="option in roles" :key="option.id" :value="option.id">
                        {{ option.name }}
                    </option>
                </select>
            </div>
        </section>

        <div
            v-if="errorMessage"
            class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-xs text-rose-600"
        >
            {{ errorMessage }}
        </div>

        <section class="relative rounded-2xl border border-border bg-panel">
            <Loading :active="loading" :is-full-page="false" />

            <div v-if="!selectedRoleId" class="px-5 py-10 text-center text-sm text-muted">
                Select a role above to manage its menu permissions.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="border-b border-border text-left text-xs uppercase tracking-wide text-muted">
                            <th class="px-5 py-3 font-semibold">Menu</th>
                            <th class="px-3 py-3 text-center font-semibold">Get</th>
                            <th class="px-3 py-3 text-center font-semibold">Show</th>
                            <th class="px-3 py-3 text-center font-semibold">Post</th>
                            <th class="px-3 py-3 text-center font-semibold">Update</th>
                            <th class="px-3 py-3 text-center font-semibold">Delete</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-if="menuTree.length === 0">
                            <td colspan="6" class="px-5 py-6 text-center text-sm text-muted">
                                No menus found.
                            </td>
                        </tr>
                        <tr v-for="item in menuTree" :key="item.id">
                            <td class="px-5 py-2.5">
                                <span :style="{ paddingLeft: `${item.depth * 20}px` }" class="inline-flex items-center gap-2">
                                    <span v-if="item.depth > 0" class="text-muted">↳</span>
                                    <span :class="item.depth === 0 ? 'font-semibold text-ink' : 'text-ink'">
                                        {{ item.title }}
                                    </span>
                                </span>
                            </td>
                            <td class="px-3 py-2.5 text-center">
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 rounded border border-border"
                                    v-model="checkedMap[cellKey(item.id, 'GET')]"
                                />
                            </td>
                            <td class="px-3 py-2.5 text-center">
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 rounded border border-border"
                                    v-model="checkedMap[cellKey(item.id, 'SHOW')]"
                                />
                            </td>
                            <td class="px-3 py-2.5 text-center">
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 rounded border border-border"
                                    v-model="checkedMap[cellKey(item.id, 'POST')]"
                                />
                            </td>
                            <td class="px-3 py-2.5 text-center">
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 rounded border border-border"
                                    v-model="checkedMap[cellKey(item.id, 'PUT')]"
                                />
                            </td>
                            <td class="px-3 py-2.5 text-center">
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 rounded border border-border"
                                    v-model="checkedMap[cellKey(item.id, 'DEL')]"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Repository from '../../repository';

const METHODS = ['GET', 'SHOW', 'POST', 'PUT', 'DEL'];

export default {
    components: {
        Loading,
    },
    data() {
        return {
            roles: [],
            menus: [],
            selectedRoleId: null,
            checkedMap: {},
            loading: false,
            saving: false,
            errorMessage: '',
        };
    },
    computed: {
        menuTree() {
            const byParent = {};
            this.menus.forEach((menu) => {
                const key = menu.parent_id || 'root';
                if (!byParent[key]) {
                    byParent[key] = [];
                }
                byParent[key].push(menu);
            });

            const flatten = (parentKey, depth) => {
                const children = byParent[parentKey] || [];
                return children.reduce((acc, menu) => {
                    acc.push({ ...menu, depth });
                    return acc.concat(flatten(menu.id, depth + 1));
                }, []);
            };

            return flatten('root', 0);
        },
    },
    watch: {
        selectedRoleId(value) {
            if (value) {
                this.fetchMenuRoles(value);
            } else {
                this.checkedMap = {};
            }
        },
    },
    created() {
        this.fetchRoles();
        this.fetchMenus();
    },
    methods: {
        cellKey(menuId, method) {
            return `${menuId}-${method}`;
        },
        fetchRoles() {
            return Repository.get('/api/role-list')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.roles = Array.isArray(result) ? result : [];
                    if (this.roles.length && !this.selectedRoleId) {
                        this.selectedRoleId = this.roles[0].id;
                    }
                })
                .catch(() => {
                    this.roles = [];
                });
        },
        fetchMenus() {
            return Repository.get('/api/menu-list')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.menus = Array.isArray(result) ? result : [];
                })
                .catch(() => {
                    this.menus = [];
                });
        },
        fetchMenuRoles(roleId) {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get('/api/menu-roles', {
                params: { role_id: roleId, per_page: 1000 },
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];

                    const map = {};
                    data.forEach((row) => {
                        map[this.cellKey(row.menu_id, row.method)] = true;
                    });
                    this.checkedMap = map;
                })
                .catch(() => {
                    this.checkedMap = {};
                    this.errorMessage = 'Failed to load menu permissions.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        saveUpdate() {
            if (!this.selectedRoleId) {
                return;
            }

            const permissions = [];
            Object.keys(this.checkedMap).forEach((key) => {
                if (!this.checkedMap[key]) {
                    return;
                }
                const separatorIndex = key.lastIndexOf('-');
                const menuId = Number(key.slice(0, separatorIndex));
                const method = key.slice(separatorIndex + 1);
                if (METHODS.includes(method)) {
                    permissions.push({ menu_id: menuId, method });
                }
            });

            this.saving = true;
            this.errorMessage = '';

            return Repository.post('/api/menu-roles-sync', {
                role_id: this.selectedRoleId,
                permissions,
            })
                .then(() => {
                    this.$showToast('Menu roles updated successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to update menu roles.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.saving = false;
                });
        },
    },
};
</script>
