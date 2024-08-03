<?php
require '../vendor/autoload.php';
use MongoDB\Client as MongoClient;
use Predis\Client as RedisClient;

$response = ['success' => false];

$host = "localhost";
$username = "root";
$password = "";
$dbname = "guvi";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $input = json_decode(file_get_contents('php://input'), true);
    
    $loginUsername = $input['username'];
    $loginPassword = $input['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $loginUsername);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($loginPassword, $user['password'])) {
        $authToken = bin2hex(random_bytes(16));

        $mongoClient = new MongoClient("mongodb://localhost:27017");
        $database = $mongoClient->profile_db;
        $collection = $database->profiles;

        $loginDetails = [
            'username' => $loginUsername,
            'email' => $user['email'],
            'last_login' => date('Y-m-d H:i:s'),
            'auth_token' => $authToken
        ];

        $collection->updateOne(
            ['username' => $loginUsername],
            ['$set' => $loginDetails],
            ['upsert' => true]
        );

        //  Redis
        $redis = new RedisClient();
        $sessionData = json_encode([
            'username' => $loginUsername,
            'email' => $user['email']
        ]);
        $redis->setex($authToken, 3600, $sessionData); 

        $response['success'] = true;
        $response['token'] = $authToken;
        $response['message'] = 'Login successful.';
    } else {
        $response['message'] = 'Invalid username or password.';
    }
} catch (PDOException $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>
