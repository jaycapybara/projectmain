<?php
header('Content-Type: application/json');

// 資料庫連線設定
$host = 'localhost';
$dbname = 'project';
$username = 'owner01'; // 根據你的 MySQL 設定修改
$password = '123456';     // 根據你的 MySQL 設定修改

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => '資料庫連接失敗: ' . $e->getMessage()]);
    exit;
}

// 處理不同的動作
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'check':
        checkAvailability();
        break;
    case 'book':
        bookReservation();
        break;
    default:
        echo json_encode(['success' => false, 'error' => '無效的動作']);
        break;
}

// 檢查座位可用性
function checkAvailability() {
    global $pdo;
    $data = json_decode(file_get_contents('php://input'), true);

    $date = $data['date'];
    $time = $data['time'];
    $people = $data['people'];
    $pets = $data['pets'];

    if (empty($date) || empty($time) || empty($people) || $pets === '') {
        echo json_encode(['success' => false, 'error' => '請填寫所有欄位']);
        return;
    }

    // 從 seats 表中獲取所有座位
    $stmt = $pdo->prepare("SELECT id, name, capacity FROM aseats");
    $stmt->execute();
    $seats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 檢查已預約的座位
    $stmt = $pdo->prepare("SELECT seat_id FROM bookings WHERE date = ? AND time = ?");
    $stmt->execute([$date, $time]);
    $booked_seats = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $response = [];
    foreach ($seats as $seat) {
        $available = !in_array($seat['id'], $booked_seats) && $seat['capacity'] >= ($people + $pets);
        $response[] = [
            'id' => $seat['id'],
            'name' => $seat['name'],
            'available' => $available
        ];
    }

    echo json_encode(['success' => true, 'seats' => $response]);
}

// 處理訂位與訂餐
function bookReservation() {
    global $pdo;
    $data = json_decode(file_get_contents('php://input'), true);

    $name = $data['name'];
    $phone = $data['phone'];
    $date = $data['date'];
    $time = $data['time'];
    $shop = $data['shop'];
    $people = $data['people'];
    $pets = $data['pets'];
    $seat_id = $data['seat_id'];
    $items = $data['items'];
    $comboQuantity = $data['comboQuantity'];
    $comboDrinks = $data['comboDrinks'];
    $total = $data['total'];

    if (empty($name) || empty($phone) || empty($date) || empty($time) || empty($shop) || empty($seat_id)) {
        echo json_encode(['success' => false, 'error' => '請填寫所有訂位欄位並選擇座位']);
        return;
    }

    // 檢查座位是否存在並確認容量
    $stmt = $pdo->prepare("SELECT name, capacity FROM aseats WHERE id = ?");
    $stmt->execute([$seat_id]);
    $seat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$seat) {
        echo json_encode(['success' => false, 'error' => '無效的座位ID']);
        return;
    }

    if ($seat['capacity'] < ($people + $pets)) {
        echo json_encode(['success' => false, 'error' => '座位容量不足']);
        return;
    }

    // 檢查座位是否已被預約
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE date = ? AND time = ? AND seat_id = ?");
    $stmt->execute([$date, $time, $seat_id]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'error' => '該座位已被預約']);
        return;
    }

    // 開始事務
    $pdo->beginTransaction();

    try {
        // 插入訂位資料
        $stmt = $pdo->prepare("
            INSERT INTO bookings (name, phone, date, time, shop, people, pets, seat_id, total_amount, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$name, $phone, $date, $time, $shop, $people, $pets, $seat_id, $total]);
        $booking_id = $pdo->lastInsertId();

        // 插入菜單項目
        if (!empty($items)) {
            $stmt = $pdo->prepare("
                INSERT INTO order_items (booking_id, item_id, item_name, quantity, price)
                VALUES (?, ?, ?, ?, ?)
            ");
            foreach ($items as $item) {
                $stmt->execute([$booking_id, $item['id'], $item['name'], $item['quantity'], $item['price']]);
            }
        }

        // 插入套餐飲料
        if ($comboQuantity > 0 && !empty($comboDrinks)) {
            $stmt = $pdo->prepare("
                INSERT INTO combo_drinks (booking_id, drink_id, drink_name, price)
                VALUES (?, ?, ?, ?)
            ");
            foreach ($comboDrinks as $drink) {
                $stmt->execute([$booking_id, $drink['id'], $drink['name'], $drink['price']]);
            }
        }

        // 提交事務
        $pdo->commit();

        // 回傳成功訊息與座位名稱
        echo json_encode(['success' => true, 'seat_name' => $seat['name']]);
    } catch (Exception $e) {
        // 回滾事務
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => '訂位失敗: ' . $e->getMessage()]);
    }
}
?>