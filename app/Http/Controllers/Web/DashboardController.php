<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $service) {}

    public function index(Request $request)
    {
        $overview = $this->service->overview();
        return view('dashboard.index', compact('overview'));
    }
}
