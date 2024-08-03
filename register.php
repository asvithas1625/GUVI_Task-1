<?php
require '../vendor/autoload.php';

$response = ['success' => false];

$host = "localhost";
$username = "root";
$password = "";
$dbname = "guvi";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $input = json_decode(file_get_contents('php://input'), true);
    
    $username = $input['username'];
    $email = $input['email'];
    $password = $input['password'];

    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $response['message'] = 'Email already exists.';
        $response['redirect'] = true;
    } else {
  
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bindParam(':password', $hashedPassword);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Registration successful.';
            $response['redirect'] = false;
        } else {
            $response['message'] = 'Error inserting data into MySQL.';
            $response['redirect'] = false;
        }
    }
} catch (PDOException $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    $response['redirect'] = false;
}

header('Content-Type: application/json');
echo json_encode($response);
?>