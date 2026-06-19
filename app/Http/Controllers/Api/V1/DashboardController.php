<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $service) {}

    public function overview(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->service->overview()]);
    }
}
