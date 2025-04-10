<?php
// 資料庫連接
$servername = "localhost";
$username = "owner01";
$password = "123456";
$dbname = "project";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $people = $_POST['people'];
    
    $sql = "INSERT INTO reservationss (name, phone, reservation_date, reservation_time, people) 
            VALUES (:name, :phone, :date, :time, :people)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':time', $time);
    $stmt->bindParam(':people', $people);
    
    $stmt->execute();
    
    echo "<script>
            alert('訂位成功！');
            if(confirm('是否前往管理頁面查看所有訂位?')) {
                window.location='admin.php';
            } else {
                window.location='reservation.html';
            }
          </script>";
    
} catch(PDOException $e) {
    echo "錯誤: " . $e->getMessage();
}

$conn = null;
?>