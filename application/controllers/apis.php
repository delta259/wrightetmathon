
<?php
  //use Symfony\Component\HttpFoundation\Request;
  //use Symfony\Component\HttpFoundation\Response;
//namespace AppBundle\Controller;
///var/www/html/wrightetmathon/Symfony

/**
 * class créée pour interragir avec les POS des autres boutiques possédant le POS.
 * Les conditions pour que de boutiques "amies" puissent communiquer des informations entre sont:
 *     ->Les class Apis des POS des boutiques doivent être identiques pour étiviter tout conflits entre les versions
 *     -> ... (to be continued)
 * 
 * route: ->ressources->méthode->action->fonction->param
 * ressources: customers
 */
//port 3306
class Apis extends CI_Controller
{
  //Retourne tous les liens, les possibilités et les ressources accessible à travers cette API web
  function index()
  {
    //
    header("HTTP/1.1 200 OK");
    header("Content-Type: application/json");
    $version_api = array('vension_API' => 0.000);
    $possibility = array(
      'vension_API' => 0.000,
      'obtenir des informations client(s)' => array(
        array(
          'Méthode' => 'GET',
          'Action'  => 'Récupération de toutes les informations dans `ospos_people` & `ospos_customers`',
          'URI'     => '/api/customers/'),
        array(
          'Méthode' => 'GET',
          'Action'  => 'Récupération de toutes les informations dans `ospos_people` & `ospos_customers` pour des clients spécifiques',
          'URI'     => '/api/customers/last_name=&first_name=&email=&phone_number=&')
      ),
      'ping connexion' => array(
        'Méthode' => 'GET',
        'Action'  => 'Vérifier la connexion entre 2 boutiques possédent le POS',
        'URI'     => '/apis/ping/'),
      'mettre à jour les informations client(s)' => array(
        array(
          'Méthode' => 'POST',
          'Action'  => 'Met à jour (update) les données de la table `ospos_customers`',
          'URI'     => '/api/customers/customers:person_id=$id'
        ),
        array(
          'Méthode' => 'POST',
          'Action'  => 'Met à jour (update) les données de la table `ospos_people`',
          'URI'     => '/api/customers/people:person_id=$id'
        )
      )
    );
    
    $possibility_json = json_encode($possibility);
    echo $possibility_json;
  }

  function ping()
  {
    header("HTTP/1.1 200 OK");
    header("Content-Type: application/json");
    $data = array('ping' => 1);
    $json = json_encode($data);
    echo $json;
  }

  function routing($inputs)
  {
        $origin = $inputs['origin'];
//    foreach($inputs['origin'] as $origin)
//    {
//          
        //fonction pour router
        switch($origin)
        {
          case 'customers':
              //
              $this->post_update_customers();
          break;
    
          case 'people':
              //
              $this->post_update_people();
          break;
    
          case 'sales':
              //
          break;
    
          case 'sale_items':
              //
          break;
    
          case 'items':
              //
          break;
    
          case 'items_kits':
              //
          break;
    
    
          //etc...
    
          default:
          break;
        }
 //   }
    //return $outputs 
  }

  function code_statut($code= NULL)
  {
    //  $this->http_response_code($code);
    if ($code !== NULL)
    {
      switch ($code)
      {
          case 100: $text = 'Continue'; break;
          case 101: $text = 'Switching Protocols'; break;
          case 200: $text = 'OK'; break;
          case 201: $text = 'Created'; break;
          case 202: $text = 'Accepted'; break;
          case 203: $text = 'Non-Authoritative Information'; break;
          case 204: $text = 'No Content'; break;
          case 205: $text = 'Reset Content'; break;
          case 206: $text = 'Partial Content'; break;
          case 300: $text = 'Multiple Choices'; break;
          case 301: $text = 'Moved Permanently'; break;
          case 302: $text = 'Moved Temporarily'; break;
          case 303: $text = 'See Other'; break;
          case 304: $text = 'Not Modified'; break;
          case 305: $text = 'Use Proxy'; break;
          case 400: $text = 'Bad Request'; break;
          case 401: $text = 'Unauthorized'; break;
          case 402: $text = 'Payment Required'; break;
          case 403: $text = 'Forbidden'; break;
          case 404: $text = 'Not Found'; break;
          case 405: $text = 'Method Not Allowed'; break;
          case 406: $text = 'Not Acceptable'; break;
          case 407: $text = 'Proxy Authentication Required'; break;
          case 408: $text = 'Request Time-out'; break;
          case 409: $text = 'Conflict'; break;
          case 410: $text = 'Gone'; break;
          case 411: $text = 'Length Required'; break;
          case 412: $text = 'Precondition Failed'; break;
          case 413: $text = 'Request Entity Too Large'; break;
          case 414: $text = 'Request-URI Too Large'; break;
          case 415: $text = 'Unsupported Media Type'; break;
          case 500: $text = 'Internal Server Error'; break;
          case 501: $text = 'Not Implemented'; break;
          case 502: $text = 'Bad Gateway'; break;
          case 503: $text = 'Service Unavailable'; break;
          case 504: $text = 'Gateway Time-out'; break;
          case 505: $text = 'HTTP Version not supported'; break;
          default:
          
          break;
      }
    }
    return $text;
  }

  
  function find_verify_params($params)
  {
    $inputs = array();
    $inputs['where'] = '';
    $inputs['champs_not_found'] = 0;
    foreach($params as $key => $value)
    {
      if(!empty($value))
      {
          if($inputs['where'] != '')
          {
            $inputs['where'] .= " AND ";
          }
          switch($key)
          {
            case 'first_name':
                //
                $inputs['where'] .= "ospos_people.first_name LIKE '%".$value."%' ";
            break;
    
            case 'last_name':
                //
                $inputs['where'] .= "ospos_people.last_name LIKE '%".$value."%' ";
            break;
    
            case 'phone_number':
                //
                $inputs['where'] .= "ospos_people.phone_number LIKE '%".$value."%' ";
            break;
    
            case 'email':
                //
                $inputs['where'] .= "ospos_people.email LIKE '%".$value."%' ";
            break;

            default:
                //
                $inputs['champs_not_found'] += 1;
            break;
          }
      }
    }
    return $inputs;
  }
  
  function get_sales_customers($search = -1)
  {
    switch($search)
    {
      case -1:
          $code = 400;
          $statut_text = $this->code_statut($code);
          $hearder_protocol = $_SERVER['SERVER_PROTOCOL'].' '.$code.' '. $statut_text;
          header($hearder_protocol);
          header("Content-Type: application/json");
          
          $tab = "le serveur n'a pas pu comprendre la requête à cause d'une syntaxe invalide.";
          $json = json_encode($tab);
          echo $json;
      break;

      default:
         $search = urldecode($search);
         $parametres = array();
         $param = array();
         $parametres = explode('&', $search);
         foreach($parametres as $key => $value)
         {
           if($value != '')
           {
               $list = explode('=', $value);
//                    $param[$key] = $value;
                $param[$list[0]] = $list[1];
            }
          }

		      // load appropriate model and controller
//		      $this->load->model('reports/Specific_customer');
//		      $this->load->library('../controllers/reports');
          $start_date 		 =	date('Y-m-d', 0);
          $end_date			 = 	date('Y-m-d');
          $transaction_subtype =	'sales$returns';
          $customer_id = $param['person_id'];
          $limit				 =	$param['limit'];  //10;
          $report_data		 =	$this->Customer->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'person_id'=>$customer_id, 'transaction_subtype'=> $transaction_subtype, 'limit'=>$limit));
          $json = json_encode($report_data);

          $code = 200;
          $statut_text = $this->code_statut($code);
          $hearder_protocol = $_SERVER['SERVER_PROTOCOL'].' '.$code.' '. $statut_text;
          header($hearder_protocol);
          header("Content-Type: application/json");
          echo $json;

