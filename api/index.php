<?php

ini_set("error_reporting", (E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR | E_NOTICE | E_COMPILE_WARNING | E_RECOVERABLE_ERROR | E_CORE_ERROR | E_ALL | E_STRICT | E_ALL));
ini_set('display_errors', '1');
session_start();
header('Access-Control-Allow-Origin: *');
header('Access-control-Allow-Headers:Access-Control-Allow-Origin, Access-Control-Allow-Headers, Origin, X-Requested-With, Content-Type,CORELATION_ID,X-CSRF-TOKEN');
header('Access-Control-Allow-Methods: GET, HEAD, POST, PUT, PATCH, DELETE, OPTIONS');
include("../Clases/class.principal.php");
header('Content-Type: application/json');

if (isset($_POST["function_name"])) {
    $function_name = (isset($_POST["function_name"])) ? $_POST["function_name"] : '';
    $parameter = (isset($_POST["parameter"])) ? $_POST["parameter"] : '';
} else {
    $arr_json = json_decode(file_get_contents("php://input"), true);
    $function_name = (isset($arr_json["function_name"])) ? $arr_json["function_name"] : '';
    $parameter = (isset($arr_json["parameter"])) ? json_encode($arr_json["parameter"]) : '';
}

//print_r($function_name);exit;

if (!empty($function_name)) {
    if (!empty($parameter)) {
        echo $function_name($parameter);
    } else {
        echo $function_name();
    }
} else {
    echo '{"error":"Ingrese el nombre de la funcion"}';
}



function update_restaurant_list($data = '')
{
    if(!isset($_SESSION['app_movil']))$_SESSION['app_movil'] = true;
    $obj_util = new util();
    $obj_rappi = new rappi();
    $obj_bd = new conector_bd();
    $obj_restaurant_location = new restaurant_location();
    if(!empty($data))
    {
        $arr_data = json_decode($data,true);
        if(isset($arr_data['latitude'])&&!empty($arr_data['latitude'])&&isset($arr_data['longitude'])&&!empty($arr_data['longitude']))
        {
           $token=$obj_rappi->getToken();
           if(!empty($token))
           {
               $creo=false;
               $arr_p['token']=$token;
               $arr_p['lat']=$arr_data['latitude'];
               $arr_p['lng']=$arr_data['longitude'];
               $arr_rest_list=$obj_rappi->getRestaurantList($arr_p);
               if(!empty($arr_rest_list))
               {
                   if(isset($arr_rest_list['stores']))
                   {
                       $arr_data=$arr_rest_list['stores'];
                       for($i = 0; $i < count($arr_data); $i++) 
                       {
                           $data=array();
                           $data['open_time']= $arr_data[$i]['schedules'][0]['open_time'];  
                           $data['close_time']= $arr_data[$i]['schedules'][0]['close_time'];  
                           $data['restaurant_name']= $obj_util->limpia_dato($arr_data[$i]['name']);  
                           $data['store_rappi_id']= $arr_data[$i]['store_id'];  
                           $data['store_rappi_address']= $arr_data[$i]['address'];  
                           $data['lat']= $arr_data[$i]['location'][1];  
                           $data['lng']= $arr_data[$i]['location'][0];  
                           $filtro="store_rappi_id='{$data['store_rappi_id']}'";
                           if(!$obj_util->existen_registros('restaurant_location', $filtro))
                           {
                             $creo= $obj_restaurant_location->crear($data); 
                           }
                           else
                           {
                               $id=$obj_bd->id_elemn(array('id'=>'id','tb'=>'restaurant_location',"filtro"=>"store_rappi_id='{$data['store_rappi_id']}'"));
                               $data['id']=$id;
                               $creo= $obj_restaurant_location->modificar($data); 
                           }
                          
                       }
                       if($creo)
                       {
                            return json_encode(array('status' => 'ok', 'content' => 'Restaurantes actualizados')); 
  
                       }
                   }
                   else
                   {
                    return json_encode(array('status' => 'error', 'content' => 'no hay listado de restauranes disponibles para la lactitud:'.$arr_data['latitude'].' y longitud:'.$arr_data['longitude'])); 

                   }
               }
               else
               {
                  return json_encode(array('status' => 'error', 'content' => 'no hay listado de restauranes disponibles para la lactitud:'.$arr_data['latitude'].' y longitud:'.$arr_data['longitude'])); 
               }
               
           }
           else
           {
             return json_encode(array('status' => 'error', 'content' => 'no se genero el token'));
           }
           
        }
        else
        {
             return json_encode(array('status' => 'error', 'content' => 'ingrese el campo longitude y latitude !'));
        }
    }
    
    
   
}

