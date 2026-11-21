<style>
.ftd-toolbar {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 10px 14px;
    margin-bottom: 12px;
}

.ftd-workspace {
    display: grid;
    grid-template-columns: 260px minmax(0, 1fr) 275px;
    gap: 12px;
    align-items: start;
}

@media (max-width: 1200px) {
    .ftd-workspace {
        grid-template-columns: 1fr;
    }
}

.ftd-panel {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    overflow: hidden;
}

.ftd-panel-hdr {
    background: #f1f5f9;
    border-bottom: 1px solid #cbd5e1;
    padding: 8px 12px;
    font-size: 12.5px;
    font-weight: 700;
    color: #1e293b;
}

.ftd-panel-body {
    padding: 10px 12px;
    max-height: 76vh;
    overflow-y: auto;
    font-size: 12px;
}

.ftd-tree-band {
    font-weight: 700;
    font-size: 11.5px;
    color: #0369a1;
    margin-top: 8px;
    margin-bottom: 4px;
    text-transform: uppercase;
}

.ftd-tree-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    padding: 4px 7px;
    border-radius: 4px;
    cursor: pointer;
    border: 1px solid transparent;
    margin-bottom: 2px;
}

.ftd-tree-item:hover {
    background: #f8fafc;
}

.ftd-tree-item.active {
    background: #e0f2fe;
    border-color: #38bdf8;
    font-weight: 600;
}

.ftd-canvas-wrap {
    background: #94a3b8;
    border: 1px solid #64748b;
    border-radius: 6px;
    padding: 28px 24px 24px 32px;
    overflow: auto;
    min-height: 76vh;
    width: 100%;
    box-sizing: border-box;
}

.ftd-paper-shell {
    position: relative;
    background: #ffffff;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.25);
    user-select: none;
    margin: 0 auto;
}

.ftd-ruler-top {
    position: absolute;
    top: -18px;
    left: 0;
    right: 0;
    height: 18px;
    background: #f8fafc;
    border-bottom: 1px solid #64748b;
    font-size: 9px;
    color: #475569;
    overflow: hidden;
}

.ftd-ruler-left {
    position: absolute;
    top: 0;
    left: -20px;
    bottom: 0;
    width: 20px;
    background: #f8fafc;
    border-right: 1px solid #64748b;
    font-size: 9px;
    color: #475569;
    overflow: hidden;
}

.ftd-grid-bg {
    background-image:
        linear-gradient(to right, rgba(148, 163, 184, 0.22) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(148, 163, 184, 0.22) 1px, transparent 1px);
}

.ftd-band {
    position: relative;
    border-bottom: 1px dashed #94a3b8;
    box-sizing: border-box;
}

.ftd-band-bar {
    background: #e2e8f0;
    border-top: 1px solid #94a3b8;
    border-bottom: 1px solid #94a3b8;
    color: #1e293b;
    font-size: 10px;
    font-weight: 700;
    padding: 1px 6px;
    line-height: 14px;
}

.ftd-band-bar.master {
    background: #fde68a;
    border-color: #d97706;
    color: #78350f;
}

.ftd-memo {
    position: absolute;
    box-sizing: border-box;
    border: 1px dashed #64748b;
    background: rgba(255, 255, 255, 0.72);
    cursor: move;
    overflow: hidden;
    white-space: nowrap;
    padding: 0 2px;
}

.ftd-memo:hover {
    border-color: #0284c7;
    background: rgba(224, 242, 254, 0.45);
}

.ftd-memo.selected {
    border: 1.5px solid #0284c7;
    background: rgba(186, 230, 253, 0.45);
    z-index: 20;
}

.ftd-memo.hidden-el {
    opacity: 0.35;
    border-style: dotted;
}

.ftd-resize-handle {
    position: absolute;
    right: -1px;
    bottom: -1px;
    width: 8px;
    height: 8px;
    background: #0284c7;
    cursor: nwse-resize;
    display: none;
}

.ftd-memo.selected .ftd-resize-handle {
    display: block;
}

