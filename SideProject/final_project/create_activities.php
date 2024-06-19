<?php
require 'config.php';
require 'premium_check.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isPremium($user_id, $conn) || getUserPremiumLevel($user_id, $conn) < 2) {
    echo "你沒有權限發布活動。";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $img = $_POST['img'];
    $act_date = $_POST['act_date'];
    $place = $_POST['place'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $url = $_POST['url'];
    $lunch = $_POST['lunch'];

    try {
        $sql = "INSERT INTO activities (user_id, title, content, img, act_date, place, STARTT, ENDT, URL, LUNCH)
                VALUES (:user_id, :title, :content, :img, :act_date, :place, :start_time, :end_time, :url, :lunch)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':img', $img);
        $stmt->bindParam(':act_date', $act_date);
        $stmt->bindParam(':place', $place);
        $stmt->bindParam(':start_time', $start_time);
        $stmt->bindParam(':end_time', $end_time);
        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':lunch', $lunch);
        $stmt->execute();

        header('Location: admin_dashboard.php');
        exit;
    } catch (PDOException $e) {
        $error = '發布活動失敗：' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>發布活動 - 免費午餐分享平台</title>
    <link rel="stylesheet" href="style_activity.css">
    <style>
        .navbar_admin {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #333;
            padding: 10px;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .navbar_admin a {
            color: white;
            padding: 14px 20px;
            text-decoration: none;
            text-align: center;
        }

        .navbar_admin a:hover {
            background-color: #ddd;
            color: black;
        }

        .container {
            padding-top: 90px;
            padding-bottom: 25px;
        }
    </style>
</head>

<body>
    <div class="navbar_admin">
        <h1 style="color: white; margin: 0;">管理員控制台</h1>
        <div>
            <a href="admin_dashboard.php">活動總覽</a>
            <a href="create_activities.php">發布新活動</a>
            <a href="manage_activity.php">管理活動</a>
            <a href="login.php">登出</a>
        </div>
    </div>

    <div class="container">
        <div class="form-container">
            <h2>發布活動</h2><br>
            <form action="create_activities.php" method="post">
                <div class="input-group">
                    <input type="text" name="title" required>
                    <label>活動名稱：</label>
                </div>
                
                <div class="input-group">
                 <!--   <label>Content:</label> -->
                    <textarea name="content" required placeholder="新增活動描述"></textarea>
                    
                </div>
                <br>
                <div class="input-group">
                    <input type="text" name="img">
                    <label>圖片URL：</label>
                </div>
                <br>
                <div class="input-group">
                    <input type="date" name="act_date" required>
                    <label>活動日期：</label>
                </div>
                <br>
                <div class="input-group">
                    <input type="text" name="place" required>
                    <label>地點：</label>
                </div>
                <br>
                <div class="input-group">
                    <input type="time" name="start_time" required>
                    <label>開始時間：</label>
                </div>
                <br>
                <div class="input-group">
                    <input type="time" name="end_time" required>
                    <label>結束時間：</label>
                </div>
                <br>
                <div class="input-group">
                    <input type="url" name="url">
                    <label>報名網址：</label>
                </div>
                <br>
                <div class="input-group">
                    <input type="text" name="lunch">
                    <label>午餐內容：</label>
                </div>
                <button type="submit" class="btn">發布活動</button>
            </form>
        </div>
    </div>

    <footer>
        <p>CopyRight © 2024 Great Purpose Team All Rights Reserved</p>
    </footer>
</body>

</html>