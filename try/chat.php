<?php
$host = "localhost";
$dbname = "testdb";
$username = "owner01"; // 根據你的資料庫設定修改
$password = "123456";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST["username"];
        $message = $_POST["message"];

        $stmt = $pdo->prepare("INSERT INTO messages (username, message) VALUES (?, ?)");
        $stmt->execute([$username, $message]);
    } else {
        $stmt = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 20");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
