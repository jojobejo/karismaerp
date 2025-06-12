<script>
    $(function() {
        $("#tbics").DataTable({
            "responsive": true,
            "lengthChange": false,
            "aaSorting": [],
            "autoWidth": false,
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    });
</script>