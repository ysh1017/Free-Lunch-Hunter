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

try {
    $sql = "SELECT * FROM activities";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$activities) {
        echo "活動不存在或你沒有權限編輯此活動。";
    }
} catch (PDOException $e) {
    $error = '獲取活動失敗：' . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $activity_id = $_POST['activity_id'];
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
        $sql = "UPDATE activities SET title = :title, content = :content, img = :img, act_date = :act_date, place = :place, STARTT = :start_time, ENDT = :end_time, URL = :url, LUNCH = :lunch WHERE activity_id = :activity_id AND user_id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':img', $img);
        $stmt->bindParam(':act_date', $act_date);
        $stmt->bindParam(':place', $place);
        $stmt->bindParam(':start_time', $start_time);
        $stmt->bindParam(':end_time', $end_time);
        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':lunch', $lunch);
        $stmt->bindParam(':activity_id', $activity_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        header('Location: manage_activity.php');
        exit;
    } catch (PDOException $e) {
        $error = '更新活動失敗：' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理活動 - 免費午餐分享平台</title>
    <link rel="stylesheet" href="styles1.css">
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
            <h2>管理活動</h2><br>
            <?php if (!empty($error)) echo "<p style='color: red;'>$error</p>"; ?>
    
            <?php if ($activities): ?>
                <?php foreach ($activities as $activity): ?>
                    <form action="manage_activity.php" method="post">
                        <div class="edit">
                            <input type="hidden" name="activity_id" value="<?php echo htmlspecialchars($activity['activity_id']); ?>">
                            <div class="input-group">
                                <input type="text" name="title" value="<?php echo htmlspecialchars($activity['title']); ?>" required>
                                <label>Title:</label>
                            </div>
                            <div class="input-group">
                                <textarea name="content" required><?php echo htmlspecialchars($activity['content']); ?></textarea>
                            </div>
                            <br>
                            <div class="input-group">
                                <input type="text" name="img" value="<?php echo htmlspecialchars($activity['img']); ?>">
                                <label>Image URL:</label>
                            </div>
                            <br>
                            <div class="input-group">
                                <input type="date" name="act_date" value="<?php echo htmlspecialchars($activity['act_date']); ?>" required>
                                <label>Activity Date:</label>
                            </div>
                            <br>
                            <div class="input-group">
                                <input type="text" name="place" value="<?php echo htmlspecialchars($activity['place']); ?>" required>
                                <label>Place:</label>
                            </div>
                            <br>
                            <div class="input-group">
                                <input type="time" name="start_time" value="<?php echo htmlspecialchars($activity['STARTT']); ?>" required>
                                <label>Start Time:</label>
                            </div>
                            <br>
                            <div class="input-group">
                                <input type="time" name="end_time" value="<?php echo htmlspecialchars($activity['ENDT']); ?>" required>
                                <label>End Time:</label>
                            </div>
                            <br>
                            <div class="input-group">
                                <input type="url" name="url" value="<?php echo htmlspecialchars($activity['URL']); ?>">
                                <label>URL:</label>
                            </div>
                            <br>
                            <div class="input-group">
                                <input type="text" name="lunch" value="<?php echo htmlspecialchars($activity['LUNCH']); ?>">
                                <label>Lunch:</label>
                            </div>
                        </div>
                        <button type="submit" class="btn">更新活動</button>
                        <br>
                    </form>
                <?php endforeach; ?>
            <?php else: ?>
                <p>活動不存在或你沒有權限編輯此活動。</p>
            <?php endif; ?>
        </div>
    </div>
    <footer>
        <p>CopyRight © 2024 Great Purpose Team All Rights Reserved</p>
    </footer>
</body>

</html>
