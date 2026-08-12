<?php

use Illuminate\Database\Seeder;

class MenuTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeded from the 'user' (admin) menu hardcoded in
     * App\Http\Controllers\Api\MenuController::menu().
     *
     * @return void
     */
    public function run()
    {
        $basePath = '/blu';

        $menus = [
            [
                'name' => 'dashboard',
                'title' => 'Dashboard',
                'icon' => 'dashboard',
                'type' => 'menu',
                'url' => "{$basePath}/dashboard",
                'order' => 1,
            ],
            [
                'name' => 'lectures',
                'title' => 'Lectures',
                'icon' => 'dosen',
                'type' => 'title',
                'url' => '#',
                'order' => 2,
                'children' => [
                    ['name' => 'lectures-data', 'title' => 'Data', 'url' => "{$basePath}/lectures"],
                ],
            ],
            [
                'name' => 'students',
                'title' => 'Students',
                'icon' => 'resident',
                'type' => 'title',
                'url' => '#',
                'order' => 3,
                'children' => [
                    ['name' => 'students-data', 'title' => 'Data', 'url' => "{$basePath}/students"],
                    ['name' => 'students-stase-log-report', 'title' => 'Stase Log Report', 'url' => "{$basePath}/report/stase-log"],
                    ['name' => 'students-presences', 'title' => 'Presences', 'url' => "{$basePath}/presences"],
                    ['name' => 'students-presences-daily', 'title' => 'Presences Daily', 'url' => "{$basePath}/presences/daily"],
                    ['name' => 'students-presences-monthly', 'title' => 'Presences Monthly', 'url' => "{$basePath}/presences/monthly"],
                    ['name' => 'students-logbook', 'title' => 'Log Book', 'url' => "{$basePath}/logbooks"],
                ],
            ],
            [
                'name' => 'monitoring',
                'title' => 'Monitoring',
                'icon' => 'resident',
                'type' => 'title',
                'url' => '#',
                'order' => 4,
                'children' => [
                    ['name' => 'monitoring-stase', 'title' => 'Stase', 'url' => "{$basePath}/students/monitoring"],
                    ['name' => 'monitoring-logbook', 'title' => 'Logbook', 'url' => "{$basePath}/students/monitoring-logbook"],
                    ['name' => 'monitoring-presence', 'title' => 'Presence', 'url' => "{$basePath}/students/monitoring-presence"],
                ],
            ],
            [
                'name' => 'registrations',
                'title' => 'Registrations',
                'icon' => 'resident',
                'type' => 'title',
                'url' => '#',
                'order' => 5,
                'children' => [
                    ['name' => 'registrations-administrasi', 'title' => 'Administrasi', 'url' => "{$basePath}/registrations"],
                    ['name' => 'registrations-journal', 'title' => 'Journal', 'url' => "{$basePath}/registrations?section=journal"],
                    ['name' => 'registrations-interview', 'title' => 'Interview', 'url' => "{$basePath}/registrations?section=interview"],
                    ['name' => 'registrations-score', 'title' => 'Score', 'url' => "{$basePath}/registrations/score"],
                ],
            ],
            [
                'name' => 'add-on',
                'title' => 'Add On',
                'icon' => 'agenda',
                'type' => 'title',
                'url' => '#',
                'order' => 6,
                'children' => [
                    ['name' => 'add-on-activities', 'title' => 'Activities', 'url' => "{$basePath}/activities"],
                    ['name' => 'add-on-formulir', 'title' => 'Formulir', 'url' => "{$basePath}/forms"],
                    ['name' => 'add-on-letters', 'title' => 'Letters', 'url' => "{$basePath}/letters"],
                    ['name' => 'add-on-accreditation', 'title' => 'Accreditation', 'url' => "{$basePath}/accreditations"],
                    ['name' => 'add-on-asset', 'title' => 'Asset', 'url' => "{$basePath}/assets"],
                ],
            ],
            [
                'name' => 'data-master',
                'title' => 'Data Master',
                'icon' => 'data-master',
                'type' => 'title',
                'url' => '#',
                'order' => 7,
                'children' => [
                    ['name' => 'data-master-form-option', 'title' => 'Form Option', 'url' => "{$basePath}/form-options"],
                    ['name' => 'data-master-study-program', 'title' => 'Study Program', 'url' => "{$basePath}/study-programs"],
                    ['name' => 'data-master-post', 'title' => 'Post', 'url' => "{$basePath}/posts"],
                    ['name' => 'data-master-stase', 'title' => 'Stase', 'url' => "{$basePath}/stases"],
                    ['name' => 'data-master-task', 'title' => 'Task', 'url' => "{$basePath}/tasks"],
                    ['name' => 'data-master-mail-log', 'title' => 'Mail Log', 'url' => "{$basePath}/mail-logs"],
                ],
            ],
            [
                'name' => 'config',
                'title' => 'Config',
                'icon' => 'config',
                'type' => 'title',
                'url' => '#',
                'order' => 8,
                'children' => [
                    ['name' => 'data-master-settings', 'title' => 'Settings', 'url' => "{$basePath}/settings"],
                    ['name' => 'data-master-admin', 'title' => 'Admin', 'url' => "{$basePath}/users"],
                    ['name' => 'data-master-role', 'title' => 'Role', 'url' => "{$basePath}/roles"],
                    ['name' => 'data-master-menu', 'title' => 'Menu', 'url' => "{$basePath}/menus"],
                    ['name' => 'data-master-menu-role', 'title' => 'Menu Role', 'url' => "{$basePath}/menu-roles"],
                    ['name' => 'data-master-action-log', 'title' => 'Action Log', 'url' => "{$basePath}/action-logs"],
                ],
            ],
        ];

        foreach ($menus as $menu) {
            $children = $menu['children'] ?? [];
            unset($menu['children']);

            $menu['icon'] = $menu['icon'] ?? null;
            $menu['is_active'] = 1;
            $menu['parent_id'] = null;
            $menu['created_at'] = now();
            $menu['updated_at'] = now();

            DB::table('menus')->updateOrInsert(['name' => $menu['name']], $menu);
            $parentId = DB::table('menus')->where('name', $menu['name'])->value('id');

            foreach ($children as $childOrder => $child) {
                $child['type'] = 'submenu';
                $child['icon'] = null;
                $child['parent_id'] = $parentId;
                $child['order'] = $childOrder + 1;
                $child['is_active'] = 1;
                $child['created_at'] = now();
                $child['updated_at'] = now();

                DB::table('menus')->updateOrInsert(['name' => $child['name']], $child);
            }
        }
    }
}
