<?php
require "../../../lib/lib.inc.php";
$name = get_Var("name","POST");
$mail = get_Var("mail","POST");
$message = get_Var("message","POST");
$clr_log = get_Var("clr_log","POST",0);
$btn_adm = get_Var("btn_adm","POST",0);
$delComm = get_Var("delComm","POST");
$hideComm = get_Var("hideComm","POST");
$apply = get_Var("apply","POST");
$isWrite = true;
$cnt = 0;
function isHidden_len($arr,$index,$pos_flag=false)
  {
    // возвращает true, если коммент скрыт (в строке есть "^hide")
    // если $pos_flag=true возвращает длину id-ка коммента
    if(preg_match("(\^hide\|)",$arr[$index]) && $pos_flag)
      {
        return (strpos($arr[$index], "^hide|")-12);
      }
    if(!preg_match("(\^hide\|)",$arr[$index]) && $pos_flag)
      {
        return (strpos($arr[$index], "|")-12);
      }
    if(preg_match("(\^hide\|)",$arr[$index])) return true;
    if(!preg_match("(\^hide\|)",$arr[$index])) return false;
  }
if($name==""||$mail==""||$message=="")
  {
    $isWrite=false;  
  }
$F=fopen("log/guestbook.log","r");
  $S = "";
if($F != false)
{
  while(feof($F) === false)
  {
    $S .= fread($F, 256);
  }
  fclose($F);
}
$messages = explode("\r\n", $S);
// удаление коментов из массива
// добавление метки "^hide" к скрываемым ( ##id_comment21^hide| )
if($delComm != "" || $hideComm != "") 
{
  $delComms = ($delComm != "")?explode(",", $delComm):[];
  $hideComms = ($hideComm != "")?explode(",", $hideComm):[];
  if($delComm != "" && $hideComm != "")
  {
    // ищем совпадающие номера скрываемых и удаляемых сообщений
    // удаляем их из массива скрываемых
    foreach ($hideComms as $key=>$value) 
    {
      if (in_array($value,$delComms))
      {
         array_splice($hideComms,$key,1);
      }
    }
  }
  $cnt = count($hideComms) + count($delComms);
  for ($i=0; $i < $cnt; $i++)
  {   
    for ($j=0; $j < count($messages)-1; $j++)
    { 
      if($hideComm != "" && count($hideComms) > 0 && $i < count($hideComms) &&
         $hideComms[$i] == substr($messages[$j], 12, isHidden_len($messages,$j,true)))
      {
        
        if(!isHidden_len($messages,$j))
        {
          $messages[$j] = substr_replace($messages[$j],"^hide",strpos($messages[$j], "|"),0);
        }
        else
        {
          $messages[$j] = substr_replace($messages[$j],"",strpos($messages[$j], "^hide|"),5);
        }
      }
      // ищем строку для удаления, 
      // узнаем есть ли в ней флаг "^hide"
      if($delComm != "" && $i < count($delComms) &&
          $delComms[$i] == substr($messages[$j],12, isHidden_len($messages,$j,true))
        )
      {
        $del_comment = array_splice($messages, $j, 1);
      }
    }
  }
  $S = implode("\r\n", $messages);
  $F = fopen("log/guestbook.log","w");
  fwrite($F, $S);
  fclose($F);
  // после записи лога - перезапрос этого же сценария
  // для "очистки" запроса 
  header("Location:".$_SERVER["PHP_SELF"]);
}

