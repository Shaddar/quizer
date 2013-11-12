<?php
include "../includes/apikey.php";
include "../includes/openid.php";
include "../includes/steam.php";
# подключаем конфиг
include("../includes/db.php");
$tab_e = "questions";
$tab_r = "records";
$tab_q = "quiz";

$curdate = date("Y-m-d H:i:s");

mysql_connect($hostname, $username, $password) or die ("Не могу создать соединение");

mysql_select_db($dbName) or die (mysql_error());
mysql_query('SET character_set_database = utf8');
mysql_query('SET NAMES utf8');  

/* Владелец? */

$query_owner = "SELECT owner FROM $tab_q WHERE id = '".$_GET['id']."'";
$res_owner = mysql_query($query_owner) or die(mysql_error());
$row_owner = mysql_fetch_row($res_owner);
$quizowner = $row_owner[0];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
    <meta http-equiv="Content-Type" content="text/html; charset=utf8" />
    <title>QUIZer - Управление</title>
	<link rel="stylesheet" type="text/css" href="../style.css" media="screen" />
	<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
	<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
	<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
	<script src="js/jquery-ui-timepicker-addon.js"></script>
	<script>
		$(function() {
			$.timepicker.regional['ru'] = {
				timeOnlyTitle: 'Выберите время',
				timeText: 'Время',
				hourText: 'Часы',
				minuteText: 'Минуты',
				secondText: 'Секунды',
				millisecText: 'Миллисекунды',
				timezoneText: 'Часовой пояс',
				currentText: 'Сейчас',
				closeText: 'Закрыть',
				timeFormat: 'HH:mm',
				dateFormat: 'yy-m-d',
				amNames: ['AM', 'A'],
				pmNames: ['PM', 'P'],
				isRTL: false
			};
			$.datepicker.setDefaults($.datepicker.regional['ru']);
			$.timepicker.setDefaults($.timepicker.regional['ru']);
			$('.timepicker').datetimepicker({stepMinute: 5});
		});
	</script>
<body>

<?php
echo $login;

$steamid = $steam->response->players[0]->steamid;

