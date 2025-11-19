<!-- jQuery -->
<script src="<?php echo base_url('assets/plugins/jquery/jquery.min.js') ?>"></script>
<!-- jQuery UI 1.11.4 -->
<script src="<?php echo base_url('assets/plugins/jquery-ui/jquery-ui.min.js') ?>"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="<?php echo base_url('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<!-- jQuery Knob Chart -->
<script src="<?php echo base_url('assets/plugins/jquery-knob/jquery.knob.min.js') ?>"></script>
<!-- daterangepicker -->
<script src="<?php echo base_url('assets/plugins/moment/moment.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/daterangepicker/daterangepicker.js') ?>"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="<?php echo base_url('assets/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') ?>"></script>
<!-- Summernote -->
<script src="<?php echo base_url('assets/plugins/summernote/summernote-bs4.min.js') ?>"></script>
<!-- overlayScrollbars -->
<script src="<?php echo base_url('assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') ?>"></script>
<!-- DataTables -->
<script src="<?php echo base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-buttons/js/dataTables.buttons.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/jszip/jszip.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/pdfmake/pdfmake.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/pdfmake/vfs_fonts.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-buttons/js/buttons.html5.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-buttons/js/buttons.print.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-buttons/js/buttons.colVis.min.js') ?>"></script>
<!-- bs-custom-file-input -->
<script src="<?php echo base_url('assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js') ?>"></script>
<!-- Select2 -->
<script src="<?php echo base_url('assets/plugins/select2/js/select2.full.min.js') ?>"></script>
<!-- AdminLTE App -->
<script src="<?php echo base_url('assets/dist/js/adminlte.js') ?>"></script>

<script>
  $(document).ready(function() {
    $('#list_tb_opname').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "<?php echo site_url('stockopname'); ?>",
        type: "POST"
      }
    });
  });
</script>

<script>
  $(function() {
    $('#tb_schedule').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });

    $('#tbpricelist').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });

    $('#tbics_erp_diff').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });

    $('#fkcashonsite').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });

    // $('#tbics_byallbarang').DataTable({
    //   "paging": true,
    //   "lengthChange": false,
    //   "searching": true,
    //   "ordering": false,
    //   "info": true,
    //   "autoWidth": false,
    //   "responsive": true,
    // });

    $('#tb_masterbr_ics').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });

    $('#tb_ics_do').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });

    $('#tb_ics_po').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });
    $('#tbgudang').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });
    $('#dailyod').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });
    $('#tracking_input_ics_byexp').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });
    $('#ics_do_byexp').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });
    $('#ics_po_byexp').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });
    $('#lsfakturbyrute').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });
    bsCustomFileInput.init();
  });
</script>

<script type="text/javascript">
  var table;
  $(document).ready(function() {

    //datatables
    table = $('#tb_qty').DataTable({

      "processing": true,
      "serverSide": true,
      "order": [],
      "ajax": {
        "url": "<?= base_url('get_data_a') ?>",
        "type": "POST"
      },
      "columnDefs": [{
        "targets": [0],
        "orderable": false,
      }, ],
    });



  });
</script>

<script>
  // Pie Chart TIM 1
  const dataTim1 = {
    labels: ['MATCH', 'NOT MATCH'],
    datasets: [{
      data: [<?php echo $stat_t1['persen_match']; ?>, <?php echo $stat_t1['persen_notmatch']; ?>],
      backgroundColor: ['#3DAF57', '#dc3545'],
    }]
  };

  const configTim1 = {
    type: 'pie',
    data: dataTim1,
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    }
  };

  new Chart(document.getElementById('pieChartTim1'), configTim1);

  // Pie Chart TIM 2
  const dataTim2 = {
    labels: ['MATCH', 'NOT MATCH'],
    datasets: [{
      data: [<?php echo $stat_t2['persen_match']; ?>, <?php echo $stat_t2['persen_notmatch']; ?>],
      backgroundColor: ['#3DAF57', '#dc3545'],
    }]
  };

  const configTim2 = {
    type: 'pie',
    data: dataTim2,
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    }
  };

  new Chart(document.getElementById('pieChartTim2'), configTim2);


  // BY EXPIRED DATE

  // Pie Chart TIM 1
  const dataTim1exp = {
    labels: ['MATCH', 'NOT MATCH'],
    datasets: [{
      data: [<?php echo $statexp_t1['persen_match']; ?>, <?php echo $statexp_t1['persen_notmatch']; ?>],
      backgroundColor: ['#3DAF57', '#dc3545'],
    }]
  };

  const configTim1exp = {
    type: 'pie',
    data: dataTim1exp,
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    }
  };

  new Chart(document.getElementById('pieChartTim1exp'), configTim1exp);

  // Pie Chart TIM 2
  const dataTim2exp = {
    labels: ['MATCH', 'NOT MATCH'],
    datasets: [{
      data: [<?php echo $statexp_t2['persen_match']; ?>, <?php echo $statexp_t2['persen_notmatch']; ?>],
      backgroundColor: ['#3DAF57', '#dc3545'],
    }]
  };

  const configTim2exp = {
    type: 'pie',
    data: dataTim2exp,
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    }
  };

  new Chart(document.getElementById('pieChartTim2exp'), configTim2exp);
</script>

</body>

</html>