if($isWrite)
  {
    if(count($messages) == 1) {$id_comment = "01";}
    else 
    {
      $id_comment = substr($messages[count($messages)-2], 12, (strpos($messages[count($messages)-2], "|")-12));
      $id_comment++;
      if($id_comment < 10) {$id_comment = "0".$id_comment;}
    }
    $n = "##id_comment".$id_comment."|".date(" M j, D [G:i:s]")."|".
          $name."|".$mail."|".$message;
    if(in_array($n, $messages) === false)
    {
      $F = fopen("log/guestbook.log","a");
      fwrite($F, $n."\r\n");
      fclose($F);
      $messages[] = $n;
      // после записи лога - перезапрос этого же сценария
      // для "очистки" запроса 
      header("Location:".$_SERVER["PHP_SELF"]);  
    }
  }

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Гостевая книга</title>
<script type="text/javascript" src="../../../lib/jquery-1.11.2.js"></script>
<script type="text/javascript" src="../../../lib/jquery-ui.js"></script>
<link rel="stylesheet" href="css/jquery-ui.css">
<link rel="stylesheet" href="css/main.css">
</head>
<body>
  <form action="" method="POST" id="frm_main" name="frm_main">
    <div>
      <span>Имя:</span>
        <input type="text" name="name" />
        <div id="error0" class="err"></div>
    </div>
    <div>
      <span>e-mail:</span>
        <input type="text" name="mail" />
        <div id="error1" class="err"></div>
    </div>
    <div>
      <span class="msgTXT">Сообщение:</span>
        <textarea name="message"></textarea>
        <div id="error2" class="err"></div>
    </div>
    <input type="submit" value="Send">
    <button id="clr_log" name="clr_log" value="1">Clear log</button>
  </form>
  <form action="" method="POST" id="frm_adm">
    <button id="btn_adm" name="btn_adm" value="1">Admin</button>
    <button id="btn_adm" name="btn_adm" value="0">User</button>
    <input type="hidden" name='delComm' value=""/>
    <input type="hidden" name='hideComm' value=""/>
    <input type='submit' name="apply" value='Apply'/>
  </form>
  <script type="text/javascript">
      // Валидация формы
      var logo = document.forms["frm_main"]["name"];
      $("input[type=text], textarea").each(function(index,elem){
        $(elem).blur(function(){
          if(elem.value == "") 
            {
              $("#error"+index).text("Поле не заполнено").animate(
                      {"height": "14px","opacity": "0.8"},"slow");
              $(elem).css("border-color", "rgb(255, 0, 0)");
            }
          else
            {
              if($(elem).prop("name") == "name" &&
                  /^\s*[a-zA-Z]+\s*$/.test(elem.value) == false)
                {
                  $("#error"+index).text("Имя введен не правильно").animate(
                      {"height": "14px","opacity": "0.8"},"slow");
                  $(elem).css("border-color", "rgb(255, 0, 0)");
                }
              else if($(elem).prop("name") == "mail" &&
                 /^\s*(\w{1,15}\.)*\w{1,64}@\w{1,64}(\.\w{1,15})+\s*$/.test(elem.value) == false)
                {
                  $("#error"+index).text("mail введен не правильно").animate(
                      {"height": "14px","opacity": "0.8"},"slow");
                  $(elem).css("border-color", "rgb(255, 0, 0)");
                }
              else
                {
                  $("#error"+index).animate(
                      {"height": "0px","opacity": "0"},"slow",
                      function(){$("#error"+index).text("")});
                  $(elem).css("border-color", "rgb(0, 255, 0)");
                }
            }
        });
      });        
      document.forms["frm_main"].onsubmit = function()
        {
          $("input[type=text], textarea").each(function(index,el){
            if(el.value == "")
            {
              return false;
            }
          });
          if($("div.err").text() != "")
            {
              return false;
            }
          // в поле name делаем первую букву заглавной, остальные - строчные
          // (если есть) убираем пробелы в name, mail
          logo.value = logo.value.replace(/^\s+|\s+$/gm,'');
          logo.value = logo.value.charAt(0).toUpperCase()+logo.value.substring(1).toLowerCase();
          $("input[name=mail]")[0].value = $("input[name=mail]")[0].value.replace(/^\s+|\s+$/gm,'');
          return true;
        };
      // очистка лог-файла
      $("#clr_log").click(function(){
        <?php
          if($clr_log == 1)
          {
            $gb_log=fopen("log/guestbook.log", "w");
            if($gb_log===false) {exit();}
            fwrite($gb_log, "");
            fclose($gb_log);
            header("Location:");
            exit;
          }
        ?>
      });
  </script>
<div id="tbl">
<?php
  for ($i=count($messages)-1; $i >= 0; $i--)
  { 
    if($messages[$i] == "") continue;
    $msg=explode("|",$messages[$i]);
    if(isHidden_len($messages,$i))
      {
        $hid0="hid";$hid1="";
      }
    else
      {
        $hid0="";$hid1="hid";
      }
    echo "<table>";
    echo "<tr class='tr_first'>";
    echo "<td class='tbl_head'>Date:</td>";
    echo "<td>".$msg[1]."<div class='containerBtn' id='ap".
            substr($msg[0],12,isHidden_len($messages,$i,true))."'>".
          "<img class='btnHide' src='img/hide.png' width='20px' height='20px' title='' />".
          "<img class='btnDel' src='img/delete.png' width='20px' height='20px' title='' />".
          "</div></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='tbl_head'>Name:</td>";
    echo "<td>".$msg[2]."</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='tbl_head'>Mail:</td>";
    echo "<td>".$msg[3]."</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='tbl_head'>Message:</td>";
    echo "<td class='msgTXT'><span class='".$hid0."'>".$msg[4]."</span>".
          "<div class='hiddenMsg ".$hid1."'>Коментарий скрыт</div></td>";
    echo "</tr>";
    echo "</table>";
  }
