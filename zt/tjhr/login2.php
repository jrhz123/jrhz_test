<?php

include('conn.php');
$name = $_POST['name'];
$password = $_POST['password'];
$user = mysql_query("select * from tjhr_user where name = '$name'");
$row = array();
if (!$row = mysql_fetch_array($user)) {
    $msg = "用户名不存在";
    echo "<script>alert('" . $msg . "');parent.location.href='login.php'; </script>";
    
} else {
    if ($row['password'] == $password) {
        $_SESSION['name'] = $name;
        echo "<script>alert('登陆成功');parent.location.href='check.php'; </script>";
    } else if ($row['password'] !== $password) {
        $a = "密码错误";
        echo "<script>alert('" . $a . "');parent.location.href='login.php'; </script>";
    }

}
?>
