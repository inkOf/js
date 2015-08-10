<?php
require_once "./connect.php";
require_once "./lib/lib.inc.php";
ob_start();

$title      = mysqli_real_escape_string($link, get_Var("title","POST"));
$title_edt  = mysqli_real_escape_string($link, get_Var("title","GET"));
$price      = mysqli_real_escape_string($link, get_Var("price","POST"));
$price_edt  = mysqli_real_escape_string($link, get_Var("price","GET"));
$weight     = mysqli_real_escape_string($link, get_Var("weight","POST"));
$weight_edt = mysqli_real_escape_string($link, get_Var("weight","GET"));
$hid_add    = (int)get_Var("hid_add","POST",0);
$hid_del    = (int)get_Var("hid_del","POST",0);
$hid_edt    = (int)get_Var("hid_edt","POST",0);
$order_by   = (int)get_Var("order_by","POST",0);
$Sort       = array(
                    '1' => "По-умолчанию", 
                    '2' => "По названию(а,б,в...)",
                    '3' => "По названию(я,ю,э...)",
                    '4' => "По цене(возрастание)",
                    '5' => "По цене(убывание)",
                    '6' => "По массе(возрастание)",
                    '7' => "По массе(убывание)"
                    );

$Del = array();

if($hid_del === 1 && !isset($edit))
{
  // ищем ключи удаляемых строк
  // проверяем флаг "edit"
  foreach ($_POST as $key => $value)
  {
    if(preg_match('/del_/', $key))
    {
      $Del[] = strip_tags((int)substr($key, 4));
    }
  }
}

if(count($_GET) > 0)
{
  foreach ($_GET as $key => $value)
  {
    if(preg_match('/edit/', $key))
    {
      $edit = strip_tags((int)$value);
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Каталог товаров</title>
  <link rel="stylesheet" href="css/main.css">
  <script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
</head>
<body>

<div id="container">

<?php
echo "<form action='index.php?' method='POST' id='add_frm'>".
    "<h2>Форма добавления / редактирования товара</h2>".
    "<label>Название товара</label>&nbsp;<input type='text' name='title' value='".
      ((isset($edit))?$title_edt:'')."' />".
    "<label>Цена, грн.</label>&nbsp;<input type='text' name='price' value='".
      ((isset($edit))?$price_edt:'')."' />".
    "<label>Масса, г</label>&nbsp;<input type='text' name='weight' value='".
      ((isset($edit))?$weight_edt:'')."' />".
    "<br />".
    "<input type='hidden' name='hid_add' value='".((isset($edit))?'':1)."' />".
    "<input type='hidden' name='hid_edt' value='".((isset($edit))?$edit:'')."' />".
    ((isset($edit))?"<input type='submit' value='Сохранить' />".
                    "<a href='index.php' class='clr_btn cancel'>Отменить</a>":
                    "<input type='submit' value='Добавить' />").
  "</form>";

if($hid_add === 1 && ($title === "" || $price === "" || $weight === ""))
{
  echo "<p id='err'>Заполните все поля</p>";
}
elseif($hid_add === 1 && $hid_edt === 0 && ($title != "" || $price != "" || $weight != ""))
{
  add_row($title, $price, $weight);
  header("Location:".$_SERVER["PHP_SELF"]);
}
elseif($hid_edt !== 0 && ($title != "" || $price != "" || $weight != ""))
{
  $sql = "UPDATE `tovars_07718` ".
            "SET `title`='$title', `price`='$price', `weight`='$weight' ".
            "WHERE `id` = '$hid_edt'";
  mysqli_query($link, $sql) or die(mysqli_errno($link).": ".mysqli_error($link));
  header("Location:".$_SERVER["PHP_SELF"]);
}

if(count($Del)>0)
{
  for ($i=0; $i < count($Del); $i++) { 
    del_row($Del[$i]);
  }
  $sql = "DELETE FROM `tovars_07718` ".
           "WHERE `id` IN (";
  $sql .= implode(',', $Del);
  $sql .= ")";
  mysqli_query($link, $sql) or die(mysqli_errno($link).": ".mysqli_error($link));
  header("Location:".$_SERVER["PHP_SELF"]);
}
?>

<form action='' method='POST' id='sort_frm'>
  <label>Сортировать по: </label> 
  <select name="order_by" id="order_by" onchange='go_sort(event)'>
    <?php
      foreach ($Sort as $k => $v)
      {
        echo "<option value='$k'";
          if($k == $order_by){
        echo " selected";
        }echo ">$v</option>";
      }
    ?>
  </select>
</form>

<?php
echo "<form action='' method ='POST' id='res_frm'>";
  switch($order_by){
    case 1: 
            show_rez_tbl(); 
            break;
    case 2: 
            show_rez_tbl("`title` ASC"); 
            break;
    case 3: 
            show_rez_tbl("`title` DESC"); 
            break;
    case 4: 
            show_rez_tbl("`price` ASC"); 
            break;
    case 5: 
            show_rez_tbl("`price` DESC"); 
            break;
    case 6: 
            show_rez_tbl("`weight` ASC"); 
            break;
    case 7: 
            show_rez_tbl("`weight` DESC"); 
            break;
    default: 
            show_rez_tbl(); 
            break;
  }
  echo "<input type='hidden' name='hid_del' value='1' />"; 
  echo "<input type='submit' value='Удалить' />"; 
  echo "<a href='clear.php?hid=1' class='clr_btn'>Очистить таблицу</a>";
  // проверка бэкап-таблицы на содержимое
  if($rez = mysqli_query($link, "SELECT id FROM `tovars_07718_backup`"))
  {
    if(mysqli_num_rows($rez) > 0)
    {
      mysqli_free_result($rez);
      echo "<a href='restore.php?hid=1' class='clr_btn cancel'>Восстановить удаленые строки</a>";
    }
  }
echo "</form>";

mysqli_close($link);
?>
</div>
<script type="text/javascript">
  var order_by = document.getElementById('order_by');
    function go_sort(event){
      if(order_by.selectedIndex != -1)
      {
        $("#sort_frm").submit();
      }
    };
</script>
</body>
</html>