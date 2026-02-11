<!-- -->
<!-- Dispay the configuration screen -->
<!-- -->
<!-- css for link in /var/www/html/wrightetmathon/css2/liens.css -->
<!-- output the header -->
<?php $this->load->view("partial/head"); ?>
<?php $this->load->view("partial/header_banner"); ?>

<div id="wrapper" class="wlp-bighorn-book">
<?php 
if(isset($_SESSION['G']->login_employee_id))
{
    $redirect = site_url("sales");
}
if(!isset($_SESSION['G']->login_employee_id))
{
    $redirect = site_url();
}
?>
  

    <?php //$this->load->view("partial/header_menu"); ?>

    <div class="wlp-bighorn-book">

        <div class="wlp-bighorn-book-content">

            <main id="login_page" class="wlp-bighorn-page-unconnect" role="main">

                <!--Contenu background gris-->
                <div class="body_page" id="loginPage">
                   

                        <div class="body_colonne">
                            <h2><?php echo $this->lang->line('modules_home');?></h2>
                            <div class="body_cadre_gris">
                            <div id="new_button"class="btnewc">
                
    <a href="<?php echo $redirect; ?>" title="retour">
      <div class='btnew c_btcouleur' style='float: left;'><span> <?php echo $this->lang->line('common_return'); ?> </span></div>
    </a>
  </div>
                <table class="table_center" width="100%">
                <td>    
                <table >
                    <tr>
                        <td align="left" >
                            <h1> <a href="<?php echo site_url("home/display_help_procedures_pdf/1"); ?>" > <?php echo $this->lang->line('display_help_procedures_pdf') . ' ' . $this->lang->line('number') . '1'; ?> </a></h1>
                        </td>
                        <td align="left" >
                            <a href="<?php echo site_url("home/display_help_procedures_pdf/1"); ?>" > <?php echo '<div class="help_aide"> - Changement Multiple de prix de Vente</div>'; ?> </a>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" >
                            <h1> <a href="<?php echo site_url("home/display_help_procedures_pdf/2"); ?>" > <?php echo $this->lang->line('display_help_procedures_pdf') . ' ' . $this->lang->line('number') . '2'; ?> </a></h1>
                        </td>                        
                        <td align="left" >
                            <a href="<?php echo site_url("home/display_help_procedures_pdf/2"); ?>" > <?php echo '<div class="help_aide"> - Inventaire Magasin</div>'; ?> </a></h1>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" >
                            <h1> <a href="<?php echo site_url("home/display_help_procedures_pdf/3"); ?>" > <?php echo $this->lang->line('display_help_procedures_pdf') . ' ' . $this->lang->line('number') . '3'; ?> </a></h1>
                        </td>
                        <td align="left" >
                            <a href="<?php echo site_url("home/display_help_procedures_pdf/3"); ?>" > <?php echo '<div class="help_aide"> - Mise à jour du prix d’un produit</div>'; ?> </a>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" >
                            <h1> <a href="<?php echo site_url("home/display_help_procedures_pdf/4"); ?>" > <?php echo $this->lang->line('display_help_procedures_pdf') . ' ' . $this->lang->line('number') . '4'; ?> </a></h1>
                        </td>
                        <td align="left" >
                            <a href="<?php echo site_url("home/display_help_procedures_pdf/4"); ?>" > <?php echo '<div class="help_aide"> - Vente / Utilisation de la Carte CADEAU</div>'; ?> </a>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" >
                            <h1> <a href="<?php echo site_url("home/display_help_procedures_pdf/5"); ?>" > <?php echo $this->lang->line('display_help_procedures_pdf') . ' ' . $this->lang->line('number') . '5'; ?> </a></h1>
                        </td>
                        <td align="left" >
                            <a href="<?php echo site_url("home/display_help_procedures_pdf/5"); ?>" > <?php echo '<div class="help_aide"> - Fusionner deux articles</div>'; ?> </a>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" >
                            <h1> <a href="<?php echo site_url("home/display_help_procedures_pdf/6"); ?>" > <?php echo $this->lang->line('display_help_procedures_pdf') . ' ' . $this->lang->line('number') . '6'; ?> </a></h1>
                        </td>
                        <td align="left" >
                            <a href="<?php echo site_url("home/display_help_procedures_pdf/6"); ?>" > <?php echo '<div class="help_aide"> - Connexion du Tiroir-Caisse</div>'; ?> </a>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" >
                            <h1> <a href="<?php echo site_url("home/display_help_procedures_pdf/7"); ?>" > <?php echo $this->lang->line('display_help_procedures_pdf') . ' ' . $this->lang->line('number') . '7'; ?> </a></h1>
                        </td>
                        <td align="left" >
                            <a href="<?php echo site_url("home/display_help_procedures_pdf/7"); ?>" > <?php echo '<div class="help_aide"> - Envoie du ticket de caisse par Mail</div>'; ?> </a>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" >
                            <h1> <a href="<?php echo site_url("home/display_help_procedures_pdf/8"); ?>" > <?php echo $this->lang->line('display_help_procedures_pdf') . ' ' . $this->lang->line('number') . '8'; ?> </a></h1>
                        </td>
                        <td align="left" >
                            <a href="<?php echo site_url("home/display_help_procedures_pdf/8"); ?>" > <?php echo '<div class="help_aide"> - Recherche Produit</div>'; ?> </a>
                        </td>
                    </tr>                    
                    <tr>
                        <td align="left" >
                            <h1> <a href="<?php echo site_url("home/display_help_procedures_pdf/9"); ?>" > <?php echo $this->lang->line('display_help_procedures_pdf') . ' ' . $this->lang->line('number') . '9'; ?> </a></h1>
                        </td>
                        <td align="left" >
                            <a href="<?php echo site_url("home/display_help_procedures_pdf/9"); ?>" > <?php echo '<div class="help_aide"> - Bon de Commande automatique</div>'; ?> </a>
                        </td>
                    </tr>                    
                    <tr>
                        <td align="left" >
                            <h1> <a href="<?php echo site_url("home/display_help_procedures_pdf/10"); ?>" > <?php echo $this->lang->line('display_help_procedures_pdf') . ' ' . $this->lang->line('number') . '10'; ?> </a></h1>
                        </td>
                        <td align="left" >
                            <a href="<?php echo site_url("home/display_help_procedures_pdf/10"); ?>" > <?php echo '<div class="help_aide"> - Commande Directe Fournisseur – E.D.I.</div>'; ?> </a>
                        </td>
                    </tr>                    
                    <tr>
                        <td align="left" >
                            <h1> <a href="<?php echo site_url("home/display_help_procedures_pdf/11"); ?>" > <?php echo $this->lang->line('display_help_procedures_pdf') . ' ' . $this->lang->line('number') . '11'; ?> </a></h1>
                        </td>
                        <td align="left" >
                            <a href="<?php echo site_url("home/display_help_procedures_pdf/11"); ?>" > <?php echo '<div class="help_aide"> - Versement Caisse/ Coffre / Banque</div>'; ?> </a>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" >
                            <h1> <a href="<?php echo site_url("home/display_help_procedures_pdf/12"); ?>" > <?php echo $this->lang->line('display_help_procedures_pdf') . ' ' . $this->lang->line('number') . '12'; ?> </a></h1>
                        </td>
                        <td align="left" >
                            <a href="<?php echo site_url("home/display_help_procedures_pdf/12"); ?>" > <?php echo '<div class="help_aide"> - Nouvel Inventaire Magasin</div>'; ?> </a>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" >
                            <h1> <a href="<?php echo site_url("home/display_help_regles_pdf/1"); ?>" > <?php echo $this->lang->line('display_help_regles_pdf') . ' ' . $this->lang->line('number') . '1'; ?> </a></h1>
                        </td>
                        <td align="left" >
                            <a href="<?php echo site_url("home/display_help_regles_pdf/1"); ?>" > <?php echo '<div class="help_aide"> - Changement Employé</div>'; ?> </a>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" >
                            <h1> <a href="<?php echo site_url("home/display_help_info_pdf/1"); ?>" > <?php echo $this->lang->line('display_help_info_pdf') . ' ' . $this->lang->line('number') . '1'; ?> </a></h1>
                        </td>
                        <td align="left" >
                            <a href="<?php echo site_url("home/display_help_info_pdf/1"); ?>" > <?php echo '<div class="help_aide"> - Nouveautés Juin 2023</div>'; ?> </a>
                        </td>
                    </tr>
                </table>
                </td>
                <td>
                <?php if(isset($_SESSION['display_help_procedures_pdf']) && $_SESSION['display_help_procedures_pdf'] != '0')
                        {
                            $display_help_procedures_pdf = $_SESSION['display_help_procedures_pdf'];
                                ?>
                            <td align="right" >
                                <object data="<?php echo "Procedures/POS_Procedure" . "$display_help_procedures_pdf" . ".pdf" ?>" type="application/pdf" width="800px" height="800px" internalinstanceid="23"></object>
                            </td>
                           <?php 
                        } ?>
                </td>

                <td>
                <?php if(isset($_SESSION['display_help_regles_pdf']) && $_SESSION['display_help_regles_pdf'] != '0')
                        {
                            $display_help_regles_pdf = $_SESSION['display_help_regles_pdf'];
                                ?>
                            <td align="right" >
                                <object data="<?php echo "Procedures/POS_Regle" . "$display_help_regles_pdf" . ".pdf" ?>" type="application/pdf" width="800px" height="800px" internalinstanceid="23"></object>
                            </td>
                           <?php 
                        } ?>
                </td>
                <?php if(isset($_SESSION['display_help_info_pdf']) && $_SESSION['display_help_info_pdf'] != '0')
                        {
                            $display_help_info_pdf = $_SESSION['display_help_info_pdf'];
                                ?>
                            <td align="right" >
                                <object data="<?php echo "Procedures/POS_Info" . "$display_help_info_pdf" . ".pdf" ?>" type="application/pdf" width="800px" height="800px" internalinstanceid="23"></object>
                            </td>
                           <?php 
                        } ?>
                </td>
                </table>
              <!--  <object data="flash_info_publish_me.pdf" type="application/pdf" width="800px" height="800px" internalinstanceid="23"></object>
 <!--   --> 
                            </div>
                        </div>

                    </div>
                </div>
            </main></div></div>
</div>
<?php
// show the footer
$this->load->view("partial/footer");
?>
