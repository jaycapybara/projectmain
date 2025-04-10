<?php
// 資料庫連接
$servername = "localhost";
$username = "owner01";
$password = "123456";
$dbname = "project";
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("資料庫連接失敗: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>訂位管理後台</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f2f5;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 25px;
        }
        .header {
            margin-bottom: 25px;
            text-align: center;
        }
        .header h1 {
            color: #2c3e50;
            font-size: 28px;
        }
        .table-wrapper {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
        }
        th {
            background: #3498db;
            color: white;
            font-weight: 600;
        }
        tr {
            transition: background 0.2s;
        }
        tr:nth-child(even) {
            background: #f8f9fa;
        }
        tr:hover {
            background: #e9ecef;
        }
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: opacity 0.2s;
        }
        .delete-btn {
            background: #e74c3c;
            color: white;
        }
        .action-btn:hover {
            opacity: 0.9;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>訂位管理後台</h1>
        </div>

        <div class="table-wrapper">
            <?php
            // 處理刪除
            if (isset($_GET['delete'])) {
                $id = filter_var($_GET['delete'], FILTER_SANITIZE_NUMBER_INT);
                $stmt = $conn->prepare("DELETE FROM reservationss WHERE id = :id");
                $stmt->bindParam(':id', $id);
                if ($stmt->execute()) {
                    echo "<script>alert('訂位已成功刪除'); window.location='admin.php';</script>";
                }
            }

            // 查詢訂位
            $stmt = $conn->prepare("SELECT * FROM reservationss ORDER BY reservation_date DESC, reservation_time DESC");
            $stmt->execute();
            $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <table>
                <thead>
                    <tr>
                        <th>訂單ID</th>
                        <th>姓名</th>
                        <th>電話</th>
                        <th>訂位日期</th>
                        <th>訂位時間</th>
                        <th>人數</th>
                        <th>建立時間</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($reservations) > 0): ?>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reservation['id']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['name']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['phone']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['reservation_date']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['reservation_time']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['people']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['created_at']); ?></td>
                                <td>
                                    <button class="action-btn delete-btn" 
                                        onclick="if(confirm('確定要刪除訂單 #<?php echo $reservation['id']; ?> 嗎？')) {
                                            window.location='admin.php?delete=<?php echo $reservation['id']; ?>'
                                        }">
                                        刪除
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="no-data">目前沒有訂位記錄</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php $conn = null; ?>
</body>
</html>