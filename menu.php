<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$servername = "localhost"; // 你的資料庫主機
$username = "owner01"; // 你的資料庫使用者名稱
$password = "123456"; // 你的資料庫密碼
$dbname = "project"; // 你的資料庫名稱

// 建立資料庫連線
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連線
if ($conn->connect_error) {
    die(json_encode(["error" => "資料庫連線失敗：" . $conn->connect_error]));
}

// 查詢菜單資料
$sql = "SELECT ID, Pname, Price, Remark, Pstatus, image_path ,menuLayer FROM product";
$result = $conn->query($sql);

$menu = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $menu[] = $row;
    }
}

// 關閉連線
$conn->close();

// 輸出 JSON
echo json_encode($menu, JSON_UNESCAPED_UNICODE);
?>