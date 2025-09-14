<?php
// Include database connection
session_start();
include("../Class/Db.php");
include("../Class/Student.php");

// Set content type to JSON
header('Content-Type: application/json');

// Check if action parameter is set
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action == 'check_email') {
        // Check if email exists
        if (isset($_POST['email'])) {
            $email = $_POST['email'];

            // Create Student instance with email as first parameter, others as null/0
            $student = new Student($email, 0, 0, 0, 0, 0, 0, 0, 0, 0);
            
            // Check if email exists using Student class method
            if ($student->checkEmailExists()) {
                echo json_encode(['exists' => true]);
            } else {
                echo json_encode(['exists' => false]);
            }
        } else {
            echo json_encode(['error' => 'Email parameter missing']);
        }
    } 
    elseif ($action == 'register') {
        // Handle registration
        $givenName = $_POST['givenName'];
        $lastName = $_POST['lastName'];
        $middleName = isset($_POST['middleName']) ? $_POST['middleName'] : '';
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        // Create Student instance with all required parameters
        $student = new Student(
            $email,         // x1
            $password,      // x2
            $givenName,     // x3
            $lastName,      // x4
            $middleName,    // x5
            'NULL',   // x6 (default birthdate)
            'NULL',      // x7 (default address)
            'default.jpg',  // x8 (default profile)
            'student',      // x9 (user_type)
            1               // x10 (is_active)
        );
        
        // Register student using Student class method
        $result = $student->registerStudent();
        
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Registration successful']);
        } else {
            echo json_encode(['success' => false, 'message' => $result['message']]);
        }
    }
    // Add login action
    // elseif (isset($_POST['em'])) {

    //         $email = $_POST['em'];
    //         $password = $_POST['password'];
            

    //         $student = new Student($email, $password, '', '', '', '', '', '', '', '');
    //         $student->studentLogin();

    //         if ($result['success']) {
    //             session_start();
    //             $_SESSION['user_id'] = $result['user']['id'];
    //             $_SESSION['user_type'] = 'student';
    //             $_SESSION['firstname'] = $result['user']['firstname'];
    //             $_SESSION['lastname'] = $result['user']['lastname'];
    //             $_SESSION['id_no'] = $result['user']['id_no'];
                
    //             echo json_encode([
    //                 'success' => true, 
    //                 'message' => 'Login successful',
    //                 'user' => $result['user']
    //             ]);
    //         } else {
    //             echo json_encode(['success' => false, 'message' => $result['message']]);
    //         }
    // }

} else {
    echo json_encode(['error' => 'No action specified']);
}
?>