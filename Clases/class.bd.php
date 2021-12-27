<?php 
class conector_bd
{
    var $host;//direccion ip del host donde nos conectamos a la bd
    var $bd;//nombre de la base de datos
    var $usuario;//usuario de conexion
    var $password;//clave del usuario de conexion
    var $link;//almacenamos el link para luego destruirlo
    var $result;//almacenamos el link para luego destruirlo
    var $sqlTransac;//almacenamos el link para luego destruirlo
    var $transac;//almacenamos el link para luego destruirlo

    function __construct()
    {
        $obj_singleton_connect_db = singleton_connect_db::getInstance();
        $this->link = $obj_singleton_connect_db->getConnection(); 
    }

    function __destruct()
    {
       //pg_close($this->link);
    }

    //funcion que ejecuta la consulta en la base de datos
    //en esta funcion envio el sql puede ser insert, update, select
    public function query($sql)
    {
        if(!isset($_SESSION['error_app_movil']))$_SESSION['error_app_movil']='';else$_SESSION['error_app_movil']='';
        
        if(SIG_GENERA_ERROR == 'true')
        {
            //ejecutamos la consulta
            $this->result = @pg_query($this->link,$sql);
            if(!$this->result) echo $sql;//si no ejecuta la consulta imprimo el sql que llega solo cuando hacemos pruebas
            return $this->result;
        }
        else
        {
            //ejecutamos la consulta
            $this->result = @pg_query($this->link,$sql);
            $result = $this->result;
            if(!$this->result)
            {
                $obj_util = new util();
                $result = pg_last_error();   
//                                print_r($result);exit;

                $result = str_replace('"', "", $result);
                $result =$obj_util->limpia_dato($result);
                $palabra='key';
                $palabra2='check';
                $palabra3='argument';
                $palabra4='syntax';
                $palabra5='ambiguous';
                if((preg_match("/$palabra5/i", $result) OR preg_match("/$palabra4/i", $result) OR preg_match("/$palabra/i", $result) OR preg_match("/$palabra2/i", $result) OR preg_match("/$palabra3/i", $result)))
                {       
                    if(!file_exists(SIG_DIR.SIG_ARCHIVO_ERROR)){
                        mkdir(SIG_DIR.SIG_ARCHIVO_ERROR);                        
                    }
                    //chmod(SIG_DIR.'/logs',0777);
                    $fecha = date("Y-m-d H:m:s");
                    $codigo = rand(1,99999999);
                    $file = fopen(SIG_DIR.SIG_ARCHIVO_ERROR, "a");                    
                    if(isset($_SESSION[SESSION_USUARIO]))
                    {
                        fwrite($file, ' && '.$codigo.' && '.$fecha.' && '.$result.' && '.$sql .'&&'.$_SESSION[SESSION_USUARIO].'&&'.$_SESSION[SUCURSAL_SESSION].' ||| '. PHP_EOL);
                        fclose($file);
                    }

                    if(isset($_SESSION['app_movil']))
                    {
                       $_SESSION['error_app_movil']=" Error en la transaccion codigo $codigo generado el $fecha ";            
                    }
                    else
                    {
                        echo "<script>alert (' Error en la transaccion codigo $codigo generado el $fecha ');</script>";            
                        return  false;
                      
                    }
               
                }
                else
                {
                   
                    $result = explode(':', $result);
                    $msj='';
                    if($result[0]!='ERROR')
                    {
                        for ($i=1;$i<count($result);$i++)
                        {
                           // print_r(trim(substr($result[$i],0,5)));
                           if(trim(substr($result[$i],0,5))!='SQL')
                             {
                                $msj.=$result[$i].': ';  
                             }
                        }                    
                        $msj=  str_replace('CONTEXT', '',$msj);
                        $msj=  str_replace('!!!', '',$msj);
                        $msj=(count($result)>=2)?substr($msj,0, (strlen($msj)-2)):$msj;
                    }
                    else
                        $msj=  $result[1];

                    if(isset($_SESSION['app_movil']))
                    {
                       $_SESSION['error_app_movil']=$msj;       
                    }
                    else
                    {
                        echo "<script>notificacion('top-right','{$msj} !!!', 'danger', '10000');</script>";        
                        return  false;
                    }
                    
                }         
                return  false;
            }
            else 
                return $result;
        }
    }
	
