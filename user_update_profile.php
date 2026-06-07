<?php
session_start();
include("db.php");

// Show errors during development (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure student is logged in
if (!isset($_SESSION['user_number']) || $_SESSION['role'] !== 'student') {
    echo "<script>alert('Access denied. Please login as student.'); window.location.href='login.php';</script>";
    exit();
}

// Get and sanitize POST data
$stud_num = trim($_POST['stud_num']);
$stud_name = trim($_POST['stud_name']);
$stud_pass = trim($_POST['stud_pass']);
$stud_tel = trim($_POST['stud_tel']);
$stud_email = trim($_POST['stud_email']);
$course_code = trim($_POST['course_code']);
$faculty = trim($_POST['faculty']);

// Basic validation
if (
    empty($stud_num) || empty($stud_name) || empty($stud_pass) || 
    empty($stud_tel) || empty($stud_email) || empty($course_code) || empty($faculty)
) {
    echo "<script>alert('All fields are required.'); window.history.back();</script>";
    exit();
}

// Update query
$query = "UPDATE user SET 
    stud_name = ?, 
    stud_pass = ?, 
    stud_tel = ?, 
    stud_email = ?, 
    course_code = ?, 
    faculty = ? 
    WHERE stud_num = ?";

$stmt = $connect->prepare($query);
$stmt->bind_param("sssssss", $stud_name, $stud_pass, $stud_tel, $stud_email, $course_code, $faculty, $stud_num);

if ($stmt->execute()) {
    echo "<script>alert('Profile updated successfully.'); window.location.href='user_profile.php';</script>";
} else {
    echo "<script>alert('Failed to update profile. Please try again.'); window.history.back();</script>";
}
?>
