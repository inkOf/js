<?php
require_once "./connect.php";
require_once "./lib/lib.inc.php";
header("Content-Type: text/html; charset=utf8");

$hid = (int)get_Var("hid","GET",0);
if($hid === 0)
{
  header("Location:index.php");
  exit;
}

$sql = "SELECT `title`, `price`, `weight` ".
       "FROM `tovars_07718_backup`";
$result = mysqli_query($link, $sql) or die(mysqli_errno($link).": ".mysqli_error($link));

$arr = array();
while($row = mysqli_fetch_assoc($result))
{
  $arr[] = $row;
}
mysqli_free_result($result);

$sql = "INSERT INTO `tovars_07718` (`title`, `price`, `weight`) ".
       "VALUES";
          for ($i = 0; $i < count($arr); $i++) { 
          	$t = mysqli_real_escape_string($link, $arr[$i]['title']);
				    $p = mysqli_real_escape_string($link, $arr[$i]['price']);
				    $w = mysqli_real_escape_string($link, $arr[$i]['weight']);
            $sql .= "('$t','$p','$w')";
            if($i < count($arr) - 1) $sql .= ",";
          }

$result = mysqli_query($link, $sql) or die(mysqli_errno($link).": ".mysqli_error($link));

mysqli_query($link, "delete from `tovars_07718_backup` where 1") or die(mysqli_errno($link).": ".mysqli_error($link));

mysqli_close($link);

header("Location:index.php");
?>