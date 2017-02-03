<?php


require_once __DIR__."/../vendor/autoload.php";
require_once 'config.php';
require_once 'class-db.php';
require_once 'class-ezapis.php';

try {
    $eZapis=new EZapis(DROPBOX_KEY, DROPBOX_SECRET,DROPBOX_TOKEN);
    $eZapis->run();
} catch (Exception $e) {
    echo $e->getMessage();
}
?>

