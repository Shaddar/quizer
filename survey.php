<?php
include "includes/apikey.php";
include "includes/openid.php";
include "includes/steam.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
    <meta http-equiv="Content-Type" content="text/html; charset=utf8" />
    <title>QUIZer</title>
	<link rel="stylesheet" type="text/css" href="http://<?php echo $domain; ?>/style.css" media="screen" />
	<link rel="icon" href="favicon.ico" type="image/x-icon">
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
 </head> 
<body>
<div id="content">
<div id="geek" title="На Главную!" onclick="location.href='http://<?php echo $domain; ?>/';"></div>
<?php
/* Настройка и подключение к БД */
include("includes/db.php");
$tab_r = "records";
$tab_e = "questions";
$tab_q = "quiz";

/* Получаем IP посетителя */

$ip = $_SERVER['REMOTE_ADDR'];
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
}

$steamid = $steam->response->players[0]->steamid;
$name = $steam->response->players[0]->personaname;

/* Залогинился? */

if (empty($steamid)) {
	echo $login;
	exit;
}

/* Проверяем, указаны ли ID и HASH викторины */

$id = intval($_GET['id']);
$hash = $_GET['hash'];
$curdate = date("Y-m-d H:i:s");

if (empty($id) or empty($hash)) {
    echo "<META HTTP-EQUIV='REFRESH' CONTENT='5;URL=index.php'>";
    echo "<br><center>Ошибка! Не указан идентификатор викторины.</center>";
    exit;
}

/* Проверяем, существует ли указанная викторина  */

$query_q = "SELECT * FROM $tab_q WHERE id = '$id' AND hash = '$hash'";
$res_q = mysql_query($query_q) or die(mysql_error());
$row_q = mysql_fetch_row($res_q);

if (empty($res_q)) {
	echo "<META HTTP-EQUIV='REFRESH' CONTENT='5;URL=index.php'>";
	echo "<br><center>Ошибка! Нет такой викторины.</center>";
	exit;
}

/* Проверяем, включена ли указанная вкиторина */

$query_s = "SELECT status, enddate FROM $tab_q WHERE id = '$id' AND hash = '$hash'";
$res_s = mysql_query($query_s) or die(mysql_error());
$row_s = mysql_fetch_row($res_s);
if ($row_s[0] == 2) {
    echo "<META HTTP-EQUIV='REFRESH' CONTENT='5;URL=index.php'>";
    echo "<br><center>Эта викторина отключена!</center>";
    exit;
}

/* Проверяем, не закончилась ли викторина */

$enddate = $row_s[1];

if ($enddate == "0000-00-00 00:00:00") {
	$timeleft = "Нет лимита времени";
} elseif (strtotime($enddate)<strtotime($curdate)) {
    echo "<META HTTP-EQUIV='REFRESH' CONTENT='5;URL=index.php'>";
    echo "<br><center>Эта викторина закончилась!</center>";
    exit;
} else {
	$enddatestr = strtotime($enddate);
	$curdatestr = strtotime($curdate);
	
	$diff = $enddatestr - $curdatestr;
 
	$days = floor($diff / (3600*24));
	$hours = floor(($diff - ($days * 3600 * 24)) / 3600);
	$minutes = floor(($diff - ($hours * 3600 + $days * 24 * 3600)) / 60);
	$seconds = $diff % 60;
	
	if ($diff < 60) {
		$timeleft = "<b>Осталось</b>: меньше минуты";
	} else {
		$timeleft = "<b>Осталось</b>: ".$days." дн. ".str_pad($hours, '2', '0', STR_PAD_LEFT).":".str_pad($minutes, '2', '0', STR_PAD_LEFT)."";
	}
}

/* Заносим данные о викторине в переменные */

$title = $row_q[2];
$descr = $row_q[3];
$link = $row_q[4];

/* Проверяем юзера на повторное участие */

$query_check = "SELECT * FROM `$tab_r` WHERE steamid = '$steamid' AND quiz_id = '$id' AND finished = '1'";
$res_check = mysql_query($query_check) or die(mysql_error());
$row_check = mysql_num_rows($res_check);

/* Линк на админку */
include("includes/auth_query.php");
$res_rights = mysql_query($query_rights) or die(mysql_error());

if($res_rights && mysql_num_rows($res_rights) > 0) {
	echo "<div id='adminlink'>[<a href='http://".$domain."/admin/'>Админка</a>]</div>";
}

