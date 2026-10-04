<?php
/**
 * Minimal PayMongo Hosted Checkout client.
 * The secret key must be provided by the server environment, never browser JS.
 */
class PayMongoClient
{
    private function configuredSecretKey(): string
    {
        if (defined('PAYMONGO_SECRET_KEY') && PAYMONGO_SECRET_KEY !== '') {
            return trim((string) PAYMONGO_SECRET_KEY);
        }
        return trim((string) getenv('PAYMONGO_SECRET_KEY'));
    }
    private string $secretKey;
    private string $apiBase = 'https://api.paymongo.com/v2';

    public function __construct()
    {
        $configFile = __DIR__ . '/paymongo_config.php';
        if (is_file($configFile)) {
            require_once $configFile;
        }

        $this->secretKey = $this->configuredSecretKey();

        if ($this->secretKey === '') {
            throw new RuntimeException('PAYMONGO_SECRET_KEY is not configured on the server.');
        }
    }

    public function createCheckoutSession(array $attributes): array
    {
        $payload = [
            'data' => [
                'attributes' => $attributes,
            ],
        ];

        $ch = curl_init($this->apiBase . '/checkout_sessions');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => $this->secretKey . ':',
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
            CURLOPT_TIMEOUT => 30,
        ]);

        $responseBody = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($responseBody === false || $curlError !== '') {
            throw new RuntimeException('PayMongo request failed: ' . $curlError);
        }

        $decoded = json_decode($responseBody, true);

        if ($httpCode < 200 || $httpCode >= 300) {
            $detail = $decoded['errors'][0]['detail'] ?? '';
            $code = $decoded['errors'][0]['code'] ?? '';
            $suffix = $detail !== '' ? $detail : ('HTTP ' . $httpCode);
            if ($code !== '') {
                $suffix .= ' [' . $code . ']';
            }
            throw new RuntimeException(
                'PayMongo checkout request failed (HTTP ' . $httpCode . '): ' . $suffix
            );
        }

        if (!isset($decoded['data']['id'], $decoded['data']['attributes']['checkout_url'])) {
            throw new RuntimeException('PayMongo returned an incomplete checkout session response.');
        }

        return $decoded['data'];
    }
}
