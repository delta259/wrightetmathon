<?php


class Vapeself extends CI_Controller
{

/*
Token:
https://IPv4:Port/Token

Produit:
InsertProduit(ProduitModel)    lien: http://IP:PORT/api/produit/InsertProduit
MajProduit(ProduitModel)    lien: http://IP:PORT/api/produit/MajProduit
//*/

//      KEY: Content-Type
//      VALUE: application/x-www-form-urlencoded

/*
    function get_Token()
    {
        //Obtenir un Token:

        //adresse
        $IPv4 = 'vs2app.com';
        
        //PORT
        $port = '7060';
        
        //paramétres
        $password_client='vs2compat';
        $username_client='vapeself@yesstore.com';

        //lien
        $url_Token = "https://". $IPv4 . ':' . $port ."/Token";
        $url_extension = "?grant_type=password&password=" . $password_client . "&username=" . $username_client;
        $url = $url_Token . $url_extension;

        //tests
        $url = "https://vs2app.com:7060/Token?grant_type=password&password=vs2compat&username=vapeself@yesstore.com";

        //resultat
       // $token = $_POST[$url];
        echo $url . '<br>';
//        echo $_POST[$url] . '<br>';
//        include("../wrightetmathon/application/views/customers/test_distributeur_vapeself.php");
    //    file_get_contents("http://vs2app.com:7060/Token");
/*        $file = file_get_contents("https://vs2app.com:7060/Token?grant_type=password&password=vs2compat&username=vapeself@yesstore.com");

        $file_2 = ps_get_buffer("https://vs2app.com:7060/Token?grant_type=password&password=vs2compat&username=vapeself@yesstore.com");
    //*/

   /*     //        $url_encode = urlencode($url);
//        $_POST[$url_encode];
//        $_GET[$url_encode];
        $raw = file_get_contents($url);
        //file_put_contents('/var/www/html/test_recherche.json', $raw);
        file_put_contents('/var/www/html/test_recherche.json', $raw);
        $json = json_decode($raw);
//*/

        //en ligne de commande: sudo dnf install mod_ssl
        //max use connections

    

/*    /////////////////////////////////////////////////
    Liste: 
get_Token() : return un objet stdClass contenant le token dans access_token
post_InsertClient() :






//*////////////////////////////////////////////////////


    function get_Token()
    {
        
        //tableau contenant les paramétres de connexion pour    Body/ x-www-form-urlencoded
       $postfields = array('grant_type' => 'password', 'password' => 'vs2compat', 'username' => 'vapeself@yesstore.com');
    //    $postfields = array('grant_type' => 'password', 'password' => 'yesvs2compat', 'username' => 'test@yesstore.com');
        

        //génération d'une chaine de caractére en encodage URL
        $postfields = http_build_query($postfields);
        
        //lien pour le Token
        $url_Token = 'https://' . $_SESSION['ip_distributeur'] .'/Token';
        
        //tableau contenant d'en tête nécessaire
        $header = array('Content-Type' => 'application/x-www-form-urlencoded');


        //la fonction curl_setopt() return true si tout va bien ou false sinon 
        //nouvelle session cURL pour le Token
        //$curl_Token = curl_init('https://vs2app.com:7060/Token');
        $curl_Token = curl_init();

        //liaison entre $curl_Token et $url_Token
        $test = curl_setopt($curl_Token, CURLOPT_URL, $url_Token); 

        //récupération du résultat sans l'afficher
        $test = curl_setopt($curl_Token, CURLOPT_RETURNTRANSFER, true);

        //pour activé la posibilité d'envoyer une request POST
        $test = curl_setopt($curl_Token, CURLOPT_POST, true);
        
        //pour HTTPS
        $test = curl_setopt($curl_Token, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
        
        //pour ajouter l'entête à envoyer avec la request 
        $test = curl_setopt($curl_Token, CURLOPT_HTTPHEADER, $header);
        
        //pour ne pas vérififier le nom dans le certificat
        $test = curl_setopt($curl_Token, CURLOPT_SSL_VERIFYHOST, 0);
        
        //pour arrêter la vérification du certificat
        $test = curl_setopt($curl_Token, CURLOPT_SSL_VERIFYPEER, 0);

        //connexion avec les données entrées
        $test = curl_setopt($curl_Token, CURLOPT_POSTFIELDS, $postfields);
        
        //exécution de la request => récéption du token
        $return_Token = curl_exec($curl_Token);
        
        $data_Token = json_decode($return_Token);

        //fermeture de la session cURL
        curl_close($curl_Token);
        
        //retourne les informations liées au Token dont l'access_token
        return $data_Token;
    }


    function post_InsertClient($token, $data_Client)
    {
        //Insert un nouveau client
        //InsertClient(ClientModel)    api/client/InsertClient

        $data_Client_json = json_encode($data_Client);
        //echo $data_Client_json;

        $postfields = $data_Client_json;

        //lien pour insérer un client
        $url_InsertClient = 'https://' . $_SESSION['ip_distributeur'] .'/api/CLIENT/InsertClient';

        //tableau contenant d'en tête nécessaire
//        $header = array('Content-Type' => 'application/json', 'Authorization' => 'bearer ' . $token->access_token,);    //'Bearer ' . 
        $header = array('Content-Type: application/json', 'Authorization: Bearer ' . $token->access_token,);    //'Bearer ' . 

        //nouvelle session cURL pour insérer un nouveau client
        $curl_InsertClient = curl_init();

        //liaison entre $curl_InsertClient et $url_InsertClient
        $test = curl_setopt($curl_InsertClient, CURLOPT_URL, $url_InsertClient); 
               
        //récupération du résultat sans l'afficher
        $test = curl_setopt($curl_InsertClient, CURLOPT_RETURNTRANSFER, true);
               
        //pour activé la posibilité d'envoyer une request POST
        $test = curl_setopt($curl_InsertClient, CURLOPT_POST, true);
               
        //pour HTTPS
        $test = curl_setopt($curl_InsertClient, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
               
        //pour ajouter l'entête à envoyer avec la request 
        $test = curl_setopt($curl_InsertClient, CURLOPT_HTTPHEADER, $header);
               
        //pour ne pas vérififier le nom dans le certificat
        $test = curl_setopt($curl_InsertClient, CURLOPT_SSL_VERIFYHOST, 0);
               
        //pour arrêter la vérification du certificat
        $test = curl_setopt($curl_InsertClient, CURLOPT_SSL_VERIFYPEER, 0);
                    
        //connexion avec les données entrées
        $test = curl_setopt($curl_InsertClient, CURLOPT_POSTFIELDS, $data_Client_json);
               
        //exécution de la request => récéption du token
        $return_InsertClient = curl_exec($curl_InsertClient);

        //converti la chaine de caractére du format json en tableau associatif
        $data_InsertClient = json_decode($return_InsertClient);

        switch($data_InsertClient)
        {
            //reponse de success
            //"Ok"
            //reponse de fail
            //"Rejected"
            case "Ok":
                //Opération réalisée avec success
                return $data_InsertClient;
            break;
            
            case "Rejected":
                //Problème au niveau de la request
            break;

            default:
                //
            break;
        }
        $info_error = curl_getinfo($curl_InsertClient);

        //fermeture de la session cURL
        curl_close($curl_InsertClient);
 
        //message return
        return $data_InsertClient;
    }



    function post_MajClient($token, $data_Client)
    {
        //MAJ des données du client
        //MajClient(ClientModel)    api/client/MajClient

        $data_Client_json = json_encode($data_Client);

        $postfields = $data_Client_json;

        //lien pour mettre à jour les données d'un client
        $url_MajClient = 'https://' . $_SESSION['ip_distributeur'] .'/api/CLIENT/MajClient';

        //tableau contenant d'en tête nécessaire
        $header = array('Content-Type: application/json', 'Authorization: Bearer ' . $token->access_token,);    //'Bearer ' . 

        //nouvelle session cURL pour mettre à jour un client
        $curl_MajClient = curl_init();

        //liaison entre $curl_MajClient et $url_MajClient
        $test = curl_setopt($curl_MajClient, CURLOPT_URL, $url_MajClient); 
               
        //récupération du résultat sans l'afficher
        $test = curl_setopt($curl_MajClient, CURLOPT_RETURNTRANSFER, true);
               
        //pour activé la posibilité d'envoyer une request POST
        $test = curl_setopt($curl_MajClient, CURLOPT_POST, true);
               
        //pour HTTPS
        $test = curl_setopt($curl_MajClient, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
               
        //pour ajouter l'entête à envoyer avec la request 
        $test = curl_setopt($curl_MajClient, CURLOPT_HTTPHEADER, $header);
               
        //pour ne pas vérififier le nom dans le certificat
        $test = curl_setopt($curl_MajClient, CURLOPT_SSL_VERIFYHOST, 0);
               
        //pour arrêter la vérification du certificat
        $test = curl_setopt($curl_MajClient, CURLOPT_SSL_VERIFYPEER, 0);
                    
        //connexion avec les données entrées
        $test = curl_setopt($curl_MajClient, CURLOPT_POSTFIELDS, $data_Client_json);
               
        //exécution de la request => récéption du token
        $return_MajClient = curl_exec($curl_MajClient);

        //converti la chaine de caractére du format json en tableau associatif
        $data_MajClient = json_decode($return_MajClient);

        switch($data_MajClient)
        {
            //reponse de success
            //"Ok"
            //reponse de fail
            //"Rejected"
            case "Ok":
                //Opération réalisée avec success
            break;
            
            case "Rejected":
                //Problème au niveau de la request
            break;

            case "Fail -> Customer not found":
                //Si le client n'est pas trové alors il est inséré
                $data_MajClient = $this->post_InsertClient($token, $data_Client);
                
            break;

            default:
                //
            break;
        }
        $info_error = curl_getinfo($curl_MajClient);

        //fermeture de la session cURL
        curl_close($curl_MajClient);
   
        //message return
        return $data_MajClient;
    }


    function get_Credit_GetFromMachine($token)
    {
        //lien pour mettre à jour les données d'un client
        $url_Credit_GetFromMachine = 'https://' . $_SESSION['ip_distributeur'] .'/api/CREDIT/GetFromMachine';

        //tableau contenant d'en tête nécessaire
        $header = array('Content-Type: application/json', 'Authorization: Bearer ' . $token->access_token,);    //'Bearer ' . 

        //nouvelle session cURL pour mettre à jour un client
        $curl_Credit_GetFromMachine = curl_init();

        //liaison entre $curl_MajClient et $url_MajClient
        $test = curl_setopt($curl_Credit_GetFromMachine, CURLOPT_URL, $url_Credit_GetFromMachine); 
               
        //récupération du résultat sans l'afficher
        $test = curl_setopt($curl_Credit_GetFromMachine, CURLOPT_RETURNTRANSFER, true);
               
        //pour activé la posibilité d'envoyer une request POST
     //   $test = curl_setopt($curl_Credit_GetFromMachine, CURLOPT_HTTPGET, true);
               
        //pour HTTPS
        $test = curl_setopt($curl_Credit_GetFromMachine, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
               
        //pour ajouter l'entête à envoyer avec la request 
        $test = curl_setopt($curl_Credit_GetFromMachine, CURLOPT_HTTPHEADER, $header);
               
        //pour ne pas vérififier le nom dans le certificat
        $test = curl_setopt($curl_Credit_GetFromMachine, CURLOPT_SSL_VERIFYHOST, 0);
               
        //pour arrêter la vérification du certificat
        $test = curl_setopt($curl_Credit_GetFromMachine, CURLOPT_SSL_VERIFYPEER, 0);
               
        //exécution de la request => récéption du token
        $return_Credit_GetFromMachine = curl_exec($curl_Credit_GetFromMachine);

        //converti la chaine de caractére du format json en tableau associatif
//        $data_Credit_GetFromMachine = json_decode($return_Credit_GetFromMachine);
        $data_Credit_GetFromMachine = $return_Credit_GetFromMachine;

 /*       switch($data_Credit_GetFromMachine)
        {
            //reponse de success
            //"Ok"
            //reponse de fail
            //"Rejected"
            case "OK":
                //Opération réalisée avec success
            break;
            
            case "Rejected":
                //Problème au niveau de la request
            break;

            default:
                //
            break;
        }//*/
        $info_error = curl_getinfo($curl_Credit_GetFromMachine);

        //fermeture de la session cURL
        curl_close($curl_Credit_GetFromMachine);

        //message return
        return $data_Credit_GetFromMachine;
    }


    function post_InsertCredit($token, $data_Credit_Client)
    {
        $data_Credit_Client_json = json_encode($data_Credit_Client, 2);

        $postfields = $data_Credit_Client_json;
        
        //lien pour mettre à jour les données d'un client
        $url_InsertCredit = 'https://' . $_SESSION['ip_distributeur'] .'/api/CREDIT/InsertCredit';

        //tableau contenant d'en tête nécessaire
        $header = array('Content-Type: application/json', 'Authorization: Bearer ' . $token->access_token,);    //'Bearer ' . 

        //print_r($header);
        //nouvelle session cURL pour mettre à jour un client
        $curl_InsertCredit = curl_init();

        //liaison entre $curl_MajClient et $url_MajClient
        $test = curl_setopt($curl_InsertCredit, CURLOPT_URL, $url_InsertCredit); 
               
        //récupération du résultat sans l'afficher
        $test = curl_setopt($curl_InsertCredit, CURLOPT_RETURNTRANSFER, true);
               
        //pour activé la posibilité d'envoyer une request POST
        $test = curl_setopt($curl_InsertCredit, CURLOPT_POST, true);
               
        //pour HTTPS
        $test = curl_setopt($curl_InsertCredit, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
               
        //pour ajouter l'entête à envoyer avec la request 
        $test = curl_setopt($curl_InsertCredit, CURLOPT_HTTPHEADER, $header);
               
        //pour ne pas vérififier le nom dans le certificat
        $test = curl_setopt($curl_InsertCredit, CURLOPT_SSL_VERIFYHOST, 0);

        //pour arrêter la vérification du certificat
        $test = curl_setopt($curl_InsertCredit, CURLOPT_SSL_VERIFYPEER, 0);
            
        //connexion avec les données entrées
        $test = curl_setopt($curl_InsertCredit, CURLOPT_POSTFIELDS, $postfields);

        //exécution de la request => récéption du token
        $return_InsertCredit = curl_exec($curl_InsertCredit);

        //converti la chaine de caractére du format json en tableau associatif
        $data_InsertCredit = json_decode($return_InsertCredit);

        switch($data_InsertCredit)
        {
            //reponse de success
            //"Ok"
            //reponse de fail
            //"Rejected"
            case "Ok":
                //Opération réalisée avec success
            break;
            
            case "Rejected":
                //Problème au niveau de la request
            break;

            default:
                //
            break;
        }
        $info_error = curl_getinfo($curl_InsertCredit);

        //fermeture de la session cURL
        curl_close($curl_InsertCredit);
   
        //message return
        return $data_InsertCredit;
    }
    


//get 
//https://vs2app.com:7060/api/STOCK/GetQuantite?ID=CaramelI001&Emplacement=Machine

    function post_InsertProduit($token, $data_InsertProuit)
    {
        $data_InsertProuit_json = json_encode($data_InsertProuit);
        //echo $data_Client_json;

        $postfields = $data_InsertProuit_json;
        
        //lien pour insérer un client
        $url_InsertProduit = 'https://' . $_SESSION['ip_distributeur'] .'/api/PRODUIT/InsertProduit';

        //tableau contenant d'en tête nécessaire
        $header = array('Content-Type: application/json', 'Authorization: Bearer ' . $token->access_token,);    //'Bearer ' . 

        //print_r($header);
        //nouvelle session cURL pour insérer un nouveau client
        $curl_InsertProduit = curl_init();

        //liaison entre $curl_InsertProduit et $url_InsertClient
        $test = curl_setopt($curl_InsertProduit, CURLOPT_URL, $url_InsertProduit); 
               
        //récupération du résultat sans l'afficher
        $test = curl_setopt($curl_InsertProduit, CURLOPT_RETURNTRANSFER, true);
               
        //pour activé la posibilité d'envoyer une request POST
        $test = curl_setopt($curl_InsertProduit, CURLOPT_POST, true);
               
        //pour HTTPS
        $test = curl_setopt($curl_InsertProduit, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
               
        //pour ajouter l'entête à envoyer avec la request 
        $test = curl_setopt($curl_InsertProduit, CURLOPT_HTTPHEADER, $header);
               
        //pour ne pas vérififier le nom dans le certificat
        $test = curl_setopt($curl_InsertProduit, CURLOPT_SSL_VERIFYHOST, 0);
               
        //pour arrêter la vérification du certificat
        $test = curl_setopt($curl_InsertProduit, CURLOPT_SSL_VERIFYPEER, 0);
                    
        //connexion avec les données entrées
        $test = curl_setopt($curl_InsertProduit, CURLOPT_POSTFIELDS, $postfields);
               
        //exécution de la request => récéption du token
        $return_InsertProduit = curl_exec($curl_InsertProduit);

        //converti la chaine de caractére du format json en tableau associatif
        $data_InsertProduit = json_decode($return_InsertProduit);

        switch($data_InsertProduit)
        {
            //reponse de success
            //"Ok"
            //reponse de fail
            //"Rejected"
            case "Ok":
                //Opération réalisée avec success
            break;
            
            case "Rejected":
                //Problème au niveau de la request
            break;

            default:
                //
            break;
        }
        $info_error = curl_getinfo($curl_InsertProduit);

        //fermeture de la session cURL
        curl_close($curl_InsertProduit);

        //message return
        return $data_InsertProduit;
    }



    function post_MajProduit($token, $data_MajProduit)
    {
        //MAJ des données du client
        //MajClient(ClientModel)    api/client/MajClient

        $data_MajProduit_json = json_encode($data_MajProduit);

        $postfields = $data_MajProduit_json;
        
        //lien pour mettre à jour les données d'un client
        $url_MajProduit = 'https://' . $_SESSION['ip_distributeur'] .'/api/PRODUIT/MajProduit';

        //tableau contenant d'en tête nécessaire
        $header = array('Content-Type: application/json', 'Authorization: Bearer ' . $token->access_token,);    //'Bearer ' . 

        //print_r($header);
        //nouvelle session cURL pour mettre à jour un client
        $curl_MajProduit = curl_init();

        //liaison entre $curl_MajProduit et $url_MajClient
        $test = curl_setopt($curl_MajProduit, CURLOPT_URL, $url_MajProduit); 
               
        //récupération du résultat sans l'afficher
        $test = curl_setopt($curl_MajProduit, CURLOPT_RETURNTRANSFER, true);
               
        //pour activé la posibilité d'envoyer une request POST
        $test = curl_setopt($curl_MajProduit, CURLOPT_POST, true);
               
        //pour HTTPS
        $test = curl_setopt($curl_MajProduit, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
               
        //pour ajouter l'entête à envoyer avec la request 
        $test = curl_setopt($curl_MajProduit, CURLOPT_HTTPHEADER, $header);
               
        //pour ne pas vérififier le nom dans le certificat
        $test = curl_setopt($curl_MajProduit, CURLOPT_SSL_VERIFYHOST, 0);
               
        //pour arrêter la vérification du certificat
        $test = curl_setopt($curl_MajProduit, CURLOPT_SSL_VERIFYPEER, 0);
                    
        //connexion avec les données entrées
        $test = curl_setopt($curl_MajProduit, CURLOPT_POSTFIELDS, $postfields);
               
        //exécution de la request => récéption du token
        $return_MajProduit = curl_exec($curl_MajProduit);

        //converti la chaine de caractére du format json en tableau associatif
        $data_MajProduit_json_decode = json_decode($return_MajProduit);

        switch($data_MajProduit_json_decode)
        {
            //reponse de success
            //"Ok"
            //reponse de fail
            //"Rejected"
            case "Ok":
                //Opération réalisée avec success
            break;
            
            case "Rejected":
                //Problème au niveau de la request
            break;

            case "Fail -> Product not found":
                //Si le client n'est pas trové alors il est inséré
                $data_MajProduit_json_decode =  $this->post_InsertProduit($token, $data_MajProduit);

            break;

            default:
                //
            break;
        }
        $info_error = curl_getinfo($curl_MajProduit);

        //fermeture de la session cURL
        curl_close($curl_MajProduit);
   
        //message return
        return $data_MajProduit_json_decode;
    }

    //insertion catégorie
    function post_InsertCatego($token, $data_InsertCatego)
    {
        $data_InsertCatego_json = json_encode($data_InsertCatego);
     
        $postfields = $data_InsertCatego_json;
        
        //lien pour insérer un client
        $url_InsertCatego = 'https://' . $_SESSION['ip_distributeur'] .'/api/CATEGORIE/InsertCatego';

        //tableau contenant d'en tête nécessaire
        $header = array('Content-Type: application/json', 'Authorization: Bearer ' . $token->access_token,);    //'Bearer ' . 

        //print_r($header);
        //nouvelle session cURL pour insérer un nouveau client
        $curl_InsertCatego = curl_init();

        //liaison entre $curl_InsertCatego et $url_InsertClient
        $test = curl_setopt($curl_InsertCatego, CURLOPT_URL, $url_InsertCatego); 
               
        //récupération du résultat sans l'afficher
        $test = curl_setopt($curl_InsertCatego, CURLOPT_RETURNTRANSFER, true);
               
        //pour activé la posibilité d'envoyer une request POST
        $test = curl_setopt($curl_InsertCatego, CURLOPT_POST, true);
               
        //pour HTTPS
        $test = curl_setopt($curl_InsertCatego, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
               
        //pour ajouter l'entête à envoyer avec la request 
        $test = curl_setopt($curl_InsertCatego, CURLOPT_HTTPHEADER, $header);
               
        //pour ne pas vérififier le nom dans le certificat
        $test = curl_setopt($curl_InsertCatego, CURLOPT_SSL_VERIFYHOST, 0);
               
        //pour arrêter la vérification du certificat
        $test = curl_setopt($curl_InsertCatego, CURLOPT_SSL_VERIFYPEER, 0);
                    
        //connexion avec les données entrées
        $test = curl_setopt($curl_InsertCatego, CURLOPT_POSTFIELDS, $postfields);
               
        //exécution de la request => récéption du token
        $return_InsertCatego = curl_exec($curl_InsertCatego);

        //converti la chaine de caractére du format json en tableau associatif
        $data_InsertCatego_json_decode = json_decode($return_InsertCatego);

        switch($data_InsertCatego_json_decode)
        {
            //reponse de success
            //"Ok"
            //reponse de fail
            //"Rejected"
            case "Ok":
                //Opération réalisée avec success
            break;
            
            case "Rejected":
                //Problème au niveau de la request
            break;

            default:
                //
            break;
        }
        $info_error = curl_getinfo($curl_InsertCatego);

        //fermeture de la session cURL
        curl_close($curl_InsertCatego);

        //message return
        return $data_InsertCatego_json_decode;
    }

    //insertion catégorie
    function post_MajCatego($token, $data_MajCatego)
    {
        $data_MajCatego_json = json_encode($data_MajCatego);
     
        $postfields = $data_MajCatego_json;
        
        //lien pour insérer un client
        $url_MajCatego = 'https://' . $_SESSION['ip_distributeur'] .'/api/CATEGORIE/MajCatego';

        //tableau contenant d'en tête nécessaire
        $header = array('Content-Type: application/json', 'Authorization: Bearer ' . $token->access_token,);    //'Bearer ' . 

        //print_r($header);
        //nouvelle session cURL pour insérer un nouveau client
        $curl_MajCatego = curl_init();

        //liaison entre $curl_InsertCatego et $url_InsertClient
        $test = curl_setopt($curl_MajCatego, CURLOPT_URL, $url_MajCatego); 
               
        //récupération du résultat sans l'afficher
        $test = curl_setopt($curl_MajCatego, CURLOPT_RETURNTRANSFER, true);
               
        //pour activé la posibilité d'envoyer une request POST
        $test = curl_setopt($curl_MajCatego, CURLOPT_POST, true);
               
        //pour HTTPS
        $test = curl_setopt($curl_MajCatego, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
               
        //pour ajouter l'entête à envoyer avec la request 
        $test = curl_setopt($curl_MajCatego, CURLOPT_HTTPHEADER, $header);
               
        //pour ne pas vérififier le nom dans le certificat
        $test = curl_setopt($curl_MajCatego, CURLOPT_SSL_VERIFYHOST, 0);
               
        //pour arrêter la vérification du certificat
        $test = curl_setopt($curl_MajCatego, CURLOPT_SSL_VERIFYPEER, 0);
                    
        //connexion avec les données entrées
        $test = curl_setopt($curl_MajCatego, CURLOPT_POSTFIELDS, $postfields);
               
        //exécution de la request => récéption du token
        $return_MajCatego = curl_exec($curl_MajCatego);

        //converti la chaine de caractére du format json en tableau associatif
        $data_MajCatego_json_decode = json_decode($return_MajCatego);

        switch($data_MajCatego_json_decode)
        {
            //reponse de success
            //"Ok"
            //reponse de fail
            //"Rejected"
            case "Ok":
                //Opération réalisée avec success
            break;
            
            case "Rejected":
                //Problème au niveau de la request
            break;

            case "Fail -> Product not found":
                //Si la catégorie n'est pas trouvé, alors elle est insérée
                $data_MajCatego_json_decode = $this->post_InsertCatego($token, $data_MajCatego);
            break;

            default:
                //
            break;
        }
        $info_error = curl_getinfo($curl_MajCatego);

        //fermeture de la session cURL
        curl_close($curl_MajCatego);

        //message return
        return $data_MajCatego_json_decode;
    }
    
    function get_GetVentes($token)
    {
        //
        //lien pour mettre à jour les données d'un client
        $url_GetVentes = 'https://' . $_SESSION['ip_distributeur'] .'/api/VENTE/GetVentes';

        //tableau contenant d'en tête nécessaire
        $header = array('Content-Type: application/json', 'Authorization: Bearer ' . $token->access_token,);    //'Bearer ' . 

        //nouvelle session cURL pour mettre à jour un client
        $curl_GetVentes = curl_init();

        //liaison entre $curl_MajClient et $url_MajClient
        $test = curl_setopt($curl_GetVentes, CURLOPT_URL, $url_GetVentes); 
               
        //récupération du résultat sans l'afficher
        $test = curl_setopt($curl_GetVentes, CURLOPT_RETURNTRANSFER, true);
               
        //pour activé la posibilité d'envoyer une request GET
        $test = curl_setopt($curl_GetVentes, CURLOPT_HTTPGET, true);
               
        //pour HTTPS
        $test = curl_setopt($curl_GetVentes, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
               
        //pour ajouter l'entête à envoyer avec la request 
        $test = curl_setopt($curl_GetVentes, CURLOPT_HTTPHEADER, $header);
               
        //pour ne pas vérififier le nom dans le certificat
        $test = curl_setopt($curl_GetVentes, CURLOPT_SSL_VERIFYHOST, 0);
               
        //pour arrêter la vérification du certificat
        $test = curl_setopt($curl_GetVentes, CURLOPT_SSL_VERIFYPEER, 0);
               
        //exécution de la request => récéption du token
        $return_GetVentes = curl_exec($curl_GetVentes);

        //converti la chaine de caractére du format json en tableau associatif
//        $return_data_GetVentes_json_decode = json_decode($return_GetVentes);
        $return_data_GetVentes_json_decode = $return_GetVentes;

        $info_error = curl_getinfo($curl_GetVentes);

        //fermeture de la session cURL
        curl_close($curl_GetVentes);

        //message return
        return $return_data_GetVentes_json_decode;
    }

    function get_GetQuantite($token, $data_GetQuantite)
    {


        $postfields = $data_GetQuantite;
        

        //génération d'une chaine de caractére en encodage URL
        $postfields = http_build_query($postfields); 




        ///STOCK/GetQuantity?VotreIDProduit=565&Emplacement=Machine
        //lien pour mettre à jour les données d'un client
        $url_GetQuantite = 'https://' . $_SESSION['ip_distributeur'] .'/api/STOCK/GetQuantite?' . $postfields;

        //tableau contenant d'en tête nécessaire
        $header = array('Content-Type: application/json', 'Authorization: Bearer ' . $token->access_token,);    //'Bearer ' . 

        //nouvelle session cURL pour mettre à jtest@yesstore.comour un client
        $curl_GetQuantite = curl_init();

        //liaison entre $curl_MajClient et $url_MajClient
        $test = curl_setopt($curl_GetQuantite, CURLOPT_URL, $url_GetQuantite); 
               
        //récupération du résultat sans l'afficher
        $test = curl_setopt($curl_GetQuantite, CURLOPT_RETURNTRANSFER, true);
               
        //pour activé la posibilité d'envoyer une request GET
        $test = curl_setopt($curl_GetQuantite, CURLOPT_HTTPGET, true);
               
        //pour HTTPS
        $test = curl_setopt($curl_GetQuantite, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
               
        //pour ajouter l'entête à envoyer avec la request 
        $test = curl_setopt($curl_GetQuantite, CURLOPT_HTTPHEADER, $header);
               
        //pour ne pas vérififier le nom dans le certificat
        $test = curl_setopt($curl_GetQuantite, CURLOPT_SSL_VERIFYHOST, 0);
               
        //pour arrêter la vérification du certificat
        $test = curl_setopt($curl_GetQuantite, CURLOPT_SSL_VERIFYPEER, 0);
               
        //exécution de la request => récéption du token
        $return_GetQuantite = curl_exec($curl_GetQuantite);

        //converti la chaine de caractére du format json en tableau associatif
        $data_GetQuantite_json_decode = json_decode($return_GetQuantite);

        $info_error = curl_getinfo($curl_GetQuantite);

        //fermeture de la session cURL
        curl_close($curl_GetQuantite);

        //message return
        return $data_GetQuantite_json_decode;
    }

    function add_ventes_into_ospos_vs_sales()
    {
        //chargement du model vapeself 
        $this->load->model("Vapeself_model");
        

        /*
		//obtention du jeton Token Bearer
		$token = $this->vapeself->get_Token();

        //obtention de toutes les nouvelles ventes
		$return_message = $this->vapeself->get_GetVentes($token);

        //*/

        
 //       $test_ventes_brutes = '[{"ID_VENTE":18,"DATEVENTE":"2019-12-06T14:05:39.853","ID_CLIENT":"1474","TOTALTTC":5.90,"REMISE":0.00,"RECREDIT":0.00,"EMPLACEMENT":"1","LISTE":"[{\"VotreID\":\"6301\",\"Quantite\":1}]","MODIFIE":0,"MON_ID":16},{"ID_VENTE":19,"DATEVENTE":"2019-12-06T14:06:57.23","ID_CLIENT":"1474","TOTALTTC":5.90,"REMISE":0.00,"RECREDIT":0.00,"EMPLACEMENT":"1","LISTE":"[{\"VotreID\":\"624\",\"Quantite\":1}]","MODIFIE":0,"MON_ID":17},{"ID_VENTE":20,"DATEVENTE":"2019-12-06T14:10:34.86","ID_CLIENT":"1474","TOTALTTC":5.90,"REMISE":0.00,"RECREDIT":0.00,"EMPLACEMENT":"1","LISTE":"[{\"VotreID\":\"7777\",\"Quantite\":1}]","MODIFIE":0,"MON_ID":18}]';
 //       $test_ventes_brutes = '';       
 //       $_SESSION['ventes_VS'] = $test_ventes_brutes;

/*
        $return_message = $_SESSION['ventes_VS'];
        
        $return_message_json_decode = json_decode($return_message); 

        

		$file = '/var/www/html/wrightetmathon/ventes.txt';    ///var/www/html/wrightetmathon/connexion_megaupload_temporaire.txt
        file_put_contents($file, $return_message, FILE_APPEND);//*/
        //unset($_SESSION['VS_all_ventes']);
        foreach($_SESSION['ventes_VS'] as $line_vente_only => $row)
		{
	//		$_SESSION['VS_all_ventes_objet'][$line_vente_only] = $row;
			$_SESSION['VS_all_ventes'][$line_vente_only]['id_vente'] = $_SESSION['ventes_VS'][$line_vente_only]->ID_VENTE;
			$_SESSION['VS_all_ventes'][$line_vente_only]['datevente'] = $_SESSION['ventes_VS'][$line_vente_only]->DATEVENTE;
			$_SESSION['VS_all_ventes'][$line_vente_only]['id_client'] = $_SESSION['ventes_VS'][$line_vente_only]->ID_CLIENT;
			$_SESSION['VS_all_ventes'][$line_vente_only]['totalttc'] = $_SESSION['ventes_VS'][$line_vente_only]->TOTALTTC;
			$_SESSION['VS_all_ventes'][$line_vente_only]['remise'] = $_SESSION['ventes_VS'][$line_vente_only]->REMISE;
			$_SESSION['VS_all_ventes'][$line_vente_only]['recredit'] = $_SESSION['ventes_VS'][$line_vente_only]->RECREDIT;
			$_SESSION['VS_all_ventes'][$line_vente_only]['emplacement'] = $_SESSION['ventes_VS'][$line_vente_only]->EMPLACEMENT;
			$_SESSION['VS_all_ventes'][$line_vente_only]['liste'] = $_SESSION['ventes_VS'][$line_vente_only]->LISTE;
			$_SESSION['VS_all_ventes'][$line_vente_only]['modifie'] = $_SESSION['ventes_VS'][$line_vente_only]->MODIFIE;
            $_SESSION['VS_all_ventes'][$line_vente_only]['mon_id'] = $_SESSION['ventes_VS'][$line_vente_only]->MON_ID;
            $_SESSION['VS_all_ventes'][$line_vente_only]['modereglement'] = $_SESSION['ventes_VS'][$line_vente_only]->MODEREGLEMENT;
			$_SESSION['VS_all_ventes'][$line_vente_only]['date_add_table'] = date("Y-m-d H:i:s");
            $_SESSION['VS_all_ventes'][$line_vente_only]['validate'] = false;
            
            $return = $this->Vapeself_model->insert_VS_sales_into_db_vs_sales($_SESSION['VS_all_ventes'][$line_vente_only]);
		}
        
    }
    function add_credit_into_ospos_vs_credit()
    {
        //chargement du model vapeself 
        $this->load->model("Vapeself_model");


        foreach($_SESSION['credit_VS'] as $line_vente_only => $row)
		{
			$_SESSION['VS_all_credit'][$line_vente_only]['id_credit'] = $_SESSION['credit_VS'][$line_vente_only]->ID_CREDIT;
			$_SESSION['VS_all_credit'][$line_vente_only]['votreid'] = $_SESSION['credit_VS'][$line_vente_only]->VOTREID;
			$_SESSION['VS_all_credit'][$line_vente_only]['datecredit'] = $_SESSION['credit_VS'][$line_vente_only]->DATECREDIT;
			$_SESSION['VS_all_credit'][$line_vente_only]['montant'] = $_SESSION['credit_VS'][$line_vente_only]->MONTANT;
			$_SESSION['VS_all_credit'][$line_vente_only]['solde'] = $_SESSION['credit_VS'][$line_vente_only]->SOLDE;
			$_SESSION['VS_all_credit'][$line_vente_only]['date_add_table'] = date("Y-m-d H:i:s");
            $_SESSION['VS_all_credit'][$line_vente_only]['validate'] = false;
            
            $return = $this->Vapeself_model->insert_VS_credit_into_db_vs_credit($_SESSION['VS_all_credit'][$line_vente_only]);
		}
        
    }

    function get_GetInfo($token, $data_GetInfo)
    {
        //Exemple de la request
        //https://vs2app.com:7060/api/PRODUIT/GetInfo?codebarre=3662572106916&id=7764

        $postfields = $data_GetInfo;
        
        //génération d'une chaine de caractére en encodage URL
        $postfields = http_build_query($postfields); 

        ///STOCK/GetQuantity?VotreIDProduit=565&Emplacement=Machine
        //lien pour mettre à jour les données d'un client
        $url_GetInfo = 'https://' . $_SESSION['ip_distributeur'] .'/api/PRODUIT/GetInfo?' . $postfields;

        //tableau contenant d'en tête nécessaire
        $header = array('Content-Type: application/json', 'Authorization: Bearer ' . $token->access_token,);    //'Bearer ' . 

        //nouvelle session cURL pour mettre à jtest@yesstore.comour un client
        $curl_GetInfo = curl_init();

        //liaison entre $curl_MajClient et $url_MajClient
        $test = curl_setopt($curl_GetInfo, CURLOPT_URL, $url_GetInfo); 
               
        //récupération du résultat sans l'afficher
        $test = curl_setopt($curl_GetInfo, CURLOPT_RETURNTRANSFER, true);
               
        //pour activé la posibilité d'envoyer une request GET
        $test = curl_setopt($curl_GetInfo, CURLOPT_HTTPGET, true);
               
        //pour HTTPS
        $test = curl_setopt($curl_GetInfo, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
               
        //pour ajouter l'entête à envoyer avec la request 
        $test = curl_setopt($curl_GetInfo, CURLOPT_HTTPHEADER, $header);
               
        //pour ne pas vérififier le nom dans le certificat
        $test = curl_setopt($curl_GetInfo, CURLOPT_SSL_VERIFYHOST, 0);
               
        //pour arrêter la vérification du certificat
        $test = curl_setopt($curl_GetInfo, CURLOPT_SSL_VERIFYPEER, 0);
               
        //exécution de la request => récéption du token
        $return_GetInfo = curl_exec($curl_GetInfo);

        //converti la chaine de caractére du format json en tableau associatif
        $data_GetInfo_json_decode = json_decode($return_GetInfo);

        $info_error = curl_getinfo($curl_GetInfo);

        //fermeture de la session cURL
        curl_close($curl_GetInfo);

        //message return
        return $data_GetInfo_json_decode;
    }

}
