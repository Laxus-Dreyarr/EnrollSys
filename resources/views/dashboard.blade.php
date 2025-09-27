<?php
if (!$user || $user->user_type !== 'admin') {
    return redirect()->route('/welcome_admin');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h1>Welcome, {{ $user->email }}!</h1>
    <!-- <p>Your name: {{ $user->name }}</p> -->

    Logout Form
    
</body>
</html>
