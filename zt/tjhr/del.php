<?php 
include('conn.php'); 

mysql_query("SET NAMES UTF8");

if ($_GET['job'] == 'list') {

    $job = $_POST['job'];
    if (!empty($_POST["selid"])) {
        //获取要修改的选项值
        $array = $_POST["selid"];
        $size = count($array);

        for ($i = 0; $i < $size; $i++) {

            if ($job == 3) {
                $sql = "delete from tjhr_wytj where id = $array[$i]";               
				echo "<script>alert('删除成功');parent.location.href='check.php'; </script>";
            } else if ($job == 1) {
                $sql = "update tjhr_wytj set state = '已审核' where id = $array[$i]";              
                echo "<script>alert('审核通过');parent.location.href='check.php'; </script>";
            } else {
                $sql = "update tjhr_wytj set state = '未审核' where id = $array[$i]";              
                echo "<script>alert('取消已审核状态');parent.location.href='check.php'; </script>";
            }
            $result = mysql_query($sql);
        }
    }

}
?>