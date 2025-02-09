<?php
include("navbar.php");
session_start();
require_once("login_check.php");
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="Description" content="中國文化大學113年畢業專題製作，組別B-07">
    <title>文大線上點餐系統</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/contact.css">
</head>

<body>
    <div class="panel-body">
        <?php
        require_once("database.php");
        if (isset($_POST['submit'])) {
            // 獲取檔案資訊
            $file_name = $_FILES['fileToUpload']['name'];
            $file_tmp = $_FILES['fileToUpload']['tmp_name'];
            $file_size = $_FILES['fileToUpload']['size'];
            $target_dir = "foodimg/";
            $target_file = $target_dir . basename($file_name);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // 檢查圖片檔案是否合法（確保是圖片格式）
        
            $check = getimagesize($file_tmp);
            $errors = array();

            if ($check === false) {
                array_push($errors, "檔案不是有效的圖片。");
            }
            if (file_exists($target_file)) {
                array_push($errors, "檔案已經存在。");
            }
            if ($file_size > 3 * 1024 * 1024 || !in_array($imageFileType, ['png', 'jpg'])) {
                array_push($errors, "檔案大小限制為 3MB，檔案類型必須為 PNG 或 JPG。");
            }
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    echo "<div class='alert alert-danger'>$error</div>";
                }
            } else {
                // 獲取表單欄位值
                $foodname = $_POST['foodname'];
                $foodprice = $_POST['foodprice'];
                $fooddetail = $_POST['fooddetail'];
                $foodprotein = $_POST['foodprotein'];
                $foodfat = $_POST['foodfat'];
                $foodcarbs = $_POST['foodcarbs'];
                $foodcalorie = $foodprotein * 4 + $foodfat * 9 + $foodcarbs * 4;

                session_start();
                $query_RecMember = "SELECT * FROM `member` WHERE `account`='" . $_SESSION["account"] . "'";
                $RecMember = mysqli_query($conn, $query_RecMember);
                $row_Recmember = mysqli_fetch_assoc($RecMember);
                $store_query = "SELECT * FROM `store` WHERE `member_id`='" . $row_Recmember["id"] . "'";
                $storeresult = mysqli_query($conn, $store_query);
                $row_Recstore = mysqli_fetch_assoc($storeresult);
                if (move_uploaded_file($file_tmp, $target_file)) {
                    // 設定食物圖片檔名
                    $foodimage = $file_name;
                    // 使用準備語句防止 SQL 注入
                    $sql = "INSERT INTO `food` (`store_id`, `food_name`, `food_image`, `food_price`, `food_detail`, `food_protein`, `food_fat`, `food_carbs`, `food_calorie`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

                    // 準備語句
                    if ($stmt = mysqli_prepare($conn, $sql)) {
                        // 綁定參數
                        mysqli_stmt_bind_param($stmt, "sssssssss", $row_Recstore["id"], $foodname, $foodimage, $foodprice, $fooddetail, $foodprotein, $foodfat, $foodcarbs, $foodcalorie);

                        // 執行查詢
                        if (mysqli_stmt_execute($stmt)) {
                            // 成功後跳轉到 upload.php
                            header('Location: food_manage.php');
                            exit;
                        } else {
                            echo "<p style='color:red;text-align:center;'>資料庫插入失敗！";
                        }

                        // 關閉準備語句
                        mysqli_stmt_close($stmt);
                    }
                } else {
                    echo "<p style='color:red;text-align:center;'>上傳檔案發生錯誤！";
                }
            }
        }
        ?>
    </div>
    <div>
        <table width="800" border="0" align="center" cellpadding="4" cellspacing="0">
            <tr valign="top">
                <td width="600">
                    <form action="food_upload.php" name="food" method="post" enctype="multipart/form-data">
                        <font size="5">餐點圖片:</font>
                        <input type="file" name="fileToUpload" id="fileToUpload" required><br>
                        <font size="5">餐點名稱:</font>
                        <input type="text" name="foodname" id="foodname" required><br>
                        <font size="5">餐點價格:</font>
                        <input type="text" name="foodprice" id="foodprice" required><br>
                        <font size="5">餐點介紹:</font>
                        <textarea name="fooddetail"></textarea><br>
                        <font size="5">餐點蛋白質(公克/g):</font>
                        <input type="text" name="foodprotein" id="foodprotein" required><br>
                        <font size="5">餐點油脂(公克/g):</font>
                        <input type="text" name="foodfat" id="foodfat" required><br>
                        <font size="5">餐點碳水化合物(公克/g):</font>
                        <input type="text" name="foodcarbs" id="foodcarbs" required><br>
                        <br>
                        <input type="submit" value="上傳" name="submit">

                        <input style="font-size: 20px;" type="button" value="回上一頁" name="button"
                            onclick="window.history.back();">

                    </form>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
