<?php
class Vapeself_model extends CI_Model
{
    //add_ventes_into_ospos_sales_distributeur() :controllers
    //insert_VS_sales_into_db_sales_distributeur
    function insert_VS_sales_into_db_vs_sales($data_ventes_insert)
    {
        //insert sales data into table ospos_vs_sales 
        return $this->db->insert('vs_sales', $data_ventes_insert);
    }

    function insert_VS_credit_into_db_vs_credit($data_credit_insert)
    {
        //insert credit data into table ospos_vs_credit
        return $this->db->insert('vs_credit', $data_credit_insert); 
    }

    function get_vs_solde_card($customer_id)
    {
        //get credit for customer
        $this->db->select('votreid, solde, datecredit, date_add_table');
        $this->db->from('vs_credit');
        $this->db->where('votreid', strval($customer_id));
        $this->db->order_by('datecredit', 'DESC');
        $this->db->limit(1);

        $vs_solde_card = $this->db->get()->result_array();
		return $vs_solde_card;
    }

    function get_new_sales()
    {
        //récuperation des nouvelles ventes
        $this->db->from('vs_sales');
        $this->db->where('validate', 0);
        $this->db->order_by('datevente', 'DESC');

        $vs_new_sales = $this->db->get()->result_array();
        return $vs_new_sales;
    }

    function get_new_credit()
    {
        //récuperation des nouveaux credits
        $this->db->from('vs_credit');
        $this->db->where('validate', 0);
        $this->db->order_by('datecredit', 'DESC');

        $vs_new_credit = $this->db->get()->result_array();
        return $vs_new_credit;
    }

    function get_vs_all_new_sale()
    {
        //get all from ospos_vs_sale && ospos_vs_credit
    /*    $this->db->select('
            vs_sales.vs_sale_id,
            vs_sales.id_vente,
            vs_sales.datevente,
            vs_sales.id_client as vs_client_sales,
            vs_sales.totalttc,
            vs_sales.remise,
            vs_sales.recredit,
            vs_sales.emplacement,
            vs_sales.liste,
            vs_sales.modifie,
            vs_sales.mon_id,
            vs_sales.date_add_table as date_add_table_sales,
            vs_sales.validate as validate_sales,
            vs_credit.vs_sale_id as vs_client_credit,
            vs_credit.id_credit,
            vs_credit.votreid,
            vs_credit.datecredit,
            vs_credit.montant,
            vs_credit.solde,
            vs_credit.date_add_table as date_add_table_credit,
            vs_credit.validate as validate_credit
        ');//*/
        $this->db->select('
            vs_sales.vs_sale_id,
            vs_sales.id_vente,
            vs_sales.datevente,
            vs_sales.id_client as vs_client_sales,
            vs_sales.totalttc,
            vs_sales.remise,
            vs_sales.recredit,
            vs_sales.emplacement,
            vs_sales.liste,
            vs_sales.modifie,
            vs_sales.mon_id,
            vs_sales.modereglement,
            vs_sales.date_add_table as date_add_table_sales,
            vs_sales.validate as validate_sales,
        ');
        $this->db->from('vs_sales');
    //    $this->db->join('vs_credit', 'vs_credit.vs_sale_id = vs_sales.vs_sale_id');
        $this->db->where('vs_sales.validate', 0);
        $this->db->order_by('date_add_table_sales');

        $vs_new = $this->db->get()->result_array();
        return $vs_new;
    }
/*
    ospos_vs_sales.vs_sale_id,
    ospos_vs_sales.id_vente,
    ospos_vs_sales.datevente,
    ospos_vs_sales.id_client as vs_client_sales,
    ospos_vs_sales.totalttc,
    ospos_vs_sales.remise,
    ospos_vs_sales.recredit,
    ospos_vs_sales.emplacement,
    ospos_vs_sales.liste,
    ospos_vs_sales.modifie,
    ospos_vs_sales.mon_id,
    ospos_vs_sales.date_add_table as date_add_table_sales,
    ospos_vs_sales.validate as validate_sales,
    ospos_vs_credit.vs_sale_id as vs_client_credit,
    ospos_vs_credit.id_credit,
    ospos_vs_credit.votreid,
    ospos_vs_credit.datecredit,
    ospos_vs_credit.montant,
    ospos_vs_credit.solde,
    ospos_vs_credit.date_add_table as date_add_table_credit,
    ospos_vs_credit.validate as validate_credit//*/

    function get_vs_sale_id($vs_sale_id)
    {
       /* $this->db->select('
            vs_sales.vs_sale_id,
            vs_sales.id_vente,
            vs_sales.datevente,
            vs_sales.id_client as vs_client_sales,
            vs_sales.totalttc,
            vs_sales.remise,
            vs_sales.recredit,
            vs_sales.emplacement,
            vs_sales.liste,
            vs_sales.modifie,
            vs_sales.mon_id,
            vs_sales.date_add_table as date_add_table_sales,
            vs_sales.validate as validate_sales,
            vs_credit.vs_sale_id as vs_client_credit,
            vs_credit.id_credit,
            vs_credit.votreid,
            vs_credit.datecredit,
            vs_credit.montant,
            vs_credit.solde,
            vs_credit.date_add_table as date_add_table_credit,
            vs_credit.validate as validate_credit
        ');//*/
        $this->db->select('
            vs_sales.vs_sale_id,
            vs_sales.id_vente,
            vs_sales.datevente,
            vs_sales.id_client as vs_client_sales,
            vs_sales.totalttc,
            vs_sales.remise,
            vs_sales.recredit,
            vs_sales.emplacement,
            vs_sales.liste,
            vs_sales.modifie,
            vs_sales.mon_id,
            vs_sales.modereglement,
            vs_sales.date_add_table as date_add_table_sales,
            vs_sales.validate as validate_sales
        ');
        $this->db->from('vs_sales');
    //    $this->db->join('vs_credit', 'vs_credit.vs_sale_id = vs_sales.vs_sale_id');
        $this->db->where('vs_sales.vs_sale_id', $vs_sale_id);

        $vs_sale_id_line = $this->db->get()->result_array();
        return $vs_sale_id_line;
    }

    function update_validate_sale_ok($vs_sale_id)
    {
        $this->db->select('vs_sales.validate');
        $this->db->where('vs_sale_id', $vs_sale_id);
        $value = array('validate' => 1);
        $this->db->update('vs_sales',  $value);
    }




}