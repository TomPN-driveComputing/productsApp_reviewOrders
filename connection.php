<?php
/**
 *
 * 09/01/2014
 * Hussain Fiaz
 * Connection Class Test
 **/

  include_once('config.php');


//include_once('../settings/config.php');

interface sqlConnectionInterface
{
  function connectToDatabase();
  function closeConnection();
}

class createConnection implements sqlConnectionInterface
{
  var $dbType = cfg_dbType;
  var $host = cfg_sqlIP;
  var $dbusername = cfg_sqlUser;
  var $dbpassword = cfg_sqlPassword;
  var $database = cfg_sqlDB;
  var $myconn;
  var $mssql = cfg_msSql;

  function connectToDatabase()    // function to connect to database
  {
    try
    {
        if ($this->mssql)
        {
            /* Connect to MS SQL*/
            $conn = new PDO("{$this->dbType}:Server={$this->host}; Database={$this->database} ",
                                 "{$this->dbusername}",
                                 "{$this->dbpassword}");
             // Set an attribute within PDO database handle.
             // PDO::ATTR_ERRMODE - Sets error reporting
             // PDO::ERRMODE_EXCEPTION - Throws error exceptions
             //Need this to get errores displayed on screen ?
             $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        else
        {
            /* Connect to MySQL   (Default)*/
            $conn = new PDO("{$this->dbType}:host={$this->host};dbname={$this->database}",
			   "{$this->dbusername}",
			   "{$this->dbpassword}");
            // Set an attribute within PDO database handle.
            // PDO::ATTR_ERRMODE - Sets error reporting
            // PDO::ERRMODE_EXCEPTION - Throws error exceptions
            //Need this to get errores displayed on screen ?
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

      $this->myconn = $conn; // we are now connected to the database
      return $this->myconn;
    }
    catch (PDOException $e)
    {
      echo "System Error : Unable to connect to Database" . $e->getMessage();
      PDO_DB::LogException('connect', __FILE__, __LINE__, $e);
    }
  }

  function closeConnection()
  {
    $this->myconn = null;
  }
}