.ftd-prop-row {
    margin-bottom: 8px;
}

.ftd-prop-row label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 2px;
}
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Karisma" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Visual Template Designer Faktur (LX-310)</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= base_url('sales_order') ?>" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Sales Order
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Toolbar Designer -->
            <div class="ftd-toolbar">
                <div class="d-flex flex-wrap align-items-center" style="gap:8px;">
                    <label class="mb-0 small font-weight-bold">Template:</label>
                    <select id="selectTemplate" class="form-control form-control-sm" style="width:220px;">
                        <?php foreach ($templates as $t): ?>
                            <option value="<?= (int) $t['id_template'] ?>" <?= ((int) $t['id_template'] === (int) $active['id_template']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['nama_template']) ?><?= ((int) $t['is_default'] === 1) ? ' (Default)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" id="inputTemplateName" class="form-control form-control-sm" style="width:210px;" value="<?= htmlspecialchars($active['nama_template']) ?>" placeholder="Nama Template...">
                    <label class="mb-0 small font-weight-bold ml-2">Zoom:</label>
                    <select id="selectZoom" class="form-control form-control-sm" style="width:85px;">
                        <option value="0.85">85%</option>
                        <option value="1" selected>100%</option>
                        <option value="1.25">125%</option>
                        <option value="1.5">150%</option>
                    </select>
                    <div class="custom-control custom-checkbox ml-2">
                        <input type="checkbox" class="custom-control-input" id="chkSnapGrid" checked>
                        <label class="custom-control-label small" for="chkSnapGrid">Snap 0.5 mm</label>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center" style="gap:6px;">
                    <button type="button" class="btn btn-sm btn-primary" id="btnSaveTemplate">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                    <button type="button" class="btn btn-sm btn-info" id="btnSaveAsTemplate">
                        <i class="fas fa-copy mr-1"></i> Simpan Baru
                    </button>
                    <button type="button" class="btn btn-sm btn-success" id="btnSetDefault">
                        <i class="fas fa-check-circle mr-1"></i> Jadikan Default
                    </button>
                    <button type="button" class="btn btn-sm btn-warning" id="btnResetDefault">
                        <i class="fas fa-undo mr-1"></i> Reset TTB 2022
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" id="btnDeleteTemplate">
                        <i class="fas fa-trash mr-1"></i> Hapus
                    </button>
                </div>
            </div>

            <div id="ftdAlertBox"></div>

            <!-- Workspace 3 Kolom: Tree/Kertas | Kanvas | Properti -->
            <div class="ftd-workspace">
                <!-- Panel Kiri -->
                <div class="ftd-panel">
                    <div class="ftd-panel-hdr">Komponen & Halaman</div>
                    <div class="ftd-panel-body">
                        <div class="mb-2 pb-2 border-bottom">
                            <div class="font-weight-bold text-primary mb-1" style="font-size:11.5px;">UKURAN KERTAS & MARGIN (mm)</div>
                            <div class="form-row">
                                <div class="col-6 mb-1">
                                    <label class="mb-0 small">Lebar</label>
                                    <input type="number" step="0.5" class="form-control form-control-sm ftd-page-inp" data-field="paper_width_mm" value="<?= (float) $active['paper_width_mm'] ?>">
                                </div>
                                <div class="col-6 mb-1">
                                    <label class="mb-0 small">Tinggi</label>
                                    <input type="number" step="0.5" class="form-control form-control-sm ftd-page-inp" data-field="paper_height_mm" value="<?= (float) $active['paper_height_mm'] ?>">
                                </div>
                                <div class="col-6 mb-1">
                                    <label class="mb-0 small">Margin Atas</label>
                                    <input type="number" step="0.5" class="form-control form-control-sm ftd-page-inp" data-field="margin_top_mm" value="<?= (float) $active['margin_top_mm'] ?>">
                                </div>
                                <div class="col-6 mb-1">
                                    <label class="mb-0 small">Margin Kanan</label>
                                    <input type="number" step="0.5" class="form-control form-control-sm ftd-page-inp" data-field="margin_right_mm" value="<?= (float) $active['margin_right_mm'] ?>">
                                </div>
                                <div class="col-6 mb-1">
                                    <label class="mb-0 small">Margin Bawah</label>
                                    <input type="number" step="0.5" class="form-control form-control-sm ftd-page-inp" data-field="margin_bottom_mm" value="<?= (float) $active['margin_bottom_mm'] ?>">
                                </div>
                                <div class="col-6 mb-1">
                                    <label class="mb-0 small">Margin Kiri</label>
                                    <input type="number" step="0.5" class="form-control form-control-sm ftd-page-inp" data-field="margin_left_mm" value="<?= (float) $active['margin_left_mm'] ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-2 pb-2 border-bottom">
                            <div class="font-weight-bold text-primary mb-1" style="font-size:11.5px;">TINGGI BAND (mm)</div>
                            <div class="form-row">
                                <div class="col-6 mb-1">
                                    <label class="mb-0 small">PageHeader</label>
                                    <input type="number" step="0.5" class="form-control form-control-sm ftd-band-inp" data-band="page_header_h">
                                </div>
                                <div class="col-6 mb-1">
                                    <label class="mb-0 small">ColHeader</label>
                                    <input type="number" step="0.5" class="form-control form-control-sm ftd-band-inp" data-band="col_header_h">
                                </div>
                                <div class="col-6 mb-1">
                                    <label class="mb-0 small">Jarak ColHdr</label>
                                    <input type="number" step="0.5" class="form-control form-control-sm ftd-band-inp" data-band="col_header_mt">
                                </div>
                                <div class="col-6 mb-1">
                                    <label class="mb-0 small">Baris Item</label>
                                    <input type="number" step="0.5" class="form-control form-control-sm ftd-band-inp" data-band="master_row_h">
                                </div>
                                <div class="col-6">
                                    <label class="mb-0 small">PageFooter</label>
                                    <input type="number" step="0.5" class="form-control form-control-sm ftd-band-inp" data-band="page_footer_h">
                                </div>
                            </div>
                        </div>

                        <div id="ftdComponentTree"></div>
                    </div>
                </div>

                <!-- Kanvas Tengah -->
                <div class="ftd-canvas-wrap">
                    <div class="ftd-paper-shell" id="ftdPaperShell">
                        <div class="ftd-ruler-top" id="ftdRulerTop"></div>
                        <div class="ftd-ruler-left" id="ftdRulerLeft"></div>
                        <div id="ftdCanvasBody" class="ftd-grid-bg" style="position:relative;overflow:hidden;"></div>
                    </div>
                </div>

                <!-- Panel Kanan (Inspector Properti) -->
                <div class="ftd-panel">
                    <div class="ftd-panel-hdr">Properti Komponen</div>
                    <div class="ftd-panel-body" id="ftdPropertyPanel">
                        <div class="text-muted text-center py-4">
                            Klik salah satu elemen di kanvas atau di daftar kiri untuk mengatur posisi, ukuran, dan gaya.
                            <div class="mt-2 small">Tips: Gunakan tombol panah <b>↑ ↓ ← →</b> untuk geser halus 0.5 mm (tahan <b>Shift</b> untuk 2 mm).</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
