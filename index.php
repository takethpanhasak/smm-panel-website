<?php
require_once 'smm_client.php';

// Initialize with your API Key and API Endpoint
$apiKey = 'c1260ecb488566d9946baf1610f63a42';
$apiUrl = 'https://chheansmm.com/api/v2';

$client = new SmmApiClient($apiKey, $apiUrl);

// Fetch account balance to display
$balanceData = $client->getBalance();
$balance = $balanceData['balance'] ?? '0.00';

// Handle order form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceId = intval($_POST['service_id'] ?? 0);
    $link      = trim($_POST['link'] ?? '');
    $quantity  = intval($_POST['quantity'] ?? 0);

    if ($serviceId && $link && $quantity) {
        $response = $client->addOrder([
            'service'  => $serviceId,
            'link'     => $link,
            'quantity' => $quantity
        ]);

        if (isset($response['order'])) {
            $message = "<div style='color: green; font-weight: bold;'>Order Placed Successfully! Order ID: " . htmlspecialchars($response['order']) . "</div>";
        } else {
            $error = $response['error'] ?? 'Unknown error occurred.';
            $message = "<div style='color: red; font-weight: bold;'>Order Failed: " . htmlspecialchars($error) . "</div>";
        }
    } else {
        $message = "<div style='color: red;'>Please fill in all required fields correctly.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMM Panel - Order Services</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .balance-box { background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 16px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
        button:hover { background: #218838; }
    </style>
</head>
<body>

    <h2>Place New Order</h2>

    <div class="balance-box">
        Account Balance: <strong>$<?= htmlspecialchars($balance) ?></strong>
    </div>

    <?= $message ?>

    <form method="POST">
        <div class="form-group">
            <label for="service_id">Service ID:</label>
            <input type="number" id="service_id" name="service_id" placeholder="Enter Service ID" required>
        </div>

        <div class="form-group">
            <label for="link">Target Link:</label>
            <input type="url" id="link" name="link" placeholder="https://example.com/your-post" required>
        </div>

        <div class="form-group">
            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" min="100" placeholder="1000" required>
        </div>

        <button type="submit">Submit Order</button>
    </form>

</body>
</html>
