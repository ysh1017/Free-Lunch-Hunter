<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'];
$activity_id = $_POST['activity_id'] ?? null;

if ($action == 'add') {
    $sql = "INSERT INTO join_activity (user_id, activity_id) VALUES (:user_id, :activity_id)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':activity_id', $activity_id);
    if ($stmt->execute()) {
        echo 'Activity added successfully.';
    } else {
        echo 'Failed to add activity.';
    }
} elseif ($action == 'delete') {
    $sql = "DELETE FROM join_activity WHERE user_id = :user_id AND activity_id = :activity_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':activity_id', $activity_id);
    if ($stmt->execute()) {
        echo 'Activity deleted successfully.';
    } else {
        echo 'Failed to delete activity.';
    }
}
?>
