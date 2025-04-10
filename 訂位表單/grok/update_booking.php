<?php
// 資料庫連線設定
$host = 'localhost';
$dbname = 'project';
$username = 'owner01'; // 根據你的 MySQL 設定修改
$password = '123456';     // 根據你的 MySQL 設定修改
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("資料庫連線失敗: " . $e->getMessage());
}

// 檢查是否有 ID
if (!isset($_GET['id']) && !isset($_POST['id'])) {
    die("缺少訂位 ID");
}

// 處理表單提交 (更新資料)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $shop = $_POST['shop'];
    $people = $_POST['people'];
    $pets = $_POST['pets'];
    $seat_id = $_POST['seat_id'];
    $total_amount = $_POST['total_amount'];

    try {
        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET name = ?, phone = ?, date = ?, time = ?, shop = ?, 
                people = ?, pets = ?, seat_id = ?, total_amount = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $phone, $date, $time, $shop, $people, $pets, $seat_id, $total_amount, $id]);

        // 重定向回主頁面
        header("Location: index.html"); // 返回原始 HTML 主頁面
        exit;
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>更新失敗: " . $e->getMessage() . "</div>";
    }
}

// 獲取現有訂位資料
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt->execute([$id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die("找不到該訂位記錄");
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>編輯訂位</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>編輯訂位</h2>
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
            <div class="mb-3">
                <label>姓名</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($booking['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label>電話</label>
                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($booking['phone']); ?>" required>
            </div>
            <div class="mb-3">
                <label>日期</label>
                <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($booking['date']); ?>" required>
            </div>
            <div class="mb-3">
                <label>時間</label>
                <input type="time" name="time" class="form-control" value="<?php echo htmlspecialchars($booking['time']); ?>" required>
            </div>
            <div class="mb-3">
                <label>分店</label>
                <input type="text" name="shop" class="form-control" value="<?php echo htmlspecialchars($booking['shop']); ?>" required>
            </div>
            <div class="mb-3">
                <label>人數</label>
                <input type="number" name="people" class="form-control" value="<?php echo htmlspecialchars($booking['people']); ?>" required>
            </div>
            <div class="mb-3">
                <label>寵物數</label>
                <input type="number" name="pets" class="form-control" value="<?php echo htmlspecialchars($booking['pets']); ?>" required>
            </div>
            <div class="mb-3">
                <label>座位ID</label>
                <input type="number" name="seat_id" class="form-control" value="<?php echo htmlspecialchars($booking['seat_id']); ?>" required>
            </div>
            <div class="mb-3">
                <label>總金額</label>
                <input type="number" name="total_amount" class="form-control" value="<?php echo htmlspecialchars($booking['total_amount']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">保存更改</button>
            <a href="index.html" class="btn btn-secondary">取消</a>
        </form>
    </div>
</body>
</html>