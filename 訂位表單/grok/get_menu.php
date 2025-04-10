<?php
// 設置回應標頭為 JSON
header('Content-Type: application/json');

// 資料庫連接參數
$servername = "localhost";
$username = "owner01";
$password = "123456";
$dbname = "project";

try {
    // 建立 PDO 連接
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 查詢菜單資料
    $stmt = $conn->prepare("SELECT id, name, price, category FROM menu1 ORDER BY category, id");
    $stmt->execute();
    $menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 返回 JSON 格式資料
    echo json_encode($menu_items);
    
} catch(PDOException $e) {
    // 錯誤處理，返回錯誤訊息
    http_response_code(500);
    echo json_encode([
        'error' => '無法載入菜單: ' . $e->getMessage()
    ]);
}

// 關閉連接
$conn = null;
?>