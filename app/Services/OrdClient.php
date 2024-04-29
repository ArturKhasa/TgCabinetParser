<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\Cabinet;
use App\Models\Clickise\Finance\Contract;
use App\Models\Ord\OrdContract;
use App\Models\Ord\OrdInvoice;
use App\Models\Ord\OrdOrganization;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrdClient
{

    const API_URl = 'https://ord.yandex.ru/api/v4/';

    private function getHeaders(): array
    {
        return [
            'Authorization' => "Bearer " . config("ord.oauth_token")
        ];
    }

    public function organization(Contract $contract, Cabinet $cabinet): ?string
    {
        $url = self::API_URl . "organization";

        $data = [
            "id"          => $contract->id . "-" . $cabinet->id,
            "type"        => $contract->type->getIdentityForOrd(),
            "isOrs"       => true,
            "isRr"        => false,
            "inn"         => $contract->inn,
            "name"        => $contract->legal_name,
            "mobilePhone" => $contract->phone,
            "platforms"   => [
                [
                    "type"    => config("ord.platform_type"),
                    "isOwned" => false,
                    "name"    => config("ord.platform_name"),
                    "url"     => config("ord.platform_url")
                ]
            ],
            "rsUrl"       => config("ord.rs_url")
        ];

        $jsonResponse = Http::withHeaders($this->getHeaders())
            ->post($url, $data)
            ->throw()
            ->json();

        if (!isset($jsonResponse["erir_id"]))
            return null;
        else
            return $jsonResponse["erir_id"];

    }

    public function contract(
        OrdOrganization $ordOrganization,
                        $amount,
                        $date,
                        $contractNumber,
    )
    {
        $url = self::API_URl . "contract";

        $id = $ordOrganization->clientId();
        $data = [
            "id"           => $ordOrganization->contract->id,
            "type"         => "contract",
            "clientId"     => $ordOrganization->id,
            "contractorId" => $ordOrganization->id,
            "isRegReport"  => true,
            "actionType"   => "distribution",
            "subjectType"  => "org-distribution",
            "number"       => "№" . $contractNumber,
            "date"         => $date,
            "amount"       => $amount,
            "isVat"        => true,
        ];

        $jsonResponse = Http::withHeaders($this->getHeaders())
            ->post($url, $data)
            ->throw()
            ->json();

        if (!isset($jsonResponse["erir_id"]))
            return null;
        else
            return $jsonResponse["erir_id"];
    }

    public function creative(Ad $ad, OrdContract $ordContract)
    {
        $url = self::API_URl . "creative";

        $adId = $ad->id;
        if (!$adId)
            $adId = $ad->getNextId();

        $data = [
            "id"          => $adId,
            "contractIds" => [
                $ordContract->number
            ],
            "description" => $ad->title,
            "type"        => "cpm",
            "form"        => "text-block",
            "urls"        => [
                $ad->getFullPromoteUrl()
            ],
            "textData"    => [
                $ad->text
            ],
            "isSocial"    => false,
            "isNative"    => false
        ];
//
//        echo "<br><br><br>";
//        print_r($data);

//        Log::channel("ord")->info(print_r($data, true));
        $jsonResponse = Http::withHeaders($this->getHeaders())
            ->post($url, $data)
            ->json();

//        Log::channel("ord")->info("post request");
//        Log::channel("ord")->info(print_r($jsonResponse, true));

        if (!isset($jsonResponse["erir_id"]))
            return null;
        else
            return [
                "erir_id"    => $jsonResponse["erir_id"],
                "erir_token" => $jsonResponse["token"]
            ];
    }

    public function getCreative(Ad $ad)
    {
        $url = self::API_URl . "creative?object_id=" . $ad->id;
        return Http::withHeaders($this->getHeaders())
            ->get($url)
            ->throw()
            ->json();
    }

    /**
     * @throws \Exception
     */
    public function invoice(OrdContract $ordContract, OrdInvoice $invoice): bool
    {
        $url = self::API_URl . "invoice";

        $data = [
            "id"             => $invoice->id,
            "contractId"     => $invoice->ordContract->number,
            "clientRole"     => $invoice->client_role,
            "contractorRole" => $invoice->contractor_role,
            "date"           => $invoice->date,
            "startDate"      => $invoice->start_date,
            "endDate"        => $invoice->end_date,
            "amount"         => $invoice->amount,
            "isVat"          => $invoice->is_vat,
            "items"          => [
                [
                    "contractId" => $invoice->ordContract->number,
                    "amount"     => $invoice->ordContract->amount,
                    "isVat"      => false
                ]
            ]
        ];

        Log::channel("ord")->info($data);
        $jsonResponse = Http::withHeaders($this->getHeaders())
            ->post($url, $data)
            ->json();

        Log::channel("ord")->info("post request");
        Log::channel("ord")->info(print_r($jsonResponse, true));

        if (!isset($jsonResponse["erir_ids"]))
            return throw new \Exception("Erir id not found");
        else {
            $erir_id = $jsonResponse["erir_ids"][0];
            $invoice->update(["erir_id" => $erir_id]);
            return true;
        }
    }

    public function invoiceCreatives(OrdInvoice $invoice, $ads): bool
    {
        $url = self::API_URl . "invoice/creatives";

        $creativesData = [];
        foreach ($ads as $ad) {
            $creativesData[] = [
                "invoiceId"   => $invoice->id,
                "contractId"  => $invoice->ordContract->number,
                "creativeIds" => [
                    $ad->id
                ]
            ];
        }

        $data = [
            "creatives" => $creativesData
        ];
        Log::channel("ord")->info('invoice/creatives');
//        Log::channel("ord")->info(print_r($data, true));
        $jsonResponse = Http::withHeaders($this->getHeaders())
            ->post($url, $data)
            ->json();

        Log::channel("ord")->info("invoice/creatives post request");
        Log::channel("ord")->info(print_r($jsonResponse, true));

        if (isset($jsonResponse["request_id"]))
            return true;

        return false;
    }

    public function statistics($statistics): bool
    {
        $url = self::API_URl . "statistics";

        $data = [
            "statistics" => $statistics
        ];
        Log::channel("ord")->info('statistics');
//        Log::channel("ord")->info(print_r($data, true));
        $jsonResponse = Http::withHeaders($this->getHeaders())
            ->post($url, $data)
            ->json();

        Log::channel("ord")->info("statistics post request");
        Log::channel("ord")->info(print_r($jsonResponse, true));

        if (isset($jsonResponse["request_id"]))
            return true;

        return false;
    }
}