    function query_sin_error($sql)
    {
        $this->result = @pg_query($this->link,$sql);
        if(!$this->result) echo $sql;//si no ejecuta la consulta imprimo el sql que llega solo cuando hacemos pruebas
        return $this->result;
    }
    public function fetch_all_sin_error($sql, $arrIdentifier=array(),$arrPar=array())
    {
        $sql .= $this->identifier($arrIdentifier);
        if(isset($arrPar['groupBy']))
            $sql .= ' GROUP BY '.$arrPar['groupBy'];
        if(isset($arrPar['orderBy']))
            $sql .= ' ORDER BY '.$arrPar['orderBy'];
        if(isset($arrPar['limit']))
            $sql .= ' LIMIT '.$arrPar['limit'];
        if(isset($arrPar['offset']))
            $sql .= ' OFFSET '.$arrPar['offset'];

        $this->query_sin_error($sql);

        if ($this->result != null)
        {
            return pg_fetch_all($this->result);
        }
    }
   

    function consultar($sql)
    {
        $query = $this->query($sql);        
        return $query;
    }

    function eliminar_car_especiales($val)
    {
        $val = trim($val);
        $val = str_replace("'", "",$val);
        $val = str_replace("\"", "",$val);
        $val = str_replace("(", "",$val);
        $val = str_replace(")", "",$val);
        $val = str_replace("&", "",$val);
        $val = str_replace("|", "",$val);
        $val = str_replace("<", "",$val);
        $val = str_replace(">", "",$val);
        $val = str_replace("--", "",$val);

        $val = trim($val);
        return $val;
    }

    function limpie($sql)
    {
            return $sql;
    }
    // function que retorna el numero de registros de una consulta

    function num_registros($sql)
    {
            $valor=$this->consultar($sql);
            return pg_num_rows($valor);
    }

    // function que retorna el numero de registros de una consulta
    function num_reg($tb, $filtro = '', $join = '')
    {
        $sql = "select count(*) from {$tb}";

        if($join != '')
           $sql .= " " . $join;
        if($filtro!='')
            $sql .= " WHERE ".$filtro;

        $consulta=$this->consultar($sql);
        $cant = pg_fetch_row($consulta);
        return $cant[0];
    }

    public function fetch()
    {
        if ($this->result != null)
        {
            return pg_fetch_object($this->result);
        }
    }

   

    //metodo que retorna el id del primer elemento que encuentre segun el filtro
    function id_elemn($arrPar)
    {
        $id = $arrPar['id'];
        $tName = $arrPar['tb'];
        $sql = 'SELECT '.$id.' id FROM '.$tName;
        //prin_r($sql);exit;
        if(isset($arrPar['filtro']) AND !empty($arrPar['filtro']))
        {
            $sql .= ' WHERE '.$arrPar['filtro'];
        }
  if(isset($arrPar['orderBy']) AND !empty($arrPar['orderBy']))
        {
            $sql .= ' ORDER BY '.$arrPar['orderBy'];
        }

        $sql .= ' limit 1;';
       // print ('el sql es '.$sql);
        $this->query($sql);
        $objId = $this->fetch();

        return (is_object($objId)) ? $objId->id: '';
    }
    
    
    function insert($tName, $arrData, $trc = false)
    {
        $sql = '';
        $sqlC = '';
        $sqlV = '';

        $sql .= 'INSERT INTO '.$tName;

        foreach($arrData as $cName => $cValue)
        {
            $sqlC .= ($sqlC ? ' ,' : ' ') . $cName;
            
           if(stristr($cValue, 'ARRAY'))
                $sqlV .= ($sqlV ? ' ,' : ' ') . (($cValue!='' or $cValue==0) ? "" . $cValue. "" : 'null');
           else if(substr($cValue, 0,7)=='(SELECT')//para una subconsulta
                 $sqlV .= ($sqlV ? ' ,' : ' ') . (($cValue!='' or $cValue==0) ? "" . $cValue. "" : 'null');
           else
                $sqlV .= ($sqlV ? ' ,' : ' ') . (($cValue!='' or $cValue==0) ? "'" . $cValue. "'" : 'null');
            
        }

        $sql .= ' ('.$sqlC.')values('.$sqlV.');';
        $this->sqlTransac .= ($trc==true or $this->transac==true) ? $sql : '';
        return ($trc==false) ? $this->query($sql): $sql;
    }


