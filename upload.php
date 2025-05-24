<?php
// 设置允许的文件类型和目录
$allowedTypes = ['audio/mpeg', 'audio/mp3'];
$uploadDir = __DIR__ . '/music/';
$jsonFile = $uploadDir . 'music-list.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['musicFiles'])) {
        die('未检测到上传文件');
    }

    $files = $_FILES['musicFiles'];
    $uploadedFiles = [];
    $errors = [];

    // 确保上传目录存在
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    for ($i = 0; $i < count($files['name']); $i++) {
        $name = basename($files['name'][$i]);
        $type = $files['type'][$i];
        $tmpName = $files['tmp_name'][$i];
        $error = $files['error'][$i];
        $size = $files['size'][$i];

        if ($error !== UPLOAD_ERR_OK) {
            $errors[] = "$name 上传失败，错误码: $error";
            continue;
        }

        if (!in_array($type, $allowedTypes)) {
            $errors[] = "$name 不是有效的 MP3 文件类型";
            continue;
        }

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($ext !== 'mp3') {
            $errors[] = "$name 文件扩展名不是 mp3";
            continue;
        }

        // 生成唯一文件名，避免重名覆盖
        do {
            $uniquePrefix = uniqid();
            $safeName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $name);
            $targetName = $uniquePrefix . '_' . $safeName;
            $targetPath = $uploadDir . $targetName;
        } while (file_exists($targetPath));

        if (move_uploaded_file($tmpName, $targetPath)) {
            $uploadedFiles[] = $targetName;
        } else {
            $errors[] = "$name 移动文件失败";
        }
    }

    // 更新 music-list.json
    if (file_exists($jsonFile)) {
        $json = json_decode(file_get_contents($jsonFile), true);
        if (!is_array($json)) $json = [];
    } else {
        $json = [];
    }

    $json = array_merge($json, $uploadedFiles);
    $json = array_unique($json); // 去重
    file_put_contents($jsonFile, json_encode(array_values($json), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    // 返回上传结果页面
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head><meta charset="UTF-8"><title>上传结果</title></head>
    <body>
    <h2>上传完成</h2>
    <?php if ($uploadedFiles): ?>
      <p>成功上传的文件：</p>
      <ul>
        <?php foreach ($uploadedFiles as $f): ?>
          <li><?php echo htmlspecialchars($f); ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <?php if ($errors): ?>
      <p style="color:red;">部分错误：</p>
      <ul>
        <?php foreach ($errors as $e): ?>
          <li><?php echo htmlspecialchars($e); ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <p><a href="index.html">返回上传页面</a></p>
    </body>
    </html>
    <?php
    exit;
}
?>
