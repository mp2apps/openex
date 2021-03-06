<?php
include 'models/config.php';
include 'models/chat.config.php';
$id = $loggedInUser->user_id;
$username = $loggedInUser->display_username;
$do = addslashes(strip_tags($_GET['do']));
if($do === 'load'){
	$db->query("SELECT * FROM (SELECT * FROM messages WHERE `hidden`='0' ORDER BY `id` DESC LIMIT 100) as last100 ORDER BY id");
	$data = $db->GET();
	foreach($data as $key => $value) {
		if(!isUserMod($id) and !isUserAdmin($id)) {
			$color = htmlentities($value['color']);
			$user = htmlentities($value['username']);
			$msg = htmlentities($value['message']);
			echo "<li id='msg_row'><b id='u_name_chat' style='color: ".$color.";'>".$user."</b>: ".$msg."</li>";
		}else{
			$color = htmlentities($value['color']);
			$user = htmlentities($value['username']);
			$msg = htmlentities($value['message']);
			$todelete = $db->real_escape_string($value['id']);
			echo "<li id='msg_row'><b id='u_name_chat' style='color: ".$color.";'>".$user."</b>: ".$msg."<a color='blue' href='#' rel=".$todelete." class='delete' onClick='deleteChat(this);'>delete</a></li>";
		}
	}
	?>
	<script>
		<?php 
		if(isUserMod($id) || isUserAdmin($id)) 
		{
		?>
			function deleteChat(t) {
				console.log("Clicked delete");
				var toDEL = $(t).parent();
				var id = $(t).attr('rel');
				console.log(id);
				
				$.post('ajax.php?do=delete', {id: id})
					.done(function(data) {
						$(toDEL).hide();
					});
			}
		<?php
		}
		?>
	</script>
	<?php
}
elseif($do === 'post'){
	if (isUserCBanned($id)) {

	die();
	
	}else{

		if(isUserAdmin($id)) 
		{
			$color = "#0404B4";
		}
		else if (isUserMod($id))
		{
			$color = "#B43104";
		} 
		else 
		{
			$color = "#000000";
		}
		$color_ = $db->real_escape_string(htmlentities(($color)));
		$user = $db->real_escape_string(htmlentities(($username))); 
		$message = $db->real_escape_string(strip_tags(($_POST['message']), '<a>'));
		$timestamp = $db->real_escape_string(gettime());
		/*
		if(isUserMod($id) || isUserAdmin($id)) {
			if (strpos($message, '/') !== FALSE) {
				/*here we'll add the code to ban users from areas of the site
				 *based on commands /sb <siteban> /cb <chatban> /ub <unban>
				 *format is simple command + user, eg /sb testuser would siteban testuser
				 */
				 /*
				$cmd = explode($message, '/' ' ');
				$message = print_r($cmd, false);
			}else{
				
			}
		}
		*/
		if($color_ == null){
			die("no username color");
		}
		if($user == null){
			die("not logged in");
		}
		if($message == null){
			die("no message entered");
		}
		if($timestamp == null){
			die("no timestamp");
		}
		$db->Query("INSERT INTO messages (color, username, message, timestamp) VALUES ('$color_','$user','$message','$timestamp')");
	}	

}
elseif($do === 'delete'){
	if(isUserMod($id) || isUserAdmin($id)) {
		$idz = $db->real_escape_string(strip_tags($_POST['id']));
		if($idz == null) {
			die("invalid request");
		}
		$query = $db->Query("UPDATE messages SET `hidden`='1' WHERE `id`='$idz'");
		$result = $db->GET($query);
		print($result);
	}else{
		die("user is not admin or moderator");
	}
}else{
	die("invalid operation");
}