<?php
require_once __DIR__."/../config.php";
require __DIR__.'/../library/GitHubAPI.php';
$github = new GitHubAPI();
$repoName = $_GET['repo_name'] ?? '';
$repository = null;

if ($repoName) {
    $response = $github->getRepositories("all"); 
    $repositories = $response['status'] == 200 ? $response['response'] : [];
    foreach ($repositories as $repo) {
        if ($repo['name'] === $repoName) {
            $repository = $repo;
            break;
        }
    }
}

if (!$repository) {
    die("Repository not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Repository</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1>Edit Repository</h1>
    <form id="edit-form">
        <div class="mb-3">
            <label for="repo_name" class="form-label">Repository Name</label>
            <input type="text" class="form-control" id="repo_name" name="repo_name" value="<?= htmlspecialchars($repository['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description"><?= htmlspecialchars($repository['description']) ?></textarea>
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="private" name="private" <?= $repository['private'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="private">Private</label>
        </div>
        <button type="submit" class="btn btn-primary">Update Repository</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#edit-form').on('submit', function(event) {
    event.preventDefault();
    
    const repoName = $('#repo_name').val();
    const description = $('#description').val();
    const privateRepo = $('#private').is(':checked');

    $.post('<?=BASE_URL?>/userdata/updateRepo.php', {
        old_name: '<?= htmlspecialchars($repository['name']) ?>', // Original repo name
        new_name: repoName,
        description: description,
        private: privateRepo
    }, function(response) {
        const result = JSON.parse(response);
        if (result.status === 'success') {
            alert('Repository updated successfully!');
            window.location.href = '<?=BASE_URL?>'; // Redirect back to the repo list
        } else {
            alert(result.message);
        }
    });
});
</script>
</body>
</html>
