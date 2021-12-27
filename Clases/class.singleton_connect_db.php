<?php
/**
 * @author Fredy Herrera
 * Singleton to connect db.
 */

class singleton_connect_db {
  // Hold the class instance.
  private static $instance = null;
  private $conn;
  
  private $host = BD_HOST;
  private $user = BD_USER;
  private $pass = BD_PASSWD;
  private $bd = BD_NAME;
  private $port = BD_PORT;
   
  // The db connection is established in the private constructor.
  private function __construct()
  {
      $datos_bd="host='$this->host' port='$this->port' dbname='$this->bd' user='$this->user' password='$this->pass'";
        //establecemos el link
      $this->conn=pg_connect($datos_bd);
  }
  
  public static function getInstance()
  {
    if(!self::$instance)
    {
      self::$instance = new singleton_connect_db();
    }

    return self::$instance;
  }
  
  public function getConnection()
  {
    return $this->conn;
  }
}
?>