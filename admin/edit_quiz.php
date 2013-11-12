<?php
include "../includes/apikey.php";
include "../includes/openid.php";
include "../includes/steam.php";
# подключаем конфиг
include("../includes/db.php");
$tab_e = "questions";
$tab_r = "records";
$tab_q = "quiz";

mysql_connect($hostname, $username, $password) or die ("Не могу создать соединение");

mysql_select_db($dbName) or die (mysql_error());
mysql_query('SET character_set_database = utf8');
mysql_query('SET NAMES utf8'); 

$id = $_GET['id'];
$hash = $_GET['hash'];

/* Владелец? */

$query_owner = "SELECT owner FROM $tab_q WHERE id = '$id'";
$res_owner = mysql_query($query_owner) or die(mysql_error());
$row_owner = mysql_fetch_row($res_owner);
$quizowner = $row_owner[0];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
    <meta http-equiv="Content-Type" content="text/html; charset=utf8" />
    <title>QUIZer - Редактирование Викторины</title>
	<link rel="stylesheet" type="text/css" href="../style.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="tablesorter/themes/blue/style.css" media="screen" />
	<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
	<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
	<script type="text/javascript" src="tablesorter/jquery.tablesorter.js"></script> 
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
	<script>
	$(document).ready(function() { 
        $("#participants").tablesorter();
		$("#winners").tablesorter(); 
	} 
	); 
	</script>
<?php
if (isset($_POST['submit'])){
	if(empty($_POST['title']) or empty($_POST['result'])){
		$need2fill_quiz = "<font color='#FF0000'><b>Заполните необходимые поля!</b></font>";
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
	
		$query = "UPDATE $tab_q SET title = '$title', descr = '$descr', result = '$result', enddate = '$enddate' WHERE id = '$id' AND hash = '$hash'";
		mysql_query($query) or die(mysql_error());
		$saved = "<font color='green'><b>Сохранено</b></font>";
		echo "<META HTTP-EQUIV='REFRESH' CONTENT='1;URL=edit_quiz.php?id=".$id."&hash=".$hash."'>";
	}
}

$query = "SELECT * FROM $tab_q WHERE id = '$id' AND hash = '$hash'";
$res = mysql_query($query) or die(mysql_error());
$row = mysql_fetch_row($res);

?>
</head> 
<body>
<?php
echo $login;

$steamid = $steam->response->players[0]->steamid;

if(isset($_SESSION['T2SteamAuth'])){

    include("../includes/auth_query.php");
    $res_rights = mysql_query($query_rights) or die(mysql_error());
	$row_rights = mysql_fetch_row($res_rights);
	$superuser = $row_rights[3];
	
	if($res_rights && mysql_num_rows($res_rights) > 0) {
		if ($steamid == $quizowner OR $superuser == 1) {
			$updatebutton = '<input type="submit" class="buttons" name="submit" value="Применить" /> ';
			$addquestbutton = '<input type="submit" class="buttons" name="addquest" value="Добавить" /> ';
			$qresult = $row[4];
			$toggle = "";
		} else {
			$updatebutton = "";
			$addquestbutton = "";
			$qresult = "Скрыто";
			$toggle = "disabled";
		}
?>
		<div id="adminlink">[<a href="http://<?php echo $domain; ?>/admin/">Администрирование</a>] [<a href="http://<?php echo $domain; ?>/">На главную</a>]</div>

		<table width="800px" align="center" border="0">
		<tr>
		<td width="45%" style="vertical-align: top;">

		<h2>Редактирование Викторины</h2>
		<form action="edit_quiz.php?id=<?php echo $id; ?>&hash=<?php echo $hash; ?>" method="post" name="edit_quiz" style="margin-left: 20px;">
		<b>Название</b><br><input type="text" name="title" maxlength="64" style="width: 300px;" value="<?php echo $row[2]; ?>" <?php echo $toggle; ?> /><br><br>
		<b>Описание</b><br><textarea name="descr" maxlength="256" style="width: 300px; height: 64px;" <?php echo $toggle; ?>><?php echo $row[3]; ?></textarea><br><br>
		<b>Результат после прохождения</b><br><textarea name="result" maxlength="256" style="width: 300px; height: 36px;" <?php echo $toggle; ?>><?php echo $qresult; ?></textarea><br><br>
		<b>Ссылка на викторину</b><br><input type="text" value="<?php echo "http://".$_SERVER['SERVER_NAME']."/".$id."/".$hash; ?>" style="width: 300px;" readonly><br><br>
		<b>Время завершения</b><br><input type="text" name="enddate" class="timepicker" maxlength="64" value="<?php echo $row[5]; ?>" style="width: 150px; text-align: center;" <?php echo $toggle; ?> /><br><br>

<?php
		echo $updatebutton;
		echo $saved;
		echo $need2fill_quiz;
		echo "</form>";

		if (isset($_POST['addquest'])){
			if(empty($_POST['quest']) or empty($_POST['answer'])){
				$need2fill_quest = "<font color='#FF0000'><b>Заполните необходимые поля!</b></font>";
			} else {
				$quest = strip_tags($_POST['quest']);
				$quest = htmlspecialchars($quest);
				$quest = addslashes($quest);
				
				$answer = strip_tags($_POST['answer']);
				$answer = htmlspecialchars($answer);
				$answer = addslashes($answer);
				
				$altanswer = strip_tags($_POST['altanswer']);
				$altanswer = htmlspecialchars($altanswer);
				$altanswer = addslashes($altanswer);
				
				$query = "INSERT INTO $tab_e (id, quiz_id, quest, imgurl, answer, status, altanswer) VALUES ('', '$id', '$quest', '".$_POST['imgurl']."', '$answer', '2', '$altanswer')";
				mysql_query($query) or die(mysql_error());
			}
		}
	
		if (!empty($_GET['qid']) && !empty($_GET['status'])) {
			if ($steamid == $quizowner OR $superuser == 1) {
				$query = "UPDATE $tab_e SET status = '".$_GET['status']."' WHERE id = '".$_GET['qid']."'";
				mysql_query($query) or die(mysql_error());
			} else {
				$notquizowner = "Вы не являетесь владельцем QUIZ-а!";
			}
		}

		if(isset($_POST['delete'])){
			if ($steamid == $quizowner OR $superuser == 1) {
			$query = "DELETE FROM $tab_e WHERE id = '".$_POST['id']."'";
			mysql_query($query) or die(mysql_error());
			} else {
				$notquizowner = "Вы не являетесь владельцем QUIZ-а!";
			}
		}

		$query_l = "SELECT * FROM $tab_e WHERE quiz_id = '$id' ORDER by id ASC";
		$res_l = mysql_query($query_l) or die(mysql_error());
?>

		</td>
		<td valign="top">
		<h2>Добавление вопроса</h2>
		<form action="edit_quiz.php?id=<?php echo $id; ?>&hash=<?php echo $hash; ?>" method="post" name="add_quest" style="margin-left: 20px;">
		<b>Текст вопроса</b><br><textarea name="quest" maxlength="256" style="width: 300px; height: 64px;" <?php echo $toggle; ?>></textarea><br><br>
		<b>URL картинки (опционально)</b><br><input type="text" name="imgurl" maxlength="256" style="width: 300px;" <?php echo $toggle; ?>><br><br>
		<b>Ответ</b><br><input type="text" name="answer" maxlength="64" style="width: 300px;" <?php echo $toggle; ?>><br><br>
		<b>Альтернативный ответ</b><br><input type="text" name="altanswer" maxlength="64" style="width: 300px;" <?php echo $toggle; ?>><br><br>

<?php
echo $addquestbutton;
echo $need2fill_quest;
?>
		</form>

		<h2>Список вопросов</h2>
		<ul>
<?php
		while ($row_l = mysql_fetch_array($res_l)) {
			$del = $row_l['id'];
			if($row_l['status'] == 2) {
				$statusimg = "off.png";
				$altimg = "Отключен";
				$newstatus = "1";
			} else {
				$statusimg = "on.png";
				$altimg = "Включен";
				$newstatus = "2";
			}
			
			$str = $row_l['quest'];
			$start = 0;
			$len = 45;
			
			echo "<a href='?id=".$id."&hash=".$hash."&qid=".$row_l['id']."&status=".$newstatus."'><img src='../img/".$statusimg."' style='vertical-align: middle;' title='".$altimg."'></a>";
			echo '<form action="" method="post" onsubmit="return confirm(\'Вы уверены?\')" style="display: inline;">';
			echo '<input type="hidden" name="id" value="' . $del . '"/><input type="submit" class="imgClassDel" value="" name="delete">';
			echo '</form>&nbsp;';
			echo "<a href='edit_quest.php?id=".$row_l['id']."'>";
			echo mb_substr($str,$start,$len, 'UTF-8');
			echo " ...</a>";
			echo "<br>";
		}
echo $notquizowner;
?>
		</ul>
		</td>
		</tr>
		<tr><td colspan="2"><hr></tr>
		<tr>
		<td colspan="2">
		<h2>Победители</h2>
			<table id="winners" class="tablesorter" width="100%" border="0">
				<thead>
				<tr align="center" style="font-weight: bold;">
					<th>Юзер</td>
					<th>SteamId</th>
					<th>IP</td>
					<th>Клики</td>
					<th>Вступил</td>
					<th>Завершил</td>
					<th>Думал</td>
				</tr>
				</thead>
				<tbody>
<?php
		$query_r = "SELECT * FROM $tab_r WHERE quiz_id = '$id' AND finished = '1'";
		$res_r = mysql_query($query_r) or die(mysql_error());
		while ($row_r = mysql_fetch_array($res_r)) {

			$query_clk = "SELECT COUNT(*) FROM logs WHERE quiz_id = '$id' AND steamid = '".$row_r['steamid']."'";
			$res_clk = mysql_query($query_clk) or die(mysql_error());
			$row_clk = mysql_fetch_row($res_clk);
			$clicks = $row_clk[0];
			
			echo "<tr>";
			echo "<td><a href='view_logs.php?qid=".$id."&steamid=".$row_r['steamid']."'>";
			if (mb_strlen($row_r['username']) > 16) {
				echo mb_substr($row_r['username'], 0, 16, 'UTF-8');
				echo " ...";
			} else {
				echo $row_r['username'];
			}
			echo "</a></td>";
			echo "<td><a href='http://steamcommunity.com/profiles/".$row_r['steamid']."'>".$row_r['steamid']."</a></td>";
			echo "<td><a href='http://whois.domaintools.com/".$row_r['ip']."'>".$row_r['ip']."</a></td>";
			echo "<td align='center'>".$clicks."</td>";

			$datetime_finished = strtotime($row_r['date']);
			$datetime_entered = strtotime($row_r['entered']);

			$diff = $datetime_finished - $datetime_entered;
		 
			$days = floor($diff / (3600*24));
			$hours = floor(($diff - ($days * 3600 * 24)) / 3600);
			$minutes = floor(($diff - ($hours * 3600 + $days * 24 * 3600)) / 60);
			$seconds = $diff % 60;
			
			
			echo "<td align='center'>";
			echo date('Y.m.d H:i', $datetime_entered);
			echo "</td>";
			echo "<td align='center'>";
			echo date('Y.m.d H:i', $datetime_finished);
			echo "</td>";
			echo "<td>".$days." дн. ".str_pad($hours, '2', '0', STR_PAD_LEFT).":".str_pad($minutes, '2', '0', STR_PAD_LEFT).":".str_pad($seconds, '2', '0', STR_PAD_LEFT)."</td>";
			echo "</tr>";
		}
?>
			</tbody>
			</table>

			<h2>Участники</h2>
			
			<table id="participants" class="tablesorter" width="100%" border="0">
				<thead>
				<tr align="center" style="font-weight: bold;">
					<th>Юзер</td>
					<th width="150px">IP</td>
					<th width="50px">Клики</td>
					<th width="150px">Вступил</td>
				</tr>
				</thead>
				<tbody>
<?php
		$query_ro = "SELECT * FROM $tab_r WHERE quiz_id = '$id' AND finished = '0'";
		$res_ro = mysql_query($query_ro) or die(mysql_error());
		while ($row_ro = mysql_fetch_array($res_ro)) {

			$query_clk = "SELECT COUNT(*) FROM logs WHERE quiz_id = '$id' AND steamid = '".$row_ro['steamid']."'";
			$res_clk = mysql_query($query_clk) or die(mysql_error());
			$row_clk = mysql_fetch_row($res_clk);
			$clicks = $row_clk[0];
			
			echo "<tr>";
			echo "<td><a href='view_logs.php?qid=".$id."&steamid=".$row_ro['steamid']."'>";
			if (mb_strlen($row_ro['username']) > 45) {
				echo mb_substr($row_ro['username'], 0, 45, 'UTF-8');
				echo " ...";
			} else {
				echo $row_ro['username'];
			}
			echo "</a></td>";
			echo "<td><a href='http://whois.domaintools.com/".$row_ro['ip']."'>".$row_ro['ip']."</a></td>";
			echo "<td align='center'>".$clicks."</td>";

			$datetime_entered = strtotime($row_ro['entered']);

			echo "<td align='center'>";
			echo date('Y.m.d H:i', $datetime_entered);
			echo "</td>";
			echo "</tr>";
		}
?>				
			</tbody>
			</table>

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