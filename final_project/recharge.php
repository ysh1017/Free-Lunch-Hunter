<?php
require 'config.php';
require 'premium_check.php';
require 'navbar.php';
session_start();

// 確認用戶已登入
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = $_POST['amount'];
    if ($amount > 0) {
        try {
            $sql = "INSERT INTO premium (user_id, money) VALUES (:user_id, :money)
                    ON DUPLICATE KEY UPDATE money = money + :money";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':money', $amount);
            $stmt->execute();

            echo "儲值成功！";
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            echo '儲值失敗：' . $e->getMessage();
        }
    } else {
        echo '儲值金額必須大於 0。';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>儲值點數</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e3f2fd;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        header {
            text-align: center;
            margin-bottom: 20px;
        }

        header h1 {
            margin: 0;
            color: #1e88e5;
        }

        header a {
            color: #1565c0;
            text-decoration: none;
        }

        form {
            text-align: center;
        }

        label {
            display: inline-block;
            margin-bottom: 10px;
            color: #1e88e5;
            font-size: 18px;
            vertical-align: middle;
        }

        .input-group {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }

        input[type="number"] {
            width: 200px;
            padding: 10px;
            margin-left: 10px;
            border: 1px solid #1e88e5;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            background-color: #1e88e5;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            vertical-align: middle;
        }

        button:hover {
            background-color: #1565c0;
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h1>儲值點數</h1>
            <a href="index.php">返回首頁</a>
        </header>

        <form method="post" action="recharge.php">
            <div class="input-group">
                <label for="amount">儲值金額：</label>
                <input type="number" name="amount" id="amount" required>
            </div>
            <button type="submit">儲值</button>
        </form>
    </div>
</body>

</html>
