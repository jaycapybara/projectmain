<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_bookings':
        $stmt = $pdo->query("SELECT b.*, a.name AS seat_name FROM bookings b JOIN aseats a ON b.seat_id = a.id");
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $order_items = [];
        foreach ($bookings as $booking) {
            $stmt = $pdo->prepare("SELECT item_id, item_name, quantity, price FROM order_items WHERE booking_id = ?");
            $stmt->execute([$booking['id']]);
            $order_items[$booking['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                'seat_id' => $booking['seat_id'],
                'seat_name' => $booking['seat_name'],
                'total_amount' => $booking['total_amount'],
                'order_items' => $order_items[$booking['id']] ?? []
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

        try {
            $pdo->beginTransaction();

            // 更新 bookings 表
            $stmt = $pdo->prepare("
                UPDATE bookings 
                SET name = ?, phone = ?, date = ?, time = ?, shop = ?, 
                    people = ?, pets = ?, seat_id = ?, total_amount = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['name'],
                $data['phone'],
                $data['date'],
                $data['time'],
                $data['shop'],
                $data['people'],
                $data['pets'],
                $data['seat_id'],
                $data['total_amount'],
                $id
            ]);

            // 刪除舊的 order_items
            $stmt = $pdo->prepare("DELETE FROM order_items WHERE booking_id = ?");
            $stmt->execute([$id]);

            // 插入新的 order_items（如果有）
            if (!empty($data['order_items'])) {
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (booking_id, item_id, item_name, quantity, price) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                foreach ($data['order_items'] as $item) {
                    $stmt->execute([$id, $item['item_id'], $item['item_name'], $item['quantity'], $item['price']]);
                }
            }

            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'create_booking':
        $data = json_decode(file_get_contents('php://input'), true);

        try {
            $pdo->beginTransaction();

            // 插入 bookings 表
            $stmt = $pdo->prepare("
                INSERT INTO bookings (name, phone, date, time, shop, people, pets, seat_id, total_amount)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['name'],
                $data['phone'],
                $data['date'],
                $data['time'],
                $data['shop'],
                $data['people'],
                $data['pets'],
                $data['seat_id'],
                $data['total_amount']
            ]);

            // 獲取剛插入的 booking ID
            $booking_id = $pdo->lastInsertId();

            // 插入 order_items（如果有）
            if (!empty($data['order_items'])) {
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (booking_id, item_id, item_name, quantity, price) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                foreach ($data['order_items'] as $item) {
                    $stmt->execute([$booking_id, $item['item_id'], $item['item_name'], $item['quantity'], $item['price']]);
                }
            }

            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'delete_booking':
        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM order_items WHERE booking_id = ?");
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

    default:
        echo json_encode(['error' => '無效的動作']);
        break;
}
?>