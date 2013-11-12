<?php
include "../includes/apikey.php";
include "../includes/openid.php";
include "../includes/steam.php";
# подключаем конфиг
include("../includes/db.php");

mysql_connect($hostname, $username, $password) or die ("Не могу создать соединение");

mysql_select_db($dbName) or die (mysql_error());
mysql_query('SET character_set_database = utf8');
mysql_query('SET NAMES utf8'); 
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
    <meta http-equiv="Content-Type" content="text/html; charset=utf8" />
    <title>Логи - <?php echo $username; ?></title>
        <link rel="stylesheet" type="text/css" href="../style.css" media="screen" />
</head> 
<body>

<?php
echo $login;

$steamid = $steam->response->players[0]->steamid;

if(isset($_SESSION['T2SteamAuth'])){

    include("../includes/auth_query.php");
    $res_rights = mysql_query($query_rights) or die(mysql_error());

	if($res_rights && mysql_num_rows($res_rights) > 0) {
		
		/* Проверяем, указаны ли ID викторины и пользователя */

		if (empty($_GET['qid']) or empty($_GET['steamid'])) {
			echo "<META HTTP-EQUIV='REFRESH' CONTENT='5;URL=index.php'>";
			echo "<meta charset=utf8>";
			echo "<br><center>Ошибка! Не указаны параметры.</center>";
			exit;
		} else {
			$qid = $_GET['qid'];
			$steamid = $_GET['steamid'];
		}

		/* Получаем hash викторины */
		$query_h = "SELECT hash FROM quiz WHERE id = '$qid'";
		$res_h = mysql_query($query_h) or die(mysql_error());
		$row_h = mysql_fetch_row($res_h);
		$hash = $row_h[0];

		/* Получаем ник */
		$query_n = "SELECT username FROM records WHERE steamid = '$steamid'";
		$res_n = mysql_query($query_n) or die(mysql_error());
		$row_n = mysql_fetch_row($res_n);
		$username = $row_n[0]
?>

		<div id="adminlink">[<a href="edit_quiz.php?id=<?php echo $qid; ?>&hash=<?php echo $hash; ?>">К викторине</a>] [<a href="http://<?php echo $domain; ?>/admin/">Администрирование</a>] [<a href="http://<?php echo $domain; ?>/">На главную</a>]</div>

		<center><h2>Варианты ответов пользователя <u><?php echo $username; ?></u></h2></center>

		<table align="center" border="0"><tr><td>

<?php

		$query_de = "SELECT entered FROM records WHERE steamid = '$steamid' AND quiz_id = '$qid'";
		$res_de = mysql_query($query_de) or die(mysql_error());
		$row_de = mysql_fetch_row($res_de);
		$entered = strtotime($row_de[0]);

		/* Получаем список вопросов */

		$query_ql = "SELECT id, quest FROM questions WHERE quiz_id = '$qid' ORDER by id ASC";
		$res_ql = mysql_query($query_ql) or die(mysql_error());

		while ($row_ql = mysql_fetch_array($res_ql)) {
			
			echo "<h3>".$row_ql['quest']."</h3>";
			
			$query_al = "SELECT answer, date FROM logs WHERE quiz_id = '$qid' AND quest_id = '".$row_ql['id']."' AND steamid = '$steamid' ORDER by date ASC";
			$res_al = mysql_query($query_al) or die(mysql_error());
			
			echo "<table border='0' style='width: 600px; margin-left: 20px;'>";
			
			while ($row_al = mysql_fetch_array($res_al)) {
			
				if (empty($lasttime)) {
					$lasttime = $entered;
				}
				
				$thistime = strtotime($row_al['date']);		
				$diff = $thistime - $lasttime;
		 
				$days = floor($diff / (3600*24));
				$hours = floor(($diff - ($days * 3600 * 24)) / 3600);
				$minutes = floor(($diff - ($hours * 3600 + $days * 24 * 3600)) / 60);
				$seconds = $diff % 60;
			
				echo "<tr>";
				echo "<td>".$row_al['answer']."</td>";
				echo "<td width='150px;' align='left'>";
				echo date("Y.m.d H:i:s", strtotime($row_al['date']));
				echo "</td>";
				echo "<td width='150px;' align='left'>";
				echo "".str_pad($hours, '2', '0', STR_PAD_LEFT).":".str_pad($minutes, '2', '0', STR_PAD_LEFT).":".str_pad($seconds, '2', '0', STR_PAD_LEFT)."";
				echo "</td>";
				echo "</tr>";
			
				$lasttime = strtotime($row_al['date']);
			
			}
			
			echo "</table>";
			
		}

?>
		</td></tr></table>
		</body>
		</html>

<?php
	} else {
		echo "<div style='width: 100%; text-align: center; margin-top: 20px;'><span style='font-size: 16px;'>Простите, но у Вас нет доступа к этому разделу!</span></div></body></html>";
		exit;
	}
}
?>