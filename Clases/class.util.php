<?php

class util {
   
    //bool - metodo que determina si existe o no registros con unas caracteristicas en una tabla
    function existen_registros($tabla, $filtro = '') {
        if (!empty($filtro))
            $sql = "select count(*) from " . trim($tabla) . " where {$filtro}";
        else
            $sql = "select count(*) from " . trim($tabla);
        //  print_r($sql);exit;
        $bd = new conector_bd();
        $query = $bd->consultar($sql);
        $row = pg_fetch_row($query);
        if ($row[0] > 0)
            return true;
        else
            return false;
    }

    

    //$cant_caracteres : Tamaño de caracteres fijados en la B.D
    function limpia_dato($val, $cant_caracteres = '') {
        if (!empty($cant_caracteres))
            $val = addslashes(trim(substr($val, 0, $cant_caracteres)));
        else
            $val = addslashes(trim($val));


        $val = str_replace("'", "", $val);
        $val = str_replace("\"", "", $val);
        $val = str_replace("(", "", $val);
        $val = str_replace(")", "", $val);
        $val = str_replace("&", "", $val);
        $val = str_replace("|", "", $val);
        $val = str_replace("<", "", $val);
        $val = str_replace(">", "", $val);
        $val = str_replace("--", "", $val);
        $val = str_replace("ñ", "Ñ", $val);
        $val = str_replace("Ñ", "Ñ", $val);
        $val = str_replace("à", "a", $val);
        $val = str_replace("À", "A", $val);
        $val = str_replace("á", "a", $val);
        $val = str_replace("Á", "A", $val);
        $val = str_replace("é", "e", $val);
        $val = str_replace("É", "E", $val);
        $val = str_replace("í", "i", $val);
        $val = str_replace("Í", "I", $val);
        $val = str_replace("ó", "o", $val);
        $val = str_replace("Ó", "O", $val);
        $val = str_replace("ú", "u", $val);
        $val = str_replace("Ú", "U", $val);
        $val = str_replace('"', "", $val);
        $val = preg_replace("[\n|\r|\n\r]", " ", $val);


        return $val;
    }

    function limpiar_metas($string, $corte = null) {

        $caracters_no_permitidos = array("ñ", "Ñ", "ò", "Ò", "Ó", "ó", "à", "À", "ì", "Ì", "Í", "í", "ú", "Ú", ".");
//        # paso los caracteres entities tipo &aacute; $gt;etc a sus respectivos html

        $s = html_entity_decode($string, ENT_COMPAT, 'UTF-8');

//        # quito todas las etiquetas html y php

        $s = strip_tags($s);
        # elimino todos los retorno de carro
        $s = str_replace("r", '', $s);
        # en todos los espacios en blanco le añado un <br /> para después eliminarlo
        $s = preg_replace('/(?<!>)n/', "<br />n", $s);
        # elimino la inserción de nuevas lineas
        $s = str_replace("n", '', $s);
        # elimino tabulaciones y el resto de la cadena
        $s = str_replace("t", '', $s);
        # elimino caracteres en blanco
        $s = preg_replace('/[ ]+/', ' ', $s);
        $s = preg_replace('/<!--[^-]*-->/', '', $s);
        $s = eregi_replace("[\n|\r|\n\r]", ' ', $string);
        # vuelvo a hacer el strip para quitar el <br /> que he añadido antes para eliminar las saltos de carro y nuevas lineas
        $s = strip_tags($s);
        # elimino los caracters como comillas dobles y simples
        $s = str_replace($caracters_no_permitidos, "", $s);

        if (isset($corte) && (is_numeric($corte))) {
            $s = mb_substr($s, 0, $corte, 'UTF-8');
        }

        return $s;
    }
    //arrPar indica que se recibe un array con todos los parametros que se utilizan que son:
    //['aliasTb'] => String con el nombre del alias de la tabla de la clase por defecto vacio
    //['join'] => String con el join que se reciba de otras tablas por defecto vacio
    //['filtro'] => String con el filtro que hemos ensamblado por defecto vacio
    //['orderBy'] => String con el campo por el cual debe ordenar, por defecto queda el id de la tabla
    //['limit'] => String con el la limitacion de registros que debe hacer si esta vacio por seguridad limita a 1000
    //nombre de la clase que estamos generando este campo es OBLIGATORIO
    function consultar($nomClase, $arrPar = array()) {
        $sql = "";
        $arrPar['aliasTb'] = (isset($arrPar['aliasTb'])) ? $arrPar['aliasTb'] : '';
        $arrPar['join'] = (isset($arrPar['join'])) ? $arrPar['join'] : '';
        $arrPar['filtro'] = (isset($arrPar['filtro'])) ? $arrPar['filtro'] : '';
        $arrPar['orderBy'] = (isset($arrPar['orderBy'])) ? $arrPar['orderBy'] : '';
        $arrPar['limit'] = (isset($arrPar['limit'])) ? $arrPar['limit'] : '';
        $arrPar['schema'] = (isset($arrPar['schema'])) ? $arrPar['schema'] . '.' : '';
        $arrPar['tb'] = (isset($arrPar['tb']) && !empty($arrPar['tb'])) ? $arrPar['tb'] : 'tb_' . $nomClase;
        $retornos = array();

        $tb = $arrPar['schema'] . $arrPar['tb'] . ' ' . $arrPar['aliasTb'];
        $id = (isset($arrPar['id']) && !empty($arrPar['id']) ? $arrPar['id'] : ($arrPar['aliasTb'] ? $arrPar['aliasTb'] . ".id_" . $nomClase : "id_" . $nomClase));
        $sql = "SELECT " . $id . " FROM {$tb} ";
        $sql .= " " . ($arrPar['join'] != '' ? $arrPar['join'] : '');
        $sql .= ($arrPar['filtro'] != '' ? " WHERE " . $arrPar['filtro'] : '');
        $sql .= " ORDER BY " . ($arrPar['orderBy'] != '' ? $arrPar['orderBy'] : " id_{$nomClase} ");
        $sql .= " " . ($arrPar['limit'] != '' ? $arrPar['limit'] : 'LIMIT 2500 OFFSET 0');

        $bd = new conector_bd();
        //PRINT_R ($sql);EXIT;
        $query = $bd->consultar($sql);

        $n = $bd->num_reg($tb, $arrPar['filtro'], $arrPar['join']);
        while ($row = pg_fetch_row($query))
            $retornos[] = new $nomClase($row[0]);
        return array('elementos' => $retornos, 'n' => $n);
    }
}

?>