    //metodo que elimina
    function delete($tName, $arrIdentifier, $trc = false)
    {
        $sql = '';
        $sql .= 'DELETE FROM '.$tName;
        $sql .= $this->identifier($arrIdentifier);
        $sql .= ';';

        $this->sqlTransac .= ($trc==true or $this->transac==true) ? $sql : '';
        return ($trc==false) ? $this->query($sql): $sql;
    }
    
    
    public function fetch_all($sql, $arrIdentifier=array(),$arrPar=array())
    {
        $sql .= $this->identifier($arrIdentifier);
        if(isset($arrPar['groupBy']))
            $sql .= ' GROUP BY '.$arrPar['groupBy'];
        if(isset($arrPar['orderBy']))
            $sql .= ' ORDER BY '.$arrPar['orderBy'];
        if(isset($arrPar['limit']))
            $sql .= ' LIMIT '.$arrPar['limit'];
        if(isset($arrPar['offset']))
            $sql .= ' OFFSET '.$arrPar['offset'];

        $this->query($sql);

        if ($this->result != null)
        {
            return pg_fetch_all($this->result);
        }
    }

    //ensambla el filtro de cualquier cosa
    private function identifier($arrIdentifier = array())
    {
        $sql = '';
        $sqlFiltro = '';
        if(sizeof($arrIdentifier)>0)
        {
            $strW = "";
            $arrTempFiltro = array();
            $arrFiltro = array();

            $arrFiltroSpecial = array(
                'like'=>"like",//LIKE
                'dif'=>"dif",//"<>"
                'menor'=>'menor',//"<",
                'menorI'=>'menorI',//,"<=",
                'mayor'=>'mayor',//">",
                'mayorI'=>'mayorI',//">=",
                'in'=>'in',//"IN",
                'notIn'=>'notIn',//"NOT IN",
                'isTrue'=>'isTrue',//"IS TRUE",
                'isNotTrue'=>'isNotTrue',//"IS NOT TRUE",
                'isNull'=>'isNull',//"IS NULL",
                'isNotNull'=>'isNotNull',//"IS NOT NOLL",
            );
            foreach($arrIdentifier as $cName => $cValue)
            {
                if(!in_array($cName, $arrFiltroSpecial))
                {
                    $str = $cName."='".$cValue."'";
                    array_push ($arrFiltro, $str);
                }
                else
                {
                    $arrTempFiltro[$cName] = $cValue;
                    unset($arrIdentifier[$cName]);
                }
                $str = "";
            }

            if(sizeof($arrTempFiltro) > 0)
                foreach($arrTempFiltro as $sName => $sValue)
                {
                    if($sName == "like")
                        foreach($sValue as $cName => $cValue)
                        {
                            array_push($arrFiltro, $cName." LIKE '".$cValue."'");
                        }
                    elseif($sName == "dif")
                        foreach($sValue as $cName => $cValue)
                        {
                            array_push($arrFiltro, $cName." <> '".$cValue."'");
                        }
                    elseif($sName == "menor")
                        foreach($sValue as $cName => $cValue)
                        {
                            array_push($arrFiltro, $cName." < '".$cValue."'");
                        }
                    elseif($sName == "menorI")
                        foreach($sValue as $cName => $cValue)
                        {
                            array_push($arrFiltro, $cName." <= '".$cValue."'");
                        }
                    elseif($sName == "mayor")
                        foreach($sValue as $cName => $cValue)
                        {
                            array_push($arrFiltro, $cName." > '".$cValue."'");
                        }
                    elseif($sName == "mayorI")
                        foreach($sValue as $cName => $cValue)
                        {
                            array_push($arrFiltro, $cName." >= '".$cValue."'");
                        }
                    elseif($sName == "in")
                        foreach($sValue as $cName => $cValue)
                        {
                            array_push($arrFiltro, $cName." IN ".$cValue);
                        }
                    elseif($sName == "notIn")
                        foreach($sValue as $cName => $cValue)
                        {
                            array_push($arrFiltro, $cName." NOT IN ".$cValue);
                        }
                    elseif($sName == "isTrue")
                        foreach($sValue as $cName => $cValue)
                        {
                            array_push($arrFiltro, $cName." IS TRUE ");
                        }
                    elseif($sName == "isNotTrue")
                        foreach($sValue as $cName => $cValue)
                        {
                            array_push($arrFiltro, $cName." IS NOT TRUE ");
                        }
                    elseif($sName == "isNull")
                        foreach($sValue as $cName => $cValue)
                        {
                            array_push($arrFiltro, $cName." IS NULL ");
                        }
                    elseif($sName == "isNotNull")
                        foreach($sValue as $cName => $cValue)
                        {
                            array_push($arrFiltro, $cName." IS NOT NULL ");
                        }
                }

            $strW = implode(" AND ", $arrFiltro);
            $sqlFiltro = ' WHERE '.$strW;
        }

        return $sqlFiltro;
    }

