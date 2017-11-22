<?php
/* *****************************************************************************
 * Class for SQL Calls
 *
 * *****************************************************************************
 * Date      Issue   Fault no./description/remedial action              Initials
 * 11/11/15     01   Created                                                 ADS
 * 24/11/15     02   Included include.php and executed gGetUserdets          ADS
 * 20/01/16     03   Added a function to get users department                ADS
 * 21/01/16     04   Added SQL_Execute for create table statements           ADS
 * 17/01/17     05   Added Curl_Call function for cURL requests              ADS
 * 20/01/17     06   Added a $cookies parameter to Curl_Call                 ADS
 * 07/04/17     07   LOG 18505: Removed errorDate from storeError            ADS
 * *****************************************************************************
 */

require_once 'connection.php';

class SQLClass {

    public $connection = null;
    public $conn = null;
    protected $resultSet;
    protected $errflg = 0;

    function __construct() {
        $this->connection = new createConnection();
        $this->conn = $this->connection->connectToDatabase();
    }
    
    public function Curl_Call($url, Array $postFields, Array $cookies = NULL) {
        try {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
            //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");  //specify the PUT verb for update
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);  //add the data string for the request
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //set return as string true
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);

            curl_setopt($ch, CURLOPT_HTTPHEADER, 'Content-Type: application/json'); //set the header as JSON
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            if ($cookies != NULL)
            {
                //This builds up a string of cookies to pass to the $url
                $cookieStr = "";
                
                foreach ($cookies as $key => $value) {
                    $cookieStr .= "{$key}={$value};";
                }
                
                curl_setopt($ch, CURLOPT_COOKIE, $cookieStr);
            }
            
            $result = curl_exec($ch); //execute and store server output

            if (curl_errno($ch)) {
                $this->errflg = 1;
                $errMsg =  curl_error($ch);
            } else {
                // Show me the result
                //var_dump($result);
                //print_r(curl_getinfo($ch));
                curl_close($ch);
            }
            
            return $result;
        } catch (Exception $ex) {

        }
    }

    public function SQL_Read($sql, $values = NULL) {
        $conn = $this->conn;
        $this->errFlg = 0;
        $result = null;
        $resultSet = null;
        try {
            $result = $conn->prepare($sql);   // Prepares the statement for execution. It will return a statement object ($result)
            if ($values != NULL) {
                foreach ($values as $key => $obj) {   // Loop round 2-dimensional array
                    $result->bindValue($obj[0], $obj[1]);   // Loops through Array of values sent from AJAX page and binds the values
                }
            }
            $result->execute();
            // $numberOfRows = $result->rowCount();
            //print_r($values);
            //$resultSet = $result->fetchAll();
            if ($result !== false) {
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    $resultSet[] = $row;
                }
            }
            //print_r($resultSet);
        } catch (Exception $ex) {
            $this->storeError($ex);
        }
        //print_r($this->resultSet);
        return $resultSet;
    }

    public function SQL_Insert($sql, $values=null, $IDColumn = NULL) {
        $conn = $this->conn;
        $numberOfRows = 0;
        $errFlg = 0;
        try {
            $result = $conn->prepare($sql);
            if ($values != null)
            {
                foreach ($values as $key => $obj) {
                    $result->bindValue($obj[0], $obj[1]);
                }
            }
            $result->execute();
            $numberOfRows = $result->rowCount();   // Get toal number of elements within the returned object ($result) - number of rows affected
            if ($IDColumn != null) {
                $lastInsertID = $conn->lastInsertId($IDColumn);
            }
        } catch (Exception $ex) {
            //call to insert sql error//
            echo $ex;
            $this->storeError($ex);
        }

        return array($numberOfRows, $lastInsertID, $this->errflg);
    }

    public function SQL_Update($sql, $values=null) {
        //Function to update an sql record
        $conn = $this->conn;
        $numberOfRows = 0;
        $errFlg = 0;
        try {
            $query = $conn->prepare($sql);
            if ($values != null)
            {
                foreach ($values as $key => $obj) {
                    $query->bindValue($obj[0], $obj[1]);
                }
            }

            $query->execute();
            $numberOfRows = $query->rowCount();   // Get toal number of elements within the returned object ($result) - number of rows affected
        } catch (Exception $ex) {
            //call to insert sql error//
            $this->storeError($ex);
        }

        return array($numberOfRows, $this->resultSet, $this->errFlg);
    }

    public function SQL_Delete($sql, $values=null) {
        $conn = $this->conn;
        $this->errFlg = 0;
        $result = null;
        $resultSet = null;

        try {
            $result = $conn->prepare($sql);   // Prepares the statement for execution. It will return a statement object ($result)
            if ($values != null)
            {
                    foreach ($values as $key => $obj) {   // Loop round 2-dimensional array
                        $result->bindValue($obj[0], $obj[1]);   // Loops through Array of values sent from AJAX page and binds the values
                    }
            }
            $result->execute();
            // $numberOfRows = $result->rowCount();
            //print_r($values);
            //$resultSet = $result->fetchAll();
        } catch (Exception $ex) {
            $this->storeError($ex);
        }
        //print_r($this->resultSet);
        return $result;
    }
    
    public function SQL_Execute($sql) {
        //This is a special system function
        //ONLY USED TO EXECUTE CREATE TABLE STATEMENTS!!!!!!!
        $conn = $this->conn;
        $this->errFlg = 0;
        $result = null;

        try {
            $result = $conn->prepare($sql);   // Prepares the statement for execution. It will return a statement object ($result)
            $result->execute();
            // $numberOfRows = $result->rowCount();
        } catch (Exception $ex) {
            $this->storeError($ex);
        }
        //print_r($this->resultSet);
        return $result;
    }

    public function getPlaceholdersInsert($dataArray) {
        $fieldNames = array();
        $protectedValues = array();
        $placeholdersArr = array();

        foreach ($dataArray as $key => $val) {
            $fieldNames[] = "$key";
            $placeholder = ":" . $key;
            $tmpArray = array(
                $placeholder, $val
            );
            array_push($protectedValues, $tmpArray);
            array_push($placeholdersArr, $placeholder);
        }
        $fields = implode(', ', $fieldNames);
        $placeholders = implode(', ', $placeholdersArr);

        return array($fields, $placeholders, $protectedValues);
    }

    public function getPlaceholdersUpdate($dataArray) {
        /*
          Function to create the placeholder pairs needed to do a protected Update
          returns:
          values ; string for update e.g. contactId = :contactId
          protectedValuesArr;  placeholder array e.g.
          $valuesArray = array(
          array(":categoryType", $categoryType),
          array(":createdDate", $createdDate)
          );
         */

        $updatePairs = array();
        $protectedValues = array();

        //lopp round dataarrya and construct the placeholder array and values string
        foreach ($dataArray as $key => $val) {
            $updatePairs[] = "$key = :$key";
            $placeholder = ":" . $key;
            $tmpArray = array(
                $placeholder, $val
            );
            array_push($protectedValues, $tmpArray);
        }
        //print_r($cols);
        $values = implode(', ', $updatePairs);      //create the update string using placeholder for protected update

        return array($values, $protectedValues);
    }

    private function storeError($ex) {
        $this->errFlg = 1;

        //$errorMsg = htmlspecialchars($ex);
        //$errorDate = date('Y-m-d H:i:s');
        $browser = $_SERVER['HTTP_USER_AGENT'];
        $error_page = $_SERVER[REQUEST_URI];

        $query = <<<INSERT
                INSERT INTO
                sys_errors
                  (errorMsg, browser, username, pageName)
                VALUES
                  (:errorMsg, :browser, :username, :pageName)
INSERT;

        $valuesArray = array(
            array(":errorMsg", $errorMsg),
            array(":browser", $browser),
            array(":username", $userData_username),
            array(":pageName", $error_page)
        );

        //$response = $this->SQL_Insert($query, $valuesArray);

        //MOD01 return array($num_rows, $errorMsgEscaped, $errflg);
        return array($num_rows, $errorMsg, $errflg);                    //MOD01
    }

    function __destruct() {
        //global $connection;

        $this->connection->closeConnection();
    }

}
