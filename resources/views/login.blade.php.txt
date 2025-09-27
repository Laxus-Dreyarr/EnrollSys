<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Your Account</title>
</head>
<body>
<center>
        <br><br><br>
        <h1>Login</h1>
        <form id="login" method="post">
            @csrf
            <input type="text" name="email" id="email" placeholder="Email..." required><br>
            <input type="password" name="password" id="password" placeholder="Password..." required><br><br>
            <button type="submit">Login</button>
        </form>
        <br><br><br><br><br>
        <button onclick="window.location.href='/index'">← Go back</button>
    </center>

<script type="text/javascript" src="{{asset('js/jquery.js')}}"></script>
<script type="text/javascript" src="{{asset('js/sweetalert.js')}}"></script>
<script type="text/javascript" src="{{asset('js/function/register.js')}}"></script>
</body>
</html>