if(isset($_SESSION['T2SteamAuth'])){

    include("../includes/auth_query.php");
    $res_rights = mysql_query($query_rights) or die(mysql_error());
	$row_rights = mysql_fetch_row($res_rights);
	$superuser = $row_rights[3];
	$quizlist = $row_rights[4];

	if($res_rights && mysql_num_rows($res_rights) > 0) {
?>
		<div id="adminlink">[<a href="http://<?php echo $domain; ?>/admin/">Администрирование</a>] [<a href="http://<?php echo $domain ?>/">На главную</a>]</div>
		<table width="800px" align="center" border="0">
		<tr>
		<td style="vertical-align: top;">
		<h2>Добавление Викторины</h2>
		<form action="index.php" method="post" name="add_quest" id="add_quest" style="margin-left: 20px;">
		<b>Название</b><br><input type="text" name="title" maxlength="64" style="width: 300px;" /><br><br>
		<b>Описание</b><br><textarea name="descr" maxlength="256" style="width: 300px;"></textarea><br><br>
		<b>Результат после прохождения</b><br><textarea name="result" maxlength="256" style="width: 300px;"></textarea><br><br>
		<b>Время завершения</b><br><input type="text" name="enddate" class="timepicker" maxlength="64" style="width: 150px; text-align: center;" /><br><br>

		<input type="submit" class="buttons" name="submit" value="Добавить" />
		</form>

<?php
		function randString( $length ) {
			$chars = "abcdef0123456789"; 
			$size = strlen($chars);
			for( $i = 0; $i < $length; $i++ ) {
				$str .= $chars[ rand( 0, $size - 1 ) ];
			}
			return $str;
		}
		$hash = randString(32);

		if (isset($_POST['submit'])){
			if(empty($_POST['title']) or empty($_POST['result'])){
				echo "Заполните необходимые поля!";
			} else {
				$title = strip_tags($_POST['title']);
				$title = htmlspecialchars($title);
				$title = addslashes($title);
				
				$descr = strip_tags($_POST['descr']);
				$descr = htmlspecialchars($descr);
				$descr = addslashes($descr);
				
				$result = strip_tags($_POST['result']);
				$result = htmlspecialchars($result);
				$result = addslashes($result);
				
				$enddate = $_POST['enddate'];
				
				$query = "INSERT INTO $tab_q (id, hash, title, descr, result, enddate, status, created, owner) VALUES ('', '$hash', '$title', '$descr', '$result', '$enddate', '2', '$curdate', '$steamid')";
				mysql_query($query) or die(mysql_error());
			}
		}

		if (!empty($_GET['id']) && !empty($_GET['status'])) {
			if ($steamid == $quizowner OR $superuser == 1) {
				$query = "UPDATE $tab_q SET status = '".$_GET['status']."' WHERE id = '".$_GET['id']."'";
				mysql_query($query) or die(mysql_error());
			} else {
				$notquizowner = "Вы не являетесь владельцем QUIZ-а!";
			}
		}

		//if (!empty($_GET['del'])) {
		if(isset($_POST['delete'])){
			$query_owner = "SELECT owner FROM $tab_q WHERE id = '".$_POST['id']."'";
			$res_owner = mysql_query($query_owner) or die(mysql_error());
			$row_owner = mysql_fetch_row($res_owner);
			$quizowner = $row_owner[0];
			if ($steamid == $quizowner OR $superuser == 1) {
				$query = "DELETE FROM $tab_q WHERE id = '".$_POST['id']."'";
				$query2 = "DELETE FROM $tab_e WHERE quiz_id = '".$_POST['id']."'";
				$query3 = "DELETE FROM $tab_r WHERE quiz_id = '".$_POST['id']."'";
				mysql_query($query) or die(mysql_error());
				mysql_query($query2) or die(mysql_error());
				mysql_query($query3) or die(mysql_error());
			} else {
				$notquizowner = "Вы не являетесь владельцем QUIZ-а!";
			}
		}

		if ($superuser == 1 OR $quizlist == 1) {
			$query_q = "SELECT * FROM $tab_q ORDER by id ASC";
		} else {
			$query_q = "SELECT * FROM $tab_q WHERE owner = '$steamid' ORDER by id ASC";
		}
		$res_q = mysql_query($query_q) or die(mysql_error());
?>
		<br><br>
		<b><span style="font-size: 16px;">Список викторин:</span></b><br><br>
		<table border="0" width="100%">
		<tr style="font-weight: bold;">
			<td>&nbsp;</td>
			<td>Создан</td>
			<td>Название</td>
			<td>Завершится</td>
			<td>Владелец</td>
		</tr>
<?php
		while ($row_q = mysql_fetch_array($res_q)) {
		
			$sidinfo = json_decode(file_get_contents("../cache/".$row_q['owner'].".json"));
			$owner = $sidinfo->response->players[0]->personaname;
			$owner = mb_substr($owner,0,16, 'UTF-8');
		
			$id = $row_q['id'];
			if($row_q['status'] == 2) {
				$statusimg = "off.png";
				$altimg = "Отключен";
				$newstatus = "1";
			} else {
				$statusimg = "on.png";
				$altimg = "Включен";
				$newstatus = "2";
			}
			
			$enddate = $row_q['enddate'];
			
			if ($enddate == "0000-00-00 00:00:00") {
				$enddate = "Не установлено";
			} elseif (strtotime($enddate)<strtotime($curdate)) {
				$enddate = "Закончилась";
			} else {
				$enddate = $row_q['enddate'];
			}
			
			echo "<tr><td>";
			echo "<a href='?id=".$row_q['id']."&status=".$newstatus."'><img src='../img/".$statusimg."' style='vertical-align: middle;' title='".$altimg."'></a>&nbsp;";
			echo '<form action="" method="post" onsubmit="return confirm(\'Вы уверены?\')" style="display: inline;">';
			echo '<input type="hidden" name="id" value="' . $id . '"/><input type="submit" class="imgClassDel" value="" name="delete">';
			echo '</form></td>';
			echo "<td width='140px'>[".$row_q['created']."]</td>";
			echo "<td><a href='edit_quiz.php?id=".$row_q['id']."&hash=".$row_q['hash']."'>".$row_q['title']."</a></td>";
			echo "<td>".$enddate."</td>";
			echo "<td width='150px'>";
			echo $owner;
			echo "</td></tr>";
		}



?>
		</table>
<?php
echo $notquizowner;
?>
		</td>
		</tr>
		</table>
		</body>
		</html>
<?php
	} else {
		echo "<div style='width: 100%; text-align: center; margin-top: 20px;'><span style='font-size: 16px;'>Простите, но у Вас нет доступа к этому разделу!</span></div></body></html>";
		exit;
	}
}
?>