<!-- This is a common code snippet to show the messages -->
<!-- It takes three global parameters held in the global session -->
<!-- $_SESSION['G']->messages = contains all the messages loaded at system start -->
<!-- $_SESSION['error_code'] = the message to be shown, loaded in the controller finding the error -->
<!-- $_SESSION['substitution_parms'] = contains up to three substitution parameters, loaded in the controller finding the error -->

<!-- output messages -->

<?php
// initialise
$message																=	array();
$count																	=	0;

// get message from global message array if not empty
if (isset($_SESSION['error_code']) && $_SESSION['error_code'] !== '' && isset($_SESSION['G']->messages[$_SESSION['error_code']]))
{
	// load message
	$message															=	$_SESSION['G']->messages[$_SESSION['error_code']];

	// test for substitution parameters
	if (isset($_SESSION['substitution_parms']))
	{
		// get substitution parameters
		while ($count <= 2)
		{
			// if not empty, search message desription for substitution
			if (isset($_SESSION['substitution_parms'][$count]))
			{
				// search message description for substitution and replace
				$message[2]												=	str_replace("$".$count, $_SESSION['substitution_parms'][$count], $message[2]);
			}

			// increment count
			$count														=	$count	+	1;
		}
	}

	// display message if message array has required elements
	if (isset($message[1]) && isset($message[2])) {
	?>
	<div class="<?php echo $message[1]; ?>" id="auto_message">
		<?php echo /*$message[0].' => '.*/$message[2]; ?>
	</div>
	<?php if ($message[1] === 'success_message'): ?>
	<script>setTimeout(function(){ var m=document.getElementById('auto_message'); if(m) m.style.transition='opacity 0.3s'; m.style.opacity='0'; setTimeout(function(){ m.style.display='none'; },300); },1000);</script>
	<?php endif; ?>
<?php
	}
}

// unset error code and substitution messages
unset($_SESSION['error_code']);
$_SESSION['substitution_parms']											=	array();
?>
