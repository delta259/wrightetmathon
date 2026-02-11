

<?php

	// prepare the data for the display
	$parm						=	$_SERVER['QUERY_STRING'];
	$pieces						=	explode('=', $parm);
	$parm						=	urldecode($pieces[1]);
	
	$pieces						=	explode('/', $parm);
	
	$customer					=	str_replace('_', ' ', $pieces[0]);

	$customer_comments			=	str_replace('_', ' ', $pieces[1]);

?>



<html>
<head>
<title><?php echo 'Customer comments'; ?></title>
</head>

<body bgcolor="#FFFFC6">
<blockquote>
<H2><?php echo 'Commentaires pour '; ?> <br> <?php echo $customer; ?></H2>
<font size="4">
	
<?php
	echo $customer_comments;
?>

</font>


</body>
</html>
