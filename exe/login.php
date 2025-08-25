<?php
session_start();
include("../Class/Db.php");
include("../Class/Admin.php");
if(isset($_POST['email'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $exe = new Admin($email, $password, 0, 0, 0, 0, 0, 0, 0, 0);
    $exe->login_admin();
}#End of isset uname!