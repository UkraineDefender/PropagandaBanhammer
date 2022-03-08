<?php

use GuzzleHttp\Client;

class BanhammerAnalytics
{
    private string $apiURL = '';
    private ?Client $httpClient;
    private ?string $id = null;

    public function __construct(string $apiURL)
    {
        $this->apiURL = $apiURL;
        $this->httpClient = new Client([
            'base_uri' => $apiURL,
            'verify' => false
        ]);
    }

    public function registerDevice(): ?string
    {
        $apiResponse = $this->httpClient->request('GET', 'devices/register') ?? null;
        $apiResponseData = $apiResponse->getBody()->getContents();
        $apiResponseJson = @json_decode($apiResponseData) ?? null;

        return $apiResponseJson->{'device'}->{'ID'} ?? null;
    }

    public function init(): void
    {
        if(!isset($_COOKIE['device-id']))
        {
            $id = $this->registerDevice();
            
            $this->id = $id;

            setcookie('device-id', $id, time() + (86400 * 365), '/');
        }
        else
        {
            $this->id = $_COOKIE['device-id'];
        }
    }

    public function sendReportResult(bool $success, string $error = null): void
    {
        if($this->id != null)
            $this->httpClient->request('GET', "devices/sendReportResult/?id=$this->id&success=" . ($success ? 'true' : 'false') . ($error != null ? "&error=$error" : ""))->getBody();
    }
}