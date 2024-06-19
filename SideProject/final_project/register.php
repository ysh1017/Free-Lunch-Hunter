<?php
require 'config.php';

$error = ''; // 初始化錯誤變數

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $birth_date = $_POST['birth_date'];
    $user_role = $_POST['user_role'];

    if ($_POST['password'] !== $_POST['confirm_password']) {
        $error = "密碼不匹配，請重新輸入。";
    } else {
        try {
            // 檢查 email 是否已存在
            $email_sql = "SELECT user_id FROM user_account WHERE email = :email";
            $stmt = $conn->prepare($email_sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $email_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 檢查 username 是否已存在
            $username_sql = "SELECT user_id FROM user_account WHERE username = :username";
            $stmt = $conn->prepare($username_sql);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $username_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($email_results) > 0) {
                $error = "該Email已被註冊，請使用其他Email。";
            } elseif (count($username_results) > 0) {
                $error = "該Username已被註冊，請使用其他Username。";
            } else {
                $sql = "INSERT INTO user_account (email, username, birth_date, password, reg_date)
                        VALUES (:email, :username, :birth_date, :password, NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':birth_date', $birth_date);
                $stmt->bindParam(':password', $password);
                $stmt->execute();

                $user_id = $conn->lastInsertId();

                // 如果是管理員，設置 premium 等級為 2
                $premium_level = ($user_role === 'admin') ? 2 : 0;
                $sql = "INSERT INTO premium (user_id, premium, money) VALUES (:user_id, :premium, 0)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':premium', $premium_level);
                $stmt->execute();

                // 註冊成功後跳轉到登入頁面
                header('Location: login.php');
                exit;
            }
        } catch (PDOException $e) {
            $error = '註冊失敗：' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>註冊 - 免費午餐分享平台</title>
    <link rel="stylesheet" href="styles_admin_and_register.css">
    <script>
        // 檢查是否有錯誤訊息
        document.addEventListener('DOMContentLoaded', function() {
            const error = "<?php echo $error; ?>";
            if (error) {
                alert(error);
            }
        });
    </script>
</head>

<body>
    <div class="container">
        <div class="form-container">
            <h2>註冊</h2><br>
            <form action="register.php" method="post">
                <div class="input-group">
                    <input type="email" name="email" required>
                    <label>Email:</label>
                </div>
                <br>
                <div class="input-group">
                    <input type="text" name="username" required>
                    <label>Username:</label>
                </div>
                <br>
                <div class="input-group">
                    <input type="password" name="password" required>
                    <label>Password:</label>
                </div>
                <br>
                <div class="input-group">
                    <input type="password" name="confirm_password" required>
                    <label>Confirm Password:</label>
                </div>
                <br>
                <div class="input-group">
                    <input type="date" name="birth_date" required>
                    <label>Birth Date:</label>
                </div>
                <br>
                <div class="input-group dropdown-group">
                    <label for="user_role">註冊類型</label>
                    <select id="user_role" name="user_role">
                    <option value="user">普通使用者</option>
                    <option value="admin">管理員</option>
                    </select>
                </div>
                <button type="submit" class="btn register-btn">Register</button>
                <div class="login-section">
                    <span>Already have an account?</span>
                    <button type="button" class="btn login-btn" onclick="window.location.href='login.php'">Login
                        ➔</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
