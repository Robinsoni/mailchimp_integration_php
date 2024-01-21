<?php

namespace App\Controllers;
use App\Models\MailChimpModel;

class MailChimpController extends BaseController
{
    private $data_center;
    private $api_key;
    private $list_id;
    public $url;

    public function __construct()
    {
        /**
         * Fetch api configuration
         */
        $account_id = 1;
        $model = new MailChimpModel() ;
        $config_array = $model->getApiConfig($account_id);

        
        $this->api_key = $config_array["apikey"];
        $this->data_center = $config_array["dataCenter"];
        $this->list_id = "355d5d457d";
        $this->url = 'https://' . $this->data_center . '.api.mailchimp.com/'.$config_array["apiVersion"];
    }
    public function index()
    {
        echo "Call curl function";
        $this->createMember();
        /*  return view('welcome_message'); */
    }
    public function getCurl($method = "GET", $endpoint = "ping",$body = [], $type = "json", $curl_debug = false)
    {  
        $curl = curl_init();
        $curl_req_array =  array(
            CURLOPT_URL => $this->url . "/" . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/' . $type,
                'Authorization: Bearer ' . $this->api_key
            ),
        );
        if(sizeof($body) > 0){
             $curl_req_array[CURLOPT_POSTFIELDS] = json_encode($body) ;
        }
        curl_setopt_array($curl,$curl_req_array); 
        $response = curl_exec($curl);
        curl_close($curl);
        if ($curl_debug) {
            echo "<pre>";
            print_r(json_decode($response, true));
            die();
        } 
        return json_decode($response, true);
    }
    public function createMember()
    { 
        /**
         * Dummy data
         */
       $member_details = [
            "email_address"     => "vrinda@gmail.com",
            "status"            =>  "subscribed",  
       ];
       $endpoint = "lists/" .$this->list_id. "/members";
       $result = $this->getCurl("POST",$endpoint,$member_details);
       echo "<pre>*** ";
       print_r($member_details);
       echo " <br>*** ";
       print_r($result);
       die();
    }
    
}
