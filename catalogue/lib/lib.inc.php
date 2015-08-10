<?php
function get_Var($nm,$mode="GET",$v="")
{	
	if ($mode == "GET")
	{
		return (isset($_GET[$nm]))?trim(htmlspecialchars($_GET[$nm])):$v;  
	}
	elseif ($mode == "POST")
	{
		return (isset($_POST[$nm]))?trim(htmlspecialchars($_POST[$nm])):$v;  
	}
	elseif ($mode == "COOKIE")
	{
		return (isset($_COOKIE[$nm]))?trim(htmlspecialchars($_COOKIE[$nm])):$v;  
	}
	elseif ($mode == "REQUEST")
	{
		return (isset($_REQUEST[$nm]))?trim(htmlspecialchars($_REQUEST[$nm])):$v;  
	}
}

function show_rez_tbl($order="", $group="", $tbl='tovars_07718')
{
  global $link;

  $order = ($order!="")?(" ORDER BY ".$order." "):"";
  $group = ($group!="")?(" GROUP BY ".$group." "):"";

  $sql = "SELECT `id`, `title`, `price`, `weight` ".
         "FROM `$tbl` $group $order";
  $result = mysqli_query($link, $sql) or die(mysqli_errno($link).": ".mysqli_error($link));

  $arr = array();
  while($row = mysqli_fetch_assoc($result))
  {
    $arr[] = $row;
  }
  mysqli_free_result($result);

  echo "<div class='cont-row'>";
  echo "<table id='rez_tbl'>";
  echo "<tr><th></th><th>Название</th><th>Цена, грн.</th><th>Масса, г</th><th></th></tr>";
    foreach($arr as $item)
    {
      echo "<tr>".
        "<td class='col_del'><input type='checkbox' name='del_".$item['id']."' /></td>".
        "<td class='ttl'>".(($item['title'] == '')?'---':$item['title'])."</td>".
        "<td>".(($item['price'] == '')?'---':$item['price'])."</td>".
        "<td>".(($item['weight'] == '')?'---':$item['weight'])."</td>".
        "<td><a href='index.php?edit={$item['id']}&title={$item['title']}&".
                      "price={$item['price']}&weight={$item['weight']}' ".
                      "class='clr_btn'>Редактировать</a></td>".
       "</tr>";
    }
  echo "</table>";
  echo "</div>";
}

function add_row($n, $p, $w, $tbl='tovars_07718')
{
  global $link;

  $sql = "INSERT INTO $tbl (`title`, `price`, `weight`)".
          "VALUES('$n', '$p', '$w')";
  $result = mysqli_query($link, $sql) or die(mysqli_errno($link).": ".mysqli_error($link));
}

function del_row($id, $tbl='tovars_07718',$backup=true)
{
  //backup - флаг для бэкапа в `tovars_backup`
  global $link;

  if($backup)
  {
    $sql = "SELECT `title`, `price`, `weight` ".
           "FROM `$tbl` ".
           "WHERE `id` = $id";
    $result = mysqli_query($link, $sql) or die(mysqli_errno($link).": ".mysqli_error($link));
    $row = mysqli_fetch_assoc($result);
    mysqli_free_result($result);

    $t = mysqli_real_escape_string($link, $row['title']);
    $p = mysqli_real_escape_string($link, $row['price']);
    $w = mysqli_real_escape_string($link, $row['weight']);
    $sql = "INSERT INTO `tovars_07718_backup` (`title`, `price`, `weight`) ".
           "VALUES ('$t','$p','$w')";
    mysqli_query($link, $sql) or die(mysqli_errno($link).": ".mysqli_error($link));
  }
}

function do_select($sel,$arr)
{
	//формирование выпадающего списка с именем $sel из массива $arr
  if (!is_array($arr))
  {
  	return 1;
  }
  echo "<select name='$sel'> <option></option>";
  	foreach($arr as $k=>$v)
  	{ 
  		echo "<option value='$k'";
  		if($k==get_Var($sel))
  		{
  			echo " selected";
  		}
  		echo ">$v</option>";
  	}
  echo "</select>";
}