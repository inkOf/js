<?php 
$host = "localhost";
$user = "root";
$pswd = "root";
$db_name = 'web105db';

$link = mysqli_connect($host, $user, $pswd) or die(mysqli_errno($link).": ".mysqli_error($link));
mysqli_select_db($link, $db_name) or die(mysqli_errno($link).": ".mysqli_error($link));
mysqli_query($link, "SET NAMES utf8");

$sql = "CREATE TABLE IF NOT EXISTS `tovars_07718`".
            "(".
                "id int not null auto_increment,".
                "title varchar(40) default null,".
                "price decimal(10,2) default null,".
                "weight int default null,".
                "PRIMARY KEY (id)".
              ")DEFAULT CHARSET='utf8'";
mysqli_query($link, $sql) or die(mysqli_errno($link).": ".mysqli_error($link));

$sql = "CREATE TABLE IF NOT EXISTS `tovars_07718_backup` LIKE `tovars_07718`";
mysqli_query($link, $sql) or die(mysqli_errno($link).": ".mysqli_error($link));