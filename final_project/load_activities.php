<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    exit;
}

$user_id = $_SESSION['user_id'];

// 获取用户加入的活动
$sql = "SELECT a.activity_id as id, 
               a.title, 
               CONCAT(a.act_date, ' ', a.STARTT) as start, 
               CONCAT(a.act_date, ' ', a.ENDT) as end, 
               'activity' as type
        FROM activities a
        JOIN join_activity ja ON a.activity_id = ja.activity_id
        WHERE ja.user_id = :user_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);


echo json_encode($activities);
