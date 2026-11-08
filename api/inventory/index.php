<?php
/**
 * API Endpoint: Inventory & Medical Supplies
 * Supports: GET (list / search), POST (create)
 */

require_once __DIR__ . '/../response.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $search = trim($_GET['search'] ?? '');

    try {
        $sql = "SELECT * FROM inventory WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (item_name LIKE :search OR category LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        $sql .= " ORDER BY item_id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        foreach ($items as &$item) {
            $item['status_class'] = match ($item['stock_status']) {
                'Adequate'     => 'ongoing',
                'Low Stock'    => 'upcoming',
                'Out of Stock' => 'completed',
                default        => 'ongoing'
            };
        }

        sendJson([
            'success' => true,
            'count'   => count($items),
            'data'    => $items
        ]);
    } catch (PDOException $e) {
        sendError('Database query failed: ' . $e->getMessage(), 500);
    }
} elseif ($method === 'POST') {
    $data = getRequestPayload();
    $action = $data['action'] ?? 'create';

    if ($action === 'adjust_stock') {
        $itemId = (int)($data['item_id'] ?? 0);
        $delta  = (int)($data['delta'] ?? 0);

        if ($itemId <= 0 || $delta === 0) {
            sendError('Invalid item_id or adjustment delta.', 422);
        }

        try {
            $stmt = $pdo->prepare("SELECT stock_quantity FROM inventory WHERE item_id = ?");
            $stmt->execute([$itemId]);
            $current = $stmt->fetchColumn();

            if ($current === false) {
                sendError('Item not found.', 404);
            }

            $newStock = max(0, (int)$current + $delta);
            $newStatus = $newStock <= 0 ? 'Out of Stock' : ($newStock <= 25 ? 'Low Stock' : 'Adequate');

            $uStmt = $pdo->prepare("UPDATE inventory SET stock_quantity = ?, stock_status = ? WHERE item_id = ?");
            $uStmt->execute([$newStock, $newStatus, $itemId]);

            sendJson([
                'success'        => true,
                'message'        => 'Stock quantity updated.',
                'stock_quantity' => $newStock,
                'stock_status'   => $newStatus,
                'status_class'   => match ($newStatus) {
                    'Adequate'     => 'ongoing',
                    'Low Stock'    => 'upcoming',
                    'Out of Stock' => 'completed',
                    default        => 'ongoing'
                }
            ]);
        } catch (PDOException $e) {
            sendError('Failed to adjust stock: ' . $e->getMessage(), 500);
        }
    }

    $name     = trim($data['item_name'] ?? '');
    $category = trim($data['category'] ?? 'General Supplies');
    $stock    = (int)($data['stock_quantity'] ?? 0);
    $unit     = trim($data['unit'] ?? 'Boxes');
    $status   = trim($data['stock_status'] ?? 'Adequate');

    $errors = [];
    if ($name === '') $errors[] = 'Item name is required.';
    if ($unit === '') $errors[] = 'Unit of measure is required.';

    if (!in_array($status, ['Adequate', 'Low Stock', 'Out of Stock'], true)) {
        $status = $stock <= 0 ? 'Out of Stock' : ($stock < 20 ? 'Low Stock' : 'Adequate');
    }

    if (!empty($errors)) {
        sendError('Validation failed.', 422, $errors);
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO inventory (item_name, category, stock_quantity, unit, stock_status)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $category, $stock, $unit, $status]);

        $newId = (int)$pdo->lastInsertId();

        sendJson([
            'success' => true,
            'message' => 'Inventory item added successfully.',
            'item_id' => $newId,
            'data' => [
                'item_id'        => $newId,
                'item_name'      => $name,
                'category'       => $category,
                'stock_quantity' => $stock,
                'unit'           => $unit,
                'stock_status'   => $status,
                'status_class'   => match ($status) {
                    'Adequate'     => 'ongoing',
                    'Low Stock'    => 'upcoming',
                    'Out of Stock' => 'completed',
                    default        => 'ongoing'
                }
            ]
        ], 201);
    } catch (PDOException $e) {
        sendError('Failed to add inventory item: ' . $e->getMessage(), 500);
    }
} else {
    sendError('Method not allowed.', 405);
}
