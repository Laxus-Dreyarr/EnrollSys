<?php
include ("../Class/Db.php");
include ("../Class/Admin.php");

if(isset($_POST['email']) && isset($_POST['password'])){
    $email = $_POST['email'];
    $password = $_POST['password'];
    $admin = new Admin($email ,$password, 0, 0, 0, 0, 0, 0, 0, 0);
    $admin->updateAdminPassword();
}