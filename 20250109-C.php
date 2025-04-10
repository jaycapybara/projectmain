<?php

// input:{"pname":"xxx","price":"xxx","remark":"xxx"}
$data = file_get_contents("php://input", "r");
// echo $data;
$mydata = array();
$mydata = json_decode($data, true);
// echo $mydata["pname"];
// echo $mydata["price"];
// echo $mydata["remark"];

if (isset($mydata["pname"]) && isset($mydata["price"]) && isset($mydata["remark"]) && isset($mydata["status"])  && isset($mydata["fileupload"])) {
    if ($mydata["pname"] != "" && $mydata["price"] != "" && $mydata["remark"] != "" && $mydata["status"] != ""  && $mydata["fileupload"] != "") {
        // p_是post
        $p_pname = $mydata["pname"];
        $p_price = $mydata["price"];
        $p_remark = $mydata["remark"];
        $p_status = $mydata["status"];
        $p_fileupload = $mydata["fileupload"];
       
          
        // && isset($mydata["fileupload"])  && $mydata["fileupload"] != ""   ,'$p_fileupload'



        $servsername = "localhost";
        $username = "owner01";
        $password = "123456";
        $dbname = "project";

        // 建立連線
        $conn = mysqli_connect($servsername, $username, $password, $dbname);
        // 確認連線
        if (!$conn) {
            die("連線錯誤" . mysqli_connect_error());
        }

        $sql = "INSERT INTO product(Pname ,Price ,Remark ,Pstatus ,image_path) VALUE('$p_pname','$p_price','$p_remark','$p_status','$p_fileupload')";
        if (mysqli_query($conn, $sql)) {
            echo '{"state": true,"message":"新增成功"}';
        } else {
            echo '{"state": false,"message":"新增失敗' . $sql . '<br>錯誤訊息:' . mysqli_error($conn) . '"}';
        }
    } else {
        echo '{"state":false,"message":"欄位不得為空白"}';
    }
} else {
    echo '{"state":false,"message":"欄位錯誤"}';
}

// if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
//     $image = $_FILES['image']['tmp_name'];

//     // 確保檔案上傳成功
//     if (is_uploaded_file($image)) {
//         $imageData = addslashes(file_get_contents($image));

//         // 插入圖片資料到資料庫
//         $sql = "INSERT INTO images (image_data) VALUES ('$imageData')";

//         if ($conn->query($sql) === TRUE) {
//             echo "圖片上傳成功!";
//         } else {
//             echo "錯誤: " . $sql . "<br>" . $conn->error;
//         }
//     } else {
//         echo "請選擇一個有效的檔案!";
//     }
// }


mysqli_close($conn);
