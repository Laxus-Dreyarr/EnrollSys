<?php

Class Admin extends Database{

    private $x1;
    private $x2;
    private $x3;
    private $x4;
    private $x5;
    private $x6;
    private $x7;
    private $x8;
    private $x9;
    private $x10;


    public function __construct($x1, $x2, $x3, $x4, $x5, $x6, $x7, $x8, $x9, $x10){
        $this->x1 = $x1;
        $this->x2 = $x2;
        $this->x3 = $x3;
        $this->x4 = $x4;
        $this->x5 = $x5;
        $this->x6 = $x6;
        $this->x7 = $x7;
        $this->x8 = $x8;
        $this->x9 = $x9;
        $this->x10 = $x10;
    }


    // Login Admin
    public function login_admin(){
        $x = $this->connect()->prepare("SELECT * FROM users WHERE email = ?");
        $x->execute([$this->x1]);
        // if login it should be a greater than
        if($x->rowCount() > 0){
            $data = $x->fetchAll(PDO::FETCH_ASSOC);
            if(password_verify($this->x2, $data[0]['password'])){
                if($data[0]['is_active'] == 0){
                    $x = null;
                    echo '0';
                }else{
                    $_SESSION['admin_id'] = $data[0]['id'];
                    // Update last login
                    date_default_timezone_set('Asia/Manila');
                    $todays_date=date("Y-m-d h:i:sa");
                    $today=strtotime($todays_date);
                    $date=date("Y-m-d h:i:sa", $today);
                    $update = $this->connect()->prepare("UPDATE users SET last_login = ? WHERE id = ?");
                    $update->execute([$date, $data[0]['id']]);
                    echo '3';
                }
            }else{
                $x = null;
                echo '2';
            }
        }else{
            $x = null;
            echo '1';
        }
    }
    


}##End of Class