if ($row_check > 0) {
	echo "<br><br>";
	echo "<center><b>";
	echo $name;
	echo "</b>, Вы уже принимали участие!";
	echo "<br><br>";
	echo "<b>Результат этой викторины:</b></center>";
        echo "<div class='div-wrapper'>";
	echo $link;
        echo "</div>";
        echo "<center><h2>Доступные викторины</h2></center>";

        
    $query = "SELECT * FROM quiz WHERE status = '1' AND id NOT IN ($id)";
    $res = mysql_query($query) or die(mysql_error());
    $d = 0;

    while($row = mysql_fetch_array($res)) {
		$query_ce = "SELECT finished FROM records WHERE steamid = '$steamid' AND quiz_id = '".$row['id']."'";
		$result = mysql_query($query_ce) or die(mysql_error());
		if ($result) {
			 if (mysql_num_rows($result) == 0) {
				$c = 2;
			 }
			 else {
				$row_ce = mysql_fetch_row($result);
				$c = $row_ce[0];
			 }
		}
		
		if ($c == 2) {
			$value = "Принять участие";
		} elseif ($c == 0) {
			$value = "Продолжить";
		} else  {
			$value = "Результат";
		}
		
		echo "<form class='form3-wrapper' action='' method='post' onSubmit='window.location.href = \"http://".$domain."/".$row['id']."/".$row['hash']."\"; return false;'>";
		echo "<input type='text' id='search' value='".$row['title']."' disabled>";
		echo "<input type='submit' value='$value' id='submit'>";
		echo "</form>";
		$d++;
		
    }
    
    if ($d == 0) {
	echo "<center>Нет доступных викторин</center>";
    }
    echo "<div id='geek' title='На Главную!' onclick=\"location.href='http://".$domain."/';\"></div></div><div class='copyrights'>".$copyrights."</div></body></html>";
	exit;
}

/* Записываем дату входа в викторину */

$query_ce = "SELECT COUNT(*) FROM $tab_r WHERE steamid = '$steamid' AND quiz_id = '$id' AND finished = '0'";
$res_ce = mysql_query($query_ce) or die(mysql_error());
$row_ce = mysql_fetch_row($res_ce);

if ($row_ce[0] == 0) {
	$query_dte = "INSERT INTO $tab_r (steamid, username, date, entered, quiz_id, ip, finished) VALUES ('$steamid', '$name', '', '".date('Y-m-d H:i:s')."', '$id', '$ip', '0')";
	mysql_query($query_dte) or die(mysql_error());
}


/* Получаем ID вопросов и заносим их в массив  */

$query_a = "SELECT id FROM $tab_e WHERE status = '1' AND quiz_id = '$id' ORDER by id ASC";
$res_a = mysql_query($query_a) or die(mysql_error());

$questids = array(null);

while ($row_a = mysql_fetch_array($res_a)) {
    $questids[] = $row_a['id'];
}

$totalsteps = count($questids) - 1;

/* Считаем кол-во правильных ответов */

$correct = 0;

for ($n = 1; $n <= $totalsteps; $n++) {

    $query_gca = "SELECT answer, altanswer FROM questions WHERE quiz_id = '$id' AND id = '".$questids[$n]."'";
    $res_gca = mysql_query($query_gca) or die(mysql_error());
    $row_gca = mysql_fetch_row($res_gca);
    $correct_answer = addslashes($row_gca[0]);
    $correct_altanswer = addslashes($row_gca[1]);
    
    $query_gla = "SELECT COUNT(*) FROM logs WHERE quiz_id = '$id' AND quest_id = '".$questids[$n]."' AND steamid = '$steamid' AND (answer = '$correct_answer' OR answer = '$correct_altanswer')";
    $res_gla = mysql_query($query_gla) or die(mysql_error());
    $row_gla = mysql_fetch_row($res_gla);
    
    if($row_gla[0] > 0) {
	$correct++;
    }

}

$curstep = $correct + 1;

if ($_GET['step'] > $curstep OR $_GET['step'] < $curstep) {
    $step = $curstep;
} elseif (empty($_GET['step'])) {
    $step = $curstep;
} else {
    $step = $_GET['step'];
}

/* Тело */

