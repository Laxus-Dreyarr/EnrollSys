<?php
session_start();
include("../Class/Db.php");
include("../Class/Student.php");
if(isset($_POST['email'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $exe = new Student($email, $password, '', '', '', '', '', '', '', '');
    $exe->studentLogin();
}#End of isset uname!