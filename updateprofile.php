<?php
require '../vendor/autoload.php';
use MongoDB\Client as MongoClient;

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $authToken = $_POST['token'] ?? '';
    $username = $_POST['username'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $age = $_POST['age'] ?? '';
    $contact = $_POST['contact'] ?? '';

    if ($authToken && $username) {
        try {
            $mongoClient = new MongoClient("mongodb://localhost:27017");
            $database = $mongoClient->profile_db;
            $collection = $database->profiles;

            $updateResult = $collection->updateOne(
                ['auth_token' => $authToken],
                ['$set' => [
                    'username' => $username,
                    'gender' => $gender,
                    'dob' => $dob,
                    'age' => $age,
                    'contact' => $contact
                ]],
                ['upsert' => true] // Use upsert to create the profile if it does not exist
            );

            if ($updateResult->getModifiedCount() > 0 || $updateResult->getUpsertedCount() > 0) {
                $response['success'] = true;
                $response['message'] = 'Profile updated successfully.';
            } else {
                $response['message'] = 'No changes were made to the profile.';
            }
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Invalid token or username.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

header('Content-Type: application/json');
echo json_encode($response);
?>
