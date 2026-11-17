<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model M_Sectionmenu
 * Mengelola konfigurasi pengelompokan menu yang ditampilkan pada setiap departemen di dashboard.
 */
class M_Sectionmenu extends CI_Model
{
    private $table = 'tb_dashboard_section_menus';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_Dashboard');
    }

    /**
     * Pastikan tabel tb_dashboard_section_menus tersedia di database
     */
    public function ensure_schema()
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        if (!$this->db->table_exists($this->table)) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->table}` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `section_key` VARCHAR(60) NOT NULL,
                `menu_key` VARCHAR(120) NOT NULL,
                `is_visible` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Ditampilkan, 0 = Disembunyikan',
                `sort_order` INT NOT NULL DEFAULT 0,
                `updated_by` VARCHAR(100) NULL DEFAULT NULL,
                `created_at` DATETIME NULL DEFAULT NULL,
                `updated_at` DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_section_menu` (`section_key`, `menu_key`),
                KEY `idx_section_key` (`section_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
    }

    /**
     * Ambil daftar seluruh departemen beserta ringkasan jumlah menu yang aktif
     */
    public function sections()
    {
        $this->ensure_schema();
        $rawSections = $this->M_Dashboard->base_module_sections(array());
        $configuredSections = $this->M_Dashboard->apply_section_menu_config($rawSections);

        // Cek section mana saja yang sudah dikustomisasi di database
        $customRows = $this->db
            ->select('section_key, COUNT(*) AS total_rows')
            ->group_by('section_key')
            ->get($this->table)
            ->result_array();
        $customMap = array();
        foreach ($customRows as $r) {
            $customMap[(string)$r['section_key']] = (int)$r['total_rows'];
        }

        $result = array();
        foreach ($rawSections as $secKey => $sec) {
            $visibleCount = count($configuredSections[$secKey]['menus'] ?? array());
            $defaultCount = count($sec['menus'] ?? array());
            $result[] = array(
                'section_key' => $secKey,
                'label' => $sec['label'],
                'icon' => $sec['icon'],
                'description' => $sec['description'],
                'is_customized' => isset($customMap[$secKey]),
                'visible_count' => $visibleCount,
                'default_count' => $defaultCount,
            );
        }

        return $result;
    }

    /**
     * Bangun katalog seluruh menu unik di sistem beserta status visibilitasnya untuk section tertentu
     */
    public function catalog_for_section($sectionKey)
    {
        $this->ensure_schema();
        $sectionKey = trim((string)$sectionKey);
        $rawSections = $this->M_Dashboard->base_module_sections(array());

        if (!isset($rawSections[$sectionKey])) {
            return null;
        }

        // Ambil konfigurasi tersimpan untuk section ini
        $savedRows = $this->db
            ->where('section_key', $sectionKey)
            ->order_by('sort_order', 'ASC')
            ->order_by('id', 'ASC')
            ->get($this->table)
            ->result_array();

        $isCustomized = !empty($savedRows);
        $savedMap = array();
        foreach ($savedRows as $idx => $row) {
            $savedMap[(string)$row['menu_key']] = array(
                'is_visible' => (int)$row['is_visible'] === 1,
                'sort_order' => (int)($row['sort_order'] ?? ($idx + 1)),
            );
        }

        // Kumpulkan semua menu unik dari seluruh section bawaan dan menu dinamis
        $catalog = array();
        $seenRoutes = array();
        $defaultSectionMenus = array();

        foreach ($rawSections as $secKey => $sec) {
            foreach (($sec['menus'] ?? array()) as $idx => $menu) {
                $mKey = (string)($menu['menu_key'] ?? '');
                if ($mKey === '') {
                    continue;
                }
                if ($secKey === $sectionKey) {
                    $defaultSectionMenus[$mKey] = $idx + 1;
                }
                if (!empty($menu['route'])) {
                    $seenRoutes[$menu['route']] = true;
                }

                if (!isset($catalog[$mKey])) {
                    $catalog[$mKey] = array(
                        'menu_key' => $mKey,
                        'title' => $menu['title'],
                        'route' => $menu['route'],
                        'icon' => $menu['icon'],
                        'tone' => $menu['tone'],
                        'description' => $menu['description'],
                        'default_sections' => array($sec['label']),
                    );
                } else {
                    if (!in_array($sec['label'], $catalog[$mKey]['default_sections'], true)) {
                        $catalog[$mKey]['default_sections'][] = $sec['label'];
                    }
                    // Gunakan metadata menu milik section target jika tersedia
                    if ($secKey === $sectionKey) {
                        $catalog[$mKey]['title'] = $menu['title'];
                        $catalog[$mKey]['route'] = $menu['route'];
                        $catalog[$mKey]['icon'] = $menu['icon'];
                        $catalog[$mKey]['tone'] = $menu['tone'];
                        $catalog[$mKey]['description'] = $menu['description'];
                    }
                }
            }
        }

        foreach ($this->M_Dashboard->dynamic_app_menus($seenRoutes) as $dynMenu) {
            $mKey = (string)($dynMenu['menu_key'] ?? '');
            if ($mKey !== '' && !isset($catalog[$mKey])) {
                $catalog[$mKey] = array(
                    'menu_key' => $mKey,
                    'title' => $dynMenu['title'],
                    'route' => $dynMenu['route'],
                    'icon' => $dynMenu['icon'],
                    'tone' => $dynMenu['tone'],
                    'description' => $dynMenu['description'],
                    'default_sections' => array('MENU APLIKASI'),
                );
            }
        }

        $menus = array();
        foreach ($catalog as $mKey => $item) {
            $isDefault = isset($defaultSectionMenus[$mKey]);
            if ($isCustomized) {
                $isVisible = isset($savedMap[$mKey]) ? $savedMap[$mKey]['is_visible'] : false;
                $sortOrder = isset($savedMap[$mKey]) && $savedMap[$mKey]['is_visible']
                    ? $savedMap[$mKey]['sort_order']
                    : ($isDefault ? 10000 + $defaultSectionMenus[$mKey] : 99999);
            } else {
                $isVisible = $isDefault;
                $sortOrder = $isDefault ? $defaultSectionMenus[$mKey] : 99999;
            }

            $item['is_default_in_section'] = $isDefault;
            $item['is_visible'] = $isVisible;
            $item['sort_order'] = $sortOrder;
            $menus[] = $item;
        }

        // Urutkan: menu yang tampil lebih dulu sesuai sort_order, lalu menu bawaan section, lalu alfabetis
        usort($menus, function ($a, $b) {
            if ($a['is_visible'] !== $b['is_visible']) {
                return $a['is_visible'] ? -1 : 1;
            }
            if ($a['sort_order'] !== $b['sort_order']) {
                return $a['sort_order'] <=> $b['sort_order'];
            }
            if ($a['is_default_in_section'] !== $b['is_default_in_section']) {
                return $a['is_default_in_section'] ? -1 : 1;
            }
            return strcasecmp($a['title'], $b['title']);
        });

        $secInfo = $rawSections[$sectionKey];
        return array(
            'section' => array(
                'section_key' => $sectionKey,
                'label' => $secInfo['label'],
                'icon' => $secInfo['icon'],
                'description' => $secInfo['description'],
                'is_customized' => $isCustomized,
            ),
            'menus' => $menus,
        );
    }

    /**
     * Simpan daftar menu yang ditampilkan untuk satu departemen
     */
    public function save_section($sectionKey, array $visibleMenuKeys)
    {
        $this->ensure_schema();
        $sectionKey = trim((string)$sectionKey);
        $rawSections = $this->M_Dashboard->base_module_sections(array());
        if (!isset($rawSections[$sectionKey])) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $updatedBy = (string)($this->session->userdata('username') ?: $this->session->userdata('nama') ?: 'admin');

        // Bersihkan dan deduplikasi menu_keys sesuai urutan yang dikirim
        $orderedKeys = array();
        foreach ($visibleMenuKeys as $key) {
            $key = trim((string)$key);
            if ($key !== '' && !in_array($key, $orderedKeys, true)) {
                $orderedKeys[] = $key;
            }
        }

        $this->db->trans_start();
        $this->db->where('section_key', $sectionKey)->delete($this->table);

        $rows = array();
        $order = 1;
        foreach ($orderedKeys as $mKey) {
            $rows[] = array(
                'section_key' => $sectionKey,
                'menu_key' => $mKey,
                'is_visible' => 1,
                'sort_order' => $order++,
                'updated_by' => $updatedBy,
                'created_at' => $now,
                'updated_at' => $now,
            );
        }

        // Tandai menu bawaan yang tidak dipilih sebagai nonaktif agar section tetap tercatat sudah dikustomisasi
        foreach (($rawSections[$sectionKey]['menus'] ?? array()) as $menu) {
            $mKey = (string)($menu['menu_key'] ?? '');
            if ($mKey !== '' && !in_array($mKey, $orderedKeys, true)) {
                $rows[] = array(
                    'section_key' => $sectionKey,
                    'menu_key' => $mKey,
                    'is_visible' => 0,
                    'sort_order' => 9999,
                    'updated_by' => $updatedBy,
                    'created_at' => $now,
                    'updated_at' => $now,
                );
            }
        }

        if (!empty($rows)) {
            $this->db->insert_batch($this->table, $rows);
        }
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Kembalikan pengelompokan menu departemen ke pengaturan bawaan kode
     */
    public function reset_section($sectionKey)
    {
        $this->ensure_schema();
        $sectionKey = trim((string)$sectionKey);
        $rawSections = $this->M_Dashboard->base_module_sections(array());
        if (!isset($rawSections[$sectionKey])) {
            return false;
        }

        return (bool)$this->db->where('section_key', $sectionKey)->delete($this->table);
    }
}

