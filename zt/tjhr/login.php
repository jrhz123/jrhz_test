<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>管理员登陆</title>
<style>
*{margin:0px; padding:0px;}
img{border:0;}
a{text-decoration:none;}
a:hover{text-decoration:underline;}
.welp{width:100%; clear:both; overflow:hidden;}
li{list-style:none; list-style-image:none; list-style-type:none;} 
.wytj{width:253px; background-color:#FFEA89; border:1px solid #DED9C0; margin:0 auto;}
.wytj label{font:bold 14px/24px "宋体"; color:#2C2C2C; float:left; display:inline; margin-left:8px;}
.tj{width:68px; height:22px; background:url(images/dl.jpg) no-repeat; cursor:pointer; border:0;}
.tel{width:115px; height:16px;float:left; display:inline;}
.pad8{padding-top:8px;}
</style>
</head>

<body>
<script>
function checklogin()
{
 
        if((login.name.value!="")&&(login.password.value!=""))
 {
  return true;
 }
 else
 {
  alert ("用户名或密码不能为空!")
  return false;
 }
}

</script>

<div class="wytj">
    <form name="login" method="post" action="login2.php" onSubmit="return checklogin()" >
  <div class="welp pad8"><label>用户名：</label><input type="text" name="name" class="tel" /></div>
  <div class="welp pad8"><label>密&nbsp;&nbsp;码：</label><input type="password" name="password" class="tel" /></div>
  <div style="text-align:center; padding:5px 0 8px;"><input type="submit" name="button" class="tj" value=""></input></div>
    </form>
</div>

</body>
</html>

