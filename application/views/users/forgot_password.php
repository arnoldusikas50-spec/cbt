<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="style.css" />
    <title>Reset Password</title>
    <style>

        * {
            box-sizing: border-box;
        }

        body {
            color: #00853a;
            font-family: 'Muli', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            overflow: hidden;
            margin: 0;
        }

        form {
            margin-top: 3rem
        }

        .container {
            border: 1px solid #00853a;
            padding: 20px 40px;
            border-radius: 5px;
        }

        .container h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        .container a {
            text-decoration: none;
            color: #00853a;
        }

        .btn {
            cursor: pointer;
            display: inline-block;
            width: 100%;
            background: #de9144ff;
            color: #FFF;
            padding: 15px;
            font-family: inherit;
            font-size: 16px;
            font-weight: bold;
            border: 0;
            border-radius: 5px;
        }

        .btn:focus {
            outline: 0;
        }

        .btn:active {
            transform: scale(0.98);
        }

        .text {
            margin-top: 30px;
        }

        .form-control {
            position: relative;
            margin: 20px 0 40px;
            width: 240px;
        }

        .form-control input {
            background-color: transparent;
            border: 0;
            border-bottom: 1px #00853a solid;
            display: block;
            width: 100%;
            padding: 10px 0 6px;
            font-size: 18px;
            color: rgba(43, 101, 69, 0.73);
            position: relative;
            z-index: 100;
        }

        .form-control input:focus,
        .form-control input:valid {
            outline: 0;
            border-bottom-color: #00853a;
        }

        .form-control label {
            position: absolute;
            top: 15px;
            left: 0;
            pointer-events: none;
        }

        .form-control label span {
            display: inline-block;
            font-size: 18px;
            min-width: 5px;
            transition: 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .form-control input:focus ~ label span,
        .form-control input:valid ~ label span {
            color: #00853a;
            transform: translateY(-30px);
        }

        input:-webkit-autofill,
        input:-webkit-autofill:focus {
            transition: background-color 600000s 0s, color 600000s 0s;
        }
        input[data-autocompleted] {
            background-color: transparent !important;
        }

        small {
            font-style: italic;
        }
    </style>
</head>
<body>
<div class="container" id="konten">
    <h2>Reset Password</h2>
    <?= form_open(base_url('reset_password/reset'), array('id' => 'reset', 'autocomplete' => 'off')) ?>
        <div class="form-control">
            <input type="text" name="username" required>
            <label>Username</label>
            <small>*Diisi username yang terdaftar</small>
        </div>

        <div class="form-control">
            <input type="password" name="password" required>
            <label>Password Baru</label>
        </div>

        <div class="form-control">
            <input type="password" name="confirm_password" required>
            <label>Ulangi Password Baru</label>
        </div>

        <button class="btn" type="submit">Reset Password</button>
    <?= form_close() ?>
</div>
<script>
    const labels = document.querySelectorAll('.form-control label')

    labels.forEach(label => {
        label.innerHTML = label.innerText
            .split('')
            .map((letter, idx) => `<span style="transition-delay:${idx * 50}ms">${letter}</span>`)
            .join('')
    })

    document.addEventListener("DOMContentLoaded", function () {
        const form = document.querySelector("form");

        form.addEventListener("submit", function (event) {
            event.preventDefault(); // Mencegah reload halaman

            // Ambil nilai input
            const username = form.querySelector("input[type='text']").value.trim();
            const password1 = form.querySelectorAll("input[type='password']")[0].value;
            const password2 = form.querySelectorAll("input[type='password']")[1].value;

            const formAction = form.getAttribute("action");

            // Validasi
            if (!username) {
                alert("Username harus diisi!");
                return;
            }
            if (password1.length < 5) {
                alert("Password harus minimal 5 karakter!");
                return;
            }
            if (password1 !== password2) {
                alert("Password baru dan ulangi password tidak sama!");
                return;
            }

            let formData = new FormData(document.getElementById('reset'));
            //console.log("data:", Object.fromEntries(formData));

            fetch(formAction, {
                method: "POST",
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    console.log('response', data)
                    if (data.status) {
                        alert("Password berhasil direset!")
                    } else {
                        alert(data.message)
                    }
                })
                .catch(error => console.error("Error:", error));

            //alert("Password berhasil direset!");
            form.reset(); // Reset form setelah submit
        });
    });
</script>
</body>
</html>
