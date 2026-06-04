<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Akun Berhasil Dibuat</title>
</head>

<body>
    <h2>Halo {{ $name }}</h2>

    <p>Akun Anda berhasil dibuat.</p>

    <p>Email: {{ $email }}</p>
    <p>Password: {{ $password }}</p>

    <p>
        <a href="{{ config('app.url') }}/satgas/login">
            Login Sekarang
        </a>
    </p>
    <p>Silakan login dan segera ubah password Anda.</p>
</body>

</html>
