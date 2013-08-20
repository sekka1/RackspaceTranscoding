<?php
namespace AlgorithmsIO\Rackspace{
    
    /**
     * Working with Rackspace's CloudFiles
     * 
     * Documentation:
     * https://github.com/rackspace/php-opencloud/blob/master/docs/userguide/objectstore.md
     * 
     * Rackspace API doc:
     * http://docs.rackspace.com/files/api/v1/cf-devguide/content/Authentication-d1e639.html#d6e754
     * 
     */
    include_once('Rackspace.php');
    
    class ObjectStore extends Rackspace{

        private $objectStore;
        private $local_file_output_location;
        private $localFilePathAndName = null;

        public function initObjectStore(){
            $this->objectStore = $this->connection->ObjectStore('cloudFiles', $this->rackspace_location);
        }
        /**
         * This is the local path to write file to
         * 
         * @param string $location
         */
        public function setLocalFileOutputLocation($location){
            $this->local_file_output_location = $location;
        }
        /**
         * Get a file from a container and a file name
         * 
         * @param string $containerName
         * @param string $filename
         * @return boolean - true for success
         */
        public function getFile($containerName, $filename){    
            $ostore = $this->connection->ObjectStore('cloudFiles', $this->rackspace_location);
            $container = $ostore->Container($containerName);
            $obj = $container->DataObject($filename);
            $didSave = $obj->SaveToFilename($this->local_file_output_location.$this->username.'_'.$containerName.'_'.$filename);
            
            if($didSave)
                $this->localFilePathAndName = $this->local_file_output_location.$this->username.'_'.$containerName.'_'.$filename;
            
            return $didSave;
        }
        public function getSavedFilePathAndName(){
            return $this->localFilePathAndName;
        }
        /**
         * Puts a file into the container with the file name and the local file
         * 
         * @param string $putFile local file to put
         * @param string $containerName - container name
         * @param string $fileName - remote file name
         * @return bool - true for success
         */
        public function putFile($putFile, $containerName, $fileName){
            $ostore = $this->connection->ObjectStore('cloudFiles', $this->rackspace_location);
            $container = $ostore->Container($containerName);
            $obj = $container->DataObject();
            $paramsArray['name'] = $fileName;
            $didCreate = $obj->Create($paramsArray, $putFile);
            
            return $didCreate;
        }
    }

}

?>
