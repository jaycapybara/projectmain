<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_bookings':
        $stmt = $pdo->query("SELECT b.*, a.name AS seat_name FROM bookings b JOIN aseats a ON b.seat_id = a.id");
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $order_items = [];
        $combo_drinks = [];
        foreach ($bookings as $booking) {
            // 獲取普通餐點
            $stmt = $pdo->prepare("SELECT item_id, item_name, quantity, price FROM order_items WHERE booking_id = ?");
            $stmt->execute([$booking['id']]);
            $order_items[$booking['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 獲取套餐飲料
            $stmt = $pdo->prepare("SELECT drink_id, drink_name, price FROM combo_drinks WHERE booking_id = ?");
            $stmt->execute([$booking['id']]);
            $combo_drinks[$booking['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $result = [];
        foreach ($bookings as $booking) {
            $result[] = [
                'id' => $booking['id'],
                'name' => $booking['name'],
                'phone' => $booking['phone'],
                'date' => $booking['date'],
                'time' => $booking['time'],
                'shop' => $booking['shop'],
                'people' => $booking['people'],
                'pets' => $booking['pets'],
                'seat_name' => $booking['seat_name'],
                'total_amount' => $booking['total_amount'],
                'order_items' => $order_items[$booking['id']] ?? [],
                'combo_drinks' => $combo_drinks[$booking['id']] ?? []
            ];
        }
        echo json_encode($result);
        break;

    case 'update_booking':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'error' => '缺少 ID']);
            break;
        }

        $stmt = $pdo->prepare("UPDATE bookings SET name = ?, phone = ?, date = ?, time = ?, shop = ?, people = ?, pets = ?, seat_id = ?, total_amount = ? WHERE id = ?");
        $stmt->execute([$data['name'], $data['phone'], $data['date'], $data['time'], $data['shop'], $data['people'], $data['pets'], $data['seat_id'], $data['total_amount'], $id]);
        
        // 更新 order_items
        $stmt = $pdo->prepare("DELETE FROM order_items WHERE booking_id = ?");
        $stmt->execute([$id]);
        if (!empty($data['order_items'])) {
            $stmt = $pdo->prepare("INSERT INTO order_items (booking_id, item_id, item_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
            foreach ($data['order_items'] as $item) {
                $stmt->execute([$id, $item['item_id'], $item['item_name'], $item['quantity'], $item['price']]);
            }
        }

        // 更新 combo_drinks
        $stmt = $pdo->prepare("DELETE FROM combo_drinks WHERE booking_id = ?");
        $stmt->execute([$id]);
        if (!empty($data['combo_drinks'])) {
            $stmt = $pdo->prepare("INSERT INTO combo_drinks (booking_id, drink_id, drink_name, price) VALUES ( ?, ?, ?, ?)");
            foreach ($data['combo_drinks'] as $drink) {
                $stmt->execute([$id, $drink['drink_id'], $drink['drink_name'], $drink['price']]);
            }
        }

        echo json_encode(['success' => true]);
        break;

    case 'delete_booking':
        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM order_items WHERE booking_id = ?");
            $stmt->execute([$id]);
            $stmt = $pdo->prepare("DELETE FROM combo_drinks WHERE booking_id = ?");
            $stmt->execute([$id]);
            $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => '缺少 ID']);
        }
        break;

    case 'get_seats':
        $stmt = $pdo->query("SELECT id, name FROM aseats");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'book':  // 新增訂位功能
        $data = json_decode(file_get_contents('php://input'), true);
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO bookings (name, phone, date, time, shop, people, pets, seat_id, total_amount, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$data['name'], $data['phone'], $data['date'], $data['time'], $data['shop'], $data['people'], $data['pets'], $data['seat_id'], $data['total']]);
            $booking_id = $pdo->lastInsertId();

            // 儲存普通餐點
            if (!empty($data['items'])) {
                $stmt = $pdo->prepare("INSERT INTO order_items (booking_id, item_id, item_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
                foreach ($data['items'] as $item) {
                    $stmt->execute([$booking_id, $item['item_id'], $item['name'], $item['quantity'], $item['price']]);
                }
            }

            // 儲存套餐飲料
            if (!empty($data['comboDrinks'])) {
                $stmt = $pdo->prepare("INSERT INTO combo_drinks (booking_id, drink_id, drink_name, price) VALUES (?, ?, ?, ?)");
                foreach ($data['comboDrinks'] as $drink) {
                    $stmt->execute([$booking_id, $drink['item_id'], $drink['name'], 1, $drink['price']]);
                }
            }

            $pdo->commit();
            $stmt = $pdo->prepare("SELECT name FROM aseats WHERE id = ?");
            $stmt->execute([$data['seat_id']]);
            $seat_name = $stmt->fetchColumn();
            echo json_encode(['success' => true, 'seat_name' => $seat_name]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['error' => '無效的動作']);
        break;
}
?>