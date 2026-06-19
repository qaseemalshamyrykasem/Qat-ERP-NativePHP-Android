<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\Agent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $q = $request->get('search', '');
        $limit = $request->get('per_page', 5);
        
        return response()->json([
            'success' => true,
            'customers' => $q ? Customer::where('name', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%")
                ->limit($limit)->get() : [],
            'products' => $q ? Product::where('name', 'like', "%{$q}%")
                ->limit($limit)->get() : [],
            'suppliers' => $q ? Supplier::where('name', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%")
                ->limit($limit)->get() : [],
            'agents' => $q ? Agent::where('name', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%")
                ->limit($limit)->get() : [],
            'sales' => $q ? Sale::where('invoice_no', 'like', "%{$q}%")
                ->limit($limit)->latest()->get() : [],
        ]);
    }
}