<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RcaApiService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('rca.url');
    }

    public function authenticate(): array
    {
        $response = Http::post($this->baseUrl . '/auth', [
            'account'  => config('rca.account'),
            'password' => config('rca.password'),
        ]);

        if ($response->failed()) {
            throw new Exception('Authentication failed');
        }

        $data = $response->json('data');

        Cache::put('rca_token', $data['token'], 3500);
        Cache::put('rca_refresh_token', $data['refresh_token'], 86400);

        return $data;
    }
    public function getToken(): string
    {
        return Cache::remember('rca_token', 3500, function () {
            return $this->authenticate()['token'];
        });
    }
    public function refreshToken(): array
    {
        $refreshToken = Cache::get('rca_refresh_token');

        if (!$refreshToken) {
            throw new Exception('No refresh token available');
        }

        $response = Http::withHeaders([
            'Token' => 'Refresh ' . $refreshToken,
            'Content-Type' => 'application/json',
        ])->patch($this->baseUrl . '/auth', []);

        if ($response->failed()) {
            throw new Exception('Token refresh failed');
        }

        $data = $response->json('data');

        Cache::put('rca_token', $data['token'], 3500);
        Cache::put('rca_refresh_token', $data['refresh_token'], 86400);

        return $data;
    }
    public function logout(): void
    {
        $token = Cache::get('rca_token');

        if (!$token) {
            return;
        }

        Http::withHeaders([
            'Token' =>  $token,
            'Content-Type' => 'application/json',
        ])->send('DELETE', $this->baseUrl . '/auth', [
            'json' => []
        ]);

        Cache::forget('rca_token');
        Cache::forget('rca_refresh_token');
    }
    private function client()
    {
        return Http::withHeaders([
            'Token'  => $this->getToken(),
            'Accept' => 'application/json',
        ]);
    }

    public function products()
    {
        $response = $this->client()->get($this->baseUrl . '/product');

        if ($response->status() === 403) {
            $this->refreshToken();

            $response = $this->client()->get($this->baseUrl . '/product');
        }

        if ($response->failed()) {
            throw new Exception(
                $response->json('message') ?? 'Failed to fetch products',
                $response->status()
            );
        }

        return $response->json();
    }
    public function company(string $taxId)
    {
        $response = $this->client()->get($this->baseUrl . '/company/' . $taxId);

        if ($response->status() === 401) {
            $this->refreshToken();

            $response = $this->client()->get($this->baseUrl . '/company/' . $taxId);
        }

        if ($response->failed()) {
            throw new Exception(
                $response->json('message') ?? 'Failed to fetch company',
                $response->status()
            );
        }

        return $response->json();
    }

    public function vehicle(?string $licensePlate, ?string $vin)
    {
        $query = array_filter([
            'licensePlate' => $licensePlate,
            'vin' => $vin,
        ]);

        $response = $this->client()->get(
            $this->baseUrl . '/vehicle',
            $query
        );

        if ($response->status() === 401) {
            $this->refreshToken();
            $response = $this->client()->get($this->baseUrl . '/vehicle', $query);
        }

        if ($response->failed()) {
            throw new \Exception(
                $response->json('message') ?? 'Failed to fetch vehicle',
                $response->status()
            );
        }

        return $response->json();
    }
    private function getWithRetry(string $uri)
    {
        $response = $this->client()->get($this->baseUrl . $uri);

        if ($response->status() === 401) {
            $this->refreshToken();
            $response = $this->client()->get($this->baseUrl . $uri);
        }

        if ($response->failed()) {
            throw new \Exception(
                $response->json('message') ?? 'Request failed',
                $response->status()
            );
        }

        return $response->json();
    }
    public function countries()
    {
        return $this->getWithRetry('/nomenclature/country');
    }
    public function counties()
    {
        return $this->getWithRetry('/nomenclature/county');
    }
    public function localities(string $countyCode)
    {
        return $this->getWithRetry('/nomenclature/locality/' . $countyCode);
    }

    public function offer(array $payload): array
    {
        $response = Http::withHeaders([
            'Token' => $this->getToken(),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->timeout(10)
            ->post($this->baseUrl . '/offer', $payload);

        if ($response->status() === 403) {
            $this->refreshToken();

            $response = Http::withHeaders([
                'Token' => $this->getToken(),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->timeout(10)
                ->post($this->baseUrl . '/offer', $payload);
        }

        if ($response->failed()) {
            throw new \Exception(
                $response->json('message') ?? 'Failed to get offer',
                $response->status()
            );
        }

        return $response->json();
    }

    /**
     * Send multiple offers concurrently
     */
    public function offerMultiple(array $payload, array $providersData): array
    {
        $responses = Http::pool(function ($pool) use ($payload, $providersData) {
            $requests = [];
            foreach ($providersData as $provider) {
                $businessName = $provider['insurer'] ?? null;
                if (!$businessName) continue;

                $offerPayload = $payload;
                $offerPayload['provider']['organization']['businessName'] = $businessName;

                $requests[$businessName] = $pool->withHeaders([
                    'Token' => $this->getToken(),
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])->timeout(20)->post($this->baseUrl . '/offer', $offerPayload);
            }

            return $requests;
        });

        $offers = [];
        foreach ($responses as $businessName => $response) {
            try {
                if ($response instanceof \Illuminate\Http\Client\ConnectionException) {
                    $offers[] = [
                        'provider' => $businessName,
                        'error' => true,
                        'message' => $response->getMessage()
                    ];
                    continue;
                }

                if ($response->status() === 403) {
                    $this->refreshToken();
                    $offers[] = [
                        'provider' => $businessName,
                        'offer' => $this->offer([
                            'provider' => ['organization' => ['businessName' => $businessName]],
                            'product' => $payload['product'] ?? []
                        ])
                    ];
                } else {
                    $offers[] = [
                        'provider' => $businessName,
                        'offer' => $response->json()
                    ];
                }
            } catch (\Exception $e) {
                $offers[] = [
                    'provider' => $businessName,
                    'error' => true,
                    'message' => $e->getMessage()
                ];
            }
        }

        return $offers;
    }

    public function downloadOffer(int $id, bool $withDirectCompensation = false)
    {
        $response = Http::withHeaders([
            'Token'  => $this->getToken(),
            'Accept' => 'application/json',
        ])->get(
            $this->baseUrl . "/offer/{$id}",
            [
                'withDirectCompensation' => $withDirectCompensation ? 1 : 0,
            ]
        );

        if ($response->status() === 403) {
            $this->refreshToken();

            $response = Http::withHeaders([
                'Token'  => $this->getToken(),
                'Accept' => 'application/json',
            ])->get(
                $this->baseUrl . "/offer/{$id}",
                [
                    'withDirectCompensation' => $withDirectCompensation ? 1 : 0,
                ]
            );
        }

        if ($response->failed()) {
            throw new \Exception(
                $response->json('message') ?? 'Failed to download offer',
                $response->status()
            );
        }

        return $response;
    }

    public function policy(string $series, string $number)
    {
        $response = Http::withHeaders([
            'Token'  => $this->getToken(),
            'Accept' => 'application/pdf',
        ])->get(
            $this->baseUrl . '/policy',
            [
                'series' => $series,
                'number' => $number,
            ]
        );

        if ($response->status() === 403) {
            $this->refreshToken();

            $response = Http::withHeaders([
                'Token'  => $this->getToken(),
                'Accept' => 'application/pdf',
            ])->get(
                $this->baseUrl . '/policy',
                [
                    'series' => $series,
                    'number' => $number,
                ]
            );
        }

        if ($response->failed()) {
            throw new \Exception(
                $response->json('message') ?? 'Failed to download policy',
                $response->status()
            );
        }

        return $response;
    }

    public function createPolicy(array $payload): array
    {
        $response = Http::withHeaders([
            'Token'        => $this->getToken(),
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/policy', $payload);

        if ($response->status() === 401 || $response->status() === 403) {
            $this->refreshToken();

            $response = Http::withHeaders([
                'Token'        => $this->getToken(),
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/policy', $payload);
        }

        if ($response->failed()) {
            throw new \Exception(
                'External API Error: ' . $response->body(),
                $response->status()
            );
        }

        return $response->json();
    }

    public function downloadPolicyById(int $id)
    {
        $response = Http::withHeaders([
            'Token'  => $this->getToken(),
            'Accept' => 'application/json',
        ])->get($this->baseUrl . "/policy/{$id}");

        if ($response->status() === 401 || $response->status() === 403) {
            $this->refreshToken();

            $response = Http::withHeaders([
                'Token'  => $this->getToken(),
                'Accept' => 'application/json',
            ])->get($this->baseUrl . "/policy/{$id}");
        }

        if ($response->failed()) {
            throw new \Exception(
                $response->json('message') ?? 'Failed to download policy',
                $response->status()
            );
        }

        return $response;
    }

}
