<?php
require_once __DIR__."/../config.php";
require __DIR__.'/../library/GitHubAPI.php';
if(empty($_SESSION['access_token'])){ 
    header("Location:".BASE_URL);
    exit();
}
$github = new GitHubAPI();

$user = $github->getProfile()['response'] ?? null;
$name = $user['name'] ?? $user['login'];
$profile = $user['avatar_url'] ?? null;


$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; 
$perPage = 30; 
$response = $github->getRepositories("all", $page, $perPage);
$repositories = $response['status'] == 200 ? $response['response'] : [];

$current_sr_no = $perPage * $page - $perPage;
$totalPages = 10; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GitHub Repositories</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container">

    <div class="text-center bg-dark p-2 rounded">
        <h1 class="text-center text-white">
            Hey <?= $name ?>!
        </h1>
        <img src="<?=$profile?>" class="rounded" width="100" height="100" />
    </div>
    <h2><?= $name ?>`s GitHub Repositories</h2>
    
    <form id="multi-delete-form">
        <div class="d-flex justify-content-between align-items-center">
            <button type="button" class="btn btn-danger " id="multi-delete-btn">Delete Selected</button>
    <nav>
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?=BASE_URL?>/userdata?page=<?= $page - 1 ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="<?=BASE_URL?>/userdata?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="<?=BASE_URL?>/userdata?page=<?= $page + 1 ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
            <a class="btn btn-warning" href="<?=BASE_URL?>/logout.php">Logout</a>
        </div>

        <table class="table table-bordered table-dark table-hover table-striped">
            <thead>
                <tr>
                    <th scope="col">
                        <input type="checkbox" id="select-all"> <!-- Select all checkbox -->
                    </th>
                    <th scope="col">#</th>
                    <th scope="col">Repository Name</th>
                    <th scope="col">Private</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($repositories as $index => $repo): ?>
                    <tr>
                        <td>
                            <input type="checkbox" class="repo-checkbox" name="repo_names[]" value="<?= htmlspecialchars($repo['name']) ?>">
                        </td>
                        <td><?= $current_sr_no + $index + 1 ?></td>
                        <td><?= htmlspecialchars($repo['name']) ?></td>
                        <td><?= $repo['private'] ? 'Yes' : 'No' ?></td>
                        <td>
                            <a class="btn btn-sm btn-success" target="_blank" href="<?= $github->getRepositoryZipUrl($repo['name'], $repo['default_branch']); ?>" download >Download</a>
                            <a class="btn btn-sm btn-warning" target="_blank" href="<?= $repo['html_url'] ?>">View</a>
                            <a class="btn  btn-sm btn-primary" href="<?=BASE_URL?>/userdata/editRepo.php?repo_name=<?= urlencode($repo['name']) ?>">Edit</a>
                            <button type="button" class="btn btn-sm btn-danger delete-btn" data-repo-name="<?= htmlspecialchars($repo['name']) ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </form>


    <!-- Pagination Controls -->
    <nav>
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?=BASE_URL?>/userdata?page=<?= $page - 1 ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="<?=BASE_URL?>/userdata?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="<?=BASE_URL?>/userdata?page=<?= $page + 1 ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Handle delete button click
    $('.delete-btn').click(function() {
        const repoName = $(this).data('repo-name');
        if (confirm(`Are you sure you want to delete the repository "${repoName}"?`)) {
            $.post('<?=BASE_URL?>/userdata/deleteRepo.php', { repo_name: repoName }, function(response) {
                const result = JSON.parse(response);
                if (result.status === 'success') {
                    location.reload(); 
                } else {
                    alert(result.message);
                }
            });
        }
    });
});

$(document).ready(function() {
    $('#select-all').click(function() {
        $('.repo-checkbox').prop('checked', this.checked);
    });
    $('#multi-delete-btn').click(function() {
        const selectedRepos = [];
        $('.repo-checkbox:checked').each(function() {
            selectedRepos.push($(this).val());
        });

        if (selectedRepos.length === 0) {
            alert('No repositories selected.');
            return;
        }

        if (confirm(`Are you sure you want to delete the selected repositories?`)) {
            $.post('<?=BASE_URL?>/userdata/multiDeleteRepo.php', { repo_names: selectedRepos }, function(response) {
                const result = JSON.parse(response);
                location.reload(); 
                if (result.status === 'success') {
                    
                } else {
                    alert(result.message);
                }
            });
        }
    });
});

</script>
</body>
</html>
