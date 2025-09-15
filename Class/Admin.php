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
        $x = $this->connect()->prepare("SELECT * FROM admin WHERE email = ?");
        $x->execute([$this->x1]);
        // if login it should be a greater than
        if($x->rowCount() > 0){
            $data = $x->fetchAll(PDO::FETCH_ASSOC);
            if(password_verify($this->x2, $data[0]['password'])){
                if($data[0]['is_active'] == 0){
                    $x = null;
                    echo '0';
                }else{
                    $_SESSION['admin_id'] = $data[0]['admin_id'];
                    $_SESSION['user_type'] = $data[0]['user_type'];
                    // Update last login
                    date_default_timezone_set('Asia/Manila');
                    $todays_date=date("Y-m-d h:i:sa");
                    $today=strtotime($todays_date);
                    $date=date("Y-m-d h:i:sa", $today);
                    $update = $this->connect()->prepare("UPDATE admin SET last_login = ? WHERE admin_id = ?");
                    $update->execute([$date, $data[0]['admin_id']]);
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
    

    public function fetch_admin_data(){
        $x = $this->connect()->prepare("SELECT * FROM admin a JOIN admin_info ai ON a.admin_id = ai.admin_id WHERE a.admin_id = ?");
        $x->execute([$this->x1]);
        if($x->rowCount() > 0){
            $data = $x->fetchAll(PDO::FETCH_ASSOC);
            $x = null;
            return $data;
        }
    }

    // The 1 commit
    public function get_statistics() {
        $db = $this->connect();
        
        // Get student count
        $students = $db->query("SELECT COUNT(*) FROM students")->fetchColumn();
        
        // Get instructor count
        $instructors = $db->query("SELECT COUNT(*) FROM instructor")->fetchColumn();
        
        // Get subject count
        $subjects = $db->query("SELECT COUNT(*) FROM subjects WHERE is_active = 1")->fetchColumn();
        
        // Get enrollment count
        $enrollments = $db->query("SELECT COUNT(*) FROM enrollments WHERE status = 'Enrolled'")->fetchColumn();
        
        return [
            'students' => $students,
            'instructors' => $instructors,
            'subjects' => $subjects,
            'enrollments' => $enrollments
        ];
    }

    public function create_subject($code, $name, $units, $year_level, $semester, $max_students, $types, $prerequisites = [], $schedules = [], $description = '') {
        $db = $this->connect();
        
        try {
            $db->beginTransaction();
            
            // Check if subject code already exists
            $checkStmt = $db->prepare("SELECT id FROM subjects WHERE code = ?");
            $checkStmt->execute([$code]);
            
            if ($checkStmt->rowCount() > 0) {
                $db->rollBack();
                return false;
            }
            
            // Check for duplicate schedules
            $uniqueSchedules = [];
            foreach ($schedules as $schedule) {
                $key = $schedule['section'] . '-' . $schedule['day'] . '-' . $schedule['start_time'] . '-' . $schedule['end_time'];
                if (!isset($uniqueSchedules[$key])) {
                    $uniqueSchedules[$key] = $schedule;
                }
            }
            $schedules = array_values($uniqueSchedules);
            
            // Insert subject
            $stmt = $db->prepare("INSERT INTO subjects (code, name, description, units, year_level, semester, max_students, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$code, $name, $description, $units, $year_level, $semester, $max_students, $this->x1]);
            $subject_id = $db->lastInsertId();
            
            // Insert prerequisites
            if (!empty($prerequisites)) {
                $stmt = $db->prepare("INSERT INTO subjectprerequisites (subject_id, prerequisite_id) VALUES (?, ?)");
                foreach ($prerequisites as $prereq_id) {
                    $stmt->execute([$subject_id, $prereq_id]);
                }
            }
            
            // Insert schedules
            if (!empty($schedules)) {
                $stmt = $db->prepare("INSERT INTO subjectschedules (subject_id, Section, day, start_time, end_time, room, Type) VALUES (?, ?, ?, ?, ?, ?, ?)");
                foreach ($schedules as $schedule) {
                    $stmt->execute([
                        $subject_id, 
                        $schedule['section'],
                        $schedule['day'], 
                        $schedule['start_time'], 
                        $schedule['end_time'], 
                        $schedule['room'],
                        $schedule['type'],
                    ]);
                }
            }
            
            $db->commit();
            // Notify Supabase about the new subject
            // $this->notify_supabase('subjects', 'INSERT', $subject_id);
            return true;
        } catch (PDOException $e) {
            $db->rollBack();
            error_log("Subject creation failed: " . $e->getMessage());
            return false;
        }
    }

    public function generate_passkey($email, $user_type) {
        $db = $this->connect();
        
        // Generate a random passkey
        $passkey = bin2hex(random_bytes(8));
        
        // Set expiration date (7 days from now)
        $expiration_date = date('Y-m-d H:i:s', strtotime('+7 days'));
        
        $stmt = $db->prepare("INSERT INTO passkeys (passkey, email, created_by, expiration_date, user_type) 
                              VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$passkey, $email, $this->x1, $expiration_date, $user_type]);
    }

    public function get_all_subjects() {
        $db = $this->connect();
        
        $stmt = $db->prepare("SELECT s.*, GROUP_CONCAT(DISTINCT p.code) as prerequisites 
                              FROM subjects s 
                              LEFT JOIN subjectprerequisites sp ON s.id = sp.subject_id 
                              LEFT JOIN subjects p ON sp.prerequisite_id = p.id 
                              WHERE s.is_active = 1 
                              GROUP BY s.id 
                              ORDER BY s.code");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_subject_with_schedules($subject_id) {
        $db = $this->connect();
        
        $stmt = $db->prepare("SELECT s.*, ss.day, ss.start_time, ss.end_time, ss.room 
                              FROM subjects s 
                              LEFT JOIN subjectschedules ss ON s.id = ss.subject_id 
                              WHERE s.id = ?");
        $stmt->execute([$subject_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_prerequisite_options() {
        $db = $this->connect();
        
        $stmt = $db->prepare("SELECT id, code, name FROM subjects WHERE is_active = 1 ORDER BY code");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    
    public function get_all_subjects_with_schedules() {
        $db = $this->connect();
        
        // First get all subjects
        $stmt = $db->prepare("
            SELECT 
                s.id, 
                s.code, 
                s.name, 
                s.units, 
                s.year_level, 
                s.semester
            FROM subjects s
            WHERE s.is_active = 1
            ORDER BY s.code
        ");
        $stmt->execute();
        $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Then get all schedules
        $stmt = $db->prepare("
            SELECT 
                ss.subject_id,
                ss.Section as section_name,
                ss.day,
                ss.start_time,
                ss.end_time,
                ss.room,
                ss.Type as schedule_type
            FROM subjectschedules ss
            INNER JOIN subjects s ON ss.subject_id = s.id
            WHERE s.is_active = 1
            ORDER BY ss.subject_id, ss.Section
        ");
        $stmt->execute();
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group schedules by subject_id
        $groupedSchedules = [];
        foreach ($schedules as $schedule) {
            $subjectId = $schedule['subject_id'];
            if (!isset($groupedSchedules[$subjectId])) {
                $groupedSchedules[$subjectId] = [];
            }
            $groupedSchedules[$subjectId][] = $schedule;
        }
        
        // Add schedules to subjects
        foreach ($subjects as &$subject) {
            $subjectId = $subject['id'];
            $subject['schedules'] = isset($groupedSchedules[$subjectId]) ? $groupedSchedules[$subjectId] : [];
        }
        
        return $subjects;
    }

    public function get_audit_logs() {
        $db = $this->connect();
        
        $stmt = $db->prepare("
            SELECT 
                a.*,
                u.firstname,
                u.lastname
            FROM auditlogs a
            LEFT JOIN users u ON a.user_id = u.id
            ORDER BY a.timestamp DESC
            LIMIT 100
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete_subject($subject_id) {
        $db = $this->connect();
        
        try {
            $db->beginTransaction();
            
            // First delete schedules
            $stmt = $db->prepare("DELETE FROM subjectschedules WHERE subject_id = ?");
            $stmt->execute([$subject_id]);
            
            // Delete prerequisites
            $stmt = $db->prepare("DELETE FROM subjectprerequisites WHERE subject_id = ? OR prerequisite_id = ?");
            $stmt->execute([$subject_id, $subject_id]);
            
            // Delete the subject
            $stmt = $db->prepare("DELETE FROM subjects WHERE id = ?");
            $stmt->execute([$subject_id]);
            
            $db->commit();
            return true;
        } catch (PDOException $e) {
            $db->rollBack();
            error_log("Subject deletion failed: " . $e->getMessage());
            return false;
        }
    }

    // Add these methods to your Admin class

    public function get_subject_by_id($subject_id) {
        $db = $this->connect();
        
        // Get subject basic info
        $stmt = $db->prepare("SELECT * FROM subjects WHERE id = ?");
        $stmt->execute([$subject_id]);
        $subject = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$subject) {
            return false;
        }
        
        // Get prerequisites
        $stmt = $db->prepare("
            SELECT p.id, p.code, p.name 
            FROM subjectprerequisites sp 
            JOIN subjects p ON sp.prerequisite_id = p.id 
            WHERE sp.subject_id = ?
        ");
        $stmt->execute([$subject_id]);
        $subject['prerequisites'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get schedules
        $stmt = $db->prepare("SELECT * FROM subjectschedules WHERE subject_id = ?");
        $stmt->execute([$subject_id]);
        $subject['schedules'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $subject;
    }

    public function update_subject($subject_id, $code, $name, $units, $year_level, $semester, $max_students, $types, $prerequisites = [], $schedules = [], $description = '') {
        $db = $this->connect();
        
        try {
            $db->beginTransaction();
            
            // Check if subject code already exists (excluding current subject)
            $checkStmt = $db->prepare("SELECT id FROM subjects WHERE code = ? AND id != ?");
            $checkStmt->execute([$code, $subject_id]);
            
            if ($checkStmt->rowCount() > 0) {
                $db->rollBack();
                return false;
            }
            
            // Check for duplicate schedules
            $uniqueSchedules = [];
            foreach ($schedules as $schedule) {
                $key = $schedule['section'] . '-' . $schedule['day'] . '-' . $schedule['start_time'] . '-' . $schedule['end_time'];
                if (!isset($uniqueSchedules[$key])) {
                    $uniqueSchedules[$key] = $schedule;
                }
            }
            $schedules = array_values($uniqueSchedules);
            
            // Update subject
            $stmt = $db->prepare("UPDATE subjects SET code = ?, name = ?, description = ?, units = ?, year_level = ?, semester = ?, max_students = ? WHERE id = ?");
            $stmt->execute([$code, $name, $description, $units, $year_level, $semester, $max_students, $subject_id]);
            
            // Delete old prerequisites
            $stmt = $db->prepare("DELETE FROM subjectprerequisites WHERE subject_id = ?");
            $stmt->execute([$subject_id]);
            
            // Insert new prerequisites
            if (!empty($prerequisites)) {
                $stmt = $db->prepare("INSERT INTO subjectprerequisites (subject_id, prerequisite_id) VALUES (?, ?)");
                foreach ($prerequisites as $prereq_id) {
                    $stmt->execute([$subject_id, $prereq_id]);
                }
            }
            
            // Delete old schedules
            $stmt = $db->prepare("DELETE FROM subjectschedules WHERE subject_id = ?");
            $stmt->execute([$subject_id]);
            
            // Insert new schedules
            if (!empty($schedules)) {
                $stmt = $db->prepare("INSERT INTO subjectschedules (subject_id, Section, day, start_time, end_time, room, Type) VALUES (?, ?, ?, ?, ?, ?, ?)");
                foreach ($schedules as $schedule) {
                    $stmt->execute([
                        $subject_id, 
                        $schedule['section'],
                        $schedule['day'], 
                        $schedule['start_time'], 
                        $schedule['end_time'], 
                        $schedule['room'],
                        $schedule['type'],
                    ]);
                }
            }
            
            $db->commit();
            return true;
        } catch (PDOException $e) {
            $db->rollBack();
            error_log("Subject update failed: " . $e->getMessage());
            return false;
        }
    }




    // private function notify_supabase($table_name, $operation, $record_id) {
    // // Supabase configuration
    //     $supabase_url = 'https://dfvapjrkotprotpbpeju.supabase.co';
    //     $supabase_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImRmdmFwanJrb3Rwcm90cGJwZWp1Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTcxNDg1OTMsImV4cCI6MjA3MjcyNDU5M30.Hou-GtB-P8qJ4fxXbC-VtyaCkDpf5Kr01DD9aSckhiU';
        
    //     $data = [
    //         'table_name' => $table_name,
    //         'operation' => $operation,
    //         'record_id' => $record_id
    //     ];
        
    //     $ch = curl_init($supabase_url . '/rest/v1/notifications');
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($ch, CURLOPT_POST, true);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, [
    //         'Content-Type: application/json',
    //         'apikey: ' . $supabase_key,
    //         'Authorization: Bearer ' . $supabase_key
    //     ]);
        
    //     $response = curl_exec($ch);
    //     curl_close($ch);
        
    //     return $response;
    // }

    // private function notify_supabase($table_name, $operation, $record_id) {
    // // Supabase configuration
    // $supabase_url = 'https://dfvapjrkotprotpbpeju.supabase.co';
    // $supabase_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImRmdmFwanJrb3Rwcm90cGJwZWp1Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTcxNDg1OTMsImV4cCI6MjA3MjcyNDU5M30.Hou-GtB-P8qJ4fxXbC-VtyaCkDpf5Kr01DD9aSckhiU';
    
    // $data = [
    //     'table_name' => $table_name,
    //     'operation' => $operation,
    //     'record_id' => $record_id
    // ];
    
    // $ch = curl_init($supabase_url . '/rest/v1/notifications');
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($ch, CURLOPT_POST, true);
    // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    // curl_setopt($ch, CURLOPT_HTTPHEADER, [
    //     'Content-Type: application/json',
    //     'apikey: ' . $supabase_key,
    //     'Authorization: Bearer ' . $supabase_key,
    //     'Prefer: return=minimal'
    // ]);
    
    // $response = curl_exec($ch);
    // $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // if (curl_errno($ch)) {
    //     error_log('Curl error: ' . curl_error($ch));
    // }
    
    // curl_close($ch);
    
    // error_log("Supabase notification sent. HTTP Code: $http_code, Response: $response");
    
    // return $response;
    // }


    private function notify_supabase($table_name, $operation, $record_id) {
        // Supabase configuration
        $supabase_url = 'https://dfvapjrkotprotpbpeju.supabase.co';
        $supabase_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImRmdmFwanJrb3Rwcm90cGJwZWp1Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTcxNDg1OTMsImV4cCI6MjA3MjcyNDU5M30.Hou-GtB-P8qJ4fxXbC-VtyaCkDpf5Kr01DD9aSckhiU';
        
        $data = [
            'table_name' => $table_name,
            'operation' => $operation,
            'record_id' => $record_id
        ];
        
        $ch = curl_init($supabase_url . '/rest/v1/notifications');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'apikey: ' . $supabase_key,
            'Authorization: Bearer ' . $supabase_key,
            'Prefer: return=minimal'
        ]);
        
        // Additional options for better debugging
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        // Log the results
        error_log("Supabase notification - Table: $table_name, Operation: $operation, Record ID: $record_id");
        error_log("HTTP Code: $http_code");
        error_log("Response: " . print_r($response, true));
        if ($error) {
            error_log("CURL Error: $error");
        }
        
        return $response;
    }

    public function forgotPassword(){
        $x = $this->connect()->prepare("SELECT * FROM admin WHERE email = ?");
        $x->execute([$this->x1]);
        if($x->rowCount() > 0){
            if($this->x2 === $this->x3){
                $hashedPassword = password_hash($this->x2, PASSWORD_DEFAULT);
                $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                $_SESSION['Email'] = $this->x1;
                $_SESSION['code'] = $otp;
                $_SESSION['newPassword'] = $hashedPassword;
                // Start the PHPmailer
                require "../Mail/phpmailer/PHPMailerAutoload.php";
                $mail = new PHPMailer;

                $mail->isSMTP();
                $mail->Host='smtp.gmail.com';
                $mail->Port=587;
                $mail->SMTPAuth=true;
                $mail->SMTPSecure='tls';

                $mail->Username='ur@gmail.com';
                $mail->Password='ur_password';

                $mail->setFrom('enrollsys@evsu.ormoc.ph', 'EnrollSys');
                $mail->addAddress($this->x1);

                $mail->isHTML(true);
                $mail->Subject="Reset Password Code";

                // The HTML email body with logo
                $mail->Body='<!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Password Reset Code</title>
                    <style>
                        body {
                            font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;
                            line-height: 1.6;
                            color: #333333;
                            margin: 0;
                            padding: 0;
                            background-color: #f7f7f7;
                        }
                        .email-container {
                            max-width: 600px;
                            margin: 0 auto;
                            background-color: #ffffff;
                            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                            border-radius: 8px;
                            overflow: hidden;
                        }
                        .email-header {
                            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
                            padding: 25px;
                            text-align: center;
                        }
                        .logo-container {
                            background-color: white;
                            border-radius: 8px;
                            padding: 15px;
                            display: inline-block;
                            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
                        }
                        .logo {
                            max-width: 180px;
                            height: auto;
                        }
                        .email-body {
                            padding: 30px;
                        }
                        .email-footer {
                            background-color: #f2f2f2;
                            padding: 20px;
                            text-align: center;
                            font-size: 12px;
                            color: #666666;
                        }
                        .code-container {
                            background-color: #f8f9fa;
                            border-left: 4px solid #3498db;
                            padding: 20px;
                            margin: 25px 0;
                            text-align: center;
                            border-radius: 4px;
                        }
                        .reset-code {
                            font-size: 32px;
                            font-weight: bold;
                            letter-spacing: 5px;
                            color: #2c3e50;
                            padding: 15px;
                            margin: 20px 0;
                            background: #ffffff;
                            border: 2px dashed #3498db;
                            border-radius: 8px;
                            display: inline-block;
                        }
                        .button {
                            display: inline-block;
                            padding: 12px 24px;
                            background-color: #3498db;
                            color: white;
                            text-decoration: none;
                            border-radius: 4px;
                            margin: 15px 0;
                            font-weight: bold;
                        }
                        .signature {
                            margin-top: 30px;
                            border-top: 1px solid #eeeeee;
                            padding-top: 20px;
                        }
                        .warning {
                            background-color: #fff3e0;
                            border-left: 4px solid #ff9800;
                            padding: 15px;
                            margin: 20px 0;
                            border-radius: 4px;
                            font-size: 14px;
                        }
                        @media screen and (max-width: 600px) {
                            .email-body {
                                padding: 20px;
                            }
                            .reset-code {
                                font-size: 24px;
                                letter-spacing: 3px;
                                padding: 10px;
                            }
                            .logo {
                                max-width: 140px;
                            }
                        }
                    </style>
                </head>
                <body>
                    <div class="email-container">
                        <div class="email-header">
                            <div class="logo-container">
                                <img src="https://enrollsys.great-site.net/log1.png" alt="EnrollSys Logo" class="logo">
                            </div>
                        </div>
                        
                        <div class="email-body">
                            <p>Dear user,</p>
                            
                            <p>You recently requested to reset your password for your EnrollSys account. Use the verification code below to complete the process.</p>
                            
                            <div class="code-container">
                                <h3 style="margin-top: 0; color: #2c3e50;">Your Password Reset Code</h3>
                                <div class="reset-code">'.$otp.'</div>
                            </div>
                            
                            <div class="warning">
                                <strong>Security Note:</strong> If you didn\'t request this password reset, please ignore this email or contact support if you have concerns about your account\'s security.
                            </div>
                            
                            <p>For assistance, please contact the EnrollSys support team at <a href="mailto:support@evsu.ormoc.ph">support@evsu.ormoc.ph</a>.</p>
                            
                            <div class="signature">
                                <p>Best regards,<br>
                                <strong>Team CyberNexus (EnrollSys)</strong><br>
                                EVSU Ormoc Campus</p>
                            </div>
                        </div>
                        
                        <div class="email-footer">
                            <p>© '.date('Y').' EnrollSys - EVSU Ormoc Campus. All rights reserved.</p>
                            <p>This is an automated message, please do not reply directly to this email.</p>
                            <p><a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>
                        </div>
                    </div>
                </body>
                </html>';

                if(!$mail->send()){
                    echo 'err';
                }else{
                    echo '5';
                }
                // echo 'success';
            }else{
                $x = null;
                echo '2';
            }
        }else{
            $x = null;
            echo '1';
        }
    }

    function updateAdminPassword(){
        $x = $this->connect()->prepare("UPDATE admin SET password = ? WHERE email = ?");
        $x->execute([$this->x2, $this->x1]);
        if($x){
            $x = null;
            echo '1';
        }else{
            $x = null;
            echo '0';
        }
    }


}##End of Class