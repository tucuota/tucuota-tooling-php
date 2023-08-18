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

    public function getCustomerMetadata($customerId) {
        try {
            $response = $this->client->get('customers/' . $customerId);
            $data = json_decode($response->getBody()->getContents(), true);
            return $data['data']['metadata'] ?? null;
        } catch (Exception $ex) {
            echo "Error fetching customer metadata: {$ex->getMessage()}\n";
            return null;
        }
    }

    public function updateCustomerMetadata($customerId, $metadata) {
        try {
            $dataToSend = [
                'metadata' => $metadata
            ];
            
            $response = $this->client->put('customers/' . $customerId, [
                'json' => $dataToSend
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            echo 'Se completo la metadata ' . json_encode($metadata) . ' en el customer ' . $customerId . PHP_EOL;
            return $data ?? null;
        } catch (Exception $ex) {
            echo "Error fetching customer update: {$ex->getMessage()}\n";
            return null;
        }
    }
}

$tucuotaService = new TuCuotaService($api_secret_sandbox); // false for production
$customers = ['CSeWwOBBVaZj', 'CS46Dyxx1kwE', 'CSdYDqpWBwkq', 'CSJLDQ12YDrj'];

foreach ($customers as $customer) {
    $paymentMethod = $tucuotaService->getPaymentMethod($customer);
    $metadata = $tucuotaService->getCustomerMetadata($customer);
    //$metadata = '{"clave":"valor"}';
    
    if ($paymentMethod) {
        $exists = (is_string($metadata) && strpos($metadata, $paymentMethod) !== false) || (is_array($metadata) && in_array($paymentMethod, $metadata));
        if($exists){
            echo 'Ya existe el PM ' . $paymentMethod . ' en la metadata del customer ' . $customer . PHP_EOL;
        }else{
            if ($metadata && is_array($metadata)) {
                $metadata['payment_method_id'] = $paymentMethod;
            } elseif (!$metadata) {
                $metadata = ['payment_method_id' => $paymentMethod];
            }            
            $tucuotaService->updateCustomerMetadata($customer, $metadata);
        }
    }else{
        echo 'El customer ' . $customer . ' no tiene PM o Pagos ' . PHP_EOL;
    }
}

// Ya existe el PM PM6eDbGwrkrJ en la metadata del customer CSeWwOBBVaZj
// Se completo la metadata {"payment_method_id":"PM6eDbGwrkrJ"} en el customer CSeWwOBBVaZj
// Se completo la metadata {"salesforce_id":"0036S00005ydRmMQAU","payment_method_id":"PM3xW1GOvDqj"} en el customer CS46Dyxx1kwE% 

?>

