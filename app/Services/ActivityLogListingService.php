<?php

namespace App\Services;

use App\Models\ActivityLogs;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ActivityLogListingService
{
    public function paginate(Request $request, ?int $companyId = null): LengthAwarePaginator
    {
        $query = ActivityLogs::with(['user', 'customer', 'company'])
            ->when($companyId, fn ($q) => $q->forCompany($companyId));

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('action', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('data->description', 'LIKE', "%{$searchTerm}%")
                    ->orWhereHas('user', function ($q2) use ($searchTerm) {
                        $q2->where('name', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('f_name', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('l_name', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('email', 'LIKE', "%{$searchTerm}%");
                    })
                    ->orWhereHas('customer', function ($q2) use ($searchTerm) {
                        $q2->where('name', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('customer_email', 'LIKE', "%{$searchTerm}%");
                    })
                    ->orWhereHas('company', function ($q2) use ($searchTerm) {
                        $q2->where('name', 'LIKE', "%{$searchTerm}%");
                    });
            });
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('status')) {
            $query->where('is_successful', $request->status === 'success');
        }

        return $query->orderByDesc('created_at')->paginate(20)->withQueryString();
    }
}
