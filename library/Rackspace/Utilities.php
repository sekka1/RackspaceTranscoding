<?php

class Utilities
{

public function __construct( ){


}

/*
* Runs a curl command
*
* @input string $url - url for curl command
* @input array $post_params - array of all the post parameters
* @input array $headers = array of additional headers
 * 					array( 'content-type: json')
*
* @return string - output of curl call
*/
public function curlPost($url, $post_params, $headers = null, $method = 'POST'){
    // Does a post and optional file upload to a given url
    // INTPUT:
    /*
        $post_params['file'] = ‘@’.'/tmp/testfile.txt’;
        $post_params['submit'] = urlencode(’submit’);;
    */
        $returnVal = '';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
		
		// Set HTTP Method
		if($method != 'POST')
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		else
			curl_setopt($ch, CURLOPT_POST, true);

        // Optionally set header values
        if( $headers != null )
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);

        if( $result )
            $returnVal = $result;
        else
            $returnVal = curl_error($ch); 

        curl_close($ch);

        return $returnVal;
}


}

?>
