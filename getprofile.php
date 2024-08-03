<?php
require '../vendor/autoload.php';
use MongoDB\Client as MongoClient;

$response = ['success' => false];

$input = json_decode(file_get_contents('php://input'), true);
$authToken = $input['token'] ?? '';

if ($authToken) {
    try {
        $mongoClient = new MongoClient("mongodb://localhost:27017");
        $database = $mongoClient->profile_db;
        $collection = $database->profiles;

        $profile = $collection->findOne(['auth_token' => $authToken]);

        if ($profile) {
            $response['success'] = true;
            $response['username'] = $profile['username'] ?? '';
            $response['profile'] = [
                'gender' => $profile['gender'] ?? '',
                'dob' => $profile['dob'] ?? '',
                'age' => $profile['age'] ?? '',
                'contact' => $profile['contact'] ?? ''
            ];
        } else {
            $response['success'] = true;
            $response['username'] = ''; 
        }
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'No auth token provided.';
}

header('Content-Type: application/json');
echo json_encode($response);
?>
