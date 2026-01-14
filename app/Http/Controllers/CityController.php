<?php

namespace App\Http\Controllers;

use App\Models\City;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CityController extends Controller
{
    public function create(Request $request): View
    {
        $returnUrl = (string) $request->query('return', url()->previous());

        return view('cities.create', compact('returnUrl'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255|unique:cities,name',
            'return' => 'nullable|url',
        ]);

        try {
            City::query()->create(['name' => $validated['name']]);

            Cache::forget('form_cities');

            $redirectTo = $validated['return'] ?? route('companies.create');

            return redirect($redirectTo)
                ->with('success', __('messages.common.success'));
        } catch (Exception $e) {
            Log::error('City store error: ' . $e->getMessage(), [
                'data' => $request->only(['name']),
            ]);

            return back()
                ->withInput()
                ->with('error', __('messages.common.error'));
        }
    }
}
