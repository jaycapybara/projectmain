<?php
    // {"id" : "XXX", "price" : "41", "remark" : "不加糖01"}

    // {"state" : true, "message" : "更新成功"}
    // {"state" : false, "message" : "更新失敗與相關錯誤訊息"}
    // {"state" : false, "message" : "欄位錯誤"}
    // {"state" : false, "message" : "欄位不得為空白"}

    $data = file_get_contents("php://input", "r");
    $mydata = array();
    $mydata = json_decode($data, true);
    if(isset($mydata["id"]) && isset($mydata["price"]) && isset($mydata["remark"]) && isset($mydata["status"]) && isset($mydata["fileupload"])){
        if($mydata["id"] != "" && $mydata["price"] != "" && $mydata["remark"] != "" && $mydata["status"] != "" && $mydata["fileupload"] != ""){
            $p_id     = $mydata["id"];
            $p_price  = $mydata["price"];
            $p_remark = $mydata["remark"];
            $p_status = $mydata["status"];
            $p_fileupload = $mydata["fileupload"];


            require_once("dbtools.php");
            $link = create_connection();
            $sql = "UPDATE product SET Price = '$p_price', Remark = '$p_remark', Pstatus = '$p_status', image_path='$p_fileupload' WHERE ID = '$p_id'";

            if(execute_sql($link, "project", $sql)){
                if(mysqli_affected_rows($link) == 1){
                    echo '{"state" : false,"message" : "更新成功"}';
                }else{
                    echo'{"state" : false, "message" : "無資料被更新"}';
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