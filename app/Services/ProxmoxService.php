<?php

namespace App\Services;

use GuzzleHttp\Client;

class ProxmoxService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://192.168.170.184:8006/api2/json/',
            'verify' => false, // Matikan verifikasi sertifikat SSL jika menggunakan sertifikat self-signed
        ]);
    }

    // Fungsi untuk autentikasi ke API Proxmox VE
    public function authenticate()
    {
        $response = $this->client->post('access/ticket', [
            'form_params' => [
                'username' => 'root',
                'password' => 'admin',
                'realm' => 'pam',
            ]
        ]);

        $body = json_decode($response->getBody(), true);
        return $body['data'];
    }

    // Fungsi untuk mendapatkan status node dari Proxmox VE
    public function getNodeStatus($node)
    {
        $authData = $this->authenticate();
        $ticket = $authData['ticket'];
        $csrfToken = $authData['CSRFPreventionToken'];

        $response = $this->client->get("nodes/{$node}/status", [
            'headers' => [
                'Cookie' => "PVEAuthCookie=$ticket",
                'CSRFPreventionToken' => $csrfToken
            ]
        ]);

        return json_decode($response->getBody(), true);
    }
}
