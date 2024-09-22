<?php
require_once __DIR__."/../config.php";
require __DIR__.'/../library/GitHubAPI.php';
$github = new GitHubAPI();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldName = $_POST['old_name'];
    $newName = $_POST['new_name'];
    $description = $_POST['description'];
    $private = $_POST['private'];

    $response = $github->updateRepository($oldName, $newName, $description, $private);

    if ($response['status'] == 200) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Could not update repository.', 'response'=>$response]);
    }
}
