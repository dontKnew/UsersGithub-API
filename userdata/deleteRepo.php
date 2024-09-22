<?php
require_once __DIR__."/../config.php";
require __DIR__.'/../library/GitHubAPI.php';
$github = new GitHubAPI();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $repoName = $_POST['repo_name'];
    $response = $github->deleteRepository($repoName);

    if ($response['status'] == 204) {
        echo json_encode(['status' => 'success']);
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Could not delete repository.' , 'response'=>$response ]);
        exit;
    }
}
