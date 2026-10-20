<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Portal extends CI_Controller
{
    public function index()
    {
        if (!$this->session->userdata('logged_in')) {
            $this->session->set_flashdata('gagal', 'Sesi Anda berakhir. Silakan login ulang.');
            redirect('Auth');
            return;
        }

        $apps = [
            
            [
                'name' => 'KIU PO',
                'url' => 'https://kiupo.karismaerp.com',
                'icon' => 'fa-shopping-cart',
                'description' => 'Purchase Order & Procurement Management.',
                'accent' => '#2563eb',
            ],
            [
                'name' => 'Warehouse',
                'url' => 'https://kiuwarehouse.karismaerp.com',
                'icon' => 'fa-warehouse',
                'description' => 'Warehouse Management System.',
                'accent' => '#0f766e',
            ],
            [
                'name' => 'KMT CORN',
                'url' => 'https://kmtcorn.karismaerp.com',
                'icon' => 'fa-seedling',
                'description' => 'Sales & Operational Management.',
                'accent' => '#d97706',
            ],
            [
                'name' => 'KPI',
                'url' => 'https://kpi.karismaerp.com',
                'icon' => 'fa-chart-line',
                'description' => 'Employee Performance Dashboard.',
                'accent' => '#4338ca',
            ],
            [
                'name' => 'POS Security',
                'url' => 'https://hrd.karismaerp.com',
                'icon' => 'fa-shield-alt',
                'description' => 'Security Activity Journal.',
                'accent' => '#0ea5e9',
            ],
            [
                'name' => 'Katalog Produk',
                'url' => 'https://kiukatalog.karismaerp.com',
                'icon' => 'fa-book-open',
                'description' => 'Digital Product Catalog.',
                'accent' => '#7c3aed',
            ],
            [
                'name' => 'Karisma Online',
                'url' => 'https://karismaonline.karismaerp.com',
                'icon' => 'fa-shopping-bag',
                'description' => 'Online Sales Platform.',
                'accent' => '#db2777',
                'hidden' => true,
            ],
            [
                'name' => 'Plafon Sales',
                'url' => 'https://plafon.karismaerp.com',
                'icon' => 'fa-credit-card',
                'description' => 'Customer Credit Limit Monitoring.',
                'accent' => '#0284c7',
            ],
            [
                'name' => 'Stockopname',
                'url' => 'https://stockopname.karismaerp.com',
                'icon' => 'fa-boxes',
                'description' => 'Stock counting and inventory reconciliation.',
                'accent' => '#14b8a6',
            ],
            [
                'name' => 'Penilaian Lingkungan Kerja',
                'url' => 'https://karismaerp.com/auth',
                'icon' => 'fa-leaf',
                'description' => 'Work Environment Assessment & Monitoring.',
                'accent' => '#16a34a',
            ],
            [
                'name' => 'Overdue',
                'url' => 'https://tagpd.karismaerp.com/',
                'icon' => 'fa-file-invoice-dollar',
                'description' => 'Overdue Invoices & Receivables Monitoring.',
                'accent' => '#e11d48',
            ],
            [
                'name' => 'Kios Transfer',
                'url' => 'https://kiostransfer.karismaerp.com',
                'icon' => 'fa-cash-register',
                'description' => 'Pencatatan metode pembayaran kios.',
                'accent' => '#0891b2',
            ],
            [
                'name' => 'Sapa Karisma',
                'url' => 'https://sapakarisma.karismaerp.com',
                'icon' => 'fa-users-cog',
                'description' => 'HRIS untuk manajemen SDM yang dikelola HRD.',
                'accent' => '#7c3aed',
            ],
   
        ];

        $apps = array_values(array_filter($apps, function ($app) {
            return empty($app['hidden']);
        }));

        $data = [
            'apps' => $apps,
            'app_count' => count($apps),
            'is_admin' => strtolower(trim((string) $this->session->userdata('username'))) === 'admin',
        ];

        $this->load->view('portal/index', $data);
    }
}
