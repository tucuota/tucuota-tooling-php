<?php

require 'vendor/autoload.php'; 
require 'config.php'; 

use GuzzleHttp\Client;

class TuCuotaService {
    private $client;
    private $url;

    public function __construct($api_secret, $useSandbox = true) {
        $this->url = $useSandbox ? 'https://sandbox.tucuota.com/api/' : 'https://api.tucuota.com/v1/';
        $this->client = new Client([
            'base_uri' => $this->url,
            'headers' => [
                'Authorization' => 'Bearer ' . $api_secret,
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function getPaymentMethod($customerId) {
        try {
            $response = $this->client->get('payments', ['query' => ['customer_id' => $customerId]]);
            $data = json_decode($response->getBody()->getContents(), true);
            return $data['data'][0]['payment_method']['id'] ?? null;
        } catch (Exception $ex) {
            echo "Error fetching payment method: {$ex->getMessage()}\n";
            return null;
        }
    }

    public function addPmsToAllUsers() {
    
        
        $endpoint = "https://api.tucuota.com/v1/customers?limit=100";
        
        while ($endpoint) {
            echo "fetching " .$endpoint . PHP_EOL;
            $response = $this->client->get($endpoint);
            $data = json_decode($response->getBody()->getContents(), true);
            
            foreach ($data['data'] as $customer) {
                $metadata = $customer['metadata'] ?: [];
                if (!key_exists('payment_method_id', $metadata)) {
                    $id = $customer['id'];
                    $paymentMethod = $this->getPaymentMethod($id);
                    if (!$paymentMethod) {
                        echo "No paymentMethod for customer $id" . PHP_EOL;
                        continue;
                    }
                    $metadata['payment_method_id'] = $paymentMethod;
                    $response = $this->client->put('customers/' . $id, [
                        'json' => ['metadata' => $metadata]
                    ]);
                    echo "Added $paymentMethod to customer $id" . PHP_EOL;
                }
            }
            $endpoint = $data['links']['next'];

        }   
    }
}

$tucuotaService = new TuCuotaService($api_secret, false); // second in false for production
$customers = $tucuotaService->addPmsToAllUsers();
