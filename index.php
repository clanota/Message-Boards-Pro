<?php
error_reporting(0);

// 加载配置
if (!file_exists('config.json')) {
    header('Location: install.php');
    exit;
}

$config = json_decode(file_get_contents('config.json'), true);
$messages = json_decode(file_get_contents('data/messages.json'), true);

// 处理表单提交
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nickname']) && isset($_POST['content'])) {
    $nickname = htmlspecialchars($_POST['nickname']);
    $content = htmlspecialchars($_POST['content']);
    
    if (!empty($nickname) && !empty($content)) {
        $newMessage = array(
            'id' => uniqid(),
            'nickname' => $nickname,
            'content' => $content,
            'created_at' => date('Y-m-d H:i:s'),
            'approved' => $config['manualReview'] ? 0 : 1
        );
        
        array_unshift($messages, $newMessage);
        file_put_contents('data/messages.json', json_encode($messages, JSON_PRETTY_PRINT));
        
    }
}

// 获取已批准的留言
$approvedMessages = array_filter($messages, function($msg) {
    return $msg['approved'] ?? false;
});
?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
        <meta name="renderer" content="webkit">
        <link rel="stylesheet" href="https://npm.elemecdn.com/mdui@1.0.2/dist/css/mdui.min.css">
        <title><?php echo $config['siteName']; ?></title>
    </head>
    <body>
        <div class="mdui-container"><br>
            <div class="mdui-card">
                <div class="mdui-card-primary">
                    <div class="mdui-card-primary-title">
                        <?php echo $config['siteName']; ?>
                    </div>
                    <div class="mdui-card-primary-subtitle">
                        <?php echo $config['siteDesc']; ?>
                        <?php if ($config['manualReview']): ?><span id="reviewStatus" style="color: green;"> - 人工审核已开启</span><?php endif; ?>
                    </div>
                </div>
                <div class="mdui-card-content">
                    <form method="post">
                            昵称：<br>
                            <input class="mdui-textfield-input" type="text" name="nickname" placeholder="请输入昵称" required><br>
                            留言内容：<br>
                            <textarea class="mdui-textfield-input" name="content" placeholder="请输入留言内容" required></textarea><br>
                        <div class="mdui-row">
                            <div class="mdui-col-xs-6">
                                <input class="mdui-btn mdui-btn-block mdui-btn-raised" type="submit" value="提交留言">
                            </div>
                            <div class="mdui-col-xs-6">
                                <a href="admin.php" class="mdui-btn mdui-btn-block mdui-btn-raised">进入后台</a>
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
                        共<?php echo count($approvedMessages); ?>条留言
                    </div>
                </div>
                <div class="mdui-card-content">
                    <?php if (!empty($messages)): ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="mdui-card mdui-m-b-2">
                                <div class="mdui-card-primary">
                                    <div class="mdui-card-primary-title"><?php echo ($msg['approved'] ?? false) ? $msg['nickname'] : '待审核后展示'; ?></div>
                                    <div class="mdui-card-primary-subtitle"><?php echo $msg['created_at']; ?></div>
                                </div>
                                <div class="mdui-card-content"><?php echo ($msg['approved'] ?? false) ? $msg['content'] : '待审核后展示'; ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="mdui-card">
                            <div class="mdui-card-content">暂无留言</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <script src="https://npm.elemecdn.com/mdui@1.0.2/dist/js/mdui.min.js"></script>
    </body>
</html>