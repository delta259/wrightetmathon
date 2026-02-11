<?php $this->load->view("partial/header_popup"); ?>


<dialog open class="fenetre modale cadre" style="      position: absolute;

    width: 560px;
    ">
    <!-- Header fenetre -->
    <div class="fenetre-header">
	<span id="page_title" class="fenetre-title">
		<?php echo $_SESSION['title']; ?>
	</span>

        <?php
        // set origin
        $_SESSION['origin']												=	"CA";
        include('../wrightetmathon/application/views/partial/show_exit.php');
        ?>


    </div>

    <!---CONTENT-->
    <div class="fenetre-content">



        <div class="centrepage">



            <div class="blocformfond creationimmediate">





	
		<?php
			include('../wrightetmathon/application/views/partial/show_messages.php');
		?>
		
		<!-- open form -->
		<?php echo form_open("reports/inventory_low_validation_by_date"); ?>		

			<!-- output supplier select -->
                <fieldset>
			<div>
				<label id="supplier_label" for="supplier_id"><?php echo $this->lang->line('recvs_supplier'); ?></label>
					<?php echo form_dropdown	(
												'supplier_id',	 
												$_SESSION['G']->supplier_pick_list, 
												$this->config->item('default_supplier_id'),
												'style="font-size:15px;margin-bottom: 20px;"'
                                                ); ?>
                                                

                
                <label id="Methodologie" for="Methodologie" ><?php echo $this->lang->line('Methodologie'); ?></label>
                    <br>
                    <input type="radio" name="metho" id="metho_by_stock" value="metho_by_stock" >
                    <label for="coucou_1" > <?php echo $this->lang->line('metho_by_stock'); ?></label><br>
                    <input type="radio" name="metho" id="metho_by_sales" value="metho_by_sales" >
                    <label for="coucou_2" > <?php echo $this->lang->line('metho_by_sales'); ?></label><br>
<input type="date" name="date_start" value="<?php echo date("Y-m-d"); ?>" id="date_start" size="1" >
 - 
<input type="date" name="date_end" value="<?php echo date("Y-m-d"); ?>" id="date_end" size="1" >
			</div>
                </fieldset>




            </div>
                    <div class="txt_milieu">

                        <?php
                        $target	=	'target="_self"';
                        echo anchor			(
                            'common_controller/common_exit/','<div class="btretour btlien ">'.$this->lang->line('common_reset').'</div>',
                            $target
                        );
                        ?>
			<?php
			echo form_submit					(	array	(
															'name'	=>	'generate_report',
															'id'	=>	'generate_report',
															'value'	=>	$this->lang->line('common_submit'),
															'class'	=>	'btsubmit sablier'
															)
												);
                        ?></div>
		</form>

        </div></div></dialog>



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
	$("#generate_report").click(function()
	{
		$('#spinner_on_bar').show();
	});
});
</script>
