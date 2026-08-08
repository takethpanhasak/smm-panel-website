<?php
/**
 * SMM Panel API Integration Client (PHP)
 * 
 * A clean, lightweight, zero-dependency PHP wrapper for connecting to
 * wholesale SMM API endpoints. Compatible with PHP 7.4 to 8.3+.
 * 
 * Powered by SMM Orange (https://smmorange.com)
 */

class SmmApiClient {
    private $apiUrl;
    private $apiKey;

    /**
     * Constructor
     * 
     * @param string $apiKey Your API Key from your SMM Orange Account
     * @param string $apiUrl Optional custom API endpoint URL
     */
    public function __construct($apiKey, $apiUrl = 'https://smmorange.com/api/v2') {
        $this->apiKey = $apiKey;
        $this->apiUrl = rtrim($apiUrl, '/');
    }

    /**
     * Retrieve list of all available services, categories, and pricing.
     */
    public function getServices() {
        return $this->request(['action' => 'services']);
    }

    /**
     * Place a new social media marketing order.
     * 
     * @param array $params Order parameters (service, link, quantity, runs, interval)
     */
    public function addOrder($params) {
        $postData = array_merge(['action' => 'add'], $params);
        return $this->request($postData);
    }

    /**
     * Get the status of an existing order.
     * 
     * @param int $orderId The ID of the order
     */
    public function getOrderStatus($orderId) {
        return $this->request([
            'action' => 'status',
            'order' => $orderId
        ]);
    }

    /**
     * Get the status of multiple orders.
     * 
     * @param array $orderIds Array of order IDs
     */
    public function getMultiOrderStatus(array $orderIds) {
        return $this->request([
            'action' => 'status',
            'orders' => implode(',', $orderIds)
        ]);
    }

    /**
     * Retrieve your current account balance and currency code.
     */
    public function getBalance() {
        return $this->request(['action' => 'balance']);
    }

    /**
     * Execute POST requests to the SMM API endpoint.
     */
    private function request(array $data) {
        $data['key'] = $this->apiKey;

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_USERAGENT, 'SmmApiClient/1.0.0 (PHP)');

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['error' => 'cURL Error: ' . $error];
        }

        if ($httpCode !== 200) {
            return ['error' => 'HTTP Error Code: ' . $httpCode];
        }

        $decoded = json_decode($response, true);
        return $decoded === null ? ['error' => 'Failed to parse JSON response: ' . $response] : $decoded;
    }
}
