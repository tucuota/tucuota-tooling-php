<?php


// Include Guzzle Library
require 'vendor/autoload.php'; // Adjust the path based on your project structure.

require 'config.php'; // Include your configuration file

use GuzzleHttp\Client;


// Authenticate
$headers = [
    'Authorization' => 'Bearer ' . $api_secret,
    'Content-Type' => 'application/json',
];

// Set Payments IDs to Cancel

$payment_ids = ['PYjRwog4XERm'];


$client = new Client();

foreach ($payment_ids as $payment_id) {

    echo "Cancel payment with ID: $payment_id? (Y/N): ";
    
    $input = strtolower(trim(fgets(STDIN))); // Read input from console
    
    if ($input === 'y') {
            
        $cancel_endpoint = 'https://api.tucuota.com/v1/payments/' . $payment_id . '/actions/cancel';

        try {
            $response = $client->post($cancel_endpoint, [
                'headers' => $headers,
            ]);
            
            // Process the response for each payment ID
            $response_code = $response->getStatusCode();
            $response_body = $response->getBody()->getContents();
            echo "Response Code: $response_code. Response Body $response_body\n";
            // echo "Response Body: $response_body\n";
            // Handle success or failure based on the response
        } catch (Exception $ex) {
            // Handle any exceptions or errors
            $error_message = $ex->getMessage();
            echo "Error: $error_message\n";
        }
    }
}

?>
