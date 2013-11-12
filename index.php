<?php
	include "includes/apikey.php";
	include "includes/openid.php";
	include "includes/steam.php";
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
	<title>QUIZer - Главная</title>
	<link rel="stylesheet" type="text/css" href="http://<?php echo $domain; ?>/style.css" media="screen" />
	<link rel="icon" href="favicon.ico" type="image/x-icon">
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
</head>

<body>
<div id="content">
<?php

echo $login;

/* Настройка и подключение к БД */
include("includes/db.php");

$user = $steam->response->players[0]->steamid;
$steamid = $steam->response->players[0]->steamid;
$curdate = date("Y-m-d H:i:s");

if(isset($_SESSION['T2SteamAuth'])){

    include("includes/auth_query.php");
    $res_rights = mysql_query($query_rights) or die(mysql_error());

	if($res_rights && mysql_num_rows($res_rights) > 0) {
		echo "<div id='adminlink'>[<a href='http://".$domain."/admin/'>Админка</a>]</div>";
	}

    $query = "SELECT * FROM quiz WHERE status = '1' AND (enddate > '$curdate' OR enddate = '0000-00-00 00:00:00')";
    $res = mysql_query($query) or die(mysql_error());
    
    while($row = mysql_fetch_array($res)) {
		$query_ce = "SELECT finished FROM records WHERE steamid = '$user' AND quiz_id = '".$row['id']."'";
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
		
		$title = $row['title'];
		
		echo "<form class='form-wrapper' action='' method='post' onSubmit='window.location.href = \"/".$row['id']."/".$row['hash']."\"; return false;'>";
		echo "<input type='text' id='search' value=\"$title\" disabled>";
		echo "<input type='submit' value='$value' id='submit'>";
		echo "</form>";
    }
	
}
?>
<div id="geek"></div>
</div>
<div class="copyrights"><?php echo $copyrights; ?></div>
</body>
</html>