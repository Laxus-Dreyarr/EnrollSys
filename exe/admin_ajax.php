<?php
session_start();
include('../Class/Db.php');
include('../Class/Admin.php');

if (!isset($_SESSION['admin_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$admin_id = $_SESSION['admin_id'];
$response = ['success' => false, 'message' => ''];

try {
    $admin = new Admin($admin_id, 0, 0, 0, 0, 0, 0, 0, 0, 0);
    
    // Log received data for debugging
    error_log("Received action: " . ($_POST['action'] ?? 'none'));
    
    if ($_POST['action'] == 'get_stats') {
        $stats = $admin->get_statistics();
        $response = ['success' => true, 'stats' => $stats];
    }
    elseif ($_POST['action'] == 'create_subject') {
        error_log("Creating subject with data: " . print_r($_POST, true));
        
        $prerequisites = isset($_POST['prerequisites']) ? $_POST['prerequisites'] : [];
        $schedules = isset($_POST['schedules']) ? $_POST['schedules'] : [];
        $types = isset($_POST['types']) ? $_POST['types'] : [];
        $description = isset($_POST['description']) ? $_POST['description'] : '';
        
        $success = $admin->create_subject(
            $_POST['code'],
            $_POST['name'],
            $_POST['units'],
            $_POST['year_level'],
            $_POST['semester'],
            $_POST['max_students'],
            $types,
            $prerequisites,
            $schedules,
            $description
        );
        
        if ($success) {
            $response = ['success' => true, 'message' => 'Subject created successfully'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to create subject. Subject code might already exist.'];
        }
    }
    elseif ($_POST['action'] == 'generate_passkey') {
        $success = $admin->generate_passkey($_POST['email'], $_POST['user_type']);
        
        if ($success) {
            $response = ['success' => true, 'message' => 'Passkey generated and sent'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to generate passkey'];
        }
    }
    elseif ($_POST['action'] == 'get_prerequisites') {
        $prerequisites = $admin->get_prerequisite_options();
        $response = ['success' => true, 'prerequisites' => $prerequisites];
    }
    elseif ($_POST['action'] == 'get_subjects') {
    $subjects = $admin->get_all_subjects_with_schedules();
    $response = ['success' => true, 'subjects' => $subjects];
    }
    elseif ($_POST['action'] == 'get_audit_logs') {
        $logs = $admin->get_audit_logs();
        $response = ['success' => true, 'logs' => $logs];
    }
    elseif ($_POST['action'] == 'delete_subject') {
        $success = $admin->delete_subject($_POST['subject_id']);
        $response = ['success' => $success, 'message' => $success ? 'Subject deleted successfully' : 'Failed to delete subject'];
    }
    elseif ($_POST['action'] == 'get_subject') {
        $subject = $admin->get_subject_by_id($_POST['subject_id']);
        if ($subject) {
            $response = ['success' => true, 'subject' => $subject];
        } else {
            $response = ['success' => false, 'message' => 'Subject not found'];
        }
    }
    elseif ($_POST['action'] == 'update_subject') {
        $prerequisites = isset($_POST['prerequisites']) ? $_POST['prerequisites'] : [];
        $schedules = isset($_POST['schedules']) ? $_POST['schedules'] : [];
        $types = isset($_POST['types']) ? $_POST['types'] : [];
        $description = isset($_POST['description']) ? $_POST['description'] : '';
        
        $success = $admin->update_subject(
            $_POST['subject_id'],
            $_POST['code'],
            $_POST['name'],
            $_POST['units'],
            $_POST['year_level'],
            $_POST['semester'],
            $_POST['max_students'],
            $types,
            $prerequisites,
            $schedules,
            $description
        );
        
        if ($success) {
            $response = ['success' => true, 'message' => 'Subject updated successfully'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to update subject. Subject code might already exist.'];
        }
    }
    
    } catch (Exception $e) {
    error_log("Error in admin_ajax.php: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'Server error: ' . $e->getMessage()];
}

header('Content-Type: application/json');
echo json_encode($response);