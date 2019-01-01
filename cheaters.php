<!DOCUMENT html>
<html>
  <head>
    <title>Cheaters</title>
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="assets/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="assets/css/stylesheetCheaters.css">
  </head>
  <body>
    <video autoplay="" loop="" oncontextmenu="return false;" id="video"><source src="assets/nuke.mp4" type="video/mp4"></video>
    <div id="container">
      <div id="content_container">
     	<div align="center">
    	  <div class="spacer"></div>
    	  <?php

    		//STEAM LIGHTOPENID API
                include "assets/lightopenid/openid.php";
    		$_STEAMAPI = "***YourAPIKey***";

    		try {
    			$openid = new LightOpenID("zxqw.uk");
    			session_start();
    			if(!$openid->mode) {
    				if(isset($_GET['login'])) {
    					$openid->identity = "http://steamcommunity.com/openid";
    					header("Location: {$openid->authUrl()}");
    				}
    				if(!isset($_SESSION['TSteamAuth'])) {
    					$login = "<div id=\"login\"><a href=\"?login\"><img src='assets/steamapi.jpg' style='width:180px; height:35px; border:none;'/></a></div>";
    				}
    			} elseif($openid->mode == "cancel") {
    				echo "User has cancelled authentication";
    			} else {
                      //login
    			if(!isset($_SESSION['TSteamAuth'])) {
    				$_SESSION['TSteamAuth'] = $openid->validate() ? $openid->identity : null;
    				$_SESSION['TSteamID64'] = str_replace("https://steamcommunity.com/openid/id/", "", $_SESSION['TSteamAuth']);
    				header("Location: cheaters");
    				}
    			}
                      //Logged in
    			if(isset($_SESSION['TSteamAuth'])) {
    				$steam64 = str_replace("https://steamcommunity.com/openid/id/", "", $_SESSION['TSteamAuth']);
    				$url = "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key={$_STEAMAPI}&steamids={$steam64}";
    				$json_object = file_get_contents($url);
    				          //Caching
    				$fileLocation = "assets/cache/{$steam64}.json";
    				file_put_contents($fileLocation, $json_object);

                      //Getting User API information
    				$json_decoded = json_decode($json_object);
    				foreach ($json_decoded->response->players as $player) {
    					$username = $player->personaname;
    					$useravatar = $player->avatarfull;
    					$login = "<div class='centerwrapper'><div class='square'><img src=$useravatar class='large'></img></div><div class='vert'><div class='spacer2'></div><p2><b>$username</b></p2><div id=\"login\"><a href=\"?logout\"><p3>Logout&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p3></a></div></div></div>";
    				}
    			}
                      //Logging out
    			if(isset($_GET['logout'])) {
    				unset($_SESSION['TSteamAuth']);
    				unset($_SESSION['TSteamID64']);
    				header("Location: cheaters");
    			}
                      //echo User information OR steam logo
    			echo $login;
    		} catch(ErrorException $e) {
    			echo $e->getMessage();
    		}

    	?>
        <div class="spacer"></div>
        <?php
              // Last updated
              $lastUpdatedLocation = "assets/cache/lastupdated.txt";
              $lastUpdated = file_get_contents($lastUpdatedLocation);
              echo "<banned>$lastUpdated</banned>";
        ?>

        <?php

              //Functions and Attributes
              $playersLocation = "assets/cache/players.txt";

              function loadPlayers($file) {
                $players = file_get_contents($file);
                $playersArray = explode("\n", $players);
                return $playersArray;
              }

              function echoTableStart() {
                echo "<table class='spacing-table'><tr><th><p>KEY</p></th><th><p>Game Time</p></th><th><p>SteamID</p></th><th></th><th><p>Username</p></th><th><p>VAC</p></th><th><p>Hours</p></th></tr>";
              }

              function echoTableEnd() {
                echo "</table>";
              }

              function splitPlayer($player, $attribute) {
                $pieces = explode(" ", $player);
                $keyid = $pieces[0];
                $gametime = $pieces[1]." ".$pieces[2];
                $steamid = $pieces[3];
                if($attribute == "keyid") {
                  return $keyid;
                } else if($attribute == "gametime") {
                  return $gametime;
                } else if($attribute == "steamid") {
                  return $steamid;
                }
              }

              function makeSteamIDLink($steamid){
                $link = "https://steamcommunity.com/profiles/".$steamid."/";
                return $link;
              }

              function checkIfBanned($steamid) {
                $playerBannedLocation = "assets/cache/players/{$steamid}Banned.json";
                $player_banned_json = json_decode(file_get_contents($playerBannedLocation));
                $gameBans = $player_banned_json->players[0]->NumberOfGameBans;
                if($player_banned_json->players[0]->VACBanned || $gameBans >= 1) {
                  return "true";
                } else {
                  return "false";
                }
              }

              function echoPlayerTable($keyid, $gametime, $steamidlink, $steamid) {
                $playerLocation = "assets/cache/players/{$steamid}.json";
                $playercsgoLocation = "assets/cache/players/{$steamid}CSGO.json";
                $player_json = json_decode(file_get_contents($playerLocation));
                $player_csgo_json = json_decode(file_get_contents($playercsgoLocation));

                $profilePicture = $player_json->response->players[0]->avatar;
                $username = $player_json->response->players[0]->personaname;
                $profile = $player_json->response->players[0]->communityvisibilitystate;

                echo "<tr>
                        <td><p>$keyid</p></td>
                        <td><p>$gametime</p></td>
                        <td><a href='$steamidlink' target='"._blank."'><p>$steamidlink</p></a></td>
                        <td><img src='$profilePicture'/></td>
                        <td><p>$username</p</td>
                        <td><banned></banned></td>";

                if($profile <= 2) {
                    $csgoHours = "<banned>PRIVATE</banned>";
                } else {
                    $playerhours = $player_csgo_json->playerstats->stats[2]->value;
                    $playerhours = (($playerhours/60)/60);
                    $playerhours = round($playerhours, 2, PHP_ROUND_HALF_UP);
                    if($playerhours == 0) {
                      $csgoHours = "<banned>PRIVATE</banned>";
                    } else {
                      $csgoHours = "<p>$playerhours</p>";
                    }

                }

                echo "<td><banned>$csgoHours</banned></td>
                      </tr>";

              }

              function echoPlayerBannedTable($keyid, $gametime, $steamidlink, $steamid) {
                $playerLocation = "assets/cache/players/{$steamid}.json";
                $playercsgoLocation = "assets/cache/players/{$steamid}CSGO.json";
                $playerBannedLocation = "assets/cache/players/{$steamid}Banned.json";
                $player_json = json_decode(file_get_contents($playerLocation));
                $player_csgo_json = json_decode(file_get_contents($playercsgoLocation));
                $player_banned_json = json_decode(file_get_contents($playerBannedLocation));

                $profilePicture = $player_json->response->players[0]->avatar;
                $username = $player_json->response->players[0]->personaname;
                $profile = $player_json->response->players[0]->communityvisibilitystate;

                //VAC info
                $daysSinceBan = $player_banned_json->players[0]->DaysSinceLastBan;
                $SinceBan = "<img src='assets/logos/vaclogo.png'/>"." ".$daysSinceBan." Days";

                echo "<tr>
                        <td><banned>$keyid</banned></td>
                        <td><banned>$gametime</banned></td>
                        <td><a href='$steamidlink' target='"._blank."'><banned>$steamidlink</banned></a></td>
                        <td><img src='$profilePicture'/></td>
                        <td><banned>$username</banned</td>
                        <td><banned>$SinceBan</banned></td>";

                if($profile <= 2) {
                    $csgoHours = "<banned>PRIVATE</banned>";
                } else {
                    $playerhours = $player_csgo_json->playerstats->stats[2]->value;
                    $playerhours = (($playerhours/60)/60);
                    $playerhours = round($playerhours, 2, PHP_ROUND_HALF_UP);
                    if($playerhours == 0) {
                      $csgoHours = "<banned>PRIVATE</banned>";
                    } else {
                      $csgoHours = "<banned>$playerhours</banned>";
                    }

                }

                echo "<td><banned>$csgoHours</banned></td>
                      </tr>";

              }

              //Table
              $players = loadPlayers($playersLocation);
              $playersAmount = (sizeof($players))-1;
              //Echo start of table
              echoTableStart();
              for($i=0; $i<$playersAmount; $i++) {
                $keyid = splitPlayer($players[$i], "keyid");
                $gametime = splitPlayer($players[$i], "gametime");
                $steamid = splitPlayer($players[$i], "steamid");
                $steamidlink = makeSteamIDLink($steamid);

                //Check if player is banned
                $ifBanned = checkIfBanned($steamid);
                if($ifBanned == "false") {
                  echoPlayerTable($keyid, $gametime, $steamidlink, $steamid);
                } else {
                  echoPlayerBannedTable($keyid, $gametime, $steamidlink, $steamid);
                }
              }
              echoTableEnd();

              echo "<div class='spacer'></div>";
              echo "<p>Made by <a href='https://steamcommunity.com/id/steffaN--' target='"._blank."'>steffaN--</a></p>";

            ?>

    	  </div>
      </div>
    </div>
  </body>
</html>
