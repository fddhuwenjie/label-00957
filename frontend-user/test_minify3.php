<?php
// 测试 components.js 中模板字符串是否被破坏
$content = file_get_contents(__DIR__ . '/public/assets/js/components.js');

// 模拟 minifyJs
$content = preg_replace('/\/\*[\s\S]*?\*\//', '', $content);
$content = preg_replace('/^\s*\/\/[^\n]*/m', '', $content);
$content = preg_replace('/([;{}])\s*\/\/[^\n]*/m', '$1', $content);

// 在压缩空白之前检查 onerror 行
$lines = explode("\n", $content);
foreach ($lines as $i => $line) {
    if (strpos($line, 'onerror') !== false) {
        echo "Line $i: " . trim($line) . "\n";
    }
    // 检查 renderMusicListItem 中的 svg path
    if (strpos($line, 'M8 5v14') !== false) {
        echo "SVG Line $i: " . trim($line) . "\n";
    }
}

$content = preg_replace('/\s+/', ' ', $content);
$content = trim($content);

// 检查 onerror 是否完整
if (preg_match('/onerror="([^"]*)"/', $content, $m)) {
    echo "\nonerror attr: " . $m[1] . "\n";
}

// 用 node 检查语法
file_put_contents(__DIR__ . '/tmp_components_check.js', $content);
echo "\nSyntax check:\n";
passthru('node --check ' . escapeshellarg(__DIR__ . '/tmp_components_check.js') . ' 2>&1');

// 也检查其他文件
$files = ['utils', 'api', 'store', 'app'];
foreach ($files as $f) {
    $c = file_get_contents(__DIR__ . '/public/assets/js/' . $f . '.js');
    $c = preg_replace('/\/\*[\s\S]*?\*\//', '', $c);
    $c = preg_replace('/^\s*\/\/[^\n]*/m', '', $c);
    $c = preg_replace('/([;{}])\s*\/\/[^\n]*/m', '$1', $c);
    $c = preg_replace('/\s+/', ' ', $c);
    $c = trim($c);
    file_put_contents(__DIR__ . '/tmp_' . $f . '_check.js', $c);
    echo "$f.js: ";
    passthru('node --check ' . escapeshellarg(__DIR__ . '/tmp_' . $f . '_check.js') . ' 2>&1');
    echo "\n";
}
