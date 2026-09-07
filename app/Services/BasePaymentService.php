<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

abstract class BasePaymentService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.paymob.base_url');
    }

    protected function request(string $method, string $url, array $data = [])
    {
        $response = Http::timeout(30)
            ->acceptJson()
            ->send($method, $this->baseUrl . $url, [
                'json' => $data,
            ]);

        return $response->json();
    }
}