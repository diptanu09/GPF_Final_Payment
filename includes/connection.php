<?php

/*********************************************** ORACLE CONNECTION ***************************************** */

$db = "(DESCRIPTION=(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = " . DB_HOST . ")(PORT = " . DB_PORT . ")))(CONNECT_DATA=(SID=" . DB_SID . ")))";
$connection = oci_connect(DB_USERNAME, DB_PASSWORD, $db);
if (!$connection) {
    die();
}

/*********************************************** ORACLE CONNECTION ***************************************** */

/*********************************************** POSTGRES CONNECTION ***************************************** */

$conn_pgsql = pg_connect("host=" . PG_HOST . " dbname=" . PG_DATABASE . " user=" . PG_USERNAME . " password=" . PG_PASSWORD);

if (!$conn_pgsql) {
    die();
}
/*********************************************** POSTGRES CONNECTION ***************************************** */
