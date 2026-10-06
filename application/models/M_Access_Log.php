<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model M_Access_Log
 * Mencatat dan mengelola riwayat log perangkat & akses pengunjung ERP.
 * Dilengkapi dengan deteksi lokasi (Kota, Provinsi, Negara, ISP).
 * Dirancang tangguh untuk lingkungan shared hosting (Hostinger) & local.
 */
class M_Access_Log extends CI_Model
{
    private $table = 'tb_access_log';

    public function __construct()
    {
        parent::__construct();
        $this->ensure_schema();
    }

    /**
     * Memastikan tabel log dan kolom lokasi tersedia di database secara otomatis
     */
    public function ensure_schema()
    {
        if (!$this->db->table_exists($this->table)) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `{$this->table}` (
                  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `ip_address` VARCHAR(45) NOT NULL,
                  `device_type` VARCHAR(50) NOT NULL DEFAULT 'Desktop',
                  `device_brand` VARCHAR(100) DEFAULT NULL,
                  `os` VARCHAR(100) DEFAULT NULL,
                  `browser` VARCHAR(100) DEFAULT NULL,
                  `city` VARCHAR(100) DEFAULT NULL,
                  `district` VARCHAR(150) DEFAULT NULL,
                  `region` VARCHAR(100) DEFAULT NULL,
                  `country` VARCHAR(100) DEFAULT NULL,
                  `country_code` VARCHAR(10) DEFAULT NULL,
                  `isp` VARCHAR(150) DEFAULT NULL,
                  `latitude` DECIMAL(10, 7) DEFAULT NULL,
                  `longitude` DECIMAL(10, 7) DEFAULT NULL,
                  `accuracy` INT(11) DEFAULT NULL,
                  `location_source` VARCHAR(20) NOT NULL DEFAULT 'IP',
                  `user_agent` TEXT DEFAULT NULL,
                  `url_accessed` TEXT NOT NULL,
                  `http_method` VARCHAR(10) NOT NULL DEFAULT 'GET',
                  `is_ajax` TINYINT(1) NOT NULL DEFAULT 0,
                  `username` VARCHAR(100) DEFAULT 'Guest',
                  `nama_user` VARCHAR(150) DEFAULT NULL,
                  `id_karyawan` INT(11) DEFAULT NULL,
                  `referrer` TEXT DEFAULT NULL,
                  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  INDEX `idx_created_at` (`created_at`),
                  INDEX `idx_ip_address` (`ip_address`),
                  INDEX `idx_username` (`username`),
                  INDEX `idx_device_type` (`device_type`),
                  INDEX `idx_city` (`city`),
                  INDEX `idx_district` (`district`),
                  INDEX `idx_country` (`country`),
                  INDEX `idx_location_source` (`location_source`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        } else {
            // Auto-migration: Tambahkan kolom lokasi jika belum ada pada tabel lama
            if (!$this->db->field_exists('city', $this->table)) {
                $this->db->query("
                    ALTER TABLE `{$this->table}` 
                    ADD COLUMN `city` VARCHAR(100) NULL AFTER `browser`,
                    ADD COLUMN `region` VARCHAR(100) NULL AFTER `city`,
                    ADD COLUMN `country` VARCHAR(100) NULL AFTER `region`,
                    ADD COLUMN `country_code` VARCHAR(10) NULL AFTER `country`,
                    ADD COLUMN `isp` VARCHAR(150) NULL AFTER `country_code`,
                    ADD INDEX `idx_city` (`city`),
                    ADD INDEX `idx_country` (`country`);
                ");
            }
            if (!$this->db->field_exists('district', $this->table)) {
                $this->db->query("
                    ALTER TABLE `{$this->table}` 
                    ADD COLUMN `district` VARCHAR(150) NULL AFTER `city`,
                    ADD COLUMN `latitude` DECIMAL(10, 7) NULL AFTER `isp`,
                    ADD COLUMN `longitude` DECIMAL(10, 7) NULL AFTER `latitude`,
                    ADD COLUMN `accuracy` INT(11) NULL AFTER `longitude`,
                    ADD COLUMN `location_source` VARCHAR(20) NOT NULL DEFAULT 'IP' AFTER `accuracy`,
                    ADD INDEX `idx_district` (`district`),
                    ADD INDEX `idx_location_source` (`location_source`);
                ");
            }
        }
    }

    /**
     * Menyimpan baris log akses baru
     */
    public function record(array $data)
    {
        try {
            $payload = [
                'ip_address'      => substr((string) ($data['ip_address'] ?? '127.0.0.1'), 0, 45),
                'device_type'     => substr((string) ($data['device_type'] ?? 'Desktop'), 0, 50),
                'device_brand'    => substr((string) ($data['device_brand'] ?? 'Unknown'), 0, 100),
                'os'              => substr((string) ($data['os'] ?? 'Unknown'), 0, 100),
                'browser'         => substr((string) ($data['browser'] ?? 'Unknown'), 0, 100),
                'city'            => substr((string) ($data['city'] ?? '-'), 0, 100),
                'district'        => substr((string) ($data['district'] ?? '-'), 0, 150),
                'region'          => substr((string) ($data['region'] ?? '-'), 0, 100),
                'country'         => substr((string) ($data['country'] ?? '-'), 0, 100),
                'country_code'    => substr((string) ($data['country_code'] ?? '-'), 0, 10),
                'isp'             => substr((string) ($data['isp'] ?? '-'), 0, 150),
                'latitude'        => !empty($data['latitude']) ? (float) $data['latitude'] : null,
                'longitude'       => !empty($data['longitude']) ? (float) $data['longitude'] : null,
                'accuracy'        => !empty($data['accuracy']) ? (int) $data['accuracy'] : null,
                'location_source' => substr((string) ($data['location_source'] ?? 'IP'), 0, 20),
                'user_agent'      => (string) ($data['user_agent'] ?? ''),
                'url_accessed'    => (string) ($data['url_accessed'] ?? '/'),
                'http_method'     => substr((string) ($data['http_method'] ?? 'GET'), 0, 10),
                'is_ajax'         => !empty($data['is_ajax']) ? 1 : 0,
                'username'        => substr((string) ($data['username'] ?? 'Guest'), 0, 100),
                'nama_user'       => substr((string) ($data['nama_user'] ?? 'Guest'), 0, 150),
                'id_karyawan'     => !empty($data['id_karyawan']) ? (int) $data['id_karyawan'] : null,
                'referrer'        => (string) ($data['referrer'] ?? ''),
                'created_at'      => date('Y-m-d H:i:s')
            ];

            $this->db->insert($this->table, $payload);
            $insertedId = $this->db->insert_id();

            // Simpan id log terakhir ke session agar sinkron saat browser mengirim koordinat GPS
            if ($insertedId && function_exists('get_instance')) {
                $CI = &get_instance();
                if (isset($CI->session)) {
                    $CI->session->set_userdata('current_access_log_id', $insertedId);
                }
            }

            return $insertedId;
        } catch (Exception $e) {
            // Safety fallback: tulis ke log file jika DB gagal
            $this->write_file_log($data, $e->getMessage());
            return false;
        }
    }

    /**
     * Mendeteksi lokasi geografis berdasarkan IP Address (Kota, Wilayah, Negara, ISP)
     * Menggunakan sistem Caching Database agar 0 delay & hemat kuota API.
     */
    public function resolve_location($ip)
    {
        $ip = trim((string) $ip);

        // 1. Cek IP Lokal / Loopback / Private Network (Jaringan Kantor Karisma ERP Jember)
        if (empty($ip) || $ip === '127.0.0.1' || $ip === '::1' || $ip === 'localhost' ||
            preg_match('/^(10\.|192\.168\.|172\.(1[6-9]|2[0-9]|3[0-1])\.)/', $ip)) {
            return [
                'city'            => 'Jember',
                'district'        => 'Kecamatan Sumbersari',
                'region'          => 'Jawa Timur',
                'country'         => 'Indonesia (Lokal)',
                'country_code'    => 'ID',
                'isp'             => 'Localhost / Jaringan Kantor',
                'latitude'        => -8.1724000,
                'longitude'       => 113.7208000,
                'accuracy'        => 5,
                'location_source' => 'Lokal'
            ];
        }

        // 2. Cek Cache DB: Jika IP publik ini sudah pernah memiliki record lokasi di tb_access_log
        try {
            $cached = $this->db
                ->select('city, district, region, country, country_code, isp, latitude, longitude, accuracy, location_source')
                ->where('ip_address', $ip)
                ->where('city IS NOT NULL')
                ->where("city != ''")
                ->where("city != '-'")
                ->order_by('id', 'DESC')
                ->limit(1)
                ->get($this->table)
                ->row_array();

            if (!empty($cached['city'])) {
                return $cached;
            }
        } catch (Exception $e) {
            // Abaikan jika query cache error
        }

        // 3. Cek Header Proxy Cloudflare / Hostinger
        $cfCountry = !empty($_SERVER['HTTP_CF_IPCOUNTRY']) ? trim((string)$_SERVER['HTTP_CF_IPCOUNTRY']) : null;
        $cfCity    = !empty($_SERVER['HTTP_CF_IPCITY']) ? trim((string)$_SERVER['HTTP_CF_IPCITY']) : null;

        if ($cfCountry || $cfCity) {
            return [
                'city'            => $cfCity ?: 'Indonesia',
                'district'        => '-',
                'region'          => '-',
                'country'         => ($cfCountry === 'ID') ? 'Indonesia' : ($cfCountry ?: 'Indonesia'),
                'country_code'    => $cfCountry ?: 'ID',
                'isp'             => 'Cloudflare Network',
                'latitude'        => null,
                'longitude'       => null,
                'accuracy'        => null,
                'location_source' => 'IP'
            ];
        }

        // 4. IP Geolocation Lookup Cepat via ip-api.com (Timeout 1.0 detik agar tidak memperlambat loading)
        try {
            $ch = curl_init("http://ip-api.com/json/{$ip}?fields=status,country,countryCode,regionName,city,isp,lat,lon");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            $response = curl_exec($ch);
            curl_close($ch);

            if ($response) {
                $json = json_decode($response, true);
                if (!empty($json) && ($json['status'] ?? '') === 'success') {
                    return [
                        'city'            => $json['city'] ?? '-',
                        'district'        => '-',
                        'region'          => $json['regionName'] ?? '-',
                        'country'         => $json['country'] ?? 'Indonesia',
                        'country_code'    => $json['countryCode'] ?? 'ID',
                        'isp'             => $json['isp'] ?? '-',
                        'latitude'        => !empty($json['lat']) ? (float)$json['lat'] : null,
                        'longitude'       => !empty($json['lon']) ? (float)$json['lon'] : null,
                        'accuracy'        => null,
                        'location_source' => 'IP'
                    ];
                }
            }
        } catch (Exception $ex) {
            // Fallback default jika koneksi lookup gagal
        }

        return [
            'city'            => 'Online Host',
            'district'        => '-',
            'region'          => '-',
            'country'         => 'Indonesia',
            'country_code'    => 'ID',
            'isp'             => '-',
            'latitude'        => null,
            'longitude'       => null,
            'accuracy'        => null,
            'location_source' => 'IP'
        ];
    }

    /**
     * Memperbarui data lokasi dengan koordinat GPS presisi dari browser
     */
    public function update_gps_location($logId, array $data)
    {
        try {
            if (empty($logId)) {
                $CI = &get_instance();
                $ip = $CI->input->ip_address();
                $row = $this->db->select('id')
                    ->where('ip_address', $ip)
                    ->order_by('id', 'DESC')
                    ->limit(1)
                    ->get($this->table)
                    ->row_array();
                if ($row) {
                    $logId = (int)$row['id'];
                }
            }

            if (empty($logId)) {
                return false;
            }

            $update = [
                'location_source' => 'GPS'
            ];

            if (isset($data['latitude']) && is_numeric($data['latitude'])) {
                $update['latitude'] = (float)$data['latitude'];
            }
            if (isset($data['longitude']) && is_numeric($data['longitude'])) {
                $update['longitude'] = (float)$data['longitude'];
            }
            if (isset($data['accuracy']) && is_numeric($data['accuracy'])) {
                $update['accuracy'] = (int)$data['accuracy'];
            }
            if (!empty($data['district']) && $data['district'] !== '-') {
                $update['district'] = substr(trim((string)$data['district']), 0, 150);
            }
            if (!empty($data['city']) && $data['city'] !== '-') {
                $update['city'] = substr(trim((string)$data['city']), 0, 100);
            }
            if (!empty($data['region']) && $data['region'] !== '-') {
                $update['region'] = substr(trim((string)$data['region']), 0, 100);
            }
            if (!empty($data['country']) && $data['country'] !== '-') {
                $update['country'] = substr(trim((string)$data['country']), 0, 100);
            }

            $this->db->where('id', (int)$logId)->update($this->table, $update);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Fallback pencatatan ke file harian di application/logs/
     */
    public function write_file_log(array $data, $errorMsg = '')
    {
        try {
            $logDir = APPPATH . 'logs/';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }

            $fileName = $logDir . 'device_access_' . date('Y-m-d') . '.log';
            $logLine = sprintf(
                "[%s] IP:%s | LOC:%s, %s | DEV:%s (%s) | OS:%s | BROWSER:%s | USER:%s | URL:%s %s%s\n",
                date('Y-m-d H:i:s'),
                $data['ip_address'] ?? '-',
                $data['city'] ?? '-',
                $data['country'] ?? '-',
                $data['device_type'] ?? '-',
                $data['device_brand'] ?? '-',
                $data['os'] ?? '-',
                $data['browser'] ?? '-',
                $data['username'] ?? 'Guest',
                $data['http_method'] ?? 'GET',
                $data['url_accessed'] ?? '/',
                $errorMsg ? " | DB_ERR: $errorMsg" : ""
            );

            @file_put_contents($fileName, $logLine, FILE_APPEND | LOCK_EX);
        } catch (Exception $ex) {
            // Silent error handling agar tidak mengganggu rendering web
        }
    }

    /**
     * Mengambil ringkasan statistik perangkat
     */
    public function get_stats(array $filters = [])
    {
        $today = date('Y-m-d');

        // Total Akses Hari Ini
        $totalToday = $this->db
            ->where("DATE(created_at) =", $today)
            ->count_all_results($this->table);

        // Perangkat Desktop Hari Ini
        $desktopToday = $this->db
            ->where("DATE(created_at) =", $today)
            ->where('device_type', 'Desktop')
            ->count_all_results($this->table);

        // Perangkat Mobile (HP) Hari Ini
        $mobileToday = $this->db
            ->where("DATE(created_at) =", $today)
            ->where_in('device_type', ['Mobile Phone', 'Tablet'])
            ->count_all_results($this->table);

        // IP Unik Hari Ini
        $ipRow = $this->db
            ->select("COUNT(DISTINCT ip_address) AS total_ip")
            ->where("DATE(created_at) =", $today)
            ->get($this->table)
            ->row_array();
        $uniqueIpToday = (int) ($ipRow['total_ip'] ?? 0);

        // Total Keseluruhan
        $totalAll = $this->db->count_all($this->table);

        return [
            'total_today'     => $totalToday,
            'desktop_today'   => $desktopToday,
            'mobile_today'    => $mobileToday,
            'unique_ip_today' => $uniqueIpToday,
            'total_all'       => $totalAll
        ];
    }

    /**
     * Mengambil daftar log akses dengan filter dan pagination
     */
    public function get_logs(array $filters = [], $limit = 50, $offset = 0)
    {
        $this->db->from($this->table);

        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(created_at) <=', $filters['date_to']);
        }
        if (!empty($filters['device_type']) && $filters['device_type'] !== 'SEMUA') {
            $this->db->where('device_type', $filters['device_type']);
        }
        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $this->db->group_start();
                $this->db->like('ip_address', $search);
                $this->db->or_like('username', $search);
                $this->db->or_like('nama_user', $search);
                $this->db->or_like('url_accessed', $search);
                $this->db->or_like('os', $search);
                $this->db->or_like('browser', $search);
                $this->db->or_like('device_brand', $search);
                $this->db->or_like('district', $search);
                $this->db->or_like('city', $search);
                $this->db->or_like('country', $search);
                $this->db->or_like('isp', $search);
                $this->db->or_like('location_source', $search);
            $this->db->group_end();
        }

        $this->db->order_by('id', 'DESC');
        $this->db->limit((int) $limit, (int) $offset);

        return $this->db->get()->result_array();
    }

    /**
     * Menghitung total data log berdasarkan filter (untuk pagination)
     */
    public function count_logs(array $filters = [])
    {
        $this->db->from($this->table);

        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(created_at) <=', $filters['date_to']);
        }
        if (!empty($filters['device_type']) && $filters['device_type'] !== 'SEMUA') {
            $this->db->where('device_type', $filters['device_type']);
        }
        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $this->db->group_start();
                $this->db->like('ip_address', $search);
                $this->db->or_like('username', $search);
                $this->db->or_like('nama_user', $search);
                $this->db->or_like('url_accessed', $search);
                $this->db->or_like('os', $search);
                $this->db->or_like('browser', $search);
                $this->db->or_like('device_brand', $search);
                $this->db->or_like('district', $search);
                $this->db->or_like('city', $search);
                $this->db->or_like('country', $search);
                $this->db->or_like('isp', $search);
                $this->db->or_like('location_source', $search);
            $this->db->group_end();
        }

        return $this->db->count_all_results();
    }

    /**
     * Pembersihan log lama (otomatis atau manual oleh admin)
     * Menjaga kapasitas database Hostinger tetap efisien
     */
    public function purge_old_logs($days = 60)
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        $this->db->where('created_at <', $cutoffDate)->delete($this->table);
        return $this->db->affected_rows();
    }
}
