<?php
$uploadDir = __DIR__ . '/music/';
$jsonFile = $uploadDir . 'music-list.json';

// 如果列表文件不存在，跳转到一个默认页面或者直接退出
if (!file_exists($jsonFile)) {
    header("HTTP/1.1 404 Not Found");
    exit('没有找到音乐列表');
}

$list = json_decode(file_get_contents($jsonFile), true);

if (!$list || count($list) === 0) {
    header("HTTP/1.1 404 Not Found");
    exit('音乐列表为空');
}

// 随机挑选一个文件
$file = $list[array_rand($list)];

// 生成可访问的音乐 URL（根据你站点根目录调整）
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$musicUrl = "{$protocol}://{$host}/music/{$file}";

// 直接跳转到音乐文件 URL
header("Location: $musicUrl");
exit;
