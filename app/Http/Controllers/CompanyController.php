<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\RolePermissionService;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    protected $rolePermissionService;

    public function __construct(RolePermissionService $rolePermissionService)
    {
        $this->rolePermissionService = $rolePermissionService;
    }

    /**
     * Generate a unique 3-char shortcode suggestion from a company name.
     */
    public function generateShortcode(Request $request)
    {
        $name = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $request->input('name', '')));

        if (strlen($name) < 1) {
            return response()->json(['shortcode' => '']);
        }

        // Build candidate list: first 3 chars, then try picking chars at different positions
        $candidates = [];
        $len = strlen($name);

        // Primary: first 3 chars (padded with digits if short)
        $base = str_pad(substr($name, 0, 3), 3, '0');
        $candidates[] = $base;

        // Variations: replace last char with 1,2,3...
        for ($i = 1; $i <= 9; $i++) {
            $candidates[] = substr($base, 0, 2) . $i;
        }
        // Variations: middle char replaced
        for ($i = 1; $i <= 9; $i++) {
            $candidates[] = substr($base, 0, 1) . $i . substr($base, 2, 1);
        }
        // Variations: pick chars spread across the name
        for ($offset = 1; $offset < $len - 1; $offset++) {
            $spread = $name[0] . $name[min($offset, $len - 1)] . $name[min($offset * 2, $len - 1)];
            $candidates[] = str_pad(substr($spread, 0, 3), 3, '0');
        }

        $excludeId = $request->input('exclude_id'); // current company id on edit

        foreach ($candidates as $candidate) {
            $candidate = strtoupper(substr($candidate, 0, 3));
            $exists = Company::where('shortcode', $candidate)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists();
            if (!$exists) {
                return response()->json(['shortcode' => $candidate]);
            }
        }

        // Last resort: random alphanumeric
        do {
            $random = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 3));
            $exists = Company::where('shortcode', $random)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists();
        } while ($exists);

        return response()->json(['shortcode' => $random]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Company::query();

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('address', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('phone', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $companies = $query->where('is_deleted', false)
            ->withCount('users')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('v2.admin.companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('v2.admin.companies.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:companies,name',
            'shortcode' => 'nullable|string|max:3|regex:/^[A-Za-z0-9]+$/|unique:companies,shortcode',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'Please check the form for errors.')
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $company = Company::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'shortcode' => $request->shortcode ? strtoupper($request->shortcode) : null,
                'phone' => $request->phone,
                'address' => $request->address,
                'is_active' => $request->has('is_active') ? true : false,
                'is_deleted' => false
            ]);
            
            // Create driver role for this company
            $this->rolePermissionService->createRole('driver', $company->id);

            return redirect()->route('admin.companies.index')
                ->with('success', 'Company created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create company. Please try again.')
                ->withInput();
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        if ($company->is_deleted) {
            abort(404);
        }

        return view('v2.admin.companies.form', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        if ($company->is_deleted) {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:companies,name,' . $company->id,
            'shortcode' => 'nullable|string|max:3|regex:/^[A-Za-z0-9]+$/|unique:companies,shortcode,' . $company->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'Please check the form for errors.')
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $company->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'shortcode' => $request->shortcode ? strtoupper($request->shortcode) : null,
                'phone' => $request->phone,
                'address' => $request->address,
                'is_active' => $request->has('is_active') ? true : false
            ]);

            return redirect()->route('admin.companies.index')
                ->with('success', 'Company updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update company. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $company = Company::findOrFail($id);

            // Soft delete by setting is_deleted to true
            $company->update(['is_deleted' => true]);

            return redirect()->route('admin.companies.index')
                ->with('success', 'Company deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete company. Please try again.');
        }
    }
}
