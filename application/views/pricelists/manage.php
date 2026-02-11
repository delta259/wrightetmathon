<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
$(document).ready(function()
{
    enable_search('<?php echo site_url("$controller_name/suggest")?>','<?php echo $this->lang->line("common_confirm_search")?>');
});


</script>
<div class="body_cadre_gris">


        <!-- output messages if not modal -->
        <?php
        if (!isset($_SESSION['show_dialog']) || $_SESSION['show_dialog'] == 0)
        {
            include('../wrightetmathon/application/views/partial/show_messages.php');
        }
        ?>

        <!-- table header -->



        <div class="submenu">
            <ul>
                <li class="search" >
                    <?php echo form_open("$controller_name/search",array('id'=>'search_form')); ?>
                    <input id='search'  placeholder="Recherche" width=" 250px;"tabindex="5" size="18" name='search' type='text' class="champ_search" title="rechercher" value="" >
                    <img src="<?php echo $_SESSION['url_image'];?>/search.png" class="img_search"    style=" margin-bottom: -16.5px;margin-left: -46px;"/>
                    </form>
                </li>

                <span class="btnewc">
                    <?php
                    // set origin
                    $_SESSION['origin']												=	"AS";
                    include('../wrightetmathon/application/views/partial/show_buttons.php');
                    ?>

        </span>
            </ul>
        </div>


<!-- Show the table -->
<?php
	echo $manage_table;
?>
<div><!-- Output Links -->
    <?php echo $links;?></div>

<!-- Close the form -->
<?php
	echo form_close();
?>
</div>
<?php
// this is the modal dialog output when updating an existing record or adding a new one.
if (($_SESSION['show_dialog'] ?? 0) == 1)
{
	include('../wrightetmathon/application/views/pricelists/form.php');
}
?>



<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>