if ($curstep <= $totalsteps) {

$query = "SELECT * FROM $tab_e WHERE status = '1' AND quiz_id = '$id' AND id = '".$questids[$curstep]."' ORDER by id ASC";
$res = mysql_query($query) or die(mysql_error());

?>
<form class="form2-wrapper" action="<?php echo $hash; ?>" method="post" name="save_records">
<center><h2><?php echo $title; ?></h2></center>
<i><?php echo $descr; ?></i>
<?php

while ($row = mysql_fetch_array($res)) {

    $answer = $row['answer'];
	$answer = addslashes($answer);
    $altanswer = $row['altanswer'];
	$altanswer = addslashes($altanswer);

    echo "<div class='div-wrapper'>";
    echo "<u><b>Вопрос №".$step."</u> из ".$totalsteps."</b>";
    echo "<br><br>";
    echo "<span style='font-size: 14px; margin-left: 10px;'>".$row['quest']."</span>";
    echo "<br><br>";

    if (!empty($row['imgurl'])) {
	echo "<img src='".$row['imgurl']."' style='border: 1px #000 solid;' width='100%'><br><br>";
    }
    echo "<input type='text' name='answer' value='' style='width: 75%; margin-left: 10px;'>&nbsp;&nbsp;";

    if (isset($_POST['submit'])){

			
		$input_answer = strip_tags($_POST['answer']);
		$input_answer = htmlspecialchars($input_answer);
		$input_answer = addslashes($input_answer);
		
		if(!empty($input_answer)){
			if (mb_strtolower($input_answer, 'UTF-8') == mb_strtolower($answer, 'UTF-8') or mb_strtolower($input_answer, 'UTF-8') == mb_strtolower($altanswer, 'UTF-8')) {
			$query = "INSERT INTO logs (id, steamid, quiz_id, quest_id, answer, date) VALUES ('', '$steamid', '$id', '".$questids[$step]."', '".$input_answer."', '".date('Y-m-d H:i:s')."')";
			mysql_query($query) or die(mysql_error());
			if ($curstep == $totalsteps) {
				$nextstep = $curstep;
				echo "<META HTTP-EQUIV='REFRESH' CONTENT='0;URL=".$hash."'>";
			} else {
				$nextstep = $curstep + 1;
				echo "<META HTTP-EQUIV='REFRESH' CONTENT='0;URL=".$hash."'>";
			}
			} else {
			$query = "INSERT INTO logs (id, steamid, quiz_id, quest_id, answer, date) VALUES ('', '$steamid', '$id', '".$questids[$step]."', '".$input_answer."', '".date('Y-m-d H:i:s')."')";
			mysql_query($query) or die(mysql_error());
			echo "<font color='red'><b>Неверно!</b></font>";
			}
		}
    }

    echo "<br><br>";
    echo "</div>";
}

?>
<div id="timeleft">
<?php echo $timeleft; ?>
</div>
<input type="submit" name="submit" id="submit" value="Проверить" />

<?php
}
if ($correct == $totalsteps) {
	$query_add = "UPDATE $tab_r SET date = '".date("Y-m-d H:i:s")."', finished = '1' WHERE steamid = '$steamid' AND quiz_id = '$id'";
    mysql_query($query_add) or die(mysql_error());
    echo "<center><h2>Поздравляем, ".$name."! Вы справились!</h2></center>";
    echo "<div class='div-wrapper'>";
    echo $link;
    echo "</div>";
	
	echo "<center><h2>Доступные викторины</h2></center>";

    $query = "SELECT * FROM quiz WHERE status = '1' AND id NOT IN ($id)";
    $res = mysql_query($query) or die(mysql_error());
    $d = 0;

    while($row = mysql_fetch_array($res)) {
	
		$query_ce = "SELECT finished FROM records WHERE steamid = '$steamid' AND quiz_id = '".$row['id']."'";
		$result = mysql_query($query_ce) or die(mysql_error());
		if ($result) {
			 if (mysql_num_rows($result) == 0) {
				$c = 2;
			 }
			 else {
				$row_ce = mysql_fetch_row($result);
				$c = $row_ce[0];
			 }
		}

		if ($c == 2) {
			$value = "Принять участие";
		} elseif ($c == 0) {
			$value = "Продолжить";
		} else  {
			$value = "Результат";
		}
		
		echo "<form class='form3-wrapper' action='' method='post' onSubmit='window.location.href = \"http://".$domain."/".$row['id']."/".$row['hash']."\"; return false;'>";
		echo "<input type='text' id='search' value='".$row['title']."' disabled>";
		echo "<input type='submit' value='$value' id='submit'>";
		echo "</form>";
		$d++;

    }
    
    if ($d == 0) {
	echo "<center>Нет доступных викторин</center>";
    }
	echo "<div id='geek' title='На Главную!' onclick=\"location.href='http://".$domain."/';\"></div></div><div class='copyrights'>".$copyrights."</div></body></html>";
	exit;
}
?>

</form>

</div>
<div class="copyrights"><?php echo $copyrights; ?></div>
</body>
</html>
