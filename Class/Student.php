<?php

Class Student extends Database {

    private $x1; // email
    private $x2; // password
    private $x3; // firstname
    private $x4; // lastname
    private $x5; // middlename
    private $x6; // birthdate
    private $x7; // address
    private $x8; // profile
    private $x9; // user_type
    private $x10; // is_active

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

    public function checkEmailExists(){
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->connect()->prepare($query);
        $stmt->execute([$this->x1]);
        return $stmt->rowCount() > 0;
    }

    public function registerStudent(){
        try {
            // Hash password
            $hashedPassword = password_hash($this->x2, PASSWORD_DEFAULT);
            $user_id = rand(100000,999999);
            date_default_timezone_set('Asia/Manila');
            $todays_date=date("Y-m-d h:i:sa");
            $today=strtotime($todays_date);
            $date=date("Y-m-d h:i:sa", $today);
            $n = "NULL";
            
            $y = $this->connect()->prepare("SELECT * FROM users WHERE email = ?  LIMIT 1 ");
            $y->execute([$this->x1]);
            if($y->rowCount() > 0){
                return ['success' => false, 'message' => ""];
            }

            // Insert into users table
            $query = "INSERT INTO users 
                     (id, email, password, profile, date_created, user_type, is_active, last_login)
                     VALUES
                     (?, ?, ?, ?, ?, ?, ?, ?)";
            $query_again = "INSERT INTO user_info 
                    (user_id, firstname, lastname, middlename, birthdate, age, address)
                    values
                    (?, ?, ?, ?, ?, ?, ?)"; 
            
            $stmt = $this->connect()->prepare($query);
            $stmt->execute([
                $user_id,
                $this->x1, 
                $hashedPassword, 
                $this->x8, 
                $date, 
                $this->x9, 
                $this->x10, 
                $n,
            ]);

            $stmt_again = $this->connect()->prepare($query_again);
            $stmt_again->execute([
                $user_id,
                $this->x3, 
                $this->x4, 
                $this->x5, 
                $this->x6, 
                $n, 
                $this->x7, 
            ]);
            
            // Generate student ID number
            $id_no = '007'.rand(100000,999999);
            $yearL = "NULL";
            $notEn = "Not Enrolled";
            $regular = "5";

            // Insert into students table
            $query2 = "INSERT INTO students (user_id, id_no, year_level, status, is_regular) 
                      VALUES (?, ?, ?, ?, ?)";
            
            $stmt2 = $this->connect()->prepare($query2);
            $stmt2->execute([$user_id, $id_no, $yearL, $notEn, $regular]);
            
            return ['success' => true, 'message' => 'Registration successful'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }


    public function studentLogin() {
        $x = $this->connect()->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $x->execute([$this->x1]);
        // if login it should be a greater than
        if($x->rowCount() > 0){
            $data = $x->fetchAll(PDO::FETCH_ASSOC);
            if(password_verify($this->x2, $data[0]['password'])){
                if($data[0]['is_active'] == 0){
                    $x = null;
                    echo '0';
                }else{
                    $_SESSION['user_id'] = $data[0]['id'];
                    $_SESSION['user_type'] = $data[0]['user_type'];
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

    // public function studentLogin() {
    //     $x = $this->connect()->prepare("SELECT u.id, u.password, ui.firstname, ui.lastname, s.id_no 
    //                 FROM users u 
    //                 JOIN user_info ui ON u.id = ui.user_id 
    //                 JOIN students s ON u.id = s.user_id 
    //                 WHERE u.email = ? LIMIT 1");
    //     $x->execute([$this->x1]);
        
    //     if($x->rowCount() > 0){
    //         $data = $x->fetchAll(PDO::FETCH_ASSOC);
    //         if(password_verify($this->x2, $data[0]['password'])){
    //             if($data[0]['is_active'] == 0){
    //                 $x = null;
    //                 echo '0';
    //             }else{
    //                 $_SESSION['user_id'] = $data[0]['id'];
    //                 $_SESSION['user_type'] = $data[0]['user_type'];
    //                 echo '3';
    //             }
    //         }else{
    //             $x = null;
    //             echo '2';
    //         }
    //     }else{
    //         $x = null;
    //         echo '1';
    //     }
    // }

    // public function studentLogin() {
    //     try {
    //         // $query = "SELECT u.id, u.password, u.firstname, u.lastname, s.id_no 
    //         //         FROM users u 
    //         //         JOIN students s ON u.id = s.user_id 
    //         //         WHERE u.email = ? AND u.user_type = 'student'";
    //         $query = "SELECT u.id, u.password, ui.firstname, ui.lastname, s.id_no 
    //                 FROM users u 
    //                 JOIN user_info ui ON u.id = ui.user_id 
    //                 JOIN students s ON u.id = s.user_id 
    //                 WHERE u.email = ? ";
    //         $stmt = $this->connect()->prepare($query);
    //         $stmt->execute([$this->x1]);
            
    //         if ($stmt->rowCount() > 0) {
    //             $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
    //             if (password_verify($this->x2, $user['password'])) {
    //                 return [
    //                     'success' => true, 
    //                     'user' => [
    //                         'id' => $user['id'],
    //                         'firstname' => $user['firstname'],
    //                         'lastname' => $user['lastname'],
    //                         'id_no' => $user['id_no']
    //                     ]
    //                 ];
    //             } else {
    //                 return ['success' => false, 'message' => 'Incorrect password'];
    //             }
    //         } else {
    //             return ['success' => false, 'message' => 'Email not found'];
    //         }
    //     } catch (PDOException $e) {
    //         return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    //     }
    // }






}
?>