<?php
require '../vendor/autoload.php';
use MongoDB\Client as MongoClient;
use Predis\Client as RedisClient;

$response = ['success' => false];

$authToken = $_GET['token'] ?? '';

if ($authToken) {
    try {
        $redisClient = new RedisClient();
        $mongoClient = new MongoClient("mongodb://localhost:27017");
        $database = $mongoClient->profile_db;
        $collection = $database->profiles;

        $cachedProfile = $redisClient->get($authToken);

        if ($cachedProfile) {
            $profile = json_decode($cachedProfile, true);
        } else {
            // If not cached, fetch from MongoDB
            $profile = $collection->findOne(['auth_token' => $authToken]);

            if ($profile) {
                // Cache the result in Redis
                $redisClient->set($authToken, json_encode($profile));
            }
        }

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
            $response['username'] = 'No profile found.'; 
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
