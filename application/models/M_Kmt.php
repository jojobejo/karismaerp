<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * M_Kmt - Model utama untuk modul KMT CORN
 * Mencakup: Dashboard YTD, Omset, Operasional, DCA, Promo/Peralatan, Gaji, Retur
 */
class M_Kmt extends CI_Model {

    // ================================================================
    // WILAYAH
    // ================================================================
    public function get_wilayah() {
        return $this->db->get('tbkmt_wilayah')->result_array();
    }

    public function get_wilayah_by_id($id) {
        return $this->db->get_where('tbkmt_wilayah', ['id' => $id])->row_array();
    }

    // ================================================================
    // HELPER
    // ================================================================
    private function index_by_bulan($arr, $key) {
        $result = [];
        foreach ($arr as $row) {
            $result[(int)$row['bulan']] = (float)($row[$key] ?? 0);
        }
        return $result;
    }

    private $nama_bulan = [
        1=>'JAN',2=>'FEB',3=>'MAR',4=>'APR',
        5=>'MEI',6=>'JUN',7=>'JUL',8=>'AGU',
        9=>'SEP',10=>'OKT',11=>'NOV',12=>'DES'
    ];

    // ================================================================
    // DASHBOARD - Agregasi per bulan
    // ================================================================
    public function get_omset_per_bulan($tahun, $id_wilayah = null) {
        $this->db->select('bulan, SUM(penj_inc_ppn_neto) as total_omset');
        $this->db->from('tbkmt_omset');
        $this->db->where('tahun', $tahun);
        if ($id_wilayah) $this->db->where('id_wilayah', $id_wilayah);
        $this->db->group_by('bulan');
        $this->db->order_by('bulan', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_operasional_per_bulan($tahun, $id_wilayah = null) {
        $this->db->select('bulan, SUM(total_biaya) as total_operasional');
        $this->db->from('tbkmt_operasional');
        $this->db->where('tahun', $tahun);
        if ($id_wilayah) $this->db->where('id_wilayah', $id_wilayah);
        $this->db->group_by('bulan');
        return $this->db->get()->result_array();
    }

    public function get_dca_per_bulan($tahun, $id_wilayah = null) {
        $this->db->select('bulan, SUM(total_biaya) as total_dca');
        $this->db->from('tbkmt_dca');
        $this->db->where('tahun', $tahun);
        if ($id_wilayah) $this->db->where('id_wilayah', $id_wilayah);
        $this->db->group_by('bulan');
        return $this->db->get()->result_array();
    }

    public function get_peralatan_per_bulan($tahun, $id_wilayah = null) {
        $this->db->select('bulan, SUM(total_biaya) as total_peralatan');
        $this->db->from('tbkmt_promo_material');
        $this->db->where('tahun', $tahun);
        if ($id_wilayah) $this->db->where('id_wilayah', $id_wilayah);
        $this->db->group_by('bulan');
        return $this->db->get()->result_array();
    }

    public function get_others_per_bulan($tahun, $id_wilayah = null) {
        $this->db->select('bulan, SUM(total_biaya) as total_others');
        $this->db->from('tbkmt_others');
        $this->db->where('tahun', $tahun);
        if ($id_wilayah) $this->db->where('id_wilayah', $id_wilayah);
        $this->db->group_by('bulan');
        return $this->db->get()->result_array();
    }

    public function get_gaji_per_bulan($tahun, $id_wilayah = null) {
        $bulan_col = [
            1=>'gaji_jan', 2=>'gaji_feb', 3=>'gaji_mar', 4=>'gaji_apr',
            5=>'gaji_mei', 6=>'gaji_jun', 7=>'gaji_jul', 8=>'gaji_agu',
            9=>'gaji_sep', 10=>'gaji_okt', 11=>'gaji_nov', 12=>'gaji_des',
        ];
        $result = [];
        foreach ($bulan_col as $no_bulan => $col) {
            $this->db->select("$no_bulan as bulan, COALESCE(SUM($col), 0) as total_gaji");
            $this->db->from('tbkmt_gaji');
            $this->db->where('tahun', $tahun);
            if ($id_wilayah) $this->db->where('id_wilayah', $id_wilayah);
            $row = $this->db->get()->row_array();
            $result[] = $row;
        }
        return $result;
    }

    public function get_ytd($tahun, $id_wilayah = null) {
        $omset       = $this->index_by_bulan($this->get_omset_per_bulan($tahun, $id_wilayah),       'total_omset');
        $operasional = $this->index_by_bulan($this->get_operasional_per_bulan($tahun, $id_wilayah), 'total_operasional');
        $dca         = $this->index_by_bulan($this->get_dca_per_bulan($tahun, $id_wilayah),         'total_dca');
        $peralatan   = $this->index_by_bulan($this->get_peralatan_per_bulan($tahun, $id_wilayah),   'total_peralatan');
        $others      = $this->index_by_bulan($this->get_others_per_bulan($tahun, $id_wilayah),      'total_others');
        $gaji        = $this->index_by_bulan($this->get_gaji_per_bulan($tahun, $id_wilayah),        'total_gaji');

        $data = [];
        for ($b = 1; $b <= 12; $b++) {
            $o  = $omset[$b]       ?? 0;
            $op = $operasional[$b] ?? 0;
            $d  = $dca[$b]         ?? 0;
            $p  = $peralatan[$b]   ?? 0;
            $ot = $others[$b]      ?? 0;
            $g  = $gaji[$b]        ?? 0;
            $total_biaya    = $op + $d + $p + $ot + $g;
            $cost_per_hasil = ($o > 0) ? round(($total_biaya / $o) * 100, 2) : 0;
            $data[] = [
                'no_bulan'       => $b,
                'bulan'          => $this->nama_bulan[$b],
                'omset'          => $o,
                'operasional'    => $op,
                'dca'            => $d,
                'peralatan'      => $p,
                'others'         => $ot,
                'gaji'           => $g,
                'total_biaya'    => $total_biaya,
                'cost_per_hasil' => $cost_per_hasil,
            ];
        }
        return $data;
    }

    public function get_summary_cards($tahun, $id_wilayah = null) {
        $ytd = $this->get_ytd($tahun, $id_wilayah);
        $s = array_fill_keys(['total_omset','total_biaya','total_gaji','total_operasional'], 0);
        foreach ($ytd as $row) {
            $s['total_omset']       += $row['omset'];
            $s['total_biaya']       += $row['total_biaya'];
            $s['total_gaji']        += $row['gaji'];
            $s['total_operasional'] += $row['operasional'];
        }
        $s['cost_per_hasil'] = $s['total_omset'] > 0
            ? round($s['total_biaya'] / $s['total_omset'] * 100, 2) : 0;
        return $s;
    }

    public function get_cost_per_hasil_wilayah($tahun) {
        $wilayah_list = $this->get_wilayah();
        $result = [];
        foreach ($wilayah_list as $w) {
            $ytd = $this->get_ytd($tahun, $w['id']);
            $q = [1=>0,2=>0,3=>0,4=>0];
            $q_omset = [1=>0,2=>0,3=>0,4=>0];
            foreach ($ytd as $row) {
                $quarter = (int)ceil($row['no_bulan'] / 3);
                $q[$quarter]       += $row['total_biaya'];
                $q_omset[$quarter] += $row['omset'];
            }
            $total_biaya = array_sum($q);
            $total_omset = array_sum($q_omset);
            $result[] = [
                'wilayah' => $w['nama_wilayah'],
                'q1' => $q_omset[1] > 0 ? round($q[1]/$q_omset[1]*100,1) : 0,
                'q2' => $q_omset[2] > 0 ? round($q[2]/$q_omset[2]*100,1) : 0,
                'q3' => $q_omset[3] > 0 ? round($q[3]/$q_omset[3]*100,1) : 0,
                'q4' => $q_omset[4] > 0 ? round($q[4]/$q_omset[4]*100,1) : 0,
                'total' => $total_omset > 0 ? round($total_biaya/$total_omset*100,1) : 0,
            ];
        }
        return $result;
    }

    // ================================================================
    // OMSET
    // ================================================================
    public function get_omset_list($filter = []) {
        $this->db->select('o.*, w.nama_wilayah');
        $this->db->from('tbkmt_omset o');
        $this->db->join('tbkmt_wilayah w', 'w.id = o.id_wilayah', 'left');
        if (!empty($filter['tahun']))      $this->db->where('o.tahun', $filter['tahun']);
        if (!empty($filter['bulan']))      $this->db->where('o.bulan', $filter['bulan']);
        if (!empty($filter['id_wilayah'])) $this->db->where('o.id_wilayah', $filter['id_wilayah']);
        $this->db->order_by('o.tanggal', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_omset_by_id($id) {
        $this->db->select('o.*, w.nama_wilayah');
        $this->db->from('tbkmt_omset o');
        $this->db->join('tbkmt_wilayah w', 'w.id = o.id_wilayah', 'left');
        $this->db->where('o.id', $id);
        return $this->db->get()->row_array();
    }

    public function insert_omset($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('tbkmt_omset', $data);
    }

    public function update_omset($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('tbkmt_omset', $data);
    }

    public function delete_omset($id) {
        return $this->db->delete('tbkmt_omset', ['id' => $id]);
    }

    // ================================================================
    // OPERASIONAL
    // ================================================================
    public function get_operasional_list($filter = []) {
        $this->db->select('op.*, w.nama_wilayah');
        $this->db->from('tbkmt_operasional op');
        $this->db->join('tbkmt_wilayah w', 'w.id = op.id_wilayah', 'left');
        if (!empty($filter['tahun']))      $this->db->where('op.tahun', $filter['tahun']);
        if (!empty($filter['bulan']))      $this->db->where('op.bulan', $filter['bulan']);
        if (!empty($filter['id_wilayah'])) $this->db->where('op.id_wilayah', $filter['id_wilayah']);
        $this->db->order_by('op.tanggal', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_operasional_by_id($id) {
        $this->db->select('op.*, w.nama_wilayah');
        $this->db->from('tbkmt_operasional op');
        $this->db->join('tbkmt_wilayah w', 'w.id = op.id_wilayah', 'left');
        $this->db->where('op.id', $id);
        return $this->db->get()->row_array();
    }

    public function insert_operasional($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('tbkmt_operasional', $data);
    }

    public function update_operasional($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('tbkmt_operasional', $data);
    }

    public function delete_operasional($id) {
        return $this->db->delete('tbkmt_operasional', ['id' => $id]);
    }

    // ================================================================
    // DCA
    // ================================================================
    public function get_dca_list($filter = []) {
        $this->db->select('d.*, w.nama_wilayah');
        $this->db->from('tbkmt_dca d');
        $this->db->join('tbkmt_wilayah w', 'w.id = d.id_wilayah', 'left');
        if (!empty($filter['tahun']))      $this->db->where('d.tahun', $filter['tahun']);
        if (!empty($filter['bulan']))      $this->db->where('d.bulan', $filter['bulan']);
        if (!empty($filter['id_wilayah'])) $this->db->where('d.id_wilayah', $filter['id_wilayah']);
        $this->db->order_by('d.tanggal_dca', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_dca_by_id($id) {
        $this->db->select('d.*, w.nama_wilayah');
        $this->db->from('tbkmt_dca d');
        $this->db->join('tbkmt_wilayah w', 'w.id = d.id_wilayah', 'left');
        $this->db->where('d.id', $id);
        return $this->db->get()->row_array();
    }

    public function insert_dca($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('tbkmt_dca', $data);
    }

    public function update_dca($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('tbkmt_dca', $data);
    }

    public function delete_dca($id) {
        return $this->db->delete('tbkmt_dca', ['id' => $id]);
    }

    // ================================================================
    // PROMO / PERALATAN
    // ================================================================
    public function get_promo_list($filter = []) {
        $this->db->select('p.*, w.nama_wilayah');
        $this->db->from('tbkmt_promo_material p');
        $this->db->join('tbkmt_wilayah w', 'w.id = p.id_wilayah', 'left');
        if (!empty($filter['tahun']))      $this->db->where('p.tahun', $filter['tahun']);
        if (!empty($filter['bulan']))      $this->db->where('p.bulan', $filter['bulan']);
        if (!empty($filter['id_wilayah'])) $this->db->where('p.id_wilayah', $filter['id_wilayah']);
        $this->db->order_by('p.tanggal', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_promo_by_id($id) {
        $this->db->select('p.*, w.nama_wilayah');
        $this->db->from('tbkmt_promo_material p');
        $this->db->join('tbkmt_wilayah w', 'w.id = p.id_wilayah', 'left');
        $this->db->where('p.id', $id);
        return $this->db->get()->row_array();
    }

    public function insert_promo($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('tbkmt_promo_material', $data);
    }

    public function update_promo($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('tbkmt_promo_material', $data);
    }

    public function delete_promo($id) {
        return $this->db->delete('tbkmt_promo_material', ['id' => $id]);
    }

    // ================================================================
    // GAJI
    // ================================================================
    public function get_gaji_list($filter = []) {
        $this->db->select('g.*, w.nama_wilayah');
        $this->db->from('tbkmt_gaji g');
        $this->db->join('tbkmt_wilayah w', 'w.id = g.id_wilayah', 'left');
        if (!empty($filter['tahun']))      $this->db->where('g.tahun', $filter['tahun']);
        if (!empty($filter['id_wilayah'])) $this->db->where('g.id_wilayah', $filter['id_wilayah']);
        $this->db->order_by('w.nama_wilayah', 'ASC');
        $this->db->order_by('g.nama', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_gaji_by_id($id) {
        $this->db->select('g.*, w.nama_wilayah');
        $this->db->from('tbkmt_gaji g');
        $this->db->join('tbkmt_wilayah w', 'w.id = g.id_wilayah', 'left');
        $this->db->where('g.id', $id);
        return $this->db->get()->row_array();
    }

    public function insert_gaji($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('tbkmt_gaji', $data);
    }

    public function update_gaji($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('tbkmt_gaji', $data);
    }

    public function delete_gaji($id) {
        return $this->db->delete('tbkmt_gaji', ['id' => $id]);
    }

    public function get_total_gaji_per_bulan_wilayah($tahun, $id_wilayah = null) {
        $bulan_cols = [
            'gaji_jan','gaji_feb','gaji_mar','gaji_apr','gaji_mei','gaji_jun',
            'gaji_jul','gaji_agu','gaji_sep','gaji_okt','gaji_nov','gaji_des'
        ];
        $select = 'w.nama_wilayah, ' . implode(', ', array_map(fn($c) => "SUM($c) as $c", $bulan_cols));
        $this->db->select($select);
        $this->db->from('tbkmt_gaji g');
        $this->db->join('tbkmt_wilayah w', 'w.id = g.id_wilayah', 'left');
        $this->db->where('g.tahun', $tahun);
        if ($id_wilayah) $this->db->where('g.id_wilayah', $id_wilayah);
        $this->db->group_by('g.id_wilayah');
        return $this->db->get()->result_array();
    }

    // ================================================================
    // RETUR
    // ================================================================
    public function get_retur_list($filter = []) {
        $this->db->select('r.*, w.nama_wilayah');
        $this->db->from('tbkmt_retur r');
        $this->db->join('tbkmt_wilayah w', 'w.id = r.id_wilayah', 'left');
        if (!empty($filter['tahun']))      $this->db->where('r.tahun', $filter['tahun']);
        if (!empty($filter['bulan']))      $this->db->where('r.bulan', $filter['bulan']);
        if (!empty($filter['id_wilayah'])) $this->db->where('r.id_wilayah', $filter['id_wilayah']);
        $this->db->order_by('r.tanggal_retur', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_retur_by_id($id) {
        $this->db->select('r.*, w.nama_wilayah');
        $this->db->from('tbkmt_retur r');
        $this->db->join('tbkmt_wilayah w', 'w.id = r.id_wilayah', 'left');
        $this->db->where('r.id', $id);
        return $this->db->get()->row_array();
    }

    public function insert_retur($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('tbkmt_retur', $data);
    }

    public function update_retur($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('tbkmt_retur', $data);
    }

    public function delete_retur($id) {
        return $this->db->delete('tbkmt_retur', ['id' => $id]);
    }

    public function get_summary_retur($tahun, $id_wilayah = null) {
        $this->db->select('w.nama_wilayah, SUM(r.nilai_retur) as total_retur, COUNT(r.id) as jumlah');
        $this->db->from('tbkmt_retur r');
        $this->db->join('tbkmt_wilayah w', 'w.id = r.id_wilayah', 'left');
        $this->db->where('r.tahun', $tahun);
        if ($id_wilayah) $this->db->where('r.id_wilayah', $id_wilayah);
        $this->db->group_by('r.id_wilayah');
        return $this->db->get()->result_array();
    }
}
