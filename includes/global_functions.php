<?php
error_reporting(1);
session_start();
ob_start();
date_default_timezone_set("Asia/Kolkata");

/* *************************************** NORMAL FUNCTIONS *************************************** */

/* *************************************** GET SITE URL *************************************** */

function getHostLink($folderPath = "")
{
    $link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/" . ltrim($folderPath, "/");
    return rtrim($link, "/");
}

/* *************************************** GET SITE URL *************************************** */

/* *************************************** REDIRECT PAGE *************************************** */
function redirectPage($pageName)
{
    $redirectScript = "";
    $redirectScript .= "<script>";
    $redirectScript .= 'window.location.href = \'' . $pageName . '\'';
    $redirectScript .= "</script>";
    echo $redirectScript;
}

/* *************************************** REDIRECT PAGE *************************************** */


/* ********************************** IMAGE RESIZE ********************************** */

function resizeImage($resourceType = null, $image_width = "", $image_height = "", $resizeWidth = "", $resizeHeight = "")
{
    $imageLayer = imagecreatetruecolor($resizeWidth, $resizeHeight);
    imagecopyresampled($imageLayer, $resourceType, 0, 0, 0, 0, $resizeWidth, $resizeHeight, $image_width, $image_height);
    return $imageLayer;
}

/* ********************************** IMAGE RESIZE ********************************** */


/* ***************************** GET IP ADDRESS ***************************** */

function getClientIP()
{
    $localIP = getHostByName(getHostName());
    return $localIP;
}

/* ***************************** GET IP ADDRESS ***************************** */

/* ***************************** DELETE A FILE ***************************** */

function deleteFile($filePath = "")
{
    $fileDeleted = "";
    if (file_exists($filePath)) {
        if (unlink($filePath)) {
            $fileDeleted = true;
        } else {
            $fileDeleted = -1;
        }
    } else {
        $fileDeleted = false;
    }
    return $fileDeleted;
}

/* ***************************** DELETE A FILE ***************************** */

/* ***************************** EMPTY A DIRECTORY ***************************** */

function emptyDirectory($directoryPath = "")
{
    $emptyDirectory = false;
    if (array_map('unlink', glob("$directoryPath/*.*"))) {
        $emptyDirectory = true;
    } else {
        $emptyDirectory = false;
    }
    return $emptyDirectory;
}

/* ***************************** EMPTY A DIRECTORY ***************************** */

/* ***************************** REMOVE HTML ENTITIES ***************************** */

function removeHTMLEntities($input = "")
{
    $output = htmlspecialchars($input);
    $output = htmlentities($output);
    return $output;
}

/* ***************************** REMOVE HTML ENTITIES ***************************** */

/* *************************************** NORMAL FUNCTIONS ********************************* */

/* *********************************** VALIDATION FUNCTIONS *********************************** */

/* *********************************** EMAIL VALIDATION *********************************** */

function emailValidation($email = "")
{
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $output = true;
    } else {
        $output = false;
    }
    return $output;
}

/* *********************************** EMAIL VALIDATION *********************************** */

/* *********************************** URL VALIDATION *********************************** */

function urlValidation($website = "")
{
    if (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $website)) {
        $output = true;
    } else {
        $output = false;
    }
    return $output;
}

/* *********************************** URL VALIDATION *********************************** */

/* ******************************** TEXT PATTERN VALIDATION ******************************** */

function textPatternValidation($text = "", $pattern = "a-zA-Z0-9")
{
    if (preg_match("/^[$pattern]*$/", $text)) {
        $output = true;
    } else {
        $output = false;
    }
    return $output;
}

/* ******************************** TEXT PATTERN VALIDATION ******************************** */

/* *********************************** VALIDATION FUNCTIONS *********************************** */

/* *********************************** ORACLE FUNCTIONS *********************************** */

function sqlFetchData($connection, $query)
{
    $parseQuery = ociparse($connection, $query);
    ociexecute($parseQuery);
    while ($fetch = oci_fetch_array($parseQuery)) {
        $data[] = $fetch;
    }
    return $data;
}
function sqlCountData($connection, $query)
{
    $parseQuery = ociparse($connection, $query);
    ociexecute($parseQuery);
    $count = 0;
    while (oci_fetch_array($parseQuery)) {
        $count++;
    }
    return $count;
}
function sqlCUDData($connection, $query)
{
    $status = false;
    $parseQuery = ociparse($connection, $query);
    if (ociexecute($parseQuery)) {
        ocicommit($connection);
        $status = true;
    }
    return $status;
}
function sqlSerialNo($connection, $tableName, $filedName = "SL_NO")
{
    $query = "SELECT MAX($filedName) SL_NO FROM $tableName ORDER BY $filedName DESC";
    $parseQuery = ociparse($connection, $query);
    ociexecute($parseQuery);
    $fetch = oci_fetch_array($parseQuery);
    $serialNo = $fetch['SL_NO'] + 1;
    return $serialNo;
}


/* *********************************** ORACLE FUNCTIONS *********************************** */




/* *********************************** POSTGRES FUNCTIONS *********************************** */

function sqlPGFetchData($connection, $query)
{
    $pgquery = pg_query($connection, $query);
    while ($fetch = pg_fetch_row($pgquery)) {
        $data[] = $fetch;
    }
    return $data;
}
function sqlPGCountData($connection, $query)
{
    $pgquery = pg_query($connection, $query);
    $count = 0;
    while (pg_fetch_row($pgquery)) {
        $count++;
    }
    return $count;
}


/* *********************************** POSTGRES FUNCTIONS *********************************** */
ob_flush();
