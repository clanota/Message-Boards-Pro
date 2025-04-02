<?php
error_reporting(0);

// 检查是否已安装
if (file_exists('config.json')) {
    die('<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
        <meta name="renderer" content="webkit">
        <link rel="stylesheet" href="https://npm.elemecdn.com/mdui@1.0.2/dist/css/mdui.min.css">
        <title>已经安装</title>
    </head>
    <body>
        <div class="mdui-container"><br>
            <div class="mdui-card">
                <div class="mdui-card-primary">
                    <div class="mdui-card-primary-title">
                        系统已安装
                    </div>
                    <div class="mdui-card-primary-subtitle">
                        如需重新安装，请先删除config.json文件
                    </div>
                </div>
            </div>
        </div>
        <br>
        <script src="https://npm.elemecdn.com/mdui@1.0.2/dist/js/mdui.min.js"></script>
    </body>
</html>');
}

// 处理表单提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $siteName = htmlspecialchars($_POST['siteName']);
    $siteDesc = htmlspecialchars($_POST['siteDesc']);
    $adminPass = password_hash($_POST['adminPass'], PASSWORD_DEFAULT);
    $manualReview = 1;
    
    // 创建data目录
    if (!file_exists('data')) {
        mkdir('data', 0755, true);
    }
    
    // 创建配置文件
    $config = array(
        'siteName' => $siteName,
        'siteDesc' => $siteDesc,
        'adminPass' => $adminPass,
        'manualReview' => $manualReview
    );
    
    file_put_contents('config.json', json_encode($config, JSON_PRETTY_PRINT));
    
    // 创建留言文件
    file_put_contents('data/messages.json', json_encode(array(), JSON_PRETTY_PRINT));
    
    // 跳转到首页
    header('Location: index.php');
    exit;
}
?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
        <meta name="renderer" content="webkit">
        <link rel="stylesheet" href="https://npm.elemecdn.com/mdui@1.0.2/dist/css/mdui.min.css">
        <title>安装留言板</title>
    </head>
    <body>
        <div class="mdui-container"><br>
            <div class="mdui-card">
                <div class="mdui-card-primary">
                    <div class="mdui-card-primary-title">
                        安装留言板
                    </div>
                    <div class="mdui-card-primary-subtitle">
                        请填写以下信息完成安装
                    </div>
                </div>
                <div class="mdui-card-content">
                    <form method="post">
                            站点名称:<br>
                            <input class="mdui-textfield-input" type="text" name="siteName" placeholder="请输入站点名称" required><br>
                            站点描述：<br>
                            <input class="mdui-textfield-input" type="text" name="siteDesc" placeholder="请输入站点描述" required><br>
                            管理员密码:<br>
                            <input class="mdui-textfield-input" type="password" name="adminPass" placeholder="请输入管理员密码" required>
                        <br>
                        <input class="mdui-btn mdui-btn-block mdui-btn-raised" type="submit" value="安装">
                    </form>
                </div>
            </div>
        </div>
        <script src="https://npm.elemecdn.com/mdui@1.0.2/dist/js/mdui.min.js"></script>
    </body>
</html>