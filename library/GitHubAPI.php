<?php
session_start();

//setting->developer setting -> personnal access token
// give permission to token which can perform action
// you can use direct without oAuth APP
class GitHubAPI {
    public $token;
    public $github_username = "dontKnew";
    private $apiUrl = "https://api.github.com/";
    private $userAgent = "GitHub-API-PHP";

    public function __construct() {
        $this->token = $_SESSION['access_token'];
        $this->github_username = $this->getProfile()['response']['login'] ?? null;
    }

    private function makeRequest($endpoint, $method = 'GET', $data = null) {
        $url = $this->apiUrl . $endpoint;
        $ch = curl_init();

        // Common cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: token $this->token",
            "User-Agent: $this->userAgent",
            "Content-Type: application/json",
        ]);
        
        // Check for POST/PUT/DELETE methods
        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        return ['status' => $httpCode, 'response' => json_decode($response, true)];
    }

    public function getRepositories($visibility = 'all', $page = 1, $per_page = 1000) {
        $endpoint = "user/repos?visibility=$visibility&per_page=$per_page&page=$page";
        $response = $this->makeRequest($endpoint, 'GET');
        
        if ($response['status'] != 200) {
            return $response;
        }
        
        return ['status' => 200, 'response' => $response['response']]; 
    }

    
    public function createRepository($name, $description = '', $private = false) {
        $endpoint = "user/repos";
        $data = [
            'name' => $name,
            'description' => $description,
            'private' => $private
        ];
        return $this->makeRequest($endpoint, 'POST', $data);
    }

    
    public function updateRepository($repoName, $name, $description = '', $private = false) {
        $endpoint = "repos/$this->github_username/$repoName";
        $data = [
            'name' => $name,
            'description' => $description,
            'private' => $private
        ];
        return $this->makeRequest($endpoint, 'PATCH', $data);
    }

    public function deleteRepository($repoName) {
        $endpoint = "repos/$this->github_username/$repoName";
        return $this->makeRequest($endpoint, 'DELETE');
    }

    public function getProfile() {
        $endpoint = "user"; 
        return $this->makeRequest($endpoint, 'GET');
    }
    public function getUserProfile($username) {
        $endpoint = "users/$username"; // For any public user profile
        return $this->makeRequest($endpoint, 'GET');
    }
    public function getRepositoryZipUrl($repoName, $branch = 'main') {
        return "https://github.com/{$this->github_username}/{$repoName}/archive/refs/heads/{$branch}.zip";
    }

}
