<?php
require_once __DIR__."/../config.php";
require __DIR__.'/../library/GitHubAPI.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $repoNames = isset($_POST['repo_names']) ? $_POST['repo_names'] : [];

    if (empty($repoNames)) {
        echo json_encode(['status' => 'error', 'message' => 'No repositories selected']);
        exit;
    }
    $github = new GitHubAPI();
    $errors = [];
    $responseAr = [];
    foreach ($repoNames as $repoName) {
        $response = $github->deleteRepository($repoName);
        if ($response['status'] != 204) { // Assuming 204 is success
            $errors[] = $repoName;
            $responseAr[] = $response;
        }
    }

    if (empty($errors)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'response'=>$responseAr, 'message' => 'Failed to delete: ' . implode(', ', $errors)]);
    }
}
?>
