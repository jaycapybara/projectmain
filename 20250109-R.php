<?php
//output:
//{"state" : true, "message" : "讀取成功", "data" : {相關資料訊息json}}
//{"state" : false, "message" : "讀取失敗與相關錯誤訊息"}
     $servsername = "localhost";
     $username = "owner01";
     $password = "123456";
     $dbname = "project";

     //建立連線
     $conn = mysqli_connect($servsername, $username, $password, $dbname);
     //確認連線
     if(!$conn){
         die("連線錯誤" . mysqli_connect_error());
     }

     //遞減: DESC 遞增: ASC
     $sql = "SELECT ID, Pname, Price, Remark, Pstatus, image_path, Created_at FROM product ORDER BY ID DESC";
     $result = mysqli_query($conn, $sql);

     if(mysqli_num_rows($result) > 0){
        $mydata = array();
        while($row = mysqli_fetch_assoc($result)){
            $mydata[] = $row;
        }



        // echo json_encode($mydata);
        echo '{"state" : true, "message" : "讀取成功", "data" : '. json_encode($mydata) .'}';
     }else{
        echo '{"state" : false, "message" : "讀取失敗"}';
     }

     



    //  $row = mysqli_fetch_assoc($result);
    //  echo $row["ID"]."<br>";
    //  echo $row["Pname"]."<br>";
    //  echo $row["Price"]."<br>";
    //  echo $row["Remark"]."<br>";
    //  echo $row["Created_at"]."<br>";

     mysqli_close($conn);
?>