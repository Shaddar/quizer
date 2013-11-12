<?php

$domain = "quiz.shaddar.com";

date_default_timezone_set('Europe/Moscow');

	$OpenID = new LightOpenID("$domain");

	session_set_cookie_params(60 * 60 * 24 * 30);
	session_start();
	
	if(!$OpenID->mode){
	
		if(isset($_GET['login'])){
			$OpenID->identity = "http://steamcommunity.com/openid";
			header("Location: {$OpenID->authUrl()}");
		}
		
		if(!isset($_SESSION['T2SteamAuth'])){
			if ($_SERVER['PHP_SELF'] == "/admin/index.php") {
				$login = ('<div id="login"><span style="font-size: 16px;">Вход в админпанель</span> <a href="?login"><img src="http://cdn.steamcommunity.com/public/images/signinthroughsteam/sits_small.png" align="top" border="0"></a></div>');
			} else {
				$login = ('<div id="login"><span style="font-size: 16px;">Залогиньтесь, чтобы принять участие</span> <a href="http://'.$domain.'/?login"><img src="http://cdn.steamcommunity.com/public/images/signinthroughsteam/sits_small.png" align="top" border="0"></a></div>');
			}
		}
		
	} elseif($OpenID->mode == "cancel"){
		echo "Пользователь отменил авторизацию.";
	} else {
		
		if(!isset($_SESSION['T2SteamAuth'])){
		
			$_SESSION['T2SteamAuth'] = $OpenID->validate() ? $OpenID->identity : null;
			$_SESSION['T2SteamID64'] = str_replace("http://steamcommunity.com/openid/id/", "", $_SESSION['T2SteamAuth']);
			
			if($_SESSION['T2SteamAuth'] !== null){
			
				$Steam64 = str_replace("http://steamcommunity.com/openid/id/", "", $_SESSION['T2SteamAuth']);
				$profile = file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key={$api}&steamids={$Steam64}");
				$buffer = fopen("/home/shaddar/public_html/quiz/cache/{$Steam64}.json", "w+");
				fwrite($buffer, $profile);
				fclose($buffer);
			
			}
			
			header("Location: http://$doamin/");
			
		}
		
	}
	
	if(isset($_GET['logout'])){
	
		unset($_SESSION['T2SteamAuth']);
		unset($_SESSION['T2SteamID64']);
		header("Location: http://$domain/");
	
	}

	if(!empty($_SESSION['T2SteamAuth'])) {
		$steam = json_decode(file_get_contents("/home/shaddar/public_html/quiz/cache/{$_SESSION['T2SteamID64']}.json"));
	}

	if(isset($_SESSION['T2SteamAuth'])){
		$login = ('<div id="login"><span style="font-size: 16px;">Вы вошли как <b><img src='.$steam->response->players[0]->avatar.' align="top"> '.$steam->response->players[0]->personaname.'</b>! <a href="?logout">Выйти</a></span></div>');
	}

?>