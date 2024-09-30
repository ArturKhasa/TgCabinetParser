<?php

namespace App\Http\Controllers;

use App\Models\Cabinet;
use App\Services\ParseAdStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookHandleController extends Controller
{
    public function parseAdStats(Request $request): JsonResponse
    {
//        ParseAdStatsService::parse($request->get('cabinetIds'));
        $cabinets = Cabinet::query()->where('id', 37)->get();
        foreach ($cabinets as $cabinet) {
            $service = new ParseAdStatsService($cabinet);
            $service->handle();
        }

        return response()->json();
    }
}