<?php
require_once 'smm_client.php';

// Provider API Configuration
$apiKey = 'kyx_299afda8925a49d4b721f16ee083f8c2'; 
$apiUrl = 'https://smmorange.com/api';

$api = new SmmApiClient($apiKey, $apiUrl);

// Handle Form Submissions
$message = '';
$messageType = 'info';
$orderStatusResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['form_action'] ?? '';

    // 1. Place Order Action
    if ($action === 'add_order') {
        $serviceId = intval($_POST['service_id'] ?? 0);
        $link = trim($_POST['link'] ?? '');
        $quantity = intval($_POST['quantity'] ?? 0);

        if ($serviceId > 0 && !empty($link) && $quantity > 0) {
            $response = $api->addOrder([
                'service'  => $serviceId,
                'link'     => $link,
                'quantity' => $quantity
            ]);

            if (isset($response['order'])) {
                $message = "Order placed successfully! Order ID: #" . htmlspecialchars($response['order']);
                $messageType = "success";
            } else {
                $errorMsg = $response['error'] ?? 'Unknown error occurred.';
                $message = "Failed to place order: " . htmlspecialchars($errorMsg);
                $messageType = "danger";
            }
        } else {
            $message = "Please fill in all order fields correctly.";
            $messageType = "danger";
        }
    }

    // 2. Check Order Status Action
    if ($action === 'check_status') {
        $orderId = trim($_POST['order_id'] ?? '');
        if (!empty($orderId)) {
            if (strpos($orderId, ',') !== false) {
                $ids = array_map('trim', explode(',', $orderId));
                $orderStatusResult = $api->getMultiOrderStatus($ids);
            } else {
                $orderStatusResult = $api->getOrderStatus(intval($orderId));
            }
        } else {
            $message = "Please enter a valid Order ID.";
            $messageType = "danger";
        }
    }
}

// Fetch Account Balance and Available Services from smmorange.com
$balanceData = $api->getBalance();
$services = $api->getServices();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMM Panel - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; padding-top: 30px; }
        .card { border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 24px; }
        .header-bg { background: linear-gradient(135deg, #0d6efd, #0dcaf0); color: white; border-radius: 12px; padding: 20px; }
    </style>
</head>
<body>
<div class="container" style="max-width: 900px;">

    <!-- Dashboard Header -->
    <div class="header-bg d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0">SMM Panel Services</h3>
            <small>Automated API Order System</small>
        </div>
        <div class="text-end">
            <span class="d-block text-white-50">Account Balance</span>
            <h4 class="mb-0">
                <?php 
                if (isset($balanceData['balance'])) {
                    echo htmlspecialchars($balanceData['balance']) . ' ' . htmlspecialchars($balanceData['currency'] ?? 'USD');
                } else {
                    echo '$0.00';
                }
                ?>
            </h4>
        </div>
    </div>

    <!-- Feedback Message Alert -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Section 1: Place New Order -->
        <div class="col-md-7">
            <div class="card p-4">
                <h5 class="card-title mb-3">Place New Order</h5>
                <form method="POST" action="">
                    <input type="hidden" name="form_action" value="add_order">

                    <div class="mb-3">
                        <label for="service_id" class="form-label">Select Service</label>
                        <select class="form-select" id="service_id" name="service_id" required>
                            <option value="">-- Choose a Service --</option>
                            <?php if (is_array($services) && !isset($services['error'])): ?>
                                <?php foreach ($services as $srv): ?>
                                    <option value="<?= htmlspecialchars($srv['service']) ?>">
                                        [ID: <?= htmlspecialchars($srv['service']) ?>] <?= htmlspecialchars($srv['name']) ?> - $<?= htmlspecialchars($srv['rate']) ?>/1k
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>Unable to load services from provider</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="link" class="form-label">Target Link / URL</label>
                        <input type="url" class="form-control" id="link" name="link" placeholder="https://instagram.com/username" required>
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" placeholder="1000" min="1" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Submit Order</button>
                </form>
            </div>
        </div>

        <!-- Section 2: Order Status Lookup -->
        <div class="col-md-5">
            <div class="card p-4">
                <h5 class="card-title mb-3">Check Order Status</h5>
                <form method="POST" action="">
                    <input type="hidden" name="form_action" value="check_status">
                    
                    <div class="mb-3">
                        <label for="order_id" class="form-label">Order ID(s)</label>
                        <input type="text" class="form-control" id="order_id" name="order_id" placeholder="e.g. 1024 or 1024,1025" required>
                        <div class="form-text">Separate multiple IDs with a comma.</div>
                    </div>

                    <button type="submit" class="btn btn-secondary w-100">Check Status</button>
                </form>

                <!-- Status Results Display -->
                <?php if ($orderStatusResult !== null): ?>
                    <hr>
                    <h6 class="fw-bold">Status Results:</h6>
                    <pre class="bg-light p-3 rounded" style="font-size: 0.85rem; max-height: 200px; overflow-y: auto;"><?= htmlspecialchars(print_r($orderStatusResult, true)) ?></pre>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
