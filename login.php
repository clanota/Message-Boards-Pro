<?php
error_reporting(0);

// 检查配置
if (!file_exists('config.json')) {
    header('Location: install.php');
    exit;
}

$config = json_decode(file_get_contents('config.json'), true);

// 处理登录
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['password'])) {
        if (password_verify($_POST['password'], $config['adminPass'])) {
            session_start();
            $_SESSION['admin_logged_in'] = true;
            header('Location: admin.php');
            exit;
        } else {
            $error = "用户名或密码错误";
        }
    } elseif (isset($_POST['destroy_session'])) {
        session_start();
        session_destroy();
        header('Location: login.php');
        exit;
    }
}
?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
        <meta name="renderer" content="webkit">
        <link rel="stylesheet" href="https://npm.elemecdn.com/mdui@1.0.2/dist/css/mdui.min.css">
        <title>管理员登录 - <?php echo $config['siteName']; ?></title>
    </head>
    <body>
        <div class="mdui-container"><br>
            <div class="mdui-card">
                <div class="mdui-card-primary">
                    <div class="mdui-card-primary-title">
                        管理员登录
                    </div>
                    <div class="mdui-card-primary-subtitle">
                        请输入管理员密码
                    </div>
                    <?php if (isset($error)): ?>
                        <div class="mdui-card-primary-subtitle mdui-text-color-red">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mdui-card-content">
                    <form method="post">
                        管理员密码：<br>
                        <input class="mdui-textfield-input" type="password" name="password" placeholder="密码" required><br>
                        <input class="mdui-btn mdui-btn-block mdui-btn-raised" type="submit" value="登录">
                    </form>
                </div>
            </div>
        </div>
        <script src="https://npm.elemecdn.com/mdui@1.0.2/dist/js/mdui.min.js"></script>
    </body>
</html>