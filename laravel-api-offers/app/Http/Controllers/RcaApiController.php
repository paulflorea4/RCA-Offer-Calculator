<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use App\Models\Quote;
use App\Services\RcaApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RcaApiController extends Controller
{
    public function company(RcaApiService $rca, string $taxId):JsonResponse
    {
        try {
            $response = $rca->company($taxId);

            return response()->json(
                 $response
            );
        }catch (\Exception $e) {
            return $this->error($e);
        }
    }
    public function vehicle(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'licensePlate' => 'required_without:vin|string',
                'vin' => 'required_without:licensePlate|string',
            ]);

            $vehicle = app(RcaApiService::class)->vehicle(
                $validated['licensePlate'] ?? null,
                $validated['vin'] ?? null
            );

            return response()->json([
                'data' => $vehicle
            ]);
        }catch (\Exception $e) {
            return $this->error($e);
        }
    }
    public function countries(RcaApiService $rca): JsonResponse
    {
        return response()->json(
            $rca->countries()
        );
    }
    public function counties(RcaApiService $rca): JsonResponse
    {
        return response()->json(
            $rca->counties()
        );
    }
    public function localities(RcaApiService $rca, string $county_code): JsonResponse
    {
        return response()->json(
            $rca->localities($county_code)
        );
    }
    public function product(RcaApiService $rca):JsonResponse {
        try {
            return response()->json(
                $rca->products()
                );
        }catch (\Exception $e) {
            return $this->error($e);
        }
    }
    public function download(int $offerId, Request $request, RcaApiService $rca)
    {
        try {

            $apiResponse = $rca->downloadOffer(
                $offerId,
                filter_var(
                    $request->query('withDirectCompensation', false),
                    FILTER_VALIDATE_BOOL
                )
            );

            $pdf = $this->extractPdfFromApiResponse($apiResponse->json());

            return $this->pdfResponse($pdf);

        } catch (\Throwable $e) {

            return $this->error($e);
        }
    }

    public function policy(Request $request, RcaApiService $rca)
    {
        $validated = $request->validate([
            'series' => 'required|string',
            'number' => 'required|string',
        ]);

        try {
            $apiResponse = $rca->policy(
                $validated['series'],
                $validated['number']
            );

            $pdf = $this->extractPdfFromApiResponse($apiResponse->json());

            return $this->pdfResponse($pdf);

        } catch (\Throwable $e) {
            return $this->error($e);
        }
    }
    private function extractPdfFromApiResponse(array $response): array
    {
        if (empty($response['data']['files'][0]['content'])) {
            throw new \Exception(
                $response['message'] ?? 'No PDF found in response'
            );
        }

        $binary = base64_decode($response['data']['files'][0]['content'], true);

        if ($binary === false) {
            throw new \Exception('PDF decode failed');
        }

        return [
            'name' => $response['data']['files'][0]['name'] ?? 'document.pdf',
            'binary' => $binary,
        ];
    }
    private function pdfResponse(array $pdf)
    {
        return response($pdf['binary'], 200)
            ->header('Content-Type', 'application/pdf')
            ->header(
                'Content-Disposition',
                'inline; filename="' . $pdf['name'] . '"'
            );
    }
    public function downloadById(int $id, RcaApiService $rca)
    {
        try {
            $apiResponse = $rca->downloadPolicyById($id);

            $pdf = $this->extractPdfFromApiResponse($apiResponse->json());

            return $this->pdfResponse($pdf);

        } catch (\Throwable $e) {
            return $this->error($e);
        }
    }
    private function error(\Throwable $e)
    {
        $status = $e->getCode();

        if ($status < 100 || $status > 599) {
            $status = 500;
        }

        return response()->json([
            'message' => $e->getMessage(),
        ], $status);
    }

    public function showRcaForm(RcaApiService $rca)
    {
        try {
            $counties = $rca->counties()['data'] ?? [];


            return view('rca', compact( 'counties'));

        } catch (\Exception $e) {
            return view('rca', [
                'counties' => [],
                'localities' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    public function offers(Request $request, RcaApiService $rca)
    {
        try {

            $providersResponse = $rca->products();
            $providersData = $providersResponse['data'] ?? [];

             $payload = $this->buildRcaPayload($request->all(),$rca);

             $apiOffers = $rca->offerMultiple($payload, $providersData);

            $person = $request->input('Person', []);
            $vehicle = $request->input('Vehicle', []);

            $quoteData = [

                'firstName' => $person['firstName'] ?? null,
                'lastName' => $person['lastName'] ?? null,
                'businessName' => $person['businessName'] ?? null,
                'cnp/cui' => $person['individualTaxId'] ?? $person['companyTaxId'] ?? null,
                'drivingLicense' => $person['drivingLicense']['issueDate'] ?? null,
                'idType' => $person['identification']['idType'] ?? null,
                'idNumber' => $person['identification']['idNumber'] ?? null,
                'email' => $person['email'] ?? null,
                'mobileNumber' => $person['mobileNumber'] ?? null,

                'county' => $person['address']['countyId'] ?? null,
                'city' => $person['address']['localityName'] ?? null,
                'street' => $person['address']['street'] ?? null,
                'houseNumber' => $person['address']['houseNumber'] ?? null,
                'building' => $person['address']['building'] ?? null,
                'staircase' => $person['address']['staircase'] ?? null,
                'floor' => $person['address']['floor'] ?? null,
                'apartment' => $person['address']['apartment'] ?? null,
                'postcode' => $person['address']['postcode'] ?? null,

                'licensePlate' => $vehicle['licensePlate'] ?? null,
                'vin' => $vehicle['vin'] ?? null,

                'driverFirstName' => $person['driverFirstName'] ?? null,
                'driverLastName' => $person['driverLastName'] ?? null,
                'driverCNP' => $person['driverTaxId'] ?? null,
                'driverIdNumber' => $person['driverIdentification']['idNumber'] ?? null,
                'driverMobilePhone' => $person['mobileNumber'] ?? null,
            ];

            Quote::create($quoteData);

            return collect($apiOffers)
                ->filter(fn ($item) =>
                    isset($item['offer']['error']) &&
                    $item['offer']['error'] === false
                )
                ->flatMap(function ($item) {

                    $providerName = $item['offer']['data']['provider']['organization']['businessName'];

                    return collect($item['offer']['data']['offers'])
                        ->map(function ($offer) use ($providerName) {

                            return [
                                'provider' => $providerName,
                                'offerId' => $offer['offerId'],
                                'price' => $offer['premiumAmount'],
                                'currency' => $offer['currency'],
                                'startDate' => $offer['startDate'],
                                'endDate' => $offer['endDate'],
                                'bonusMalus' => $offer['bonusMalusClass'],
                                'expiry' => $offer['offerExpiryDate'],
                                'pid' => $offer['pid'] ?? null,
                            ];
                        });

                })
                ->values()
                ->toArray();
        } catch (\Exception $e) {

            return $this->error($e);
        }
    }

    public function showOffers(Request $request, RcaApiService $rca)
    {
        $offers = $this->offers($request, $rca);

        return view('offers', compact('offers'));
    }

    public function issuePolicy(Request $request, RcaApiService $rca)
    {
        $payload = [

            'offerId' => $request->offerId,

            'includeDirectCompensation' => false,

            'payment' => [
                'method' => 'pos',
                'currency' => 'RON',
                'amount' => $request->amount,
                'date' => now()->format('Y-m-d'),
                'documentNumber' => 'DOC-'.time(),
            ]
        ];


        $policy = $rca->createPolicy($payload);

        return view('policy', compact('policy'));
    }

    private function buildRcaPayload(array $form,RcaApiService $rca): array
    {
        $person = $form['Person'] ?? [];
        $vehicleForm = $form['Vehicle'] ?? [];

        $isCompany = ($person['type'] ?? 'individual') === 'company';

        $policyholder = $this->buildPolicyholder(
            $person,
            $isCompany,
            $rca
        );

        return [
            'provider' => [
                'organization' => [
                    'businessName' => ''
                ],
                'authentication' => [
                    'account' => '',
                    'password' => '',
                    'code' => ''
                ]
            ],
            'product' => [
                'motor' => $this->buildMotor(),
                'policyholder' => $policyholder,
                'vehicle' => $this->buildVehicle(
                    $vehicleForm,
                    $person,
                    $policyholder,
                    $isCompany,
                    $rca
                )
            ]
        ];
    }
    private function buildPolicyholder(array $person, bool $isCompany,RcaApiService $rca): array
    {
        if ($isCompany) {
            return $this->buildCompany($person,$rca);
        }

        $address = $this->buildAddress($person['address']);

        return [
                'firstName' => $person['firstName'] ?? null,
                'lastName' => $person['lastName'] ?? null,
                'taxId' => $person['individualTaxId'] ?? null,
                'isForeignPerson'=> false,
                'email' => $person['email'] ?? null,
                'mobileNumber' => $person['mobileNumber'] ?? null,
                'identification' => [
                    'idType' => $person['identification']['idType'] ?? 'CI',
                    'idNumber' => $person['identification']['idNumber'] ?? null
                ],
                'drivingLicense' => $person['drivingLicense'] ?? null,
                'address' => $address
            ];
    }
    private function buildCompany(array $person,RcaApiService $rca): array{
        $companyTaxId = $person['companyTaxId'] ?? null;

        $resultResponse = $this->company($rca, $companyTaxId);
        $result = $resultResponse->getData(true);

        if (!empty($result['error'])) {
            throw new \Exception($result['message'] ?? 'Unknown error');
        }

        if($result['error'])
            throw new \Exception($result['message']);

        return [
            'businessName' => $result['data']['businessName'] ?? null,
            'caenCode' => $result['caenCode'] ?? null,
            'taxId' => $result['taxId'] ?? null,
            'email' => $result['email'] ?? null,
            'mobileNumber' => $result['mobileNumber'] ?? null,
            'address' => $result['address'] ?? null,
        ];
    }
    private function buildAddress(array $address): array
    {
        return [
            'country' => 'RO',
            'county' => $address['countyCode'] ?? '',
            'city' => $address['localityName'] ?? '',
            'cityCode' => $address['localitySiruta'] ?? '',
            'street' => $address['street'] ?? '',
            'houseNumber' => $address['houseNumber'] ?? '',
            'building' => $address['building'] ?? '',
            'staircase' => $address['staircase'] ?? '',
            'floor' => $address['floor'] ?? "0",
            'apartment' => $address['apartment'] ?? '',
            'postcode' => $address['postcode'] ?? '',
        ];
    }
    private function buildMotor(): array
    {
        $date = now()->addDay()->toDateString();

        return [
            'startDate' =>  $date,
            'termTime' =>  12,
        ];
    }
    private function buildVehicle(array $vehicle, array $person,array $policyholder, bool $isCompany,RcaApiService $rca): array
    {
        $foundVehicle = $rca->vehicle($vehicle['licensePlate'], null);

        if($foundVehicle['error'])
            throw new \Exception($foundVehicle['message']);

        $owner = $isCompany
            ? [
                'businessName' => $policyholder['businessName'],
                'taxId' => $policyholder['taxId'],
            ]
            : $policyholder;

        $driver = $isCompany ?
            [
                'firstName' => $person['driverFirstName'] ?? null,
                'lastName' => $person['driverLastName'] ?? null,
                'taxId' => $person['driverTaxId'] ?? '',
                'identification' => ['idNumber' => $person['driverIdentification']['idNumber']] ?? null,
            ]:
            [
            'firstName' => $policyholder['firstName'] ?? null,
            'lastName' => $policyholder['lastName'] ?? null,
            'taxId' => $policyholder['taxId'] ?? '',
            'identification' => ['idNumber' => $policyholder['identification']['idNumber']] ?? null,
            ];

        return [
            'owner' => $owner,
            'driver' => [$driver],
            'licensePlate' => $foundVehicle['data']['licensePlate'] ?? '',
            'registrationType' => $foundVehicle['data']['registrationType'] ?? null,
            'vin' => $vehicle['vin'] ?? '',
            'vehicleType' => $foundVehicle['data']['vehicleType'] ?? null,
            'brand' => $foundVehicle['data']['brand'] ?? '',
            'model' => $foundVehicle['data']['model'] ?? '',
            'yearOfConstruction' => $foundVehicle['data']['yearOfConstruction'] ?? 1900,
            'engineDisplacement' => $foundVehicle['data']['engineDisplacement'] ?? 0,
            'enginePower' => $foundVehicle['data']['enginePower'] ?? 0,
            'totalWeight' => $foundVehicle['data']['totalWeight']+1 ?? 0,
            'seats' => $foundVehicle['data']['seats'] ?? 0,
            'fuelType' => $foundVehicle['data']['fuelType'] ?? null,
            'firstRegistration' => $foundVehicle['data']['firstRegistration'] ?? null,
            'currentMileage' => $foundVehicle['data']['currentMileage'] ?? 1,
            'usageType'=> 'personal',
            'identification' => [
                'idNumber' => 'H123456'
            ],
        ];
    }
}
