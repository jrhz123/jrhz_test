<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>我要推荐</title>
<style>
*{margin:0px; padding:0px;}
img{border:0;}
a{text-decoration:none;}
a:hover{text-decoration:underline;}
li{list-style:none; list-style-image:none; list-style-type:none;} 
.list{width:650px;}
.list li{border-bottom:1px dashed #DADADA; padding:7px 0;}
.list li h3{font:bold 14px/24px "宋体"; color:#E30000;}
.list li h3 span{color:#333; margin-left:30px;}
.list li p{font:normal 12px/20px "宋体"; color:#444; text-indent:2em;}
</style>
</head>

<body>
<div class="list">
       <?php
include('conn.php');
mysql_query("SET NAMES UTF8");

$q="select * from tjhr_wytj where state = '已审核'";
$result = mysql_query($q);
while($row = mysql_fetch_array($result))
{
 echo"<ul><li><h3>我推荐，我评议<span>【".$row["category"]."】</span></h3><p>".$row["content"]."</p></li></ul><ul>";
 
}
?>
</div>
</body>
</html>
