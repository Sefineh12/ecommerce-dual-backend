<?php
require_once '../config.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($data['user_id']) && !empty($data['items'])) {
        try {
            // Start Database ACID Transaction
            $db->beginTransaction();

            $total_amount = 0;
            foreach ($data['items'] as $item) {
                $total_amount += $item['unit_price'] * $item['quantity'];
            }

            // 1. Insert into orders table
            $order_query = "INSERT INTO orders (user_id, total_amount, status) VALUES (:user_id, :total, 'paid')";
            $stmt = $db->prepare($order_query);
            $stmt->bindParam(':user_id', $data['user_id']);
            $stmt->bindParam(':total', $total_amount);
            $stmt->execute();

            $order_id = $db->lastInsertId();

            // 2. Insert order items & Update Product Stock
            foreach ($data['items'] as $item) {
                // Check stock
                $stock_stmt = $db->prepare("SELECT stock_quantity FROM products WHERE id = :p_id FOR UPDATE");
                $stock_stmt->bindParam(':p_id', $item['product_id']);
                $stock_stmt->execute();
                $product = $stock_stmt->fetch();

                if ($product['stock_quantity'] < $item['quantity']) {
                    throw new Exception("Insufficient stock for product ID: " . $item['product_id']);
                }

                // Insert Item
                $item_query = "INSERT INTO order_items (order_id, product_id, quantity, unit_price) 
                              VALUES (:order_id, :product_id, :qty, :price)";
                $item_stmt = $db->prepare($item_query);
                $item_stmt->bindParam(':order_id', $order_id);
                $item_stmt->bindParam(':product_id', $item['product_id']);
                $item_stmt->bindParam(':qty', $item['quantity']);
                $item_stmt->bindParam(':price', $item['unit_price']);
                $item_stmt->execute();

                // Deduct Stock
                $update_stock = $db->prepare("UPDATE products SET stock_quantity = stock_quantity - :qty WHERE id = :p_id");
                $update_stock->bindParam(':qty', $item['quantity']);
                $update_stock->bindParam(':p_id', $item['product_id']);
                $update_stock->execute();
            }

            // Commit Transaction
            $db->commit();

            http_response_code(201);
            echo json_encode(["message" => "Order processed successfully.", "order_id" => $order_id]);

        } catch (Exception $e) {
            // Rollback on Failure
            $db->rollBack();
            http_response_code(400);
            echo json_encode(["message" => "Order failed: " . $e->getMessage()]);
        }
    }
}
?>
