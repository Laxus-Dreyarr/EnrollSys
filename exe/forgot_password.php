<?php
session_start();
include("../Class/Db.php");
include("../Class/Admin.php");

if(isset($_POST['email'])){
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $admin = new Admin($email ,$password, $confirmPassword, 0, 0, 0, 0, 0, 0, 0);
    $admin->forgotPassword();
}