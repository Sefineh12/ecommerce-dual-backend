<?php
require_once '../config.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"), true);
$action = $_GET['action'] ?? '';

if ($action === 'register') {
    if (!empty($data['full_name']) && !empty($data['email']) && !empty($data['password'])) {
        $query = "INSERT INTO users (full_name, email, password_hash) VALUES (:name, :email, :password)";
        $stmt = $db->prepare($query);

        $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);

        $stmt->bindParam(':name', $data['full_name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', $password_hash);

        try {
            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(["message" => "User registered successfully."]);
            }
        } catch (PDOException $e) {
            http_response_code(400);
            echo json_encode(["message" => "Email already exists or invalid data."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Incomplete data provided."]);
    }
} elseif ($action === 'login') {
    if (!empty($data['email']) && !empty($data['password'])) {
        $query = "SELECT id, full_name, password_hash, role FROM users WHERE email = :email LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $data['email']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            if (password_verify($data['password'], $row['password_hash'])) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Login successful.",
                    "user" => [
                        "id" => $row['id'],
                        "name" => $row['full_name'],
                        "role" => $row['role']
                    ]
                ]);
            } else {
                http_response_code(401);
                echo json_encode(["message" => "Invalid password."]);
            }
        } else {
            http_response_code(404);
            echo json_encode(["message" => "User not found."]);
        }
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Invalid action specified."]);
}
?>
