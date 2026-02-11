<?php
	// output header
	$this->load->view("partial/header");
?>

<div class="body_cadre_gris">

<?php
	// get userdata data
	$values					=	array();
	$values					=	$this->session->all_userdata();
	$image_path				=	base_url().'barcodes/'.$values['item_number'].'.jpeg';

	// messages
	if($values['success_or_failure'] == 'S')
		{
			echo "<div class='success_message'>".$values['message']."</div>";
		}


	if($values['success_or_failure'] == 'F')
		{
			echo "<div class='error_message'>".$values['message']."</div>";
		}
?>

<img src="<?php echo $image_path; ?>" alt="Barcode" style="width:350px;height:280px">

</div>
<?php
	// show the footer
    $this->load->view("partial/pre_footer");
	$this->load->view("partial/footer");
?>