function get_restaurant_list($data = '')
{
    if(!isset($_SESSION['app_movil']))$_SESSION['app_movil'] = true;
    $obj_util = new util();
    $filtro='';
    $obj_bd = new conector_bd();
    if(!empty($data))
    {
        $arr_data = json_decode($data,true);
        if(isset($arr_data['distance'])&&!empty($arr_data['distance'])&&isset($arr_data['latitude'])&&!empty($arr_data['latitude'])&&isset($arr_data['longitude'])&&!empty($arr_data['longitude']))
        {
            $filtro.="(6387 * acos( cos( radians('{$arr_data['latitude']}') ) * cos( radians(lat) ) * cos( radians(lng) - radians('{$arr_data['longitude']}') ) + sin( radians('{$arr_data['latitude']}') ) * sin( radians(lat) ) ) ) <= {$arr_data['distance']}"; 
        }
         
    }
    
    
    $arrPar=array();
    $arrPar['filtro'] = $filtro;
    $arrPar['orderBy'] = "restaurant_name";
    $arrPar['tb'] = "restaurant_location";
    $arrPar['id'] = "id";
    $elementos = $obj_util->consultar("restaurant_location", $arrPar);
    $n = $elementos['n'];
    $elementos = $elementos['elementos'];
    $cont=0;
    $arr_resp=array();
     if ($n > 0) 
     {
          foreach ($elementos as $codigos) 
          {
              $distance='0';
              
               if(isset($arr_data['latitude'])&&!empty($arr_data['latitude'])&&isset($arr_data['longitude'])&&!empty($arr_data['longitude']))
                {
                    $distance=$obj_bd->id_elemn(array('id'=>"(6387 * acos( cos( radians('{$arr_data['latitude']}') ) * cos( radians(lat) ) * cos( radians(lng) - radians('{$arr_data['longitude']}') ) + sin( radians('{$arr_data['latitude']}') ) * sin( radians(lat) ) ) )",'tb'=>'restaurant_location',"filtro"=>"id='{$codigos->codigo}'"));
                }
              
              
              $arr_resp[$cont]['id']=$codigos->codigo;
              $arr_resp[$cont]['restaurant_name']=$codigos->restaurant_name;
              $arr_resp[$cont]['store_rappi_id']=$codigos->store_rappi_id;
              $arr_resp[$cont]['store_rappi_address']=$codigos->store_rappi_address;
              $arr_resp[$cont]['created_date']=$codigos->created_date;
              $arr_resp[$cont]['open_time']=$codigos->open_time;
              $arr_resp[$cont]['close_time']=$codigos->close_time;
              $arr_resp[$cont]['latitude']=$codigos->lat;
              $arr_resp[$cont]['longitude']=$codigos->lng;
              $arr_resp[$cont]['type_distance']='KM';
              $arr_resp[$cont]['distance']= number_format($distance,2);
              $cont++;
          }
          return json_encode(array('status' => 'ok', 'data' => $arr_resp));
     }
     else
     {
           return json_encode(array('status' => 'error', 'content' => 'No hay registros!'));
     }
   
}


?>