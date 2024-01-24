<?php

namespace App\Controllers;

use App\Models\MailChimpModel;

class MailChimpController extends BaseController
{
    private $data_center;
    private $api_key;
    private $list_id;
    public $url;
    private $mail_chimp_model;
    public $session;
    public function __construct()
    {
        $this->session = session();
        /**
         * Load the model
         */

        $this->mail_chimp_model = new MailChimpModel();
        /**
         * Fetch api configuration
         */

        $account_id = 1;
        $config_array = $this->mail_chimp_model->getApiConfig($account_id);


        $this->api_key = $config_array["apikey"];
        $this->data_center = $config_array["dataCenter"];
        $this->list_id = "355d5d457d";
        $this->url = 'https://' . $this->data_center . '.api.mailchimp.com/' . $config_array["apiVersion"];
    }
    public function index()
    {
        echo "Call curl function";
        $this->fetchListMemebersFromAPI();
        /*  return view('welcome_message'); */
    }
    public function getCurl($method = "GET", $endpoint = "ping", $body = [], $type = "json", $curl_debug = false)
    {
        $curl = curl_init();
        $curl_req_array = array(
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
        if (sizeof($body) > 0) {
            $curl_req_array[CURLOPT_POSTFIELDS] = json_encode($body);
        }
        curl_setopt_array($curl, $curl_req_array);
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
         * Testing
         */
        helper(['form']);
        $validation = \Config\Services::validation();

        $data = $this->request->getPost(['email', 'member_status']);

        // Checks whether the submitted data passed the validation rules.
        $rules = [
            'email' => 'required|max_length[255]|min_length[5]',
            'member_status' => 'required|max_length[50]|min_length[5]',
        ];
        $validation->setRules($rules);
        if (!$validation->withRequest($this->request)->run()) { 
            // Validation failed, set flash data and redirect back to the form view
            $this->session->setFlashdata('validation_errors', $validation->getErrors());
            $this->session->setFlashdata('old_data', $this->request->getPost());
            return   redirect()->to('/');
        }


        // Gets the validated data. 
       $check_db_data = $this->mail_chimp_model->getMemberByEmail($data["email"]);
       if(isset($check_db_data["id"])){
            $this->session->setFlashdata('validation_errors', ["Member Already Exists"]);
            $this->session->setFlashdata('old_data', $this->request->getPost());
            return redirect()->to('/');
       }
        
        /**
         * Dummy data
         */
        $data["listId"] = $this->list_id;
        $data["status"] = 1;
        $member_details = [
            "email_address" => $data["email"],
            "status" => $data["member_status"],
        ];
        $endpoint = "lists/" . $this->list_id . "/members";
        $result = $this->getCurl("POST", $endpoint, $member_details);

        $error_message = NULL;
        if (!isset($result["id"])) {
            $error_message = $result["title"] . " - " . $result["detail"] . " status code: " . $result["status"];
            $data["status"] = 5;
        } else {
            $data["member_id"] = $result["id"];
            $data["status"] = 2;
        }
        $data["api_error"] = $error_message;
        $this->mail_chimp_model->upsertMember($data);
        return redirect()->to('/');
    }
    public function fetchListMemebersFromAPI()
    {

        /*  Different status to set for member ** 
         *   1 - Created by connector
         *   2 - Synced / post to api
         *   3 - updated
         *   4 - fetched from api 
         *   5 - error from api while creation
         */
        
        $db_members = $this->mail_chimp_model->fetchAllListMember($this->list_id);
       
        $members_map = [];
        foreach ($db_members as $db_member) {
            $members_map[$db_member["member_id"]] = $db_member;
        }

        /**
         * Fetch members from APi
         */
        $endpoint = "lists/" . $this->list_id . "/members?offset=0&count=1000";
        $results = $this->getCurl("GET", $endpoint);
        echo "<pre>*** fetched db** ";
        $total_items = $results["total_items"];
        $fetched_data_size = sizeof($results["members"]);
        /**
         * Fetch for offset pagination
         */
        
        for($i = $fetched_data_size; $i < $total_items;($i = $fetched_data_size)) {
            $endpoint = "lists/" . $this->list_id . "/members?offset=". $fetched_data_size."&count=1000";
            $offset_results = $this->getCurl("GET", $endpoint);
            $results["members"] = array_merge($results["members"],$offset_results["members"]);
            /** There is no new data fetched. */
            if($fetched_data_size == sizeof($results["members"])){
                break;
            }else{
                $fetched_data_size = sizeof($results["members"]);
            }
        }
       
        $data = array();
        foreach ($results["members"] as $result) {
            if (isset($members_map[$result["id"]])) {
                continue;
            }
            $data[] = [
                "member_id" => $result["id"],
                "email" => $result["email_address"],
                "member_status" => $result["status"],
                "listId" => $this->list_id,
                "status" => 4,
            ];
        }
        $inserted_log = 0;
        if (sizeof($data) > 0) {
            $inserted_log = $this->mail_chimp_model->insertMembersToDB($data);
        } else {
            echo "<pre>*** data already exists.";
        }
        if ($inserted_log > 0) {
            echo "<pre>*** Data inserted successfully ** <br>";
        }
        echo "<pre>*** inserted log** " . $inserted_log;
        /* print_r($data);
        echo " <br>*** ";
        die(); */
        return redirect()->to('/');
    }

    public function fetchMembersFromDb()
    {
        $db_members = $this->mail_chimp_model->fetchAllListMember($this->list_id);
        return view(
            'mailchimp/datatable',
            [
                "db_members" => $db_members,
                "validation_errors" => $this->session->getFlashdata("validation_errors"),
                "old_data" => $this->session->getFlashdata("old_data"),

            ]
        );
    }

}
