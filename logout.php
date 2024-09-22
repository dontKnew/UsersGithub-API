<?php
require_once __DIR__."/config.php";

if(!session_id()){
    session_start();
}

unset($_SESSION['access_token']);
unset($_SESSION['state']);

header("Location:".BASE_URL);
?>