    public function begin($trc=false)
    {
        $this->transac = true;
        $this->sqlTransac .= ($trc==false) ? 'BEGIN;' : '';
    }

    public function commit($trc=false)
    {
        $this->sqlTransac .= ($trc==false) ? 'COMMIT;' : '';
        return ($trc==false) ? $this->query($this->sqlTransac) : $this->sqlTransac;
    }

        function update($tName, $arrData, $arrIdentifier, $trc = false)
    {
        $sql = '';
        $sqlC = '';//sql de los campos
        $sqlW = '';//sql del where

        $sql .= 'UPDATE '.$tName.' SET ';
        
        $key = array_keys($arrIdentifier);
        $key = $key[0];

        foreach($arrData as $cName => $cValue)
        {
            if($key <> $cName)
            {
                if(substr($cName,0,4)=='arr_')//Si es un campo de tipo Array se quitan las comillas
                    $sqlC .= ($sqlC ? ' ,' : ' ') . (($cValue=='' or $cValue==null or $cValue==NULL) ? $cName .'= null ': $cName .'='. $cValue);
                else
                    $sqlC .= ($sqlC ? ' ,' : ' ') . (($cValue=='' or $cValue==null or $cValue==NULL) ? $cName .'= null ': $cName .'=\'' . $cValue. '\'');
            }
        }


        $sql .= $sqlC; //
        $sql .= $this->identifier($arrIdentifier);
        $sql .= ';';
        
        $this->sqlTransac .= ($trc==true or $this->transac==true) ? $sql : '';
        return ($trc==false) ? $this->query($sql): $sql;
    }

   public function fetch_todo()
   {
      if ($this->result != null)
      {
         $arrObj = array();
         while($obj = pg_fetch_object($this->result))
         {
            $arrObj[] = $obj;
         }
         return $arrObj;
      }
   }
}

?>
