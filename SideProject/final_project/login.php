<?php
require 'config.php';
session_start();

$error = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $sql = "SELECT user_id, password FROM user_account WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];

            // 檢查是否為管理員
            $sql = "SELECT premium FROM premium WHERE user_id = :user_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':user_id', $user['user_id']);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $premium = $stmt->fetch();

            if ($premium && $premium['premium'] == 2) {
                // 登入成功後如果是管理員，跳轉到管理員控制台
                header('Location: admin_dashboard.php');
            } else {
                // 否則跳轉到主頁面
                header('Location: index.php');
            }
            exit;
        } else {
            $error = "登入失敗，帳號或密碼錯誤。";
        }
    } catch (PDOException $e) {
        $error = '登入失敗：' . $e->getMessage();
    }
}

if ($error) {
    header('Location: login.php?error=' . urlencode($error) . '&email=' . urlencode($email));
    exit;
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登入 - 免費午餐分享平台</title>
    <link rel="stylesheet" href="styles_login.css">
    <script src="https://code.jquery.com/jquery-3.4.1.js" integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
</head>

<body>
    <div class="container">
        <div class="form-container">
            <h2>登入</h2><br>
            <form action="login.php" method="post">
                <div class="input-group">
                <input type="email" name="email" required value="<?php echo htmlspecialchars(isset($_GET['email']) ? $_GET['email'] : ''); ?>">
                    <label>Email:</label>
                </div>
                <br>
                <div class="input-group">
                    <input type="password" name="password" required>
                    <label>Password:</label>
                </div>
                <button type="submit" class="btn login-btn">Login</button>
                <div class="register-section">
                    <span>Don't have an account?</span>
                    <button type="button" class="btn register-btn"
                        onclick="window.location.href='register.php'">Register ➔</button>
                </div>
            </form>
        </div>
    </div>
    <script>
    $(document).ready(function () {
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('error')) {
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: decodeURIComponent(urlParams.get('error')),
            });
        }
    });
    </script>
</body>

</html>