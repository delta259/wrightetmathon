<?php
class Pricelists extends CI_Controller
{
	function index()
	{
		// set module id
		$_SESSION['module_id']											=	"19";

		// set data array
		$data = array();

		// manage session
		$_SESSION['controller_name']=	strtolower(get_class($this));
		unset($_SESSION['report_controller']);

		// set list title if undelete
		switch ($_SESSION['undel'] ?? 0)
		{
			case	1:
					$data['title']	=	$this->lang->line('common_undelete');
			break;

			default:
					$data['title']	=	'';
			break;
		}

		// set up the pagination
		$config						=	$this->Common_routines->set_up_pagination();
		$config['base_url'] 		= 	site_url('/pricelists/index');
		$config['total_rows'] 		= 	$this->Pricelist->count_all();
		$this						->	pagination->initialize($config);

		// setup output data
		$data['links']				=	$this->pagination->create_links();
		$data['controller_name']	=	strtolower(get_class($this));
		$data['form_width']			=	$this->Common_routines->set_form_width();
		$data['pricelist_data']		=	$this->Pricelist->get_all($config['per_page'], $this->uri->segment( $config['uri_segment'] ) );

		$this->load->view('pricelists/manage',$data);
	}

	function search()
	{
		$search		= $this->input->post('search');
		$pricelists	= $this->Pricelist->search($search);
		$html		= '';

		foreach ($pricelists->result() as $pl)
		{
			$default_badge = ($pl->pricelist_default === 'Y')
				? '<span style="background:#dcfce7;color:#166534;border:1px solid #22c55e;padding:2px 8px;border-radius:12px;font-size:0.8rem;font-weight:500;">Oui</span>'
				: '<span style="background:#fef2f2;color:#991b1b;border:1px solid #ef4444;padding:2px 8px;border-radius:12px;font-size:0.8rem;font-weight:500;">Non</span>';

			// currency name
			$currency_name = '';
			if (isset($_SESSION['G']->currency_pick_list[$pl->pricelist_currency])) {
				$currency_name = $_SESSION['G']->currency_pick_list[$pl->pricelist_currency];
			} else {
				$currency_name = $pl->pricelist_currency;
			}

			$html .= '<tr class="pl-row" data-href="'.site_url('pricelists/view/'.$pl->pricelist_id).'" style="cursor:pointer;">';
			$html .= '<td>'.htmlspecialchars($pl->pricelist_name).'</td>';
			$html .= '<td>'.htmlspecialchars($pl->pricelist_description).'</td>';
			$html .= '<td style="text-align:center;">'.htmlspecialchars($currency_name).'</td>';
			$html .= '<td style="text-align:center;">'.$default_badge.'</td>';
			$html .= '<td style="text-align:center;white-space:nowrap;">';
			$html .= '<a href="#" onclick="if(confirm(\'Supprimer cette liste de prix ?\')){window.location=\''.site_url('pricelists/delete/'.$pl->pricelist_id).'\';} return false;" title="Supprimer" style="text-decoration:none;">';
			$html .= '<svg width="18" height="18" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>';
			$html .= '</a></td>';
			$html .= '</tr>';
		}

		if ($pricelists->num_rows() == 0)
		{
			$html .= '<tr><td colspan="5" style="text-align:center;padding:20px;color:#64748b;">'.$this->lang->line('common_no_persons_to_display').'</td></tr>';
		}

		echo $html;
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Pricelist->get_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function view($pricelist_id=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']							=	new stdClass();
		$_SESSION['transaction_id']								=	$pricelist_id;

		// load currencies pick list
		$currency_pick_list					=	array();
		foreach($this->Currency->get_all()->result() as $row)
		{
			$currency_pick_list[$row->currency_id] =	$row->currency_name;
		}
		$_SESSION['currency_pick_list']		=	$currency_pick_list;

		// set origin
		switch ($origin)
		{
			case	'0':
					unset($_SESSION['origin']);
			break;

			default:
					$_SESSION['origin']							=	$origin;
			break;
		}

		// set data
		switch ($pricelist_id)
		{
			// create new
			case	-1:
					$_SESSION['$title']							=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']							=	1;
			break;

			// update existing
			default:
					$_SESSION['transaction_info']				=	$this->Pricelist->get_info($_SESSION['transaction_id']);

					switch ($_SESSION['undel'] ?? 0)
					{
						case	1:
								$_SESSION['$title']				=	$this->lang->line('common_undelete').'  '.$_SESSION['transaction_info']->pricelist_name;
						break;

						default:
								$_SESSION['$title']				=	$this->lang->line('common_edit').'  '.$_SESSION['transaction_info']->pricelist_name;
						break;
					}
					unset($_SESSION['new']);
			break;
		}

		$this->load->view('pricelists/form');
	}

	function save()
	{
		// save orignal data but only first time through
		if (($_SESSION['first_time'] ?? 0) != 1)
		{
			unset($_SESSION['original_pricelist_name']);
			$_SESSION['original_pricelist_name']						=	$_SESSION['transaction_info']->pricelist_name;

			unset($_SESSION['original_pricelist_default']);
			$_SESSION['original_pricelist_default']						=	$_SESSION['transaction_info']->pricelist_default;

			$_SESSION['first_time']										=	1;
		}

		// load input data
		$_SESSION['transaction_info']->pricelist_name					=	$this->input->post('pricelist_name');
		$_SESSION['transaction_info']->pricelist_description			=	$this->input->post('pricelist_description');
		$_SESSION['transaction_info']->pricelist_currency				=	$this->input->post('pricelist_currency');
		$_SESSION['transaction_info']->pricelist_default				=	$this->input->post('pricelist_default');
		$_SESSION['transaction_info']->deleted							=	0;
		$_SESSION['transaction_info']->branch_code						=	$this->config->item('branch_code');

		// do data verifications
		$this															->	verify();

		// if here then all checks succeeded so do the update
		$this															->	Pricelist->save();

		// reload pick list
		$this															->	Pricelist->load_pick_list();

		// test for added or updated and set appropriate message
		switch ($_SESSION['new'] ?? 0)
		{
			case	1:
					$_SESSION['error_code']								=	'05080';
			break;

			default:
					$_SESSION['error_code']								=	'05090';
			break;
		}

		unset($_SESSION['new']);
		unset($_SESSION['first_time']);
		redirect("pricelists");
	}

	function delete($pricelist_id)
	{
		if ($this->Pricelist->delete($pricelist_id))
		{
			$_SESSION['error_code']										=	'01655';
		}
		else
		{
			$_SESSION['error_code']										=	'00350';
		}

		$this->Pricelist->load_pick_list();
		redirect("pricelists");
	}

	function verify()
	{
		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_info']->pricelist_name)
			OR 	empty($_SESSION['transaction_info']->pricelist_description)
			OR 	empty($_SESSION['transaction_info']->pricelist_currency)
			OR 	empty($_SESSION['transaction_info']->pricelist_default)
			)
		{
			$_SESSION['error_code']			=	'00030';
			redirect("pricelists/view/" . $_SESSION['transaction_id']);
		}

		// check pricelist code duplicate only if new or changed
		if (($_SESSION['new'] ?? 0) == 1 OR $_SESSION['transaction_info']->pricelist_name != $_SESSION['original_pricelist_name'])
		{
			$count							=	$this->Pricelist->check_duplicate();
			if ($count > 0)
			{
				$_SESSION['error_code']		=	'05060';
				redirect("pricelists/view/" . $_SESSION['transaction_id']);
			}
		}

		// check pricelist default duplicate only if new or changed AND = y
		if ($_SESSION['transaction_info']->pricelist_default == 'Y')
		{
			if (($_SESSION['new'] ?? 0) == 1 OR $_SESSION['transaction_info']->pricelist_default != $_SESSION['original_pricelist_default'])
			{
				$count							=	$this->Pricelist->check_duplicate_default();
				if ($count > 0)
				{
					$_SESSION['error_code']		=	'05070';
					redirect("pricelists/view/" . $_SESSION['transaction_id']);
				}
			}
		}
	}
}
?>
