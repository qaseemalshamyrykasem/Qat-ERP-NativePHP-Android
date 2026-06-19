<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreSettingRequest;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Setting::class);
        return Setting::orderBy('setting_key')->paginate(min(100, $request->integer('per_page', 50)));
    }

    public function show(string $key): JsonResponse
    {
        $value = Setting::get($key);
        return response()->json(['success' => true, 'key' => $key, 'value' => $value]);
    }

    public function update(StoreSettingRequest $request): JsonResponse
    {
        $this->authorize('create', Setting::class);
        Setting::set($request->input('setting_key'), $request->input('setting_value'));
        return response()->json(['success' => true, 'message' => 'تم حفظ الإعداد']);
    }
}
