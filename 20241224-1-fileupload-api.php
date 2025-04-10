<?php
    // echo $_FILES['file']['name'].'<br>';
    // echo $_FILES['file']['type'].'<br>';
    // echo $_FILES['file']['tmp_name'].'<br>';
    // echo $_FILES['file']['size'].'<br>';
    // echo $_FILES['file']['error'].'<br>';

    if(isset($_FILES['file']['name']) && $_FILES['file']['name'] != ""){
        if($_FILES['file']['type'] == 'image/jpeg' || $_FILES['file']['type'] == 'image/png'){
            //檔案(圖片)上傳至伺服器(後端主機)
            $filename =$_FILES['file']['name'];
            //  date("YmdHis")."_".

            $location = 'upload/' . $filename;
            if(move_uploaded_file($_FILES['file']['tmp_name'], $location)){
                $datainfo = array();
                $datainfo["state"]        = true;
                $datainfo["message"]      = '檔案上傳成功';
                $datainfo["name"]         = $_FILES['file']['name'];
                $datainfo["location"]     = $location;
                $datainfo["type"]         = $_FILES['file']['type'];
                $datainfo["tmp_name"]     = $_FILES['file']['tmp_name'];
                $datainfo["size"]         = $_FILES['file']['size'];
                $datainfo["error"]        = $_FILES['file']['error'];
                echo json_encode($datainfo);
            }else{
                $errorinfo = array();
                $errorinfo["state"] = false;
                $errorinfo["message"] = '檔案上傳失敗';
                $errorinfo["error"]    = $_FILES['file']['error'];
                echo json_encode($errorinfo);
            }
        }else{
            echo '{"state" : false, "message" : "檔案必須為jpeg or png!"}';
        }
    }else{
        echo '{"state" : false, "message" : "檔案不存在!"}';
    }
?>