<?php
    // {"id" : "XXX"}


    // {"state" : true, "message" : "刪除成功"}
    // {"state" : false, "message" : "無資料被刪除"}
    // {"state" : false, "message" : "更新失敗與相關錯誤訊息"}
    // {"state" : false, "message" : "欄位錯誤"}
    // {"state" : false, "message" : "欄位不得為空白"}

    $data = file_get_contents("php://input", "r");
    $mydata = array();
    $mydata = json_decode($data, true);
    if(isset($mydata["id"])){
        if($mydata["id"] != ""){
            $p_id     = $mydata["id"];

            require_once("dbtools.php");
            $link = create_connection();
            $sql = "DELETE FROM product WHERE ID ='$p_id'";

            if(execute_sql($link, "project", $sql)){
                if(mysqli_affected_rows($link) == 1){
                    echo '{"state" : true, "message" : "刪除成功"}';
                }else{
                    echo '{"state" : false, "message" : "無資料被刪除"}';
                }
            }else{
                echo '{"state" : false, "message" : "更新失敗與相關錯誤訊息"}';
            }
            mysqli_close($link);
        }else{
            echo '{"state" : false, "message" : "欄位不得為空白"}';
        }
    }else{
        echo '{"state" : false, "message" : "欄位錯誤"}';
    }
?>