<?php
class Message extends CI_Model 
{	
	// load global messages to session
	function load_messages()
	{
		// initialise
		if (!isset($_SESSION['G']) || !is_object($_SESSION['G'])) {
			$_SESSION['G'] = new stdClass();
		}
		unset($_SESSION['G']->messages);
		$messages_list													=	array();
		
		// open definitions file
		$fp																=	fopen("/var/www/html/wrightetmathon/application/messages/messages.def", "r");
		
		// test if file was opened
		if (!$fp)
		{
			$_SESSION['error_code']										=	'05620';
			redirect("home");
		}
		
		// test EOF
		while(!feof($fp)) 
		{
			// read a line
			$line														=	fgets($fp);
			
			// ignore comment lines
			if (strpos($line, "//") === false) 
			{
				// explode the line to find the fields required
				// $messages[0] = message_id
				// $messages[1] = message class
				// $messages[2] = message description
				// $messages[3] = deleted
				// $messages[4] = unused, should be blank
				$messages												=	explode("->", $line);
				
				// test active
				if (isset($messages[3]) && $messages[3] == '0')
				{
					// load the messages
					$messages_list[$messages[0]]			 			=	$messages;
				}
			}
		}
		
		// at EOF close the file
		fclose($fp);
		
		// load the bulk action pick list to the session
		$_SESSION['G']->messages										=	$messages_list;
		
		// return
		return;
	}
}
?>
