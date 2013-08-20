<?php
namespace AlgorithmsIO\Ffmpeg{
    
    /**
     * Running FFmpeg CLI tool
     * 
     * 
     */
    
    class Ffmpeg{
        
        private $FFMPEG_Binary;
        private $logFile;

        private $sourceFilePathAndName;
        private $outputFilePathAndName;
        private $transcodeTemplateName;
        
        /**
         * Full path to the source file
         * ex: /tmp/source.mov
         * 
         * @param string $source
         */
        public function setSourceFilePathAndName($source){
            $this->sourceFilePathAndName = $source;
        }
        /**
         * Full path to the output file
         * ex: /tmp/out.mp4
         * 
         * @param string $output
         */
        public function setOutputFilePathAndName($output){
            $this->outputFilePathAndName = $output;
        }
        /**
         * Trancoding template to use.
         * 
         * @param string $template
         */
        public function setTrancodingTemplate($template){
            $this->transcodeTemplateName = $template;
        }
        public function setFFMPEGBinaryLocaiton($location){
            $this->FFMPEG_Binary = $location;
        }
        public function setLogFile($location){
            $this->logFile = $location;
        }
        /**
         * Converts into Apple/iPhone formats.  Everything is the same except for the size
         * 
         * iPhone converstion chart: https://develop.participatoryculture.org/index.php/ConversionMatrix
         * $size string: http://ffmpeg.org/ffmpeg-utils.html
         * 
         * Videos to test with: http://support.apple.com/kb/HT1425
         * 
         * @param string $size
         */
        private function executeIphoneTranscode($size){
            
            $ffmpeg_options = '-y -i '.$this->sourceFilePathAndName.' -acodec aac -ac 2 -strict experimental -ab 160k -s '.$size.'  -vcodec libx264 -preset slow -profile:v baseline -level 30 -maxrate 10000000 -bufsize 10000000 -b 1200k -f mp4 -threads 0 '.$this->outputFilePathAndName;
        
            echo shell_exec($this->FFMPEG_Binary." ".$ffmpeg_options." </dev/null >/dev/null 2>".$this->logFile);
        
        }
        /**
         * Transcoding into an android format
         * 
         * ffmpeg options: https://develop.participatoryculture.org/index.php/ConversionMatrix
         * 
         * @param string $size
         */
        private function executeAndroidTranscode($size){
            $ffmpeg_options = '-y -i '.$this->sourceFilePathAndName.' -acodec aac -ab 160k -s '.$size.' -vcodec libx264 -preset slow -profile:v baseline -level 30 -maxrate 10000000 -bufsize 10000000 -f mp4 -threads 0 '.$this->outputFilePathAndName;
        
            echo shell_exec($this->FFMPEG_Binary." ".$ffmpeg_options." </dev/null >/dev/null 2>".$this->logFile);
        
        }
        /**
         * Transcode based on a template that was given
         * 
         */
        public function transcode(){
            switch ($this->transcodeTemplateName) {
                /**
                 * Apple
                 */
                case 'iPhone':
                    $this->executeIphoneTranscode('sntsc');
                    break;
                case 'iPhone_5':
                    $this->executeIphoneTranscode('hd1080');
                    break;
                case 'iPhone_4':
                    $this->executeIphoneTranscode('vga');
                    break;
                case 'iPad':
                    $this->executeIphoneTranscode('xga');
                    break;
                case 'iPad_3':
                    $this->executeIphoneTranscode('hd1080');
                    break;
                case 'Apple_TV':
                    $this->executeIphoneTranscode('hd720');
                    break;
                /**
                 * Samsung
                 */
                case 'Samsung_s_s2_plus':
                    $this->executeIphoneTranscode('480x800');
                    break;
                case 'Samsung_s3':
                    $this->executeIphoneTranscode('720x1280');
                    break;
                case 'Samsung_galaxy_nexus':
                    $this->executeIphoneTranscode('720x1280');
                    break;
                case 'Samsung_galaxy_tab':
                    $this->executeIphoneTranscode('600x1024');
                    break;
                case 'Samsung_galaxy_tab_10.1':
                    $this->executeIphoneTranscode('800x1280');
                    break;
                case 'Samsung_galaxy_note':
                    $this->executeIphoneTranscode('800x1280');
                    break;
                case 'Samsung_galaxy_note_2':
                    $this->executeIphoneTranscode('1080x1920');
                    break;
                case 'Samsung_infuse_4g':
                    $this->executeIphoneTranscode('800x1280');
                    break;
                case 'Samsung_epic_touch_4g':
                    $this->executeIphoneTranscode('480x800');
                    break;
                /**
                 * HTC
                 */
                case 'HTC_wildfire':
                    $this->executeIphoneTranscode('240x320');
                    break;
                case 'HTC_desire':
                case 'HTC_droid_incredible':
                case 'HTC_thunderbolt':
                case 'HTC_evo_4g':
                    $this->executeIphoneTranscode('480x800');
                    break;
                case 'HTC_sensation':
                    $this->executeIphoneTranscode('540x960');
                    break;
                case 'HTC_rezound':
                case 'HTC_one_x':
                    $this->executeIphoneTranscode('720x1280');
                    break;
                /**
                 * Motorola
                 */
                case 'motorola_droid_x':
                    $this->executeIphoneTranscode('854x480');
                    break;
                case 'motorola_droid_x2':
                    $this->executeIphoneTranscode('1280x720');
                    break;
                case 'motorola_razr':
                    $this->executeIphoneTranscode('960x540');
                    break;
                case 'motorola_xoom':
                    $this->executeIphoneTranscode('1280x800');
                    break;
                /**
                 * Kindle
                 */
                case 'kindle_fire':
                    $this->executeIphoneTranscode('1224x600');
                    break;
                default:
                    break;
            }
        }
    }

}

?>
