<script>
  (function() {
    var idleSeconds = 0;
    var maxIdleSeconds = 900; // 15 menit = 900 detik
    var timeoutUrl = "<?= base_url('Auth/timeout') ?>";
    var timerInterval = null;

    function resetIdleTimer() {
      idleSeconds = 0;
    }

    // Tangkap aktivitas pengguna (mouse, keyboard, touch, scroll)
    var activityEvents = ['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll', 'click'];
    for (var i = 0; i < activityEvents.length; i++) {
      window.addEventListener(activityEvents[i], resetIdleTimer, true);
    }

    // Cek setiap 1 detik
    timerInterval = setInterval(function() {
      idleSeconds++;
      if (idleSeconds >= maxIdleSeconds) {
        clearInterval(timerInterval);
        window.location.href = timeoutUrl;
      }
    }, 1000);

    // Tangani respons 401 dari request AJAX
    if (window.jQuery) {
      $(document).ajaxError(function(event, jqXHR) {
        if (jqXHR.status === 401) {
          var res = jqXHR.responseJSON;
          if (res && res.redirect) {
            window.location.href = res.redirect;
          } else {
            window.location.href = timeoutUrl;
          }
        }
      });
    }
  })();
</script>