?>
</div>
<script type="text/javascript">
  if(<?php echo $btn_adm;?> == 1 || <?php echo strlen($apply);?> > 0)
    {
      $("img.btnHide").css("visibility", "visible");
      $("img.btnDel").css("visibility", "visible");
      $("input[name=apply]").css("display","inline-block");
      $("#frm_adm").css("width","138px");
      $(function(){
        $("img.btnHide").each(function(index,elem){
          if($(elem).parents("tbody").find("span").hasClass("hid"))
            {
              elem.src = "img/show.png";
              elem.title = "Показать коментарий";
            }
        });
      });
    }
  else
    {
      $(function(){
        $("td.msgTXT").click(function(){
          $(this).find("span").toggleClass("hid");
          $(this).find("span").next().toggleClass("hid");
        });
      });
    }
  $(function(){
    var delComm = "";
    var hideComm = "";
    
    $("img.btnDel").click(function(){
      $(this).parents("table").animate({"opacity":0.5});
      if($(this).parents("table").css("opacity") < 1)
      {
        $(this).parents("table").animate({"opacity":1});
        $(this)[0].src = "img/delete.png";
        $(this).tooltip({
          content: "Удалить коментарий"
        });
        if(delComm.indexOf($(this).parent().attr("id").substr(2)) == 0 &&
            delComm.search(/,/) == -1)
        {
          delComm = delComm.replace($(this).parent().attr("id").substr(2),"");
          return;
        }
        if(delComm.indexOf($(this).parent().attr("id").substr(2)) == 0 &&
            delComm.search(/,/) != -1)
        {
          delComm = delComm.replace($(this).parent().attr("id").substr(2)+",","");
        }
        else
        {
          delComm = delComm.replace(","+$(this).parent().attr("id").substr(2),"");
        }
        return;
      }
      else
      {
        $(this)[0].src = "img/restore.png";
        $(this).tooltip({
          content: "Восстановить коментарий"
        });
        if(delComm.length == 0) delComm = $(this).parent().attr("id").substr(2);
        else delComm += "," + $(this).parent().attr("id").substr(2);
      }
    });
    $("img.btnHide").click(function(){
      if($(this).parents("tbody").find("span").hasClass("hid"))
      {
        $(this).parents("tbody").find("span").removeClass("hid");
        $(this).parents("tbody").find("span").next().addClass("hid");
        $(this)[0].src = "img/hide.png";
        $(this).tooltip({
          content: "Скрыть коментарий"
        });
        if(hideComm.indexOf($(this).parent().attr("id").substr(2)) == -1)
        {
          if(hideComm.length == 0) hideComm = $(this).parent().attr("id").substr(2);
          else hideComm += "," + $(this).parent().attr("id").substr(2);
          return;
        }
        if(hideComm.indexOf($(this).parent().attr("id").substr(2)) == 0 &&
            hideComm.search(/,/) == -1)
        {
          hideComm = hideComm.replace($(this).parent().attr("id").substr(2),"");
          return;
        }
        if(hideComm.indexOf($(this).parent().attr("id").substr(2)) == 0 &&
            hideComm.search(/,/) != -1)
        {
          hideComm = hideComm.replace($(this).parent().attr("id").substr(2)+",","");
        }
        else
        {
          hideComm = hideComm.replace(","+$(this).parent().attr("id").substr(2),"");
        }
        return;
      }
      else
      {
        $(this).parents("tbody").find("span").addClass("hid");
        $(this).parents("tbody").find("span").next().removeClass("hid");
        $(this)[0].src = "img/show.png";
        $(this).tooltip({
          content: "Показать коментарий"
        });
        if(hideComm.length == 0) hideComm = $(this).parent().attr("id").substr(2);
        else hideComm += "," + $(this).parent().attr("id").substr(2);
      }
    });
    $("#frm_adm").submit(function(event){
      // event.preventDefault();
      $("input[name=delComm]").prop("value",delComm);
      $("input[name=hideComm]").prop("value",hideComm);
      $("#btn_adm").prop("value","1");
      // $.post("guestbook.php", {btn_adm: 1, delComm: delComm});
    });
    $("img.btnHide").tooltip({
      content: "Скрыть коментарий"
    });
    $("img.btnDel").tooltip({
      content: "Удалить коментарий"
    });
  });
  
</script>
<!--div id="inf" style="clear: both;">
  <pre>
    <!?php
      // var_dump($_POST);
      print_r($GLOBALS);
      // echo $GLOBALS["POST"];
    ?>
  </pre>
</div-->

<!--script type="text/javascript">
<!?php foreach ($_POST as $key => $value) { ?>
  console.log('<!?php echo $key." => ".$value;?>');
<!?php }?>
</script-->

</body>
</html>