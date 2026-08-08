<?php
/**
 * SMM Panel API Integration Client (PHP)
 */
class SmmApiClient {
    private $apiKey;
    private $apiUrl;

    /**
     * Constructor
     *
     * @param string $apiKey Your API Key
     * @param string $apiUrl API endpoint URL
     */
    public function __construct($apiKey, $apiUrl = 'https://chheansmm.com/api/v2') {
        $this->apiKey = $apiKey;
        $this->apiUrl = rtrim($apiUrl, '/');
    }

    /**
     * Get account balance using custom cURL POST request
     */
    public function getBalance() {
        $endpoint = $this->apiUrl;
        $payload = [
            'key' => $this->apiKey,
            'action' => 'balance',
        ];

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => 'SmmApiClient/1.0.0 (PHP)',
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['error' => 'cURL Error: ' . $error];
        }

        $decoded = json_decode($response, true);
        return $decoded === null ? ['error' => 'Failed to parse JSON response: ' . $response] : $decoded;
    }

    /**
     * Retrieve list of all available services.
     */
    public function getServices() {
        return $this->request(['action' => 'services']);
    }

    /**
     * Place a new order.
     *
     * @param array $params Order parameters (service, link, quantity)
     */
    public function addOrder(array $params) {
        $postData = array_merge(['action' => 'add'], $params);
        return $this->request($postData);
    }

    /**
     * Get status of a single order.
     *
     * @param int $orderId
     */
    public function getOrderStatus($orderId) {
        return $this->request(['action' => 'status', 'order' => $orderId]);
    }

    /**
     * Get status of multiple orders.
     *
     * @param array $orderIds
     */
    public function getMultiOrderStatus(array $orderIds) {
        return $this->request(['action' => 'status', 'orders' => implode(',', $orderIds)]);
    }

    /**
     * Execute POST requests to the SMM API endpoint via cURL.
     */
    private function request(array $data) {
        $data['key'] = $this->apiKey;

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => 'SmmApiClient/1.0.0 (PHP)',
        ]);

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
