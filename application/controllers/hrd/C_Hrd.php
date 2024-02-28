<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_Hrd extends CI_Controller
{


    function __construct()
    {
        parent::__construct();
        $this->load->model('M_Hrd');
        $this->load->helper('tanggal_helper');
    }

    public function index()
    {
        $data['page_title'] = 'KARISMA';

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/body.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    // TAMPILAN HALAMAN ADD

    public function hrd_add_tamu()
    {
        $data['page_title'] = 'KARISMA';
        $data['tamu'] = $this->M_Hrd->getalltamulb()->result();
        $data['laporan']    = $this->M_Hrd->get_all_tamu_lb()->result();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/hrd/v_add_tamu.php', $data);
        $this->load->view('partial/main/footer.php');
    }


    // TAMPILAN HALAMAN LAP DISTRIBUSI
    public function lap_distribusi()
    {
        $data['page_title'] = 'KARISMA';

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/hrd/lapbody.php', $data);
        $this->load->view('partial/main/footer.php');
        $this->load->view('content/hrd/ajaxhrd.php');
    }

    function get_lap_distribusi()
    {
        $list = $this->M_Hrd->get_datatables();
        $data = array();
        $no = $this->input->post('start');
        foreach ($list as $field) {
            $no++;
            $row = array();
            $row[] = $field->tglkeluar;
            $row[] = $field->tglmasuk;
            $row[] = $field->nopol;
            $row[] = $field->nolambung;
            $row[] = $field->namadriver;
            $row[] = $field->namahelper;
            $row[] = $field->tujuan;
            $row[] = $field->jamkeluar;
            $row[] = $field->kmkeluar;
            $row[] = $field->jammasuk;
            $row[] = $field->kmmasuk;
            $row[] = $field->keterangan;
            $row[] =  '<td>
            <a href="' . base_url('edit_lap_distribusi_hrd/' . $field->id . '') . '" class="btn btn-warning btn-sm">
            <i class="fa fa-solid fa-pencil-alt"></i>
            </a>
            </td>
            <a href="' . base_url('hapus_lap_distribusi_hrd/' . $field->id . '') . '" class="btn btn-danger btn-sm">
            <i class="fa fa-solid fa-trash-alt"></i>
            </a>
            </td>';
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->M_Hrd->count_all(),
            "recordsFiltered" => $this->M_Hrd->count_filtered(),
            "data" => $data,
        );
        //output dalam format JSON
        echo json_encode($output);
    }


    // FUNGSI CRUD
    public function input_lap_distribusi()
    {
        $nopol = $this->input->post('nopol');
        $nolambung = $this->input->post('nolambung');
        $nm_driver = $this->input->post('nm_driver');
        $nm_helper = $this->input->post('nm_helper');
        $tujuan = $this->input->post('tujuan');
        $tgl_keluar = $this->input->post('tgl_keluar');
        $jm_keluar = $this->input->post('jm_keluar');
        $km_keluar = $this->input->post('km_keluar');
        $tgl_masuk = $this->input->post('tgl_masuk');
        $jm_masuk = $this->input->post('jm_masuk');
        $km_masuk = $this->input->post('km_masuk');
        $keterangan = $this->input->post('keterangan');
        $inputer = $this->session->userdata('nama_user');

        $data = array(
            'nopol' => $nopol,
            'nolambung' => $nolambung,
            'namadriver' => $nm_driver,
            'namahelper' => $nm_helper,
            'tujuan' => $tujuan,
            'tglkeluar' => $tgl_keluar,
            'jamkeluar' => $jm_keluar,
            'kmkeluar' => $km_keluar,
            'tglmasuk' => $tgl_masuk,
            'jammasuk' => $jm_masuk,
            'kmmasuk' => $km_masuk,
            'keterangan' => $keterangan,
            'inputer' => $inputer,
        );
        $this->M_Hrd->addlapdistribusihrd($data);
        redirect('hrd_lap_distribusi');
    }

    public function v_edit_lap_distribusi($id)
    {
        $data['page_title'] = 'KARISMA';
        $data['laporan'] = $this->M_Hrd->get_lap_id($id)->result();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/hrd/v_lap_dis.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function v_hapus_lap_distribusi_hrd($id)
    {
        $data['page_title'] = 'KARISMA';
        $data['laporan'] = $this->M_Hrd->get_lap_id($id)->result();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/hrd/v_lap_dis_hapus.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function edit_lap_distribusi()
    {
        $id = $this->input->post('id_isi');
        $nopol = $this->input->post('nopol');
        $nolambung = $this->input->post('nolambung');
        $nm_driver = $this->input->post('nm_driver');
        $nm_helper = $this->input->post('nm_helper');
        $tujuan = $this->input->post('tujuan');
        $tgl_keluar = $this->input->post('tgl_keluar');
        $jm_keluar = $this->input->post('jm_keluar');
        $km_keluar = $this->input->post('km_keluar');
        $tgl_masuk = $this->input->post('tgl_masuk');
        $jm_masuk = $this->input->post('jm_masuk');
        $km_masuk = $this->input->post('km_masuk');
        $keterangan = $this->input->post('keterangan');
        $inputer = $this->session->userdata('nama_user');

        $data = array(
            'nopol' => $nopol,
            'nolambung' => $nolambung,
            'namadriver' => $nm_driver,
            'namahelper' => $nm_helper,
            'tujuan' => $tujuan,
            'tglkeluar' => $tgl_keluar,
            'jamkeluar' => $jm_keluar,
            'kmkeluar' => $km_keluar,
            'tglmasuk' => $tgl_masuk,
            'jammasuk' => $jm_masuk,
            'kmmasuk' => $km_masuk,
            'keterangan' => $keterangan,
            'inputer' => $inputer,
        );
        $this->M_Hrd->editlapdistribusihrd($id, $data);
        redirect('hrd_lap_distribusi');
    }
    public function hapus_lap_distribusi_hrd()
    {
        $id     = $this->input->post('id_isi');

        $this->M_Hrd->hapus_lap_distribusi_hrd($id);
        redirect('hrd_lap_distribusi');
    }


    public function lap_tamu()
    {
        $data['page_title'] = 'KARISMA';
        $data['laporan']    = $this->M_Hrd->get_all_tamu()->result();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/hrd/laptamubody.php', $data);
        $this->load->view('partial/main/footer.php');
        $this->load->view('content/hrd/ajaxhrd.php');
    }

    // FUNGSI CRUD

    public function tambah_lap_tamu()
    {

        date_default_timezone_set('Asia/Jakarta');
        $tgl = date("Y-m-d");
        $jm  = date("H:i");
        $nama = $this->input->post('nama');
        $perusahaan = $this->input->post('perusahaan');
        $alamat = $this->input->post('alamat');
        $jumlahpersonil = $this->input->post('jumlahpersonil');
        $tujuan = $this->input->post('tujuan');
        $keterangan = $this->input->post('keterangan');

        $data = array(
            'tanggal' => $tgl,
            'nama' => $nama,
            'perusahaan' => $perusahaan,
            'alamat' => $alamat,
            'jumlahpersonil' => $jumlahpersonil,
            'tujuan' => $tujuan,
            'jammasuk' => $jm,
            'keterangan' => $keterangan,
        );

        $this->M_Hrd->addlaptamuhrd($data);
        redirect('hrd_add_tamu');
    }

    public function konfirm_buku_tamu()
    {
        date_default_timezone_set('Asia/Jakarta');
        $jm  = date("H:i");

        $id = $this->input->post('id');
        $tanggal = $this->input->post('tanggal');
        $nama = $this->input->post('nama');
        $perusahaan = $this->input->post('perusahaan');
        $alamat = $this->input->post('alamat');
        $jumlahpersonil = $this->input->post('jumlahpersonil');
        $tujuan = $this->input->post('tujuan');
        $jammasuk = $this->input->post('jammasuk');
        $keterangan = $this->input->post('keterangan');

        $data = array(
            'tanggal' => $tanggal,
            'nama' => $nama,
            'perusahaan' => $perusahaan,
            'alamat' => $alamat,
            'jumlahpersonil' => $jumlahpersonil,
            'tujuan' => $tujuan,
            'jammasuk' => $jammasuk,
            'jamkeluar' => $jm,
            'keterangan' => $keterangan,
        );
        
        $this->M_Hrd->konfirmtamulb($data);
        $this->M_Hrd->hapus_lap_tamu_lb($id);
        redirect('hrd_add_tamu');
    }

    public function edit_lap_tamu()
    {
        $id = $this->input->post('id');
        $tanggal = $this->input->post('tanggal');
        $nama = $this->input->post('nama');
        $perusahaan = $this->input->post('perusahaan');
        $alamat = $this->input->post('alamat');
        $jumlahpersonil = $this->input->post('jumlahpersonil');
        $tujuan = $this->input->post('tujuan');
        $jammasuk = $this->input->post('jammasuk');
        $jamkeluar = $this->input->post('jamkeluar');
        $keterangan = $this->input->post('keterangan');

        $data = array(
            'id' => $id,
            'tanggal' => $tanggal,
            'nama' => $nama,
            'perusahaan' => $perusahaan,
            'alamat' => $alamat,
            'jumlahpersonil' => $jumlahpersonil,
            'tujuan' => $tujuan,
            'jammasuk' => $jammasuk,
            'jamkeluar' => $jamkeluar,
            'keterangan' => $keterangan,
        );
        $this->M_Hrd->editlaptamu($id, $data);
        redirect('hrd_lap_tamu');
    }
    public function hapus_lap_tamu_hrd()
    {
        $id     = $this->input->post('id_isi');

        $this->M_Hrd->hapus_lap_tamu_hrd($id);
        redirect('hrd_lap_tamu');
    }



    //karyawan keluar masuk
    public function lap_karykm()
    {
        $data['page_title'] = 'KARISMA';
        $data['laporan']    = $this->M_Hrd->get_all_laporan_karykm()->result();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/hrd/lapkaryawankmbody.php', $data);
        $this->load->view('partial/main/footer.php');
        $this->load->view('content/hrd/ajaxhrd.php');
    }

    // FUNGSI CRUD
    public function tambah_lap_karykm()
    {
        $tanggal = $this->input->post('tanggal');
        $nama = $this->input->post('nama');
        $departemen = $this->input->post('departemen');
        $status = $this->input->post('status');
        $jammasuk = $this->input->post('jammasuk');
        $jamkeluar = $this->input->post('jamkeluar');
        $nopol = $this->input->post('nopol');
        $keterangan = $this->input->post('keterangan');


        $data = array(
            'tanggal' => $tanggal,
            'nama' => $nama,
            'departemen' => $departemen,
            'status' => $status,
            'jammasuk' => $jammasuk,
            'jamkeluar' => $jamkeluar,
            'nopol' => $nopol,
            'keterangan' => $keterangan,

        );
        $this->M_Hrd->addlapkarykm($data);
        redirect('hrd_lap_Karyawan_KM');
    }
    public function edit_lap_karykm()
    {
        $id = $this->input->post('id');
        $tanggal = $this->input->post('tanggal');
        $nama = $this->input->post('nama');
        $departemen = $this->input->post('departemen');
        $status = $this->input->post('status');
        $jammasuk = $this->input->post('jammasuk');
        $jamkeluar = $this->input->post('jamkeluar');
        $nopol = $this->input->post('nopol');
        $keterangan = $this->input->post('keterangan');

        $data = array(
            'id' => $id,
            'tanggal' => $tanggal,
            'nama' => $nama,
            'status' => $status,
            'departemen' => $departemen,
            'jammasuk' => $jammasuk,
            'jamkeluar' => $jamkeluar,
            'nopol' => $nopol,
            'keterangan' => $keterangan,
        );
        $this->M_Hrd->editlapkarykm($id, $data);
        redirect('hrd_lap_Karyawan_KM');
    }
    public function hapus_lap_karykm()
    {
        $id     = $this->input->post('id_isi');

        $this->M_Hrd->hapuslapkarykm($id);
        redirect('hrd_lap_Karyawan_KM');
    }


    //karyawan expedisi
    public function lap_expedisi()
    {
        $data['page_title'] = 'KARISMA';
        $data['laporan']    = $this->M_Hrd->get_all_laporan_expedisi()->result();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/hrd/lapexpedisibody.php', $data);
        $this->load->view('partial/main/footer.php');
        $this->load->view('content/hrd/ajaxhrd.php');
    }

    public function kpi_digital()
    {
        $kduser = $this->session->userdata('kode');
        $data['kpi']    = $this->M_hrd->getkpi($kduser);
    }


    // FUNGSI CRUD
    public function tambah_lap_expedisi()
    {
        $tanggal = $this->input->post('tanggal');
        $jammasuk = $this->input->post('jammasuk');
        $jamkeluar = $this->input->post('jamkeluar');
        $nopol = $this->input->post('nopol');
        $namadriver = $this->input->post('namadriver');
        $notlpndriver = $this->input->post('notlpndriver');
        $perusahaanpengirim = $this->input->post('perusahaanpengirim');
        $namabarang = $this->input->post('namabarang');
        $jumlahbarang = $this->input->post('jumlahbarang');
        $keterangan = $this->input->post('keterangan');


        $data = array(
            'tanggal' => $tanggal,
            'jammasuk' => $jammasuk,
            'jamkeluar' => $jamkeluar,
            'nopol' => $nopol,
            'namadriver' => $namadriver,
            'notlpndriver' => $notlpndriver,
            'perusahaanpengirim' => $perusahaanpengirim,
            'namabarang' => $namabarang,
            'jumlahbarang' => $jumlahbarang,
            'keterangan' => $keterangan,

        );
        $this->M_Hrd->addlapexpedisi($data);
        redirect('hrd_lap_expedisi');
    }
    public function edit_lap_expedisi()
    {
        $id = $this->input->post('id');
        $tanggal = $this->input->post('tanggal');
        $jammasuk = $this->input->post('jammasuk');
        $jamkeluar = $this->input->post('jamkeluar');
        $nopol = $this->input->post('nopol');
        $namadriver = $this->input->post('namadriver');
        $notlpndriver = $this->input->post('notlpndriver');
        $perusahaanpengirim = $this->input->post('perusahaanpengirim');
        $namabarang = $this->input->post('namabarang');
        $jumlahbarang = $this->input->post('jumlahbarang');
        $keterangan = $this->input->post('keterangan');

        $data = array(
            'id' => $id,
            'tanggal' => $tanggal,
            'jammasuk' => $jammasuk,
            'jamkeluar' => $jamkeluar,
            'nopol' => $nopol,
            'namadriver' => $namadriver,
            'notlpndriver' => $notlpndriver,
            'perusahaanpengirim' => $perusahaanpengirim,
            'namabarang' => $namabarang,
            'jumlahbarang' => $jumlahbarang,
            'keterangan' => $keterangan,
        );
        $this->M_Hrd->editlapexpedisi($id, $data);
        redirect('hrd_lap_expedisi');
    }
    public function hapus_lap_expedisi()
    {
        $id     = $this->input->post('id_isi');

        $this->M_Hrd->hapuslapexpedisi($id);
        redirect('hrd_lap_expedisi');
    }


    //laporan issue
    public function lap_issue()
    {
        $data['page_title'] = 'KARISMA';
        $data['laporan']    = $this->M_Hrd->export_lap_issue();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/hrd/lapissuebody.php', $data);
        $this->load->view('partial/main/footer.php');
        $this->load->view('content/hrd/ajaxhrd.php');
    }

    // FUNGSI CRUD
    public function tambah_lap_issue()
    {
        $tanggal = $this->input->post('tanggal');
        $issue = $this->input->post('issue');
        $lokasi = $this->input->post('lokasi');
        $nama = $this->input->post('nama');

        $data = array(
            'tanggal' => $tanggal,
            'issue' => $issue,
            'lokasi' => $lokasi,
            'nama' => $nama,
        );
        $this->M_Hrd->addlapissue($data);
        redirect('hrd_lap_issue');
    }
    public function edit_lap_issue()
    {
        $id = $this->input->post('id');
        $tanggal = $this->input->post('tanggal');
        $issue = $this->input->post('issue');
        $lokasi = $this->input->post('lokasi');
        $nama = $this->input->post('nama');

        $data = array(
            'id' => $id,
            'tanggal' => $tanggal,
            'issue' => $issue,
            'lokasi' => $lokasi,
            'nama' => $nama,
        );
        $this->M_Hrd->editlapissue($id, $data);
        redirect('hrd_lap_issue');
    }
    public function hapus_lap_issue()
    {
        $id     = $this->input->post('id_isi');

        $this->M_Inventaris->hapuslapissue($id);
        redirect('hrd_lap_issue');
    }
    public function search_lap_distribusi()
    {
        $vkolom = $this->input->post('sc_bar');
        $vcari  = $this->input->post('nmsearch');
        $_SESSION['varkolom'] = $vkolom;
        $_SESSION['varcari'] = $vcari;

        redirect('v_cari_lap_distribusi');
    }
    public function v_cari_lap_distribusi()
    {
        $data['page_title'] = 'KARISMA';
        $valkolom       = $_SESSION['varkolom'];
        $varcari        = $_SESSION['varcari'];
        $data['vcari']    = $this->M_Hrd->cari_lap_distribusi($valkolom, $varcari)->result();
        $data['laporan']    = $this->M_Hrd->get_all_laporan()->result();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/hrd/v_cari_lap_distribusi.php', $data);
        $this->load->view('partial/main/footer.php');
        $this->load->view('content/hrd/ajaxhrd.php');
    }
    public function hrd_all_karyawan()
    {
        $data['page_title'] = 'HISTORI SERVICE TRUK';
        $data['karyawan']    = $this->M_Hrd->get_all_karyawan()->result();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/hrd/karyawanall.php', $data);
        $this->load->view('partial/main/footer.php');
        $this->load->view('content/hrd/ajaxhrd.php');
    }
    public function hrd_data_truk()
    {
        $data['page_title'] = 'HISTORI SERVICE TRUK';
        $data['truk']    = $this->M_Hrd->get_all_truk_service_histori()->result();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/hrd/trukbodyhrd.php', $data);
        $this->load->view('partial/main/footer.php');
        $this->load->view('content/hrd/ajaxhrd.php');
    }
    public function update_km_now_service_truk()
    {
        $id         = $this->input->post('id');
        $kmsekarang = $this->input->post('kmnow');

        $dataupdate = array(
            'km_sekarang' => $kmsekarang
        );
        $this->M_Hrd->update_km_service($id, $dataupdate);
        redirect('hrd_data_truk');
    }
    public function update_km_past_service_truk()
    {
        $id         = $this->input->post('id');
        $kmsekarang = $this->input->post('kmnow');

        $dataupdate = array(
            'km_sebelum' => $kmsekarang
        );
        $this->M_Hrd->update_km_service($id, $dataupdate);
        redirect('hrd_data_truk');
    }

    public function add_karyawan()
    {
        $nik = $this->input->post('nik_isi');
        $nmkaryawan = $this->input->post('nm_isi');
        $departemen = $this->input->post('departemen_i');
        $almt_isi = $this->input->post('alamat_isi');
        $tgl_lahir = $this->input->post('tgl_isi');

        $dataupdate = array(
            'nik'       => $nik,
            'nama_lengkap' => $nmkaryawan,
            'departemen' => $departemen,
            'alamat'    => $almt_isi,
            'tgl_lahir' => $tgl_lahir
        );
        $this->M_Hrd->add_karyawan($dataupdate);
        redirect('hrd_all_karyawan');
    }

    public function edit_karyawan()
    {
        $id         = $this->input->post('id');
        $nik = $this->input->post('nik_isi');
        $nmkaryawan = $this->input->post('nm_isi');
        $departemen = $this->input->post('departemen_i');
        $almt_isi = $this->input->post('alamat_isi');
        $tgl_lahir = $this->input->post('tgl_isi');

        $dataupdate = array(
            'nik'       => $nik,
            'nama_lengkap' => $nmkaryawan,
            'departemen' => $departemen,
            'alamat'    => $almt_isi,
            'tgl_lahir' => $tgl_lahir
        );
        $this->M_Hrd->update_karyawan($id, $dataupdate);
        redirect('hrd_all_karyawan');
    }
    public function export_laporan_issue()
    {
        include APPPATH . 'third_party/PHPExcel/PHPExcel.php';
        $excel = new PHPExcel();
        $excel->getProperties()->setCreator('it_karisma')
            ->setLastModifiedBy('lap_issue_hrd_')
            ->setTitle("Rekap Laporan Issue")
            ->setSubject("Rekap Laporan Issue")
            ->setDescription("Rekap Laporan Issue")
            ->setKeywords("Laporan Issue");

        $style_col = array(
            'font' => array('bold' => true),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ),
            'borders' => array(
                'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),
                'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),
                'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),
                'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN)
            )
        );

        $style_row = array(
            'alignment' => array(
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ),
            'borders' => array(
                'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),
                'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),
                'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),
                'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN)
            )
        );

        $excel->setActiveSheetIndex(0)->setCellValue('A1', "Rekap Laporan Issue");
        $excel->getActiveSheet()->mergeCells('A1:E1');
        $excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(TRUE);
        $excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(15);
        $excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $excel->setActiveSheetIndex(0)->setCellValue('A3', "NO");
        $excel->setActiveSheetIndex(0)->setCellValue('B3', "TANGGAL");
        $excel->setActiveSheetIndex(0)->setCellValue('C3', "DESKRIPSI ISU");
        $excel->setActiveSheetIndex(0)->setCellValue('D3', "LOKASI");
        $excel->setActiveSheetIndex(0)->setCellValue('E3', "NAMA PENEMU");

        $excel->getActiveSheet()->getStyle('A3')->applyFromArray($style_col);
        $excel->getActiveSheet()->getStyle('B3')->applyFromArray($style_col);
        $excel->getActiveSheet()->getStyle('C3')->applyFromArray($style_col);
        $excel->getActiveSheet()->getStyle('D3')->applyFromArray($style_col);
        $excel->getActiveSheet()->getStyle('E3')->applyFromArray($style_col);

        $export = $this->M_Hrd->export_lap_issue();

        $no = 1;
        $numrow = 4;
        foreach ($export as $data) {
            $excel->setActiveSheetIndex(0)->setCellValue('A' . $numrow, $no);
            $excel->setActiveSheetIndex(0)->setCellValue('B' . $numrow, $data->tanggal);
            $excel->setActiveSheetIndex(0)->setCellValue('C' . $numrow, $data->issue);
            $excel->setActiveSheetIndex(0)->setCellValue('D' . $numrow, $data->lokasi);
            $excel->setActiveSheetIndex(0)->setCellValue('E' . $numrow, $data->nama);
            $excel->getActiveSheet()->getStyle('A' . $numrow)->applyFromArray($style_row);
            $excel->getActiveSheet()->getStyle('B' . $numrow)->applyFromArray($style_row);
            $excel->getActiveSheet()->getStyle('C' . $numrow)->applyFromArray($style_row);
            $excel->getActiveSheet()->getStyle('D' . $numrow)->applyFromArray($style_row);
            $excel->getActiveSheet()->getStyle('E' . $numrow)->applyFromArray($style_row);
            $no++;
            $numrow++;
        }

        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(85);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(35);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
        $excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $excel->getActiveSheet(0)->setTitle("Rekap Laporan Issue HRD");
        $excel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="laporan_issue_hrd.xlsx"');
        header('Cache-Control: max-age=0');


        $write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $write->save('php://output');
    }

    // SERVERSIDE SYSTEM 

    function get_server_issue()
    {

        $list = $this->M_Hrd->get_datatables();
        $data = array();
        foreach ($list as $field) {
            $row = array();
            $row[] = $field->kode_barang;
            $row[] = $field->nama_barang;
            $row[] = $field->qty_box;
            $row[] = $field->qty_pcs;
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->M_Inventor->count_all(),
            "recordsFiltered" => $this->M_Inventor->count_filtered(),
            "data" => $data,
        );
        //output dalam format JSON
        echo json_encode($output);
    }
}
