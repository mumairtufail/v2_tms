<?php

namespace App\Services;

use App\Models\ActivityLogs;
use App\Models\Company;
use App\Models\Order;
use Illuminate\Support\Collection;
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

    public function forOrder(Company $company, Order $order, int $limit = 50): Collection
    {
        return ActivityLogs::with(['user', 'customer'])
            ->forCompany($company->id)
            ->forOrder($order->id)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function (ActivityLogs $log) {
                return [
                    'id' => $log->id,
                    'actor' => $log->actor_name,
                    'description' => $log->description,
                    'successful' => (bool) $log->is_successful,
                    'created_at' => $log->created_at?->toIso8601String(),
                    'created_at_human' => $log->created_at?->diffForHumans(),
                    'created_at_label' => $log->created_at?->format('M j, Y g:i A'),
                ];
            })
            ->values();
    }
}
