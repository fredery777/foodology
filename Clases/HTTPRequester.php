<?php

class HTTPRequester {

    /**
     * @description Make HTTP-GET call
     * @param       $url
     * @param       array $params
     * @return      HTTP-Response body or an empty string if the request fails or is empty
     */
    public static function HTTPGet($arr_data=array()) {
        
         $url=$arr_data['url'];
        $params=$arr_data['params'];
        $token=(isset($arr_data['token'])&&!empty($arr_data['token']))?$arr_data['token']:'';
        $json=(isset($arr_data['json'])&&!empty($arr_data['json']))?$arr_data['josn']:false;
        $host=(isset($arr_data['host'])&&!empty($arr_data['host']))?$arr_data['host']:'';
        $arr_aut=(isset($arr_data['arr_aut'])&&!empty($arr_data['arr_aut']))?$arr_data['arr_aut']:array();
        $arr_headers=(isset($arr_data['arr_headers'])&&!empty($arr_data['arr_headers']))?$arr_data['arr_headers']:array();
        
        if ($json) {
            $headers[] = 'Accept: application/json';
            $headers[] = 'Content-Type: application/json';
        }
        $headers[] = 'Accept-Encoding: gzip, deflate';
        $headers[] = 'Cache-Control: no-cache';
        $headers[] = 'Connection: keep-alive';
        
        if(!empty($arr_headers))
        {
            for ($i = 0; $i < count($arr_headers); $i++) 
            {
                     $headers[] = $arr_headers[$i];
            }
        }
        
        if (!empty($token)) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        if (!empty($arr_aut)) {
            $headers[] = 'Authorization: ' . $arr_aut['type'] . ' ' . base64_encode($arr_aut['user_name'] . ':' . $arr_aut['user_pass']);
        }
        if (!empty($host)) {
            $headers[] = 'Host: ' . $host;
        }
        
        $query = http_build_query($params);
        
        
        
            $url_get=$url . '?' . $query;
          //  print_r($url_get);exit;
        //setup the request, you can also use CURLOPT_URL
        $ch = curl_init($url_get);

        // Returns the data/output as a string instead of raw data
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       
        //Set your auth headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // get stringified data/output. See CURLOPT_RETURNTRANSFER
        $response = curl_exec($ch);

        // get info about the request
        $info = curl_getinfo($ch);
        // close curl resource to free up system resources
        curl_close($ch);
        if ($err) {
            $arr_error = array();
            $arr_error['errors'] = array('message' => $err);
            return json_encode($arr_error);
        }
        

        return $response;
    }

    /**
     * @description Make HTTP-POST call
     * @param       $url
     * @param       array $params
     * @return      HTTP-Response body or an empty string if the request fails or is empty
     */
    public static function HTTPPost($arr_data=array())
    {
        $url=$arr_data['url'];
        $params=$arr_data['params'];
        $token=(isset($arr_data['token'])&&!empty($arr_data['token']))?$arr_data['token']:'';
        $json=(isset($arr_data['json'])&&!empty($arr_data['json']))?$arr_data['json']:false;
        $host=(isset($arr_data['host'])&&!empty($arr_data['host']))?$arr_data['host']:'';
        $arr_aut=(isset($arr_data['arr_aut'])&&!empty($arr_data['arr_aut']))?$arr_data['arr_aut']:array();
        $arr_headers=(isset($arr_data['arr_headers'])&&!empty($arr_data['arr_headers']))?$arr_data['arr_headers']:array();
        
      
        $query = ($json) ? json_encode($params) : http_build_query($params); // Encode the data array into a JSON string
        

        if ($json) {
            $headers[] = 'Accept: application/json';
            $headers[] = 'Content-Type: application/json';
        }
        $headers[] = 'Accept-Encoding: gzip, deflate';
        $headers[] = 'Cache-Control: no-cache';
        $headers[] = 'Connection: keep-alive';
        
        if(!empty($arr_headers))
        {
            for ($i = 0; $i < count($arr_headers); $i++) 
            {
                     $headers[] = $arr_headers[$i];
            }
        }
        
        if (!empty($token)) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        if (!empty($arr_aut)) {
            $headers[] = 'Authorization: ' . $arr_aut['type'] . ' ' . base64_encode($arr_aut['user_name'] . ':' . $arr_aut['user_pass']);
        }
        if (!empty($host)) {
            $headers[] = 'Host: ' . $host;
        }
      

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $query,
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);

        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $arr_error = array();
            $arr_error['errors'] = array('message' => $err);
            return json_encode($arr_error);
        }

        return $response;
    }

    /**
     * @description Make HTTP-PUT call
     * @param       $url
     * @param       array $params
     * @return      HTTP-Response body or an empty string if the request fails or is empty
     */
    public static function HTTPPut($url, array $params, $token = '', $json = false, $host = '', $arr_aut = array()) {
        $query = ($json) ? json_encode($params) : http_build_query($params); // Encode the data array into a JSON string

        if ($json) {
            $headers[] = 'Accept: application/json';
            $headers[] = 'Content-Type: application/json';
        }
        $headers[] = 'Accept-Encoding: gzip, deflate';
        $headers[] = 'Cache-Control: no-cache';
        $headers[] = 'Connection: keep-alive';
        if (!empty($token)) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        if (!empty($arr_aut)) {
            $headers[] = 'Authorization: ' . $arr_aut['type'] . ' ' . base64_encode($arr_aut['user_name'] . ':' . $arr_aut['user_pass']);
        }

        if (!empty($host)) {
            $headers[] = 'Host: ' . $host;
        }


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => $query,
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);

        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $arr_error = array();
            $arr_error['errors'] = array('message' => $err);
            return json_encode($arr_error);
        }

        return $response;
    }

    /**
     * @category Make HTTP-DELETE call
     * @param    $url
     * @param    array $params
     * @return   HTTP-Response body or an empty string if the request fails or is empty
     */
    public static function HTTPDelete($url, array $params, $token = '', $json = false, $host = '', $arr_aut = array()) {
        $query = ($json) ? json_encode($params) : http_build_query($params); // Encode the data array into a JSON string

        if ($json) {
            $headers[] = 'Accept: application/json';
            $headers[] = 'Content-Type: application/json';
        }

        $headers[] = 'Accept-Encoding: gzip, deflate';
        $headers[] = 'Cache-Control: no-cache';
        $headers[] = 'Connection: keep-alive';

        if (!empty($token)) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        if (!empty($arr_aut)) {
            $headers[] = 'Authorization: ' . $arr_aut['type'] . ' ' . base64_encode($arr_aut['user_name'] . ':' . $arr_aut['user_pass']);
        }

        if (!empty($host)) {
            $headers[] = 'Host: ' . $host;
        }
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_POSTFIELDS => $query,
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            $arr_error = array();
            $arr_error['errors'] = array('message' => $err);
            return json_encode($arr_error);
        }

        return $response;
    }

}

?>