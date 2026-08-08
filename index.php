<?php
// 1. Include the SmmApiClient wrapper class
require_once 'smm_client.php';

// 2. Initialize with your API credentials
// Replace with your actual API Key from your provider (e.g. Chhean Smm or SMM Orange)
$apiKey = 'kyx_299afda8925a49d4b721f16ee083f8c2'; 
$apiUrl = 'https://chheansmm.com/api/v2'; // Or 'https://smmorange.com/api/v2'

$client = new SmmApiClient($apiKey, $apiUrl);

// Fetch account balance to display on the header
$balanceData = $client->getBalance();
$balance = $balanceData['balance'] ?? '0.00';

// Handle form submission for placing an order
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
            $message = "<div style='color:green;'>Success! Order ID: " . htmlspecialchars($response['order']) . "</div>";
        } else {
            $error = $response['error'] ?? 'Unknown error';
            $message = "<div style='color:red;'>Failed: " . htmlspecialchars($error) . "</div>";
        }
    } else {
        $message = "<div style='color:red;'>Please fill in all fields.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SMM Services Panel</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 40px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input, select { width: 100%; padding: 8px; box-sizing: border-box; }
        button { background: #007bff; color: #fff; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .balance-badge { background: #e9ecef; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>

    <h2>Order Social Media Marketing</h2>

    <div class="balance-badge">
        <strong>Account Balance:</strong> $<?= htmlspecialchars($balance) ?>
    </div>

    <?= $message ?>

    <form method="POST">
        <div class="form-group">
            <label for="service_id">Service ID:</label>
            <input type="number" id="service_id" name="service_id" placeholder="e.g. 1" required>
        </div>

        <div class="form-group">
            <label for="link">Target Link:</label>
            <input type="url" id="link" name="link" placeholder="https://instagram.com/your-post" required>
        </div>

        <div class="form-group">
            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" min="100" placeholder="1000" required>
        </div>

        <button type="submit">Submit Order</button>
    </form>

</body>
</html>
