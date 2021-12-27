<?php
class restaurant_location
{
    public $codigo;
    public $restaurant_name;
    public $store_rappi_id;
    public $store_rappi_address;
    public $created_date;
    public $open_time;
    public $close_time;
    public $lat;
    public $lng;
    
   public function __construct($id = 0)
    {
        if ($id) $this->consultar($id);
    }
    
     public function consultar($id = 0)
    {
        if ($id)
        {
            $sql = "SELECT * FROM restaurant_location WHERE id = $id";
            
            $conector_bd = new conector_bd();
            $conector_bd->query($sql);
            $consulta = $conector_bd->fetch();
            
            $this->codigo                       = $consulta->id;
            $this->restaurant_name              = $consulta->restaurant_name;
            $this->store_rappi_id               = $consulta->store_rappi_id;
            $this->store_rappi_address          = $consulta->store_rappi_address;
            $this->created_date                 = $consulta->created_date;
            $this->open_time                    = $consulta->open_time;
            $this->close_time                   = $consulta->close_time;
            $this->lat                          = $consulta->lat;
            $this->lng                          = $consulta->lng;
        }
    }
 
    function crear($datos, $trc=false)
    {
           $bd = new conector_bd();
            return $bd->insert('restaurant_location', $datos, $trc);
    }
    function modificar($datos, $trc=false)
      {
        $bd= new conector_bd();
        $arrId['id'] = $datos['id'];
        return $bd->update('restaurant_location', $datos, $arrId, $trc);
      }

   function eliminar($id, $trc=false)
   {
       $arrId['id'] = $id;
       $bd= new conector_bd();
       return $bd->delete('restaurant_location', $arrId, $trc);
  
   }
}
?>
