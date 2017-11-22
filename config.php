<?php
/*
 * Bliss Bitesize Config Settings
 * These setttings are to be configured on each site for eaach instance of Bliss Bitesize
 *
 * Make a copy of this file and rename to config.php
 *
 *
 *   PREFIX ALL VARIABLES WITH 'cfg_' so it's apparent they come from this config file
 *
 *  NOTE: using define stops these from being changed in code BUT also refer to them without the $
 */


//DataBase Settings
define("cfg_dbType", "sqlsrv");          // For MySQL dbType = 'mysql'. For MSSQL dbType = 'sqlsrv'
define("cfg_mySql","0");                //1= MySql Db in Use
define("cfg_msSql","1");                //1= MS Sql Db in Use
define("cfg_sqlIP","DESKTOP-PJ6PKA5");
define("cfg_sqlUser","drivecomp");
define("cfg_sqlPassword","ev1rd");
define("cfg_sqlDB","training");
//-->define("cfg_sqlDB","sop");

?>