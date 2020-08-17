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
.list li h5{font:bold 14px/24px "宋体"; color:#E30000;}
.list li p{font:normal 12px/20px "宋体"; color:#444; text-indent:2em;}
</style>
</head>

<body>
<script type="text/javascript" src="jquery.js"></script>  
<script type="text/javascript"> 

			$( function(){  
        var check_box = function(){  
            $i = 0;  
            $( ':input:checkbox' ).each( function(){  
                if( $( this ).is( ':checked' ) ){  
                    $i++;  
                }  
            } );  
            if( $i == 0 ){  
                alert( '至少选择一个!' );  
                return false;  
            }else{  
                return true;  
            }  
        }  
        $( 'form' ).bind( 'submit', check_box );  
    } );  
</script>
<div class="list">
<form name="form" action="del.php?job=list" method="post">
       <?php
	   error_reporting(0);
session_start();
include('conn.php');
mysql_query("SET NAMES UTF8");

$q="select * from tjhr_wytj";
$result = mysql_query($q);
while($row = mysql_fetch_array($result))
{
	?>
	<input id="chkbx" type="checkbox" name="selid[]" value="<?php echo $row[id]; ?>">
	<?php
     echo"<ul><li><h3>我推荐，我评议<span>【".$row["category"]."】</span></h3><p>".$row["content"]."</p><p>审核状态：".$row["state"]."</p></li></ul><ul>";
 
}
?>
</div>
                <p>操作:
                    <input type="radio" name="job" value="1" />通过审核
                    <input type="radio" name="job" value="0" />取消通过
                    <input type="radio" name="job" value="3" />删除
                    <input type="submit" name="submit" value="确定">
                </p>
            </div>
</form>
</div>
</body>
</html>
