<?php $this->load->view("partial/header_popup"); ?>


<div open class="fenetre modale" style="
        position: absolute;
    left: 50%;
    right: 50%;
    top: 5%;
    transform: translate(-50%,50%);
    width: 600px;
    z-index: 101;">
<!-- width: 862px; -->
    <!-- Header fenetre -->
    <div class="fenetre-header">
	<span id="confirm_title" class="fenetre-title">
        <?php //echo $this->lang->line('modules_'.$_SESSION['controller_name']).'  '.$this->lang->line('sales_CN_select_invoice');
        //echo 'Etes-vous sûr de lancer la procédure d\'extinction de l\'ordinateur ?' ;
        echo $this->lang->line('common_shutdown_warning_question');
        ?>
    </span>
        <?php
//        include('../wrightetmathon/application/views/partial/show_exit.php');
        ?>


    </div>



    <!-- Contenu de la fenetre-->
    <div class="fenetre-content">



        <div class="centrepage">


            <div class="blocformfond creationimmediate">
	<?php
		include('../wrightetmathon/application/views/partial/show_messages.php');
	?>
	
	<fieldset >
		<!-- show enter invoice -->
		<div>
			<?php 
				echo form_open("home/shutdown_all");
				?>
					<table class="table_center">
						<thead>
							<tr>
								<th><?php //echo $this->lang->line('sales_CN_select_invoice'); ?></th>
							</tr>
						</thead><!--
                        Les porcédures suivantes vont s'executer: <br>
                        1. Sauvegarde de la base de données <br>
                        2. Mise à jour du position <br>
                        3. Mise à jour base article <br>
                        4. Extinction de l'ordinateur <br> <!--  -->
                        <?php echo $this->lang->line('common_shutdown_warning_info'); ?>
						<tbody>
							<tr>									
								<td style='text-align:center'><?php /*echo form_input	(array	(
																					'name'=>'CN_original_invoice',
																					'id'=>'CN_original_invoice',
																					'value'=>$_SESSION['CSI']['SHV']->CN_original_invoice,
																					'style'=>'text-align:right;font-size:20pt',
																					'size'=>'10','autofocus'		=>	'autofocus',));
                                //*/											
                                
                                
                                
                                ?></td>
							</tr>
							<tr>	
                            <td><input type="radio" id="shutdaown_all_yes" name="shutdown_all" value="yes" ><label for="shutdaown_all_yes" ><?php echo $this->lang->line('common_yes'); ?><label></td>
                            <td><input type="radio" id="shutdaown_all_no" name="shutdown_all" value="no" checked="checked" ><label for="shutdaown_all_no" ><?php echo $this->lang->line('common_no'); ?><label></td>
                            </tr>
                            <tr><td><br></td></tr>
						</tbody>	
					</table>
<!--
                    <h1>Merci de ne pas éteindre l'ordinateur pendant la procédure</h1><br>
                    <center><h2>ATTENTION: <br> NE PAS ETIENDRE L'ORDINATEUR PENDANT LA PROCEDURE<h2></center> <!--  -->
                    <center><h2><?php echo $this->lang->line('common_shutdown_warning_alert'); ?><h2></center>
        </div>
    </fieldset>
            </div>
                <div class="txt_milieu">
                    <?php /*
                    $target	=	'target="_self"';
                    echo anchor			(
                        'common_controller/common_exit/','<div class="btretour btlien">'.$this->lang->line('common_reset').'</div>',
                        $target
                    );//*/
                    ?>

                    <?php echo form_submit					(array	(
                            'name'	=>	'submit',
                            'id'	=>	'submit',
                            'value'	=>	$this->lang->line('common_submit'),
                            'class'	=>	'btsubmit'
                        ));
                        ?>
                </div>
				<?php
				echo form_close();
			?>
		</div>
        </div>
        </div>
<!-- div for spinner -->
<div id="spinner" class="spinner2" style="display:none;">
    <!--  <img id="img-spinner" src="<?php /*echo base_url();*/?>images/M&Wloader.gif" alt="Loading"/>-->

    <div id="floatingCirclesG">
        <div class="f_circleG" id="frotateG_01"></div>
        <div class="f_circleG" id="frotateG_02"></div>
        <div class="f_circleG" id="frotateG_03"></div>
        <div class="f_circleG" id="frotateG_04"></div>
        <div class="f_circleG" id="frotateG_05"></div>
        <div class="f_circleG" id="frotateG_06"></div>
        <div class="f_circleG" id="frotateG_07"></div>
        <div class="f_circleG" id="frotateG_08"></div>
    </div>
</div>

<!-- script for spinner -->
<script type="text/javascript">
    $(document).ready(function()
    {
        $('.sablier').click(function()
        {
            $('#spinner').show();
        });
    });
</script>
<script type="text/javascript" language="javascript">
$(document).ready(function()
{
	$("#show_spinner").click(function()
	{		
		$('#spinner_on_bar').show();
	});	
});
</script>
