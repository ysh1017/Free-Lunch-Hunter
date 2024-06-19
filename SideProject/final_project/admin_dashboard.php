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
    echo "你沒有權限訪問此頁面。";
    exit;
}

// 獲取排序方式
$order = isset($_GET['order']) ? $_GET['order'] : 'asc'; // 默認升序

// 獲取用戶發佈的活動
$sql = "SELECT * FROM activities ORDER BY act_date $order, STARTT $order";
$stmt = $conn->prepare($sql);
$stmt->execute();
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 將活動轉換為行事曆事件格式
$events = [];
foreach ($activities as $activity) {
    $events[] = [
        'title' => htmlspecialchars($activity['title']),
        'start' => htmlspecialchars($activity['act_date'] . 'T' . $activity['STARTT']),
        'end' => htmlspecialchars($activity['act_date'] . 'T' . $activity['ENDT']),
        'description' => htmlspecialchars($activity['content'])
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理員控制台 - 免費午餐分享平台</title>
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
            display: flex;
            padding-top: 0px;
            /* to prevent content from being hidden behind the navbar */
        }

        .sort-container {
            display: flex;
            text-align: center;
            justify-content: center;
            width: 100%;
            padding-top: 50px;
            padding-bottom: 0px;
            margin: 25px;
        }

        .sort-container form {
            display: inline-block;
        }

        .sort-container select {
            padding: 5px 10px;
            font-size: 16px;
        }
    </style>

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
    <div class="sort-container">
        <form method="get" action="admin_dashboard.php">
            <label for="order">排序方式：</label>
            <select name="order" id="order" onchange="this.form.submit()">
                <option value="asc" <?php if (isset($_GET['order']) && $_GET['order'] == 'asc')
                    echo 'selected'; ?>>
                    按時間由舊到新</option>
                <option value="desc" <?php if (isset($_GET['order']) && $_GET['order'] == 'desc')
                    echo 'selected'; ?>>
                    按時間由新到舊</option>
            </select>
        </form>
    </div>
    <div class="container">
        <div id="activities">
            <?php foreach ($activities as $activity): ?>
                <div class="activity">
                    <h3 class="activity-title"><?php echo htmlspecialchars($activity['title']); ?></h3>
                    <p class="activity-content"><?php echo htmlspecialchars($activity['content']); ?></p>
                    <?php if (!empty($activity['img'])): ?>
                        <img src="<?php echo htmlspecialchars($activity['img']); ?>" alt="Activity Image"
                            style="max-width:30%; height:auto; object-fit: contain;'">
                    <?php endif; ?>
                    <p>活動日期：<?php echo htmlspecialchars($activity['act_date']); ?></p>
                    <p>地點：<?php echo htmlspecialchars($activity['place']); ?></p>
                    <p>開始時間：<?php echo htmlspecialchars($activity['STARTT']); ?></p>
                    <p>結束時間：<?php echo htmlspecialchars($activity['ENDT']); ?></p>
                    <p>報名網址：<a
                            href="<?php echo htmlspecialchars($activity['URL']); ?>"><?php echo htmlspecialchars($activity['URL']); ?></a>
                    </p>
                    <p>午餐內容：<?php echo htmlspecialchars($activity['LUNCH']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            $('#calendar').fullCalendar({
                events: <?php echo json_encode($events); ?>,
                eventRender: function (event, element) {
                    element.qtip({
                        content: event.description
                    });
                }
            });
        });
    </script>
    <footer>
        <p>CopyRight © 2024 Great Purpose Team All Rights Reserved</p>
    </footer>
</body>

</html>