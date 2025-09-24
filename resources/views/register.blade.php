<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <center>
        <br><br>
        <h1>Create Account</h1><br>
        <form action="#" method="post" id="register">
            @csrf
            @method('post')
            <input type="text" name="name" id="name" placeholder="Input your name..."><br>
            <input type="text" name="email" id="email" placeholder="Email address..."><br>
            <input type="text" name="password" id="password" placeholder="Password..."><br>
            <input type="text" name="pwdRepeat" id="pwdRepeat" placeholder="Repeat password..."><br><br>
            <button type="submit">Register</button>
        </form>
        <br><br><br><br><br>
        <button onclick="index()">← Go back</button>
    </center>

<script type="text/javascript" src="{{asset('js/jquery.js')}}"></script>
<script type="text/javascript" src="{{asset('js/sweetalert.js')}}"></script>
<script type="text/javascript" src="{{asset('js/function/register.js')}}"></script>
</body>
</html>