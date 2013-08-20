<?php

require_once("library/Rackspace/ObjectStore.php");
require_once("library/Ffmpeg/Ffmpeg.php");

define('OUTPUT_FILE_LOCATION', '/tmp/');
define('FFMPEG_BINARY_LOCATION','/usr/local/bin/ffmpeg');
define('FFMPEG_LOG_LOCATION','/tmp/ffmpeg.log');

/*
 * Usage:
 * 
$paramsArray['username'] = 'garlandio';
$paramsArray['auth_key'] = 'ff15666ef8ec6b7567bb43fa35c3c5ac';
$paramsArray['input_container'] = 'Input_1';    
$paramsArray['output_container'] = 'Output_1';
$paramsArray['input_filename'] = 'cloudfiles1';    
$paramsArray['output_filename'] = '123.mpeg';
$paramsArray['transcode_template'] = 'iPhone_5';
    
$a = new RackspaceTranscode();
$a->run($paramsArray);
*/

/**
 * Transcode a video
 * 
 * 1) Get file from Rackspace CloudFiles
 * 2) Transcode
 * 3) Put transcoded file to Rackspace CloudFiles
 */
class RackspaceTranscode{
    
    private $results;
    
    /**
     * This is the function that the worker wrapper will call and pass in the 
     * parameters specified with this "algorithm"
     * 
     * @param array $paramsArray
     */
    public function run($paramsArray){
        
        $username = $paramsArray['username'];
        $auth_key = $paramsArray['auth_key'];
        $tenant = '';//$paramsArray['tenant'];
        $rackspace_input_container = $paramsArray['input_container'];
        $rackspace_output_container = $paramsArray['output_container'];
        $rackspace_input_filename = $paramsArray['input_filename'];
        $rackspace_output_filename = $paramsArray['output_filename'];
        $transcode_template = $paramsArray['transcode_template'];


        $rack = new \AlgorithmsIO\Rackspace\ObjectStore();
        $rack->setAuthentication($username, $tenant, $auth_key);
        $rack->authenticate();
        $rack->initObjectStore();
        $rack->setLocalFileOutputLocation(OUTPUT_FILE_LOCATION);
        
//echo $rack->getAuthToken()."\n";

        // Retrieve File to process
        $didGet = $rack->getFile($rackspace_input_container, $rackspace_input_filename);
        $localFilePathAndName = $rack->getSavedFilePathAndName();

        /**
         * The transcoding work
         */
        $outputFilePathAndName = OUTPUT_FILE_LOCATION.'output_'.$username.'_'.$rackspace_output_container.'_'.$rackspace_output_filename;
        
        $ffmpeg = new \AlgorithmsIO\Ffmpeg\Ffmpeg();
        $ffmpeg->setFFMPEGBinaryLocaiton(FFMPEG_BINARY_LOCATION);
        $ffmpeg->setLogFile(FFMPEG_LOG_LOCATION);
        $ffmpeg->setSourceFilePathAndName($localFilePathAndName);
        $ffmpeg->setOutputFilePathAndName($outputFilePathAndName);
        $ffmpeg->setTrancodingTemplate($transcode_template);
        $ffmpeg->transcode();

        // Put transcoded file back into Rackspace
        $didPut = $rack->putFile($outputFilePathAndName, $rackspace_output_container, $rackspace_output_filename);      
        
        if($didGet && $didPut)
            $this->results = '{"getFile":"success","transcode":"success",putFile":"success"}';
        else
            $this->results = '{"getFile":"failed","transcode":"failed",putFile":"failed"}';
        
        
        // Remove locale files
        unset($localFilePathAndName);
        unset($outputFilePathAndName);
        
    }
    /**
     * This is a required function.  The worker wrapper will call this after the
     * "runMe" function runs to get the results.  The results will be uploaded
     * back into the caller's datasource bucket.
     * 
     * @return string
     */
    public function getResults(){
        return $this->results;
    }
}






?>