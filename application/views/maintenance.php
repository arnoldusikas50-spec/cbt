<?php ?>
<!doctype html>
<html>
  <head>
    <title>Under Maintenance</title>
    <meta charset="utf-8"/>
    <meta name="robots" content="noindex"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            text-align: center; padding: 20px; font: 20px Helvetica, sans-serif; color: #5efe08ff;
            background: linear-gradient(90deg, #5e83ab 40%, #fb4141ff  100%); /* ikasmedia */
            /*background-image: url("assets/img/maintenance.gif"); */
        }
        @media (min-width: 768px){
            body{ padding-top: 150px; }
        }
        h1 { font-size: 50px; }
        article { display: block; text-align: left; max-width: 650px; margin: 0 auto; }
        a { color: #dc8100; text-decoration: none; }
        a:hover { color: #efe8e8; text-decoration: none; }
        .btn {
            border: none;
            color: white;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            cursor: pointer;
            border-radius: 8px;
            background-color: #f44c36ff;
        }
        .btn:hover {
            background-color: #009c6dff; /* Green */
            color: white;
        }
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            padding-top: 100px; /* Location of the box */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0,0,0); /* Fallback color */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
        }

        /* Modal Content */
        .modal-content {
            color: #282828;
            background-color: #fefefe;
            margin: auto;
            padding: 10px;
            border: 1px solid #888;
            width: 60%;
        }

        .modal-desc {
            padding: 20px;
        }

        /* The Close Button */
        .close {
            color: #aaaaaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
  </head>
  <body>
    <img src="<?= base_url() ?>/assets/img/maintenance.gif" width="200" height="121">
  <article>
      <h2>Dalam Proses Maintenance</h2>
      <div>
          <p>Mohon maaf, untuk sementara aplikasi tidak dapat dibuka. Jika ada yang perlu disampaikan silahkan hubungi Admin Sekolah</p>
          <p>&mdash; Admin Sekolah &mdash;</p>
          <p>[ RANDY - 085339035155 ]</p>
          <button class="btn" onclick="confirm()">Logout</button>
          
      </div>
  </article>
  <div id="myModal" class="modal">
      <div class="modal-content">
          <span class="close">&times;</span>
          <div class="modal-desc">
              <p>Konfirmasi logout</p>
              <button class="btn" onclick="logout()">Logout</button>
          </div>
      </div>

  </div>
  <script>
      let base_url = '<?=base_url()?>';
      function confirm() {
          var modal = document.getElementById("myModal");
          modal.style.display = "block";
          var span = document.getElementsByClassName("close")[0];
          span.onclick = function() {
              modal.style.display = "none";
          }

          window.onclick = function(event) {
              if (event.target === modal) {
                  modal.style.display = "none";
              }
          }
      }
      function logout() {
          location.href = base_url + "logout";
      }
  </script>
  </body>
</html>