<?php
require_once __DIR__ . '/../config/database.php';

class Order
{
    /**
     * Create order and order_items inside a transaction.
     * $orderData expects keys: user_id, subtotal, shipping_fee, tax, discount, total, payment_method, notes
     * $items is an array of item arrays with product_id, name, sku, quantity, unit_price, total_price, tax
     */
    public static function create(PDO $pdo, array $orderData, array $items)
    {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO orders
                (order_number, user_id, subtotal, shipping_fee, tax_amount, discount_amount, total_amount, payment_method, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $orderNumber = 'ORD' . date('YmdHis') . rand(100,999);

            $stmt->execute([
                $orderNumber,
                $orderData['user_id'] ?? null,
                $orderData['subtotal'],
                $orderData['shipping_fee'],
                $orderData['tax'],
                $orderData['discount'],
                $orderData['total'],
                $orderData['payment_method'] ?? 'cod',
                $orderData['notes'] ?? ''
            ]);

            $orderId = $pdo->lastInsertId();

            $insertItem = $pdo->prepare("INSERT INTO order_items
                (order_id, product_id, product_name_snapshot, sku_snapshot, quantity, unit_price, total_price, tax_amount)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            foreach ($items as $it) {
                $insertItem->execute([
                    $orderId,
                    $it['product_id'] ?? null,
                    $it['name'] ?? '',
                    $it['sku'] ?? '',
                    intval($it['quantity'] ?? 1),
                    floatval($it['unit_price'] ?? 0),
                    floatval($it['total_price'] ?? 0),
                    floatval($it['tax'] ?? 0)
                ]);
            }

            $pdo->commit();
            return (int)$orderId;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }
}
