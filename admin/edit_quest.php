<?php
include "../includes/apikey.php";
include "../includes/openid.php";
include "../includes/steam.php";
# подключаем конфиг
include("../includes/db.php");
$tab_e = "questions";

mysql_connect($hostname, $username, $password) or die ("Не могу создать соединение");

mysql_select_db($dbName) or die (mysql_error());
mysql_query('SET character_set_database = utf8');
mysql_query('SET NAMES utf8'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
    <meta http-equiv="Content-Type" content="text/html; charset=utf8" />
    <title>QUIZer - Редактирование вопроса</title>
	<link rel="stylesheet" type="text/css" href="../style.css" media="screen" />
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
?>

<?php

	if (!empty($_GET['id'])) {
		$id = $_GET['id'];
	} else {
		$id = "";
	}

	$query_get_qid = "SELECT quiz_id FROM $tab_e WHERE id = '$id'";
	$res_get_qid = mysql_query($query_get_qid) or die(mysql_error());
	$row_get_qid = mysql_fetch_row($res_get_qid);
	$qid = $row_get_qid[0];

	$query_owner = "SELECT owner FROM quiz WHERE id = '$qid'";
	$res_owner = mysql_query($query_owner) or die(mysql_error());
	$row_owner = mysql_fetch_row($res_owner);
	$quizowner = $row_owner[0];

	if ($steamid == $quizowner OR $superuser == 1) {
		if (isset($_POST['edit'])) {
			$answer = addslashes($_POST['answer']);
			$altanswer = addslashes($_POST['altanswer']);
			$quest = addslashes($_POST['quest']);
			$query_e = "UPDATE $tab_e SET quest = \"$quest\", imgurl = '".$_POST['imgurl']."', answer = '$answer', altanswer = '$altanswer' WHERE id = '".$_GET['id']."'";
			mysql_query($query_e) or die(mysql_error());
			$saved = "<font color='green'><b>Сохранено</b></font>";
			echo "<META HTTP-EQUIV='REFRESH' CONTENT='1;URL=".$_SERVER['PHP_SELF']."?id=".$_GET['id']."'>";
		}

		/* Проверяем, выбран ли вопрос */

		if (empty($_GET['id'])){
			echo "Не выбран вопрос!";
			exit;
		}

		/* Получаем текст вопроса */

		$query = "SELECT * FROM $tab_e WHERE id='".$_GET['id']."'";
		$res = mysql_query($query) or die(mysql_error());
		$row = mysql_fetch_row($res);

		/* Получаем id викторины */

		$query_v = "SELECT hash FROM quiz WHERE id = '".$row[1]."'";
		$res_v = mysql_query($query_v) or die(mysql_error());
		$row_v = mysql_fetch_row($res_v);
?>
		<div id="adminlink">[<a href="edit_quiz.php?id=<?php echo $row[1]; ?>&hash=<?php echo $row_v[0] ?>">Назад</a>] [<a href="http://<?php echo $domain; ?>/admin/">Администрирование</a>] [<a href="http://<?php echo $domain; ?>/">На главную</a>]</div>

		<table width="800px" align="center" border="0">
		<tr>
		<td width="45%" style="vertical-align: top;">

		<!-- Форма редактирования -->
		<h2>Редактирование вопроса</h2>
		<form action="edit_quest.php?id=<?php echo $_GET['id']; ?>" method="post" name="edit_quest" style="margin-left: 20px;">
		<b>Текст вопроса</b><br><textarea name="quest" maxlength="256" style="width: 300px;"><?php echo $row[2]; ?></textarea><br><br>
		<b>URL картинки (опционально)</b><br><input type="text" name="imgurl" maxlength="256" style="width: 300px;" value="<?php echo $row[3]; ?>"><br><br>
		<b>Ответ</b><br><input type="text" name="answer" maxlength="64" style="width: 300px;" value="<?php echo $row[4]; ?>"><br><br>
		<b>Альтернативный ответ</b><br><input type="text" name="altanswer" maxlength="64" style="width: 300px;" value="<?php echo $row[6]; ?>"><br><br>

		<input type="submit" class="buttons" name="edit" value="Обновить" />
<?php
		echo $saved;
?>
		</form>
		</td></tr></table>

		</body>
		</html>
<?php
		} else {
			echo "Вы не являетесь владельцем QUIZ-а</body></html>";
			exit;
		}
	} else {
		echo "<div style='width: 100%; text-align: center; margin-top: 20px;'><span style='font-size: 16px;'>Простите, но у Вас нет доступа к этому разделу!</span></div></body></html>";
		exit;
	}
}
?>