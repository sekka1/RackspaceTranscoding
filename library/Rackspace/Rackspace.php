<?php

namespace AlgorithmsIO\Rackspace{

    error_reporting(E_ALL);
    /*
     * Controls Rackspace's compute
     * 
     */
     require_once('opencloud_lib/rackspace.inc');
     require_once('Utilities.php');


      /*
       * Example usage of this class to spin up a server and apply monitoring checks to it
       * 
     $hostname = 'test-103';

     $rack = new Rackspace();
     $rack->setAuthentication('garlandio', '771404', 'ff15666ef8ec6b7567bb43fa35c3c5ac');
     $rack->authenticate();
     //$rack->imageList();
     //$rack->flavorList();


     $ip = $rack->serverCreate($hostname);

     $didAdd = $rack->loadBalancerAddNode(85923, $ip);

     if($didAdd){
            echo "added into LB\n";

             // It takes a little while before this entity shows up on Rackspace.  Try for the next 10 mins
             $count = 0;
             $didAddAgentID = FALSE;

             while(!$didAddAgentID && ($count < 100)){

                    // Update the monitoring entity with this server's name as the agent_id
                    $didAddAgentID = $rack->setEntityAgentID($hostname);

                    // Sleep for 10 seconds
                    sleep(10);
                    $count++;
             }

            if($didAddAgentID){

                    // Proceed to add in all the various checks for this server
                    $rack->addStandardChecks();
            }
     }
     else{
            echo "failed adding into LB\n";

             // Probably should delete this server.  Something went wrong
     }


      //$didAddAgentID = $rack->setEntityAgentID($hostname);



     echo "\n\nCompleted Server build\n\n";

     $errors = $rack->getErrors();
     if(count($errors)>0){
            print_r($errors);
     }else{
            echo "No Errors\n";
     }
    */


    class Rackspace{

            // Authentication
            private $auth_url = 'https://identity.api.rackspacecloud.com/v2.0/';
            protected $username = null;
            protected $tenant = null;
            protected $apiKey = null;
            protected $connection = null;
            private $auth_token = null; // After authenticating saving the auth token

            // Rackspace datacenter
            protected $rackspace_location = 'DFW';

            // OpenStack Compute object
            private $compute = null;
            private $entity_id = null; // monitory entity_id - unique identifier for the monitoring object

            // Utilities object
            private $utilities = null;

            // Server specs
            //private $rackspace_flavor = 5; // 4096 Mem, 160GB Disk
            private $rackspace_flavor = 3; // 1024 Mem, 40GB Disk
            private $rackspace_base_image = '5cbbc337-0004-411f-acad-f27b1dfd4979'; // our algorithms.io base-ubutnu-2 image

            // Load Balancer params
            private $rackspace_lb_id = null;

            // Alarms and notification
            private $notification_plan_id = 'npaOzMRXPS';  // Sends to garlandk@gmail.com

            // Errors array
            private $errors = array();

            public function __contruct(){
            }
            public function setAuthentication($username, $tenant, $apiKey){

                    $this->utilities = new \Utilities();

                    $this->username = $username;
                    $this->tenant = $tenant;
                    $this->apiKey = $apiKey;
            }
            public function setLocation($location){
                    $this->rackspace_location = $location;
            }
            public function setLoadbalancerID($lb_id){
                    $this->rackspace_lb_id = $lb_id;
            }
            public function setFlavor($flavor){
                    $this->rackspace_flavor = (int)$flavor;
            }
            public function getAuthToken(){
                    return $this->auth_token;
            }
            public function getErrors(){
                    return $this->errors;
            }
            public function authenticate(){
                    $credentials = array(
                        'username' => $this->username,
                        'tenantName' => $this->tenant,
                        'apiKey' => $this->apiKey
                    );
                    $this->connection = new \OpenCloud\Rackspace($this->auth_url, $credentials);

                    //echo "auth token: ".$this->connection->Token()."\n\n";

                    // Saving the auth token so it can be used later for calls the SDK doesnt support yet
                    $this->auth_token = $this->connection->Token();

                    // Setting Rackspace location
                    $this->compute = $this->connection->Compute('cloudServersOpenStack', $this->rackspace_location);
            }
            /*
             * Lists images available to this user
             * 
             */
            public function imageList(){

                    $imlist = $this->compute->ImageList();
                    while($image = $imlist->Next()) {
                        printf("\t%s - %s\n", $image->id, $image->name);
                    }
            }
            /*
             * Lists the various flavors for this user.  Profile of server 4GB of memory, cpu, etc
             * 
             */
            public function flavorList(){

                    $flist = $this->compute->FlavorList();
                    while($flavor = $flist->Next())
                            echo 'Flavor name: '. $flavor->name. ' - ram: '. $flavor->ram . ' - disk: '.$flavor->disk . "\n";
            }
            /*
             * Create a server
             */
            public function serverCreate($hostname){

                    $server = $this->compute->Server();

                    $server->name = $hostname;
                    $server->flavor = $this->compute->Flavor($this->rackspace_flavor);
                    $server->image = $this->compute->Image($this->rackspace_base_image);
                    $server->Create();

                    echo "Server id: ";
                    print("ID=".$server->id."..." . $server->addresses."\n");

                    $server->WaitFor("ACTIVE", 600, '\dot');

                    $server_ip = $this->getServersIPAddress($server);

                    return $server_ip;
            }
            /*
             * Get the servers IP address.  It might not be populated immediately in Rackspace's API.  We
             * have to poll it for a litle bit or fail after a certain amount of time.
             * 
             * @INPUT: server object
             */
            public function getServersIPAddress($server){

                    $ip = '';

                     // It takes a little while before the IP shows up on Rackspace.  Try for the next 10 mins
                     $count = 0;
                     $didGet = FALSE;

                     while(!$didGet && ($count < 20)){

                            // Goto Rackspace and fetch the new value
                            $server->Refresh();

                            // Get IP
                            $ip = $server->ip(4);

    echo "in getServersIPAddress.  count is: ".$count." Ip is: ".$ip."\n";

                            if($ip != ''){
                                    $didGet = true;
                            }

                            // Sleep for 10 seconds
                            sleep(10);
                            $count++;
                     }
                     return $ip;
            }
            /*
             * Add a server to an existing load balancer
             */
            public function loadBalancerAddNode($loadbalancer_id, $ip){

                    $didAddSuccessfully = false;

                    //$this->utilities = new Utilities();

                    $data_array['nodes'][0]['address'] = $ip;
                    $data_array['nodes'][0]['port'] = 80;
                    $data_array['nodes'][0]['condition'] = 'ENABLED';
                    $data_array['nodes'][0]['type'] = 'PRIMARY';

                    $post_params = json_encode($data_array);

                    $header = array('Content-Type: application/json',
                                                    'X-Auth-Token: '.$this->auth_token
                                                    );

                    $url = 'https://dfw.loadbalancers.api.rackspacecloud.com/v1.0/'.$this->tenant.'/loadbalancers/'.$loadbalancer_id.'/nodes';


                    $result_json = $this->utilities->curlPost($url, $post_params, $header);

                    $result_array = json_decode($result_json, true);

                    if(json_last_error() == 'JSON_ERROR_NONE'){
                            if(isset($result_array['nodes'])){
                                    if($result_array['nodes'][0]['status'] == 'ONLINE'){
                                            $didAddSuccessfully = true;
                                    }
                            }
                    }

                    return $didAddSuccessfully;
            }
            /*
             * Sets the agent_id in the entity with the given hostname to the hostname
             * 
             * The agent_id is used by Rackspace monitoring to correlate the info coming in to the server
             */
             public function setEntityAgentID($hostname){

                    $didSetEntity = false;

                    $entity_array = $this->getEntitiesList();

                    if($entity_array != null){
                            // Find the entity with the label $hostname
                            $entity = $this->getEntityByLabelName($entity_array, $hostname);
    echo "getEntityByLabelName.  Entity Information:\n";
    print_r($entity);
                            if($entity != null){
                                    // Set the agent_id with the given entity

                                    $this->updateEntity($entity['id'], 'agent_id', $hostname);

                                    // Save entity_id
                                    $this->entity_id = $entity['id'];

                                    $didSetEntity = true;
                            }else{
                                    // Error - getEntityByLabelName is null
                                    array_push($this->errors, array('getEntityByLabelName returned a null list'));
                            }

                    }else{
                            // Error - $entity_array is null
                            array_push($this->errors, array('getEntitiesList returned a null list'));
                    }
                    return $didSetEntity;
             }
             /*
              * Gets the Monitoriing Entities list
              */
             public function getEntitiesList(){

                    $results = null;

                    $url = 'https://monitoring.api.rackspacecloud.com/v1.0/'.$this->tenant.'/entities';

                    $header = array('Content-Type: application/json', 'X-Auth-Token: '.$this->auth_token);

                    $result_json = $this->utilities->curlPost($url, null, $header, 'GET');

                    $result_array = json_decode($result_json, true);

                    if(json_last_error() == 'JSON_ERROR_NONE'){
                            $results = $result_array;
                    }else{
                            array_push($this->errors, array('getEntitiesList returns none valid json', $result_json));
                    }
                    return $results;
             }
             /*
              * Returns the entity by the label name
              * 
              * Input: array returned by $this->getEntitiesList()
              */
              public function getEntityByLabelName($entity_array, $search_for_label){

                    $return_entity = null;

                    foreach($entity_array['values'] as $anEntity){
                            if($anEntity['label'] == $search_for_label){
                                    $return_entity = $anEntity;
                            }
                    }
                    return $return_entity;
              }
              /*
               * Update entity
               */
              public function updateEntity($entity_id, $key, $value){

                    $url = 'https://monitoring.api.rackspacecloud.com/v1.0/'.$this->tenant.'/entities/'.$entity_id;

                    $post_params = '{"'.$key.'":"'.$value.'"}';

                    $header = array('Content-Type: application/json', 'X-Auth-Token: '.$this->auth_token);

                    $result = $this->utilities->curlPost($url, $post_params, $header, 'PUT');

                    echo $result;
               }
                    /*
                     * Get a listing of all the checks for this server.
                     */
                    public function listAllChecks(){

                            $url = 'https://monitoring.api.rackspacecloud.com/v1.0/'.$this->tenant.'/entities/'.$this->entity_id.'/checks/';

                            $header = array('Content-Type: application/json', 'X-Auth-Token: '.$this->auth_token);

                            $result = $this->utilities->curlPost($url, $post_params, $header, 'GET');

                            return $result;
                    }
               /*
                * Add all the standard checks for the server
                */
                public function addStandardChecks(){
                    $this->checkAddAgentMemory($this->entity_id);
                            $this->checkAddAgentLoadAverage($this->entity_id);
                            $this->checkAddAgentCpu($this->entity_id);
                            $this->checkAddAgentDisk($this->entity_id);
                            $this->checkAddAgentNetwork($this->entity_id);
                            $this->checkAddAgentFilesystem($this->entity_id);
                }
                    /*
                     * Check - Add agent.memory
                     */
                    public function checkAddAgentMemory($entity_id){
                            $url = 'https://monitoring.api.rackspacecloud.com/v1.0/'.$this->tenant.'/entities/'.$entity_id.'/checks';
                            $post_params = '{"label":"agent.memory","type":"agent.memory","timeout":30,"period":100,"target_alias":"default"}';
                            $header = array('Content-Type: application/json', 'X-Auth-Token: '.$this->auth_token);
                            $result = $this->utilities->curlPost($url, $post_params, $header, 'POST');
                            echo $result;
                    }
                    /*
                     * Check - Add agent.load_average
                     */
                    public function checkAddAgentLoadAverage($entity_id){
                            $url = 'https://monitoring.api.rackspacecloud.com/v1.0/'.$this->tenant.'/entities/'.$entity_id.'/checks';
                            $post_params = '{"label":"agent.load_average","type":"agent.load_average","timeout":30,"period":100,"target_alias":"default"}';
                            $header = array('Content-Type: application/json', 'X-Auth-Token: '.$this->auth_token);
                            $result = $this->utilities->curlPost($url, $post_params, $header, 'POST');
                            echo $result;
                    }
                    /*
                     * Check - Add agent.cpu
                     */
                    public function checkAddAgentCpu($entity_id){
                            $url = 'https://monitoring.api.rackspacecloud.com/v1.0/'.$this->tenant.'/entities/'.$entity_id.'/checks';
                            $post_params = '{"label":"agent.cpu","type":"agent.cpu","timeout":30,"period":100,"target_alias":"default"}';
                            $header = array('Content-Type: application/json', 'X-Auth-Token: '.$this->auth_token);
                            $result = $this->utilities->curlPost($url, $post_params, $header, 'POST');
                            echo $result;
                    }
                    /*
                     * Check - Add agent.disk
                     */
                    public function checkAddAgentDisk($entity_id){
                            $url = 'https://monitoring.api.rackspacecloud.com/v1.0/'.$this->tenant.'/entities/'.$entity_id.'/checks';
                            $post_params = '{"label":"agent.disk","type":"agent.disk","timeout":30,"period":100,"target_alias":"default","details":{"target":"/dev/xvda1"}}';
                            $header = array('Content-Type: application/json', 'X-Auth-Token: '.$this->auth_token);
                            $result = $this->utilities->curlPost($url, $post_params, $header, 'POST');
                            echo $result;
                    }
                    /*
                     * Check - Add agent.network
                     */
                    public function checkAddAgentNetwork($entity_id){
                            $url = 'https://monitoring.api.rackspacecloud.com/v1.0/'.$this->tenant.'/entities/'.$entity_id.'/checks';
                            $post_params = '{"label":"agent.network","type":"agent.network","timeout":30,"period":100,"target_alias":"default","details":{"target":"eth0"}}';
                            $header = array('Content-Type: application/json', 'X-Auth-Token: '.$this->auth_token);
                            $result = $this->utilities->curlPost($url, $post_params, $header, 'POST');
                            echo $result;
                    }
                    /*
                     * Check - Add agent.filesystem
                     */
                    public function checkAddAgentFilesystem($entity_id){
                            $url = 'https://monitoring.api.rackspacecloud.com/v1.0/'.$this->tenant.'/entities/'.$entity_id.'/checks';
                            $post_params = '{"label":"agent.filesystem","type":"agent.filesystem","timeout":30,"period":100,"target_alias":"default","details":{"target":"/"}}';
                            $header = array('Content-Type: application/json', 'X-Auth-Token: '.$this->auth_token);
                            $result = $this->utilities->curlPost($url, $post_params, $header, 'POST');
                            echo $result;
                    }
                    /*
                     * Adds in all the standard alarms into this server
                     */
                    public function addStandardAlarms(){
                            $allChecksJson = $this->listAllChecks();
                            $allChecksArray = json_decode($allChecksJson, true);

                            if(json_last_error()==0){
                                    if(is_array($allChecksArray['values'])){
                                            foreach($allChecksArray['values'] as $aCheck){

    echo "processing this check type: ".$aCheck['type']."\n";

                                                    switch($aCheck['type']){
                                                            case 'agent.disk':
                                                                    $this->alarmAgentDisk($aCheck['id']);
                                                                    break;
                                                            case 'agent.load_average':
                                                                    $this->alarmAgentLoadAverage($aCheck['id']);
                                                                    break;
                                                            case 'agent.network':
                                                                    $this->alarmAgentNetwork($aCheck['id']);
                                                                    break;
                                                            case 'agent.memory':
                                                                    $this->alarmAgentMemory($aCheck['id']);
                                                                    break;
                                                            case 'agent.filesystem':
                                                                    $this->alarmAgentFilesystem($aCheck['id']);
                                                                    break;
                                                            case 'agent.cpu':
                                                                    $this->alarmAddAgentCPU($aCheck['id']);
                                                                    break;
                                                    }
                                            }
                                    }
                            }
                    }
                    /*
                     * add - agent.cpu alarm
                     */
                    public function alarmAddAgentCPU($check_id){
                            $url = 'https://monitoring.api.rackspacecloud.com/v1.0/'.$this->tenant.'/entities/'.$this->entity_id.'/alarms';
                            $post_params = '{"check_id":"'.$check_id.'","criteria":"if (metric[\"sys_percent_average\"] >= 90) { return new AlarmStatus(CRITICAL); } return new AlarmStatus(OK);","notification_plan_id":"'.$this->notification_plan_id.'"}';
                            $header = array('Content-Type: application/json', 'X-Auth-Token: '.$this->auth_token);
                            $result = $this->utilities->curlPost($url, $post_params, $header, 'POST');
                    }
                    /*
                     * add - agent.load_average alarm
                     */
                    public function alarmAgentLoadAverage($check_id){

                            // 15m alarm	
                            $url = 'https://monitoring.api.rackspacecloud.com/v1.0/'.$this->tenant.'/entities/'.$this->entity_id.'/alarms';
                            $post_params = '{"check_id":"'.$check_id.'","criteria":"if (metric[\"15m\"] >= 1.25) { return new AlarmStatus(CRITICAL); } return new AlarmStatus(OK);","notification_plan_id":"'.$this->notification_plan_id.'"}';
                            $header = array('Content-Type: application/json', 'X-Auth-Token: '.$this->auth_token);
                            $result = $this->utilities->curlPost($url, $post_params, $header, 'POST');

                            // 5m warning
                            $post_params = '{"check_id":"'.$check_id.'","criteria":"if (metric[\"5m\"] >= 1.25) { return new AlarmStatus(WARNING); } return new AlarmStatus(OK);","notification_plan_id":"'.$this->notification_plan_id.'"}';
                            $result = $this->utilities->curlPost($url, $post_params, $header, 'POST');
                    }
                    /*
                     * add - agent.network alarms
                     */
                    public function alarmAgentNetwork($check_id){

                            // tx_collisions > 0
                            $url = 'https://monitoring.api.rackspacecloud.com/v1.0/'.$this->tenant.'/entities/'.$this->entity_id.'/alarms';
                            $post_params = '{"check_id":"'.$check_id.'","criteria":"if (metric[\"tx_collisions\"] > 0) { return new AlarmStatus(CRITICAL); } return new AlarmStatus(OK);","notification_plan_id":"'.$this->notification_plan_id.'"}';
                            $header = array('Content-Type: application/json', 'X-Auth-Token: '.$this->auth_token);
                            $result = $this->utilities->curlPost($url, $post_params, $header, 'POST');

                            // rx_errors > 0
                            $post_params = '{"check_id":"'.$check_id.'","criteria":"if (metric[\"rx_errors\"] > 0) { return new AlarmStatus(CRITICAL); } return new AlarmStatus(OK);","notification_plan_id":"'.$this->notification_plan_id.'"}';
                            $result = $this->utilities->curlPost($url, $post_params, $header, 'POST');

                            // tx_errors > 0
                            $post_params = '{"check_id":"'.$check_id.'","criteria":"if (metric[\"tx_errors\"] > 0) { return new AlarmStatus(CRITICAL); } return new AlarmStatus(OK);","notification_plan_id":"'.$this->notification_plan_id.'"}';
                            $result = $this->utilities->curlPost($url, $post_params, $header, 'POST');

                            // rx_dropped > 0
                            $post_params = '{"check_id":"'.$check_id.'","criteria":"if (metric[\"rx_dropped\"] > 0) { return new AlarmStatus(CRITICAL); } return new AlarmStatus(OK);","notification_plan_id":"'.$this->notification_plan_id.'"}';
                            $result = $this->utilities->curlPost($url, $post_params, $header, 'POST');

                            // tx_dropped > 0
                            $post_params = '{"check_id":"'.$check_id.'","criteria":"if (metric[\"tx_dropped\"] > 0) { return new AlarmStatus(CRITICAL); } return new AlarmStatus(OK);","notification_plan_id":"'.$this->notification_plan_id.'"}';
                            $result = $this->utilities->curlPost($url, $post_params, $header, 'POST');
                    }
                    /*
                     * add - agent.memory alarms
                     */ 
                    public function alarmAgentMemory($check_id){
                            $url = 'https://monitoring.api.rackspacecloud.com/v1.0/'.$this->tenant.'/entities/'.$this->entity_id.'/alarms';
                            $post_params = '{"check_id":"'.$check_id.'","criteria":"if (metric[\"free\"] < 25000) { return new AlarmStatus(CRITICAL); } return new AlarmStatus(OK);","notification_plan_id":"'.$this->notification_plan_id.'"}';
                            $header = array('Content-Type: application/json', 'X-Auth-Token: '.$this->auth_token);
                            $result = $this->utilities->curlPost($url, $post_params, $header, 'POST');
                    }
                    /*
                     * add - agent.memory alarms
                     */
                    public function alarmAgentFilesystem($check_id){
                            $url = 'https://monitoring.api.rackspacecloud.com/v1.0/'.$this->tenant.'/entities/'.$this->entity_id.'/alarms';
                            $post_params = '{"check_id":"'.$check_id.'","criteria":"if (metric[\"avail\"] < 2068524) { return new AlarmStatus(CRITICAL); } return new AlarmStatus(OK);","notification_plan_id":"'.$this->notification_plan_id.'"}';
                            $header = array('Content-Type: application/json', 'X-Auth-Token: '.$this->auth_token);
                            $result = $this->utilities->curlPost($url, $post_params, $header, 'POST');
                    }
                    /*
                     * add - agent.disk alarms
                     */
                    public function alarmAgentDisk($check_id){
                            // rtime > 500111  in ms - read time from disk??
                            $url = 'https://monitoring.api.rackspacecloud.com/v1.0/'.$this->tenant.'/entities/'.$this->entity_id.'/alarms';
                            $post_params = '{"check_id":"'.$check_id.'","criteria":"if (metric[\"rtime\"] > 500111) { return new AlarmStatus(CRITICAL); } return new AlarmStatus(OK);","notification_plan_id":"'.$this->notification_plan_id.'"}';
                            $header = array('Content-Type: application/json', 'X-Auth-Token: '.$this->auth_token);
                            $result = $this->utilities->curlPost($url, $post_params, $header, 'POST');

                            // wtime > 500111
                            $post_params = '{"check_id":"'.$check_id.'","criteria":"if (metric[\"wtime\"] > 700111) { return new AlarmStatus(CRITICAL); } return new AlarmStatus(OK);","notification_plan_id":"'.$this->notification_plan_id.'"}';
                            $result = $this->utilities->curlPost($url, $post_params, $header, 'POST');
                    }
    }
            /*
             * Function to display the server->WaitFor function when building a server
             * 
             */
            function dot($server) {
                    printf("%s %3d%%\n", $server->status, $server->progress);
            }	
        
}

?>