</div>

<script>
(function () {
    const PX_PER_MM = 3.779527559;
    let tpl = <?= json_encode($active) ?>;
    let zoom = 1;
    let selectedKey = null;

    function roundHalf(val) {
        return Math.round(val * 2) / 2;
    }

    function showAlert(msg, type) {
        const cls = type === 'error' ? 'alert-danger' : 'alert-success';
        $('#ftdAlertBox').html(
            '<div class="alert ' + cls + ' alert-dismissible fade show py-2 small" role="alert">'
            + $('<div>').text(msg).html()
            + '<button type="button" class="close py-1" data-dismiss="alert">&times;</button></div>'
        );
    }

    function syncPageInputs() {
        $('.ftd-page-inp').each(function () {
            const f = $(this).data('field');
            $(this).val(parseFloat(tpl[f] || 0));
        });
        $('.ftd-band-inp').each(function () {
            const b = $(this).data('band');
            $(this).val(parseFloat((tpl.bands && tpl.bands[b]) || 0));
        });
    }

    function renderRulers() {
        const wMm = parseFloat(tpl.paper_width_mm || 210);
        const hMm = parseFloat(tpl.paper_height_mm || 140);
        const scale = PX_PER_MM * zoom;

        let topHtml = '';
        for (let cm = 0; cm <= Math.floor(wMm / 10); cm++) {
            topHtml += '<span style="position:absolute;left:' + (cm * 10 * scale + 2) + 'px;top:2px;border-left:1px solid #64748b;padding-left:2px;height:16px;">' + cm + '</span>';
        }
        $('#ftdRulerTop').html(topHtml);

        let leftHtml = '';
        for (let cm = 0; cm <= Math.floor(hMm / 10); cm++) {
            leftHtml += '<span style="position:absolute;top:' + (cm * 10 * scale + 1) + 'px;left:2px;border-top:1px solid #64748b;width:18px;">' + cm + '</span>';
        }
        $('#ftdRulerLeft').html(leftHtml);
    }

    function renderTree() {
        const bandLabels = {
            page_header: 'PageHeader1',
            column_header: 'ColumnHeader1',
            master_data: 'MasterData (Baris Item)',
            page_footer: 'PageFooter1'
        };
        let html = '';
        ['page_header', 'column_header', 'master_data', 'page_footer'].forEach(function (bandKey) {
            html += '<div class="ftd-tree-band">' + bandLabels[bandKey] + '</div>';
            tpl.elements.filter(function (el) { return el.band === bandKey; }).forEach(function (el) {
                const activeCls = (el.key === selectedKey) ? ' active' : '';
                html += '<div class="ftd-tree-item' + activeCls + '" data-key="' + el.key + '">'
                    + '<span class="text-truncate">' + $('<div>').text(el.label).html() + '</span>'
                    + '<input type="checkbox" class="ftd-tree-vis" data-key="' + el.key + '" ' + (el.visible ? 'checked' : '') + ' title="Tampilkan saat cetak">'
                    + '</div>';
            });
        });
        $('#ftdComponentTree').html(html);
    }

    function renderCanvas() {
        const scale = PX_PER_MM * zoom;
        const paperW = parseFloat(tpl.paper_width_mm || 210);
        const paperH = parseFloat(tpl.paper_height_mm || 140);
        const mTop = parseFloat(tpl.margin_top_mm || 0);
        const mRight = parseFloat(tpl.margin_right_mm || 0);
        const mBottom = parseFloat(tpl.margin_bottom_mm || 0);
        const mLeft = parseFloat(tpl.margin_left_mm || 0);
        const b = tpl.bands || {};

        const contentW = Math.max(50, paperW - mLeft - mRight);
        const contentH = Math.max(50, paperH - mTop - mBottom);

        $('#ftdPaperShell').css({
            width: (paperW * scale) + 'px',
            height: (paperH * scale) + 'px',
            paddingTop: (mTop * scale) + 'px',
            paddingRight: (mRight * scale) + 'px',
            paddingBottom: (mBottom * scale) + 'px',
            paddingLeft: (mLeft * scale) + 'px'
        });

        const gridStep = 5 * scale;
        const $body = $('#ftdCanvasBody');
        $body.css({
            width: (contentW * scale) + 'px',
            height: (contentH * scale) + 'px',
            backgroundSize: gridStep + 'px ' + gridStep + 'px'
        }).empty();

        const bandDefs = [
            { key: 'page_header', title: 'PageHeader: PageHeader1', h: parseFloat(b.page_header_h || 13.76), mt: 0, cls: '' },
            { key: 'column_header', title: 'ColumnHeader: ColumnHeader1', h: parseFloat(b.col_header_h || 21.17), mt: parseFloat(b.col_header_mt || 2), cls: '' },
            { key: 'master_data', title: 'MasterData: Band3 (Contoh 1 Baris)', h: parseFloat(b.master_row_h || 10), mt: 0, cls: 'master' },
            { key: 'page_footer', title: 'PageFooter: PageFooter1', h: parseFloat(b.page_footer_h || 34.78), mt: 0, cls: '', isFooter: true }
        ];

        bandDefs.forEach(function (bd) {
            const $bar = $('<div class="ftd-band-bar ' + bd.cls + '"></div>').text(bd.title);
            const $band = $('<div class="ftd-band"></div>').attr('data-band', bd.key).css({
                width: (contentW * scale) + 'px',
                height: (bd.h * scale) + 'px',
                marginTop: (bd.mt * scale) + 'px'
            });

            if (bd.isFooter) {
                const $wrap = $('<div></div>').css({
                    position: 'absolute',
                    left: 0,
                    bottom: 0,
                    width: (contentW * scale) + 'px'
                });
                $wrap.append($bar).append($band);
                $body.append($wrap);
            } else {
                $body.append($bar).append($band);
            }

            tpl.elements.filter(function (el) { return el.band === bd.key; }).forEach(function (el) {
                const txt = (el.custom_text && el.custom_text.trim() !== '') ? el.custom_text : (el.sample_text || el.label);
                const $memo = $('<div class="ftd-memo"></div>')
                    .attr('data-key', el.key)
                    .toggleClass('selected', el.key === selectedKey)
                    .toggleClass('hidden-el', !el.visible)
                    .css({
                        left: (parseFloat(el.left_mm) * scale) + 'px',
                        top: (parseFloat(el.top_mm) * scale) + 'px',
                        width: (parseFloat(el.width_mm) * scale) + 'px',
                        height: (parseFloat(el.height_mm) * scale) + 'px',
                        lineHeight: (parseFloat(el.height_mm) * scale) + 'px',
                        fontSize: (parseFloat(el.font_size_pt || 11) * zoom) + 'pt',
                        fontWeight: el.bold ? 'bold' : 'normal',
                        fontStyle: el.italic ? 'italic' : 'normal',
                        textAlign: el.align || 'left',
                        outline: el.border ? '1px solid #000' : 'none'
                    })
                    .text(txt)
                    .append('<div class="ftd-resize-handle"></div>');
                $band.append($memo);
            });
        });

        renderRulers();
    }

    function renderProperties() {
        const el = tpl.elements.find(function (item) { return item.key === selectedKey; });
        if (!el) {
            $('#ftdPropertyPanel').html(
                '<div class="text-muted text-center py-4">'
                + 'Klik salah satu elemen di kanvas atau di daftar kiri untuk mengatur posisi, ukuran, dan gaya.'
                + '<div class="mt-2 small">Tips: Gunakan tombol panah <b>↑ ↓ ← →</b> untuk geser halus 0.5 mm (tahan <b>Shift</b> untuk 2 mm).</div>'
                + '</div>'
            );
            return;
        }

        const html = ''
            + '<div class="font-weight-bold text-primary mb-2">' + $('<div>').text(el.label).html() + '</div>'
            + '<div class="form-row">'
            + '  <div class="col-6 ftd-prop-row"><label>Left (mm)</label><input type="number" step="0.5" class="form-control form-control-sm ftd-prop" data-prop="left_mm" value="' + el.left_mm + '"></div>'
            + '  <div class="col-6 ftd-prop-row"><label>Top (mm)</label><input type="number" step="0.5" class="form-control form-control-sm ftd-prop" data-prop="top_mm" value="' + el.top_mm + '"></div>'
            + '  <div class="col-6 ftd-prop-row"><label>Width (mm)</label><input type="number" step="0.5" class="form-control form-control-sm ftd-prop" data-prop="width_mm" value="' + el.width_mm + '"></div>'
            + '  <div class="col-6 ftd-prop-row"><label>Height (mm)</label><input type="number" step="0.5" class="form-control form-control-sm ftd-prop" data-prop="height_mm" value="' + el.height_mm + '"></div>'
            + '</div>'
            + '<div class="form-row">'
            + '  <div class="col-6 ftd-prop-row"><label>Font Size (pt)</label><input type="number" step="0.25" class="form-control form-control-sm ftd-prop" data-prop="font_size_pt" value="' + el.font_size_pt + '"></div>'
            + '  <div class="col-6 ftd-prop-row"><label>Perataan</label>'
            + '    <select class="form-control form-control-sm ftd-prop" data-prop="align">'
            + '      <option value="left"' + (el.align === 'left' ? ' selected' : '') + '>Kiri</option>'
            + '      <option value="center"' + (el.align === 'center' ? ' selected' : '') + '>Tengah</option>'
            + '      <option value="right"' + (el.align === 'right' ? ' selected' : '') + '>Kanan</option>'
            + '    </select>'
            + '  </div>'
            + '</div>'
            + '<div class="d-flex flex-wrap mb-2" style="gap:12px;">'
            + '  <label class="mb-0"><input type="checkbox" class="ftd-prop-chk" data-prop="bold" ' + (el.bold ? 'checked' : '') + '> Bold</label>'
            + '  <label class="mb-0"><input type="checkbox" class="ftd-prop-chk" data-prop="italic" ' + (el.italic ? 'checked' : '') + '> Italic</label>'
            + '  <label class="mb-0"><input type="checkbox" class="ftd-prop-chk" data-prop="border" ' + (el.border ? 'checked' : '') + '> Border</label>'
            + '  <label class="mb-0"><input type="checkbox" class="ftd-prop-chk" data-prop="visible" ' + (el.visible ? 'checked' : '') + '> Tampil</label>'
            + '</div>'
            + '<div class="ftd-prop-row">'
            + '  <label>Teks Kustom (opsional, untuk label/rekening)</label>'
            + '  <input type="text" class="form-control form-control-sm ftd-prop-text" data-prop="custom_text" value="' + $('<div>').text(el.custom_text || '').html() + '" placeholder="Kosongkan untuk data otomatis">'
            + '</div>';
        $('#ftdPropertyPanel').html(html);
    }

    function selectElement(key) {
        selectedKey = key;
        renderTree();
        renderCanvas();
        renderProperties();
    }

    function refreshTemplateDropdown(list, activeId) {
        let opts = '';
        (list || []).forEach(function (t) {
            const sel = (parseInt(t.id_template, 10) === parseInt(activeId, 10)) ? ' selected' : '';
            const def = (parseInt(t.is_default, 10) === 1) ? ' (Default)' : '';
            opts += '<option value="' + t.id_template + '"' + sel + '>' + $('<div>').text(t.nama_template + def).html() + '</option>';
        });
        $('#selectTemplate').html(opts);
    }

    $(document).ready(function () {
        syncPageInputs();
        renderTree();
        renderCanvas();
        renderProperties();

        // Pilih komponen dari daftar kiri
        $(document).on('click', '.ftd-tree-item', function (e) {
            if ($(e.target).is('input')) return;
            selectElement($(this).data('key'));
        });

        $(document).on('change', '.ftd-tree-vis', function () {
            const k = $(this).data('key');
            const el = tpl.elements.find(function (x) { return x.key === k; });
            if (el) {
                el.visible = $(this).is(':checked');
                renderCanvas();
                if (selectedKey === k) renderProperties();
            }
        });

        // Drag & Resize di kanvas
        $(document).on('mousedown', '.ftd-memo', function (e) {
            e.stopPropagation();
            const key = $(this).data('key');
            const isResize = $(e.target).hasClass('ftd-resize-handle');
            selectElement(key);

            const el = tpl.elements.find(function (x) { return x.key === key; });
            if (!el) return;

            const startX = e.clientX;
            const startY = e.clientY;
            const origLeft = parseFloat(el.left_mm);
            const origTop = parseFloat(el.top_mm);
            const origW = parseFloat(el.width_mm);
            const origH = parseFloat(el.height_mm);
            const scale = PX_PER_MM * zoom;
            const snap = $('#chkSnapGrid').is(':checked');

            $(document).on('mousemove.ftdDrag', function (ev) {
                const dxMm = (ev.clientX - startX) / scale;
                const dyMm = (ev.clientY - startY) / scale;
                if (isResize) {
                    let nw = Math.max(4, origW + dxMm);
                    let nh = Math.max(3, origH + dyMm);
                    el.width_mm = snap ? roundHalf(nw) : parseFloat(nw.toFixed(2));
                    el.height_mm = snap ? roundHalf(nh) : parseFloat(nh.toFixed(2));
                } else {
                    let nl = Math.max(0, origLeft + dxMm);
                    let nt = Math.max(0, origTop + dyMm);
                    el.left_mm = snap ? roundHalf(nl) : parseFloat(nl.toFixed(2));
                    el.top_mm = snap ? roundHalf(nt) : parseFloat(nt.toFixed(2));
                }
                const $m = $('.ftd-memo[data-key="' + key + '"]');
                $m.css({
                    left: (el.left_mm * scale) + 'px',
                    top: (el.top_mm * scale) + 'px',
                    width: (el.width_mm * scale) + 'px',
                    height: (el.height_mm * scale) + 'px',
                    lineHeight: (el.height_mm * scale) + 'px'
                });
                $('.ftd-prop[data-prop="left_mm"]').val(el.left_mm);
                $('.ftd-prop[data-prop="top_mm"]').val(el.top_mm);
                $('.ftd-prop[data-prop="width_mm"]').val(el.width_mm);
                $('.ftd-prop[data-prop="height_mm"]').val(el.height_mm);
            });

            $(document).one('mouseup.ftdDrag', function () {
                $(document).off('mousemove.ftdDrag');
            });
        });

        // Geser presisi pakai panah keyboard
        $(document).on('keydown', function (e) {
            if (!selectedKey || $(e.target).is('input, select, textarea')) return;
            const step = e.shiftKey ? 2 : 0.5;
            const el = tpl.elements.find(function (x) { return x.key === selectedKey; });
            if (!el) return;

            let moved = true;
            if (e.key === 'ArrowLeft') el.left_mm = Math.max(0, roundHalf(parseFloat(el.left_mm) - step));
            else if (e.key === 'ArrowRight') el.left_mm = roundHalf(parseFloat(el.left_mm) + step);
            else if (e.key === 'ArrowUp') el.top_mm = Math.max(0, roundHalf(parseFloat(el.top_mm) - step));
            else if (e.key === 'ArrowDown') el.top_mm = roundHalf(parseFloat(el.top_mm) + step);
            else moved = false;

            if (moved) {
                e.preventDefault();
                renderCanvas();
                renderProperties();
            }
        });

        // Perubahan dari panel properti
        $(document).on('input change', '.ftd-prop', function () {
            const el = tpl.elements.find(function (x) { return x.key === selectedKey; });
            if (!el) return;
            const prop = $(this).data('prop');
            el[prop] = (prop === 'align') ? $(this).val() : parseFloat($(this).val() || 0);
            renderCanvas();
        });

        $(document).on('change', '.ftd-prop-chk', function () {
            const el = tpl.elements.find(function (x) { return x.key === selectedKey; });
            if (!el) return;
            el[$(this).data('prop')] = $(this).is(':checked');
            renderTree();
            renderCanvas();
        });

        $(document).on('input', '.ftd-prop-text', function () {
            const el = tpl.elements.find(function (x) { return x.key === selectedKey; });
            if (!el) return;
            el[$(this).data('prop')] = $(this).val();
            renderCanvas();
        });

        // Perubahan ukuran kertas, margin, & tinggi band
        $('.ftd-page-inp').on('input change', function () {
            tpl[$(this).data('field')] = parseFloat($(this).val() || 0);
            renderCanvas();
        });
        $('.ftd-band-inp').on('input change', function () {
            tpl.bands[$(this).data('band')] = parseFloat($(this).val() || 0);
            renderCanvas();
        });

        $('#selectZoom').on('change', function () {
            zoom = parseFloat($(this).val() || 1);
            renderCanvas();
        });

        // Ganti template aktif
        $('#selectTemplate').on('change', function () {
            const id = $(this).val();
            $.getJSON('<?= base_url('sales/template-faktur/get/') ?>' + id, function (res) {
                if (res && res.success) {
                    tpl = res.template;
                    selectedKey = null;
                    $('#inputTemplateName').val(tpl.nama_template);
                    syncPageInputs();
                    renderTree();
                    renderCanvas();
                    renderProperties();
                }
            });
        });

        // Simpan template
        $('#btnSaveTemplate').on('click', function () {
            tpl.nama_template = $('#inputTemplateName').val().trim() || tpl.nama_template;
            $.ajax({
                url: '<?= base_url('sales/template-faktur/save') ?>',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(tpl)
            }).done(function (res) {
                if (res && res.success) {
                    tpl = res.template;
                    refreshTemplateDropdown(res.templates, tpl.id_template);
                    showAlert(res.message, 'success');
                } else {
                    showAlert((res && res.message) || 'Gagal menyimpan.', 'error');
                }
            });
        });

        // Simpan Sebagai Baru
        $('#btnSaveAsTemplate').on('click', function () {
            const newName = prompt('Masukkan nama template baru:', ($('#inputTemplateName').val().trim() || tpl.nama_template) + ' (Copy)');
            if (!newName || !newName.trim()) return;
            const payload = $.extend(true, {}, tpl, { nama_template: newName.trim() });
            $.ajax({
                url: '<?= base_url('sales/template-faktur/save-as') ?>',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(payload)
            }).done(function (res) {
                if (res && res.success) {
                    tpl = res.template;
                    $('#inputTemplateName').val(tpl.nama_template);
                    refreshTemplateDropdown(res.templates, tpl.id_template);
                    showAlert(res.message, 'success');
                } else {
                    showAlert((res && res.message) || 'Gagal membuat template.', 'error');
                }
            });
        });

        // Jadikan Default
        $('#btnSetDefault').on('click', function () {
            $.ajax({
                url: '<?= base_url('sales/template-faktur/set-default') ?>',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ id_template: tpl.id_template })
            }).done(function (res) {
                if (res && res.success) {
                    tpl = res.template;
                    refreshTemplateDropdown(res.templates, tpl.id_template);
                    showAlert(res.message, 'success');
                }
            });
        });

        // Reset ke default TTB 2022
        $('#btnResetDefault').on('click', function () {
            if (!confirm('Kembalikan semua posisi elemen ke pengaturan awal TTB 2022 Q LOT PAJAK 1.11?')) return;
            $.ajax({
                url: '<?= base_url('sales/template-faktur/reset-default') ?>',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ id_template: tpl.id_template })
            }).done(function (res) {
                if (res && res.success) {
                    tpl = res.template;
                    syncPageInputs();
                    renderTree();
                    renderCanvas();
                    renderProperties();
                    showAlert(res.message, 'success');
                }
            });
        });

        // Hapus template non-default
        $('#btnDeleteTemplate').on('click', function () {
            if (!confirm('Hapus template "' + tpl.nama_template + '"?')) return;
            $.ajax({
                url: '<?= base_url('sales/template-faktur/delete') ?>',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ id_template: tpl.id_template })
            }).done(function (res) {
                if (res && res.success) {
                    showAlert(res.message, 'success');
                    const firstId = res.templates && res.templates[0] ? res.templates[0].id_template : 0;
                    refreshTemplateDropdown(res.templates, firstId);
                    $('#selectTemplate').trigger('change');
                } else {
                    showAlert((res && res.message) || 'Gagal menghapus.', 'error');
                }
            });
        });
    });
})();
</script>
</body>
