<?php
class rappi
{
 
    function getToken() 
    {
        $url=URL_BASE_API_RAPPI.'auth/guest_access_token';
        $token='';
        $arr_data['url']=$url;
        $arr_data['params']=array();
        $arr_resp= HTTPRequester::HTTPPost($arr_data);
        if(!empty($arr_resp))
        {
          $arr= json_decode($arr_resp,true);
          $token=$arr['access_token'];
        }
        return $token;
    } 
    
    function getRestaurantList($arrIn=array()) 
    {
        $url=URL_BASE_API_RAPPI.'restaurant-bus/stores/catalog/home/v2';
        $arr_data['url']=$url;
        $arr_data['json']=true;
        $arr=array();
        
        $arr_p['lat']=$arrIn['lat'];
        $arr_p['lng']=$arrIn['lng'];
        $arr_p['store_type']='restaurant';
        $arr_p['is_prime']=false;
        $arr_p['states']=array('opened','unavailable','closed');
   
        $arr_data['params']=$arr_p;
        $arr_data['token']=$arrIn['token'];
        $arr_resp= HTTPRequester::HTTPPost($arr_data);
        if(!empty($arr_resp))
        {
          $arr= json_decode($arr_resp,true);
        }
        return $arr;
    } 
	
}
?>