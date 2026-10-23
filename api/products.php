<?php
require_once '../config.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $query = "SELECT p.id, p.name, p.description, p.price, p.stock_quantity, c.name as category 
              FROM products p 
              JOIN categories c ON p.category_id = c.id";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $products = $stmt->fetchAll();

    http_response_code(200);
    echo json_encode(["products" => $products]);
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!empty($data['category_id']) && !empty($data['name']) && !empty($data['price'])) {
        $query = "INSERT INTO products (category_id, name, description, price, stock_quantity) 
                  VALUES (:cat_id, :name, :desc, :price, :stock)";
        $stmt = $db->prepare($query);

        $stmt->bindParam(':cat_id', $data['category_id']);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':desc', $data['description']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':stock', $data['stock_quantity']);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["message" => "Product created successfully."]);
        }
    }
}
?>
