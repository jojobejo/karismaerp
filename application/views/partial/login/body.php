<style>
  .img1 {
    position: relative;
    height: 120px;
    width: 330px;
  }
</style>

<body class="hold-transition login-page">

  <?php if ($this->session->flashdata("gagal")) : ?>
    <div class="card card-danger text-center">
      <div class="card-header">
        Terjadi Keaslahan Data !!!
        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i>
          </button>
        </div>
        <!-- /.card-tools -->
      </div>
      <!-- /.card-header -->
      <div class="card-body">
        <?php echo $this->session->flashdata("gagal") ?>
      </div>
      <!-- /.card-body -->
    </div>

  <?php endif ?>

  <div class="login-box">
    <!-- /.login-logo -->
    <div class="card card-outline card-primary">
      <div class="card-header text">
        <img src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="" class="img1">
      </div>

      <div class="card-body">

        <p class="login-box-msg"></p>

        <form action="<?php echo base_url('login/process') ?>" method="post">
          <div class="input-group mb-3">
            <input type="text" class="form-control" name="user_isi" id="user_isi" placeholder="username">
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-user"></span>
              </div>
            </div>
          </div>
          <div class="input-group mb-3">
            <input type="password" class="form-control" name="pass_isi" id="pas_isi" placeholder="Password">
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-lock"></span>
              </div>
            </div>
          </div>
          <div class="col-xl">
            <button type="submit" class="btn btn-primary btn-block">Log in</button>
            <!-- /.col -->
          </div>
        </form>

        <!-- /.card-body -->
      </div>
      <!-- /.card -->
    </div>
    <!-- /.login-box -->
    <script>
      // assumes you're using jQuery
      $(document).ready(function() {
        $('.confirm-div').hide();
        <?php if ($this->session->flashdata('gagal')) { ?>
          $('.confirm-div').html('<?php echo $this->session->flashdata('gagal'); ?>').show();
        <?php } ?>
      });
    </script>