<style>
    body {
        background-image: url("../images/bg-undian.jpg");
        background-repeat: no-repeat;
        background-size: cover;
    }

    .modalCenter {
        top: 35% !important;
        transform: translateY(-50%) !important;
    }

    .h3Center {
        text-align: center;
    }

    .h2Center {
        text-align: center;
        font-weight: bold;
        font-size: 35px;
    }

    .h3hadiah {
        text-align: center;
        position: relative;
        height: 10px;
    }


    .h3noUndian {
        text-align: center;
        font-weight: bold;
        top: 100px;
    }

    .h3tko {
        text-align: center;
        position: relative;
        width: 200px;
        height: 5px;
    }

    input {
        text-align: center;
    }

    .logo1 {
        position: absolute;
        left: 700px;
    }

    .logo2 {
        position: absolute;
        left: 300px;
        top: -30px;
    }

    .logo3 {
        position: absolute;
        left: 800px;
        top: 250px;
    }

    .posCard1 {
        position: absolute;
        top: 350px;
        left: 350px;
        width: 100px;
        height: 100px;
        border-radius: 20px;
        border: black;
        border-style: solid;
    }

    .posWinSilver {
        position: absolute;
        top: 450px;
        left: 190px;
        height: max-content;
        width: 1220px;
    }

    .posnoundi {
        position: absolute;
        top: 230px;
        left: 310px;
        width: 950px;
        height: 150px;
        border-radius: 50px;
        border-style: solid;
        border: black;
    }

    .poswin1 {
        position: absolute;
        top: 150px;
        left: -10px;
    }

    .poswin2 {
        position: absolute;
        top: 100px;
        left: 430px;
    }

    .bg-primary {
        background-color: #007bff !important;
    }

    .bg-custom-1 {
        background-color: rgba(0, 0, 0, 0.2);
    }

    .lblhide {
        position: absolute;
        top: 10px;
    }

    .card-pilih {
        background-image: linear-gradient(#DCBB60, #FFE880, #B59451, #966D2F);
    }

    .card-undi {
        background-image: linear-gradient(#FFE880, #B59451, #966D2F);
    }

    .h3FontCus {
        font-family: "DynaPuff", sans-serif;
        /* text-decoration: underline; */
        font-weight: bold;
        font-size: 120px;
        top: -20px;
    }

    .sizes {
        height: 200px;
        width: auto;
    }

    .img-thumbnail {
        background-color: transparent;
        border: none;
    }

    .pemenang {
        position: absolute;
        left: 100px;
        top: 10px;
    }

    .home {
        position: absolute;
        left: 90px;
        top: 10px;
    }

    .btnMulaiUndi {
        position: absolute;
        top: 100px;
    }

    .btn-primary,
    .btn-primary:hover,
    .btn-primary:active,
    .btn-primary:visited {
        height: 50px;
        width: 500px;
        background-color: green;
    }

    .btn-warning,
    .btn-warning:hover,
    .btn-warning:active,
    .btn-warning:visited {
        background-color: linear-gradient(#FFE880, #B59451, #966D2F) !important;
    }

    .container-x {
        position: absolute;
        top: 365px;
        left: 800px;
        width: 200px;
        height: 100px;
        inline-size: auto;
    }

    .txtHadiah {
        font-family: 'Lobstertwo', sans-serif;
        font-size: 60px;
        color: #FFE880;
    }

    table {
        border: 0px;
    }

    table.fontTb {
        font-weight: 800;
        font-size: 35px;
    }

    .borderless td,
    .borderless th {
        border: none;
    }

    td.a {
        text-align: right;
    }

    td.b {
        text-align: center;
    }

    td.c {
        text-align: center;
    }

    ul {
        list-style: none;
        display: flex;
        flex-direction: column;
        flex-flow: column wrap;
        height: 150px;
    }

    .tbwin1 {
        min-height: 20px;
        height: 70px;
    }

    .tbwin2 {
        max-height: 300px;
        max-width: 880px;
        width: 750px;
        height: 300px;
        margin-left: 120px;
    }
</style>

<body class="hold-transition layout-top-nav">
    <div class="wrapper">

        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="AdminLTELogo" height="150" width="300">
        </div>

        <div class="content-wrapper">
            <section class="content">
                <div class="container">
                    <div class="card-body">
                        <div class="d-flex flex-row justify-content-center">
                            <h3 class="col-md-2 h3FontCus" id="lblAngka"> x </h3>
                            <h3 class="col-md-2 h3FontCus" id="lblAngka1"> x </h3>
                            <h3 class="col-md-2 h3FontCus" id="lblAngka2"> x </h3>
                            <h3 class="col-md-2 h3FontCus" id="lblAngka3"> x </h3>
                        </div>
                        <div class="card-footers">
                            <button type="button" class="btn btn-block btn-success mt-2 mb-2 mr-2" id="btnBerhenti" disabled hidden>Berhenti</button>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h4>Daftar Pemenang:</h4>
                        <table class="table table-bordered" id="tblPemenang">
                            <thead>
                                <tr>
                                    <th>Pemenang Ke</th>
                                    <th>Nomor Undian</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <script>
        /* --------- KONFIG ---------- */
        const TOTAL_PEMENANG = 5; // target
        const DURASI_PUTAR = 500; // ms sebelum otomatis berhenti
        const JEDA_LANJUT = 500; // ms setelah tampil pemenang

        /* --------- VARIABEL -------- */
        let rollingInterval; // id setInterval
        let isRunning = false; // status angka berputar
        let winners = []; // array nomor valid sesi ini
        let dbExisting = []; // nomor yg sdh ada di DB (di‑inject server)

        /* ------ FUNGSI UTIL -------- */
        const rand = () => Math.floor(Math.random() * 10);

        function showDigits() {
            $('#lblAngka').text(rand());
            $('#lblAngka1').text(rand());
            $('#lblAngka2').text(rand());
            $('#lblAngka3').text(rand());
        }

        function currentNumber() {
            return $('#lblAngka').text() +
                $('#lblAngka1').text() +
                $('#lblAngka2').text() +
                $('#lblAngka3').text();
        }

        /* ------ LOGIKA PUTAR ------- */
        function startRolling() {
            if (isRunning) return;
            isRunning = true;

            rollingInterval = setInterval(showDigits, 50);

            setTimeout(() => {
                stopRollingAuto(); // otomatis berhenti
            }, DURASI_PUTAR);
        }

        function stopRollingAuto() {
            clearInterval(rollingInterval);
            isRunning = false;

            let nomor = currentNumber();

            // Skip syarat
            if (
                nomor === '0000' ||
                winners.includes(nomor) ||
                dbExisting.includes(nomor)
            ) {
                setTimeout(startRolling, 50); // langsung putar ulang
                return;
            }

            winners.push(nomor);
            tampilkan(nomor, winners.length);

            if (winners.length < TOTAL_PEMENANG) {
                setTimeout(startRolling, JEDA_LANJUT); // lanjut batch berikut
            } else {
                $('.card-footers').prepend(
                    '<div class="alert alert-info text-center">Tekan <strong>ENTER</strong> untuk menyimpan &amp; refresh.</div>'
                );
            }
        }

        /* ----- TAMPILKAN UI ------- */
        function tampilkan(nomor, urut) {
            $('#tblPemenang tbody').append(`
        <tr>
            <td>Pemenang ${urut}</td>
            <td>${nomor}</td>
        </tr>
    `);
        }

        /* ----- SIMPAN KOLEKTIF ---- */
        function simpanSemua() {
            let selesai = 0;

            winners.forEach(nomor => {
                $.post('extravaganza_savewin', {
                    noundi: nomor
                }, res => {
                    selesai++;

                    // add ke cache agar tidak terpilih di sesi berikut
                    if (res.status === 'ok') dbExisting.push(nomor);

                    if (selesai === winners.length) {
                        location.reload(); // refresh setelah semua respon diterima
                    }
                }, 'json');
            });
        }

        /* ------ KEY BINDING ------- */
        $(document).on('keydown', function(e) {
            const key = e.key || e.code || e.keyCode;

            // Cegah jika fokus di input atau textarea
            const tag = document.activeElement.tagName;
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(tag)) return;

            console.log("Key pressed:", key); // bantu debug

            // SPACE → mulai undian
            if ((key === ' ' || key === 'Space' || key === 32) && winners.length === 0 && !isRunning) {
                e.preventDefault();
                startRolling();
            }

            // ENTER → simpan & reload
            if ((key === 'Enter' || key === 13) && winners.length === TOTAL_PEMENANG && !isRunning) {
                e.preventDefault();
                simpanSemua();
            }
        });
    </script>