<?php
error_reporting(0);

// 检查配置
if (!file_exists('config.json')) {
    header('Location: install.php');
    exit;
}

$config = json_decode(file_get_contents('config.json'), true);
$messages = json_decode(file_get_contents('data/messages.json'), true);

// 检查登录状态
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// 处理审核操作
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];
    $messageId = $_POST['id'];
    
    foreach ($messages as &$msg) {
        if ($msg['id'] === $messageId) {
            if ($action === 'approve') {
                $msg['approved'] = 1;
            } elseif ($action === 'reject') {
                $msg['approved'] = 0;
            } elseif ($action === 'delete') {
                $msg = null;
            }
            break;
        }
    }
    
    $messages = array_filter($messages);
    file_put_contents('data/messages.json', json_encode($messages, JSON_PRETTY_PRINT));
}

// 处理设置更新
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_settings'])) {
        $config['manualReview'] = $config['manualReview'] ? 0 : 1;
        file_put_contents('config.json', json_encode($config, JSON_PRETTY_PRINT));
        header('Location: admin.php');
        exit;
    }
    
    if (isset($_POST['update_siteName'])) {
        $config['siteName'] = htmlspecialchars($_POST['siteName']);
        file_put_contents('config.json', json_encode($config, JSON_PRETTY_PRINT));
        header('Location: admin.php');
        exit;
    }
    
    if (isset($_POST['update_siteDesc'])) {
        $config['siteDesc'] = htmlspecialchars($_POST['siteDesc']);
        file_put_contents('config.json', json_encode($config, JSON_PRETTY_PRINT));
        header('Location: admin.php');
        exit;
    }
    
    if (isset($_POST['update_password']) && !empty($_POST['newPassword'])) {
        $config['adminPass'] = password_hash($_POST['newPassword'], PASSWORD_DEFAULT);
        file_put_contents('config.json', json_encode($config, JSON_PRETTY_PRINT));
        session_destroy();
        header('Location: login.php');
        exit;
    }
    
    // 处理退出登录
    if (isset($_POST['logout'])) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

// 获取待审核留言
$pendingMessages = array_filter($messages, function($msg) {
    return !($msg['approved'] ?? false);
});
?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
        <meta name="renderer" content="webkit">
        <link rel="stylesheet" href="https://npm.elemecdn.com/mdui@1.0.2/dist/css/mdui.min.css">
        <title>留言管理 - <?php echo $config['siteName']; ?></title>
    </head>
    <body>
        <div class="mdui-container"><br>
            <div class="mdui-card">
                    <div class="mdui-card-primary">
                        <div class="mdui-card-primary-title">站点配置</div>
                        <div class="mdui-card-primary-subtitle">修改密码与站点名</div>
                    </div>
                    <div class="mdui-card-content">
                        <form method="post">
                            管理员密码：
                            <div class="mdui-row-xs-2">
                                <div class="mdui-col">
                                    <input class="mdui-textfield-input" type="password" name="newPassword" placeholder="新密码" required/>
                                </div>
                                <div class="mdui-col">
                                    <button type="submit" class="mdui-btn mdui-btn-block mdui-btn-raised mdui-ripple" name="update_password">更新密码</button>
                                </div>
                            </div>
                        </form>
                        <br>
                        <form method="post">
                            站点名称：
                            <div class="mdui-row-xs-2">
                                <div class="mdui-col">
                                    <input class="mdui-textfield-input" type="text" name="siteName" placeholder="新站点名称" value="<?php echo $config['siteName']; ?>" required/>
                                </div>
                                <div class="mdui-col">
                                    <button type="submit" class="mdui-btn mdui-btn-block mdui-btn-raised mdui-ripple" name="update_siteName">更新名称</button>
                                </div>
                            </div>
                        </form>
                        <br>
                        <form method="post">
                            站点描述：
                            <div class="mdui-row-xs-2">
                                <div class="mdui-col">
                                    <input class="mdui-textfield-input" type="text" name="siteDesc" placeholder="新站点描述" value="<?php echo $config['siteDesc']; ?>" required/>
                                </div>
                                <div class="mdui-col">
                                    <button type="submit" class="mdui-btn mdui-btn-block mdui-btn-raised mdui-ripple" name="update_siteDesc">更新描述</button>
                                </div>
                            </div>
                        </form>
                        <br>
                        <form method="post" class="mdui-form">
                            <div class="mdui-row-xs-3">
                                <div class="mdui-col">
                                    <button class="mdui-btn mdui-btn-block mdui-btn-raised" type="submit" name="update_settings" value="1">
                                        <?php echo $config['manualReview'] ? '禁用审核' : '启用审核'; ?>
                                    </button>
                                </div>
                                <div class="mdui-col">
                                    <a href="index.php" class="mdui-btn mdui-btn-block mdui-btn-raised mdui-color-theme">前往首页</a>
                                </div>
                                <div class="mdui-col">
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="logout" value="1">
                                        <button class="mdui-btn mdui-btn-block mdui-btn-raised" type="submit">退出登录</button>
                                    </form>
                                </div>
                            </div>
                        </form>
                    </div>
            </div><br>
            
            <div class="mdui-card">
                <div class="mdui-card-primary">
                    <div class="mdui-card-primary-title">
                        留言列表
                    </div>
                    <div class="mdui-card-primary-subtitle">
                        共<?php echo count($messages); ?>条留言，<?php echo count($pendingMessages); ?>条待审核
                    </div>
                </div>
                <div class="mdui-card-content">
                    <?php if (!empty($messages)): ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="mdui-card">
                                <div class="mdui-card-primary">
                                    <div class="mdui-card-primary-title"><?php echo $msg['nickname']; ?></div>
                                    <div class="mdui-card-primary-subtitle">发布于<?php echo $msg['created_at']; ?></div>
                                </div>
                                <div class="mdui-card-content">
                                    <?php if ($msg['approved'] ?? false): ?>
                                        <span style="color: green;">已通过</span>
                                    <?php endif; ?>
                                    <?php echo $msg['content']; ?>
                                </div>
                                <div class="mdui-card-actions">
                                    <form method="post" class="mdui-float-left">
                                        <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button class="mdui-btn mdui-btn-raised mdui-btn-dense mdui-color-green" type="submit">过审</button>
                                    </form>
                                    <form method="post" class="mdui-float-left">
                                        <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button class="mdui-btn mdui-btn-raised mdui-btn-dense mdui-color-orange" type="submit">打回</button>
                                    </form>
                                    <form method="post" class="mdui-float-left">
                                        <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button class="mdui-btn mdui-btn-raised mdui-btn-dense mdui-color-red" type="submit">删除</button>
                                    </form>
                                </div>
                            </div>
                            <br>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>暂无留言</p>
                    <?php endif; ?>
                </div>
            </div>
            <br>
            <div class="mdui-card">
    <div class="mdui-card-primary">
    <div class="mdui-card-primary-title">致谢</div>
    <div class="mdui-card-primary-subtitle">感谢那些为曦予留言板开发作出贡献的个人/项目</div>
    </div>
    <div class="mdui-card-content">
     前端框架：Mdui V1<br>
     前端编写：曦予<br>
     后端编写：Trae IDE & 曦予<br>
     当前版本：V25.4.2<br>
     『愿一生可爱，一生被爱』
    </div>
        </div><br>
    <script src="https://npm.elemecdn.com/mdui@1.0.2/dist/js/mdui.min.js"></script>
    </body>
</html>