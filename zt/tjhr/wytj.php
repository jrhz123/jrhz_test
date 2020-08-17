<?php
include('conn.php');
session_start();

$tel = $_POST['tel'];
$category = $_POST['category'];
$content = $_POST['content'];
mysql_query("SET NAMES UTF8");
$a='未审核';
$result = mysql_query("insert into tjhr_wytj set  tel = '$tel', category = '$category', content= '$content', state = '$a' ");
if($result){
echo "<script>alert('提交成功');location.href='wytj.html';</script>";
}else{
echo "<script>alert('提交失败!');location.href='wytj.html';</script>";
}

?>