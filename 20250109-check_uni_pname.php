<?php
    // {"pname" : "XXX"}



//   "{""state"" : true, ""message"" : ""該產品名稱不存在可以使用""}
// {""state"" : false, ""message"" : ""該產品名稱已存在不可以使用""}
// {""state"" : false, ""message"" : ""欄位錯誤""}
// {""state"" : false, ""message"" : ""欄位不得為空白""}"


    $data = file_get_contents("php://input", "r");
    $mydata = array();
    $mydata = json_decode($data, true);
    if(isset($mydata["pname"])){
        if($mydata["pname"] != ""){
            $p_pname = $mydata["pname"];

            require_once("dbtools.php");
            $link = create_connection();
            $sql = "SELECT pname FROM product WHERE Pname ='$p_pname'";
            $result = execute_sql($link,"project",$sql);

            if(mysqli_num_rows($result) == 1){
               
                    echo '{"state" : false, "message" : "該產品名稱已存在，不可以使用"}';
                }else{
                    echo '{"state" : true, "message" : "該產品名稱不存在，可以使用"}';
                }
          
            mysqli_close($link);
        }else{
            echo '{"state" : false, "message" : "欄位不得為空白"}';
        }
    }else{
        echo '{"state" : false, "message" : "欄位錯誤"}';
    }
?>