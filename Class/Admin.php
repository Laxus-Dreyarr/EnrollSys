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
                echo '3';
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