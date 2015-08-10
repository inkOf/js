<?php
require_once "./connect.php";
require_once "./lib/lib.inc.php";
header("Content-Type: text/html; charset=utf8");

$hid = (int)get_Var("hid","GET",0);
if($hid === 0)
{
  header("Location:index.php");
  exit();
}

mysqli_query($link, "delete from `tovars_07718` where 1") or die(mysqli_errno($link).": ".mysqli_error($link));

mysqli_close($link);

header("Location:index.php");
?>