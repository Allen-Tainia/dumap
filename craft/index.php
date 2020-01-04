<?php
include '../config.php';
include '../library/vars.php';
include '../library/global.php';

//session_start();  // This is handled in global.php now

if(session('access_token')) {

    //CHECK SESSION TIMEOUT
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 600)) {
        // last request was more than 10 minutes ago
        session_unset();
        session_destroy();
        session_write_close();
        setcookie(session_name(),'',0,'/');
        session_regenerate_id(true);
        header("Location: /");
        die();
    }
    $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

    $user = apiRequest($apiURLBase);
    $guilds = apiRequest($apiURLGuilds);
    $guildmember = apiBotRequest($apiURLGuildMember, $user->id);
    $data = json_decode($guildmember);
    $blacklistfile = file_get_contents('./data/blacklist.json');
    $blacklist = json_decode($blacklistfile, false);

    $isbanned = false;
    foreach($blacklist as $banned){
        if($banned->id == $user->id) {
            $isbanned = true;
        }
    }

    $found = FALSE;
    if($isbanned == false) {
        foreach ($data->roles as $field) {
            if ($field == ALPHA_AUTHORIZED_ROLE_ID) {
                $found = TRUE;
                echo <<<EOL

<!DOCTYPE html>
<head>
<meta content="text/html;charset=utf-8" http-equiv="Content-Type">
<meta content="utf-8" http-equiv="encoding">
<link rel="stylesheet" type="text/css" href="../css/craft2.css">
<link href="https://fonts.googleapis.com/css?family=Aldrich&display=swap" rel="stylesheet"> 
</head>
<title>Dual Universe Crafting Calculator r0.16</title>
<html>
<body>
<button id="skillsButton">Skills</button>
<span class="nav"><a href="http://dual.sh/index.php">Back to Dual.sh</a></span>
<div class="main-container flex-container">
	<div class="flex-box1 section-divider">
		<div class="section-header list-item">Your Inventory</div>
		<br>
		<div id="invList" class="grid-container1">
			<div></div>
			<div class="list-header">Item Name</div>
			<div class="list-header">Quantity</div>
		</div>
		<button id="invAddBut">+ Add More Items +</button>
	</div>
	<div class="flex-box1 section-divider">
		<div class="section-header list-item">Items To Craft</div>
		<br>
		<div id="cftList" class="grid-container1">
			<div></div>
			<div class="list-header">Item Name</div>
			<div class="list-header">Quantity</div>
		</div>
		<button id="cftAddBut">+ Add More Items +</button>
	</div>
	<div class="flex-box1 section-divider">
		<div class="section-header list-item">Required Ore</div>
		<span>Total Ore: <span id="totalOre"></span> Liters</span>
		<br>
		<div id="oreList" class="grid-container2">
			<div class="list-header">Item Name</div>
			<div class="list-header">Quantity</div>
			<div></div>
		</div>
	</div>
	<div class="flex-box2 section-divider">
		<div class="section-header list-item">Crafting Queue</div>
		<span>Total Time: <span id="totalTime"></span></span>
		<br>
		<div id="queueList" class="grid-container3">
			<div class="list-header">Item Name</div>
			<div class="list-header">Quantity</div>
			<div class="list-header">Time</div>
			<div></div>
		</div>
	</div>
</div>

<!-- Item selection modal -->
<div id="itemsModal" class="modal">

  <!-- Modal content -->
  <div class="modal-content">
    <div class="modal-header">
      <span id="itemsModalClose" class="modal-close">&times;</span>
      <h2>Item Selection</h2>
    </div>
    <div id="itemAccordion" class="modal-body">
    </div>
  </div>

</div>

<!-- skills modal -->
<div id="skillsModal" class="modal">

  <!-- Modal content -->
  <div class="modal-content">
    <div class="modal-header">
      <span id="skillsModalClose" class="modal-close">&times;</span>
      <h2>Skill Selection</h2>
    </div>
    <div id="skillAccordion" class="modal-body">
    </div>
  </div>

</div>

<script src="../js/Crafting_Calc r016.js"></script>
<script src="../js/Crafting_Calc_interface.js"></script>
	
</body>
</html>


EOL;
            }
        }
    }

    if ($found == FALSE) {
        echo '<h3>Unauthorized</h3>';
        echo '<p><a href="?action=logout">Log Out</a></p>';

    }
} else {
    echo '<h3>You must login before you can view this page, taking you back to the homepage now.</h3>';
    echo '<p>If this page does not automatically redirect you, <a href="http://dual.sh/index.php">click here.</a></p>';
    header('Refresh: 5; URL=http://dual.sh/index.php');
}

?>