         // $inputs = $this->find_verify_params($param);
         // $data_returned = $this->Customer->get_all_info_with_inputs($inputs);
         // if($data_returned->num_rows > 0)
         // {
         //   $code = 200;
         // }
         // if($data_returned->num_rows <= 0)
         // {
         //   $code = 404;
         // }
         // $statut_text = $this->code_statut($code);
         // $data_returned = $data_returned->result_array();
      break;
    }
  }

  function sales($search)
  {
    $request_method = $_SERVER['REQUEST_METHOD'];
    switch($request_method)
    {
      case 'GET':
          //get_customers
          $this->get_sales_customers($search);
      break;

      case 'POST':
          $identification = urldecode($identification);
          list($origin, $param_value) = explode(':', $identification);
          list($param, $value) = explode('=', $param_value);
          
          $inputs =array();
          $inputs['origin'] = $origin;

          $this->routing($inputs);
          
          //post_customers
    //      $this->post_update_customers();
      break;

      case 'PUT':
      case 'DELETE':

      default:
          //
          $code = 405;
          $statut_text = $this->code_statut($code);
          $hearder_protocol = $_SERVER['SERVER_PROTOCOL'].' '.$code.' '. $statut_text;
          header($hearder_protocol);
          header("Content-Type: application/json");
          $tab = "methode ".$request_method." non supportée par l\'API";
          $json = json_encode($tab);
          echo $json;
      break;
    }
  }

  function customers($identification = -1)
  {

    $request_method = $_SERVER['REQUEST_METHOD'];
    switch($request_method)
    {
      case 'GET':
          //get_customers
          $this->get_customers($identification);
      break;

      case 'POST':
          $identification = urldecode($identification);
          list($origin, $param_value) = explode(':', $identification);
          list($param, $value) = explode('=', $param_value);
          
          $inputs =array();
          $inputs['origin'] = $origin;

          $this->routing($inputs);
          
          //post_customers
    //      $this->post_update_customers();
      break;

      case 'PUT':
      case 'DELETE':

      default:
          //
          $code = 405;
          $statut_text = $this->code_statut($code);
          $hearder_protocol = $_SERVER['SERVER_PROTOCOL'].' '.$code.' '. $statut_text;
          header($hearder_protocol);
          header("Content-Type: application/json");
          $tab = "methode ".$request_method." non supportée par l\'API";
          $json = json_encode($tab);
          echo $json;
      break;
    }
  }

  function get_customers($identification = -1)
  {
    switch($identification)
    {
      case -1:
          //
          $data_returned = $this->Customer->get_all()->result_array();
          
          $code = 200;
          $statut_text = $this->code_statut($code);
      break;

      default:
          $identification = urldecode($identification);
          $parametres = array();
          $param = array();
          $parametres = explode('&', $identification);
          foreach($parametres as $key => $value)
          {
            if($value != '')
            {
                $list = explode('=', $value);
//                $param[$key] = $value;
                $param[$list[0]] = $list[1];
            }
          }
          $inputs = $this->find_verify_params($param);
          $data_returned = $this->Customer->get_all_info_with_inputs($inputs);
          if($data_returned->num_rows > 0)
          {
            $code = 200;
          }
          if($data_returned->num_rows <= 0)
          {
            $code = 404;
          }
          $statut_text = $this->code_statut($code);
          $data_returned = $data_returned->result_array();
      break;
    }
    
    $protocol = $_SERVER['SERVER_PROTOCOL'];
    $hearder_protocol = $_SERVER['SERVER_PROTOCOL'].' '.$code.' '. $statut_text;
  
    header($hearder_protocol);
    header("Content-Type: application/json");
    $json = json_encode($data_returned);
    echo $json;
//    echo '<br>'.json_encode($inputs['champs_not_found']).'<br>'.$header_http;
  }

  function post_update_customers($machin =-1)
  {
    //récupération du body de la request (json)
    $body_file = file_get_contents('php://input');
    $data_update = json_decode($body_file);
    $update_custumer_counts = $data_update;

    //update customer
    $return_msg = $this->Customer->update_customer($update_custumer_counts);
    
    switch(intval($return_msg))
    {
      case 1:
          //code  
          $code = 200;
      break;

      case 0:
          //code  
          $code = 404;
      break;

      default:
      break;
    }
    $data_json = json_encode($return_msg);
    $statut_text = $this->code_statut($code);
    $protocol = $_SERVER['SERVER_PROTOCOL'];
    $hearder_protocol = $_SERVER['SERVER_PROTOCOL'].' '.$code.' '. $statut_text;
    header($hearder_protocol);
    header("Content-Type: application/json");
    echo $data_json;
  }

  function post_update_people($machin =-1)
  {
    //récupération du body de la request (json)
    $body_file = file_get_contents('php://input');
    $data_update = json_decode($body_file);
    $update_custumer_counts = $data_update;

    //update customer
    $return_msg = $this->Person->update_people($update_custumer_counts);
    
    switch(intval($return_msg))
    {
      case 1:
          //code  
          $code = 200;
      break;

      case 0:
          //code  
          $code = 404;
      break;

      default:
      break;
    }
    $data_json = json_encode($return_msg);
    $statut_text = $this->code_statut($code);
    $protocol = $_SERVER['SERVER_PROTOCOL'];
    $hearder_protocol = $_SERVER['SERVER_PROTOCOL'].' '.$code.' '. $statut_text;
    header($hearder_protocol);
    header("Content-Type: application/json");
    echo $data_json;
  }
}