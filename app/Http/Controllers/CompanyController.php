<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Company;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function create(Request $request): View
    {
        $cities = City::query()->orderBy('name')->get(['id', 'name']);
        $returnUrl = (string) $request->query('return', url()->previous());

        return view('companies.create', compact('cities', 'returnUrl'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255|unique:companies,name',
            'city_id' => 'required|integer|exists:cities,id',
            'return'  => 'nullable|url',
        ]);

        try {
            Company::query()->create([
                'name'    => $validated['name'],
                'city_id' => $validated['city_id'],
            ]);

            Cache::forget('form_companies');

            $redirectTo = $validated['return'] ?? route('books.create');

            return redirect($redirectTo)
                ->with('success', __('messages.common.success'));
        } catch (Exception $e) {
            Log::error('Company store error: ' . $e->getMessage(), [
                'data' => $request->only(['name', 'city_id']),
            ]);

            return back()
                ->withInput()
                ->with('error', __('messages.common.error'));
        }
    }
}
