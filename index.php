<?php 
session_start();
require_once __DIR__."/config.php";
if(isset($_SESSION['access_token'])){ 
    header("Location:".BASE_URL."/userdata");
    exit();
}else{
    if(!session_id()){ 
        session_start(); 
    }   
    $gitClient = new Github_OAuth_Client(array( 
        'client_id' => CLIENT_ID, 
        'client_secret' => CLIENT_SECRET, 
        'redirect_uri' => REDIRECT_URL 
    )); 
    // get code when user granted the permission to app
    if(isset($_GET['code'])){ 
        if(!$_GET['state'] || $_SESSION['state'] != $_GET['state']) { 
            header("Location: ".$_SERVER['PHP_SELF']); 
        } 
        $accessToken = $gitClient->getAccessToken($_GET['state'], $_GET['code']); 
        $_SESSION['access_token'] = $accessToken; 
        header('Location:'.BASE_URL); 
    }else{ 
        // first states, if access token not found then display login button
        $_SESSION['state'] = hash('sha256', microtime(TRUE) . rand() . $_SERVER['REMOTE_ADDR']); 
        $authUrl = $gitClient->getAuthorizeURL($_SESSION['state']); 
        $login_button = '<a href="'.htmlspecialchars($authUrl).'"><img src="https://cdn.prod.website-files.com/5c2a9a234fdbba7439c48fa4/64f89feeca2dade0792d6c0f_632cc59b0ce5f831d6ce0c8c_Screen%20Shot%202022-09-22%20at%204.13.26%20PM-p-500.webp"></a>'; 
    } 
}

?>
<div class="container" style="display: flex;justify-content:center;margin-top:100px">
    <?php echo $login_button ; ?>
</div>