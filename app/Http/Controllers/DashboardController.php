<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Manifest;
use App\Models\Company;
use App\Models\User;
use App\Models\Customer;
use App\Models\Equipment;
use App\Models\ActivityLogs;
use App\Models\CompanyUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Get current month and previous month for comparison
            $currentMonth = Carbon::now()->startOfMonth();
            $previousMonth = Carbon::now()->subMonth()->startOfMonth();

            // Calculate basic stats with error handling
            $stats = $this->getBasicStats($currentMonth, $previousMonth);

            // Get chart data with error handling
            $chart_data = $this->getChartData();

            // Get order status distribution with error handling
            $order_status_distribution = $this->getOrderStatusDistribution();

            // Get equipment status with error handling
            $equipment_status = $this->getEquipmentStatus();

            // Get customer types distribution with error handling
            $customer_types = $this->getCustomerTypesDistribution();

            // Get recent activities with error handling
            $recent_activities = $this->getRecentActivities();

            // Get recent orders with error handling
            $recent_orders = $this->getRecentOrders();

            return view('index', compact(
                'stats',
                'chart_data',
                'order_status_distribution',
                'equipment_status',
                'customer_types',
                'recent_activities',
                'recent_orders'
            ));
        } catch (\Exception $e) {
            Log::error('Dashboard Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'company_id' => Auth::user()->company_id ?? null
            ]);

            // Return dashboard with safe empty data if there's an error
            return view('index', [
                'stats' => $this->getEmptyStats(),
                'chart_data' => $this->getEmptyChartData(),
                'order_status_distribution' => [],
                'equipment_status' => [],
                'customer_types' => [],
                'recent_activities' => collect(),
                'recent_orders' => collect()
            ]);
        }
    }

    private function getBasicStats($currentMonth, $previousMonth)
    {
        try {
            // Get company context for multi-tenant filtering
            $companyId = $this->getCompanyId();

            // Total orders this month with error handling
            $ordersQuery = Order::where('created_at', '>=', $currentMonth)->where('is_deleted', 0);
            $previousOrdersQuery = Order::whereBetween('created_at', [$previousMonth, $currentMonth])->where('is_deleted', 0);

            if ($companyId) {
                $ordersQuery->where('company_id', $companyId);
                $previousOrdersQuery->where('company_id', $companyId);
            }

            $total_orders = $ordersQuery->count() ?? 0;
            $previous_orders = $previousOrdersQuery->count() ?? 0;

            // Calculate completion rate (orders that are completed vs total)
            $completed_orders = $ordersQuery->where('status', 'completed')->count() ?? 0;
            $orders_completion_rate = $total_orders > 0 ? round(($completed_orders / $total_orders) * 100) : 0;

            // Total revenue this month with error handling
            $revenueQuery = Order::where('created_at', '>=', $currentMonth)->where('is_deleted', 0);
            $previousRevenueQuery = Order::whereBetween('created_at', [$previousMonth, $currentMonth])->where('is_deleted', 0);

            if ($companyId) {
                $revenueQuery->where('company_id', $companyId);
                $previousRevenueQuery->where('company_id', $companyId);
            }

            $total_revenue = $revenueQuery->sum('revenue') ?? 0;
            $previous_revenue = $previousRevenueQuery->sum('revenue') ?? 0;

            // Calculate revenue growth with safe division
            $revenue_growth = $previous_revenue > 0
                ? round((($total_revenue - $previous_revenue) / $previous_revenue) * 100)
                : ($total_revenue > 0 ? 100 : 0);

            // Active manifests with error handling
            $manifestQuery = Manifest::where('draft', 0);
            $previousManifestQuery = Manifest::whereBetween('created_at', [$previousMonth, $currentMonth])->where('draft', 0);

            if ($companyId) {
                $manifestQuery->where('company_id', $companyId);
                $previousManifestQuery->where('company_id', $companyId);
            }

            $active_manifests = $manifestQuery->whereNotIn('status', ['completed', 'cancelled'])->count() ?? 0;
            $previous_manifests = $previousManifestQuery->whereNotIn('status', ['completed', 'cancelled'])->count() ?? 0;

            $manifest_change = $previous_manifests > 0
                ? round((($active_manifests - $previous_manifests) / $previous_manifests) * 100)
                : ($active_manifests > 0 ? 100 : 0);

            // Active companies and users with error handling
            if (Auth::user() && (Auth::user()->is_super_admin ?? false)) {
                $active_companies = Company::where('is_active', 1)->where('is_deleted', 0)->count() ?? 0;
                $total_users = User::where('is_active', 1)->where('is_deleted', 0)->count() ?? 0;
            } else {
                $active_companies = $companyId ? 1 : 0;
                $userQuery = User::where('is_active', 1)->where('is_deleted', 0);
                if ($companyId) {
                    $userQuery->where('company_id', $companyId);
                }
                $total_users = $userQuery->count() ?? 0;
            }

            return [
                'total_orders' => (int) $total_orders,
                'orders_completion_rate' => (int) $orders_completion_rate,
                'total_revenue' => (float) $total_revenue,
                'revenue_growth' => (int) $revenue_growth,
                'active_manifests' => (int) $active_manifests,
                'manifest_change' => (int) $manifest_change,
                'active_companies' => (int) $active_companies,
                'total_users' => (int) $total_users,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting basic stats: ' . $e->getMessage());
            return $this->getEmptyStats();
        }
    }

    private function getOrderStatusDistribution()
    {
        try {
            $companyId = $this->getCompanyId();

            $query = Order::where('is_deleted', 0)->selectRaw('status, COUNT(*) as count');

            if ($companyId) {
                $query->where('company_id', $companyId);
            }

            $result = $query->groupBy('status')->pluck('count', 'status')->toArray();

            // Ensure we have default statuses and safe values
            $defaultStatuses = ['new', 'pending', 'in_transit', 'completed', 'cancelled'];
            $finalResult = [];

            foreach ($defaultStatuses as $status) {
                $count = $result[$status] ?? 0;
                if ($count > 0) {
                    $finalResult[$status] = (int) $count;
                }
            }

            return $finalResult;
        } catch (\Exception $e) {
            Log::error('Error getting order status distribution: ' . $e->getMessage());
            return [];
        }
    }

    private function getEquipmentStatus()
    {
        try {
            $companyId = $this->getCompanyId();

            $query = Equipment::selectRaw('COALESCE(status, "unknown") as status, COUNT(*) as count');

            if ($companyId) {
                $query->where('company_id', $companyId);
            }

            $result = $query->groupBy('status')->pluck('count', 'status')->toArray();

            // Ensure safe values
            $safeResult = [];
            foreach ($result as $status => $count) {
                $safeResult[$status] = (int) $count;
            }

            return $safeResult ?: [];
        } catch (\Exception $e) {
            Log::error('Error getting equipment status: ' . $e->getMessage());
            return [];
        }
    }

    private function getCustomerTypesDistribution()
    {
        try {
            $query = Customer::where('is_deleted', 0)
                ->selectRaw('COALESCE(customer_type, "other") as customer_type, COUNT(*) as count')
                ->groupBy('customer_type');

            $result = $query->pluck('count', 'customer_type')->toArray();

            // Ensure safe values and filter out empty ones
            $safeResult = [];
            foreach ($result as $type => $count) {
                if ($count > 0) {
                    $safeResult[$type] = (int) $count;
                }
            }

            return $safeResult;
        } catch (\Exception $e) {
            Log::error('Error getting customer types distribution: ' . $e->getMessage());
            return [];
        }
    }

    private function getChartData()
    {
        try {
            $companyId = $this->getCompanyId();

            // Get last 6 months revenue data
            $months = [];
            $revenues = [];
            $margins = [];

            for ($i = 5; $i >= 0; $i--) {
                try {
                    $month = Carbon::now()->subMonths($i);
                    $monthStart = $month->copy()->startOfMonth();
                    $monthEnd = $month->copy()->endOfMonth();

                    $revenueQuery = Order::whereBetween('created_at', [$monthStart, $monthEnd])
                        ->where('is_deleted', 0);

                    $marginQuery = Order::whereBetween('created_at', [$monthStart, $monthEnd])
                        ->where('is_deleted', 0);

                    if ($companyId) {
                        $revenueQuery->where('company_id', $companyId);
                        $marginQuery->where('company_id', $companyId);
                    }

                    $monthRevenue = $revenueQuery->sum('revenue') ?? 0;
                    $monthMargin = $marginQuery->sum('margin') ?? 0;

                    $months[] = $month->format('M Y');
                    $revenues[] = (float) $monthRevenue;
                    $margins[] = (float) $monthMargin;
                } catch (\Exception $e) {
                    Log::error('Error processing month data: ' . $e->getMessage());
                    // Add empty data for this month to maintain array consistency
                    $months[] = Carbon::now()->subMonths($i)->format('M Y');
                    $revenues[] = 0;
                    $margins[] = 0;
                }
            }

            return [
                'months' => $months,
                'revenues' => $revenues,
                'margins' => $margins,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting chart data: ' . $e->getMessage());
            return $this->getEmptyChartData();
        }
    }

    private function getRecentActivities()
    {
        try {
            $companyId = $this->getCompanyId();

            $query = ActivityLogs::with(['user' => function ($q) {
                $q->select('id', 'f_name', 'l_name', 'profile_image');
            }]);

            if ($companyId && !(Auth::user() && (Auth::user()->is_super_admin ?? false))) {
                $query->whereHas('user', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                });
            }

            return $query->orderBy('created_at', 'desc')->limit(10)->get();
        } catch (\Exception $e) {
            Log::error('Recent Activities Error: ' . $e->getMessage());
            return collect();
        }
    }

    private function getRecentOrders()
    {
        try {
            $companyId = $this->getCompanyId();

            $query = Order::with(['shipper:id,name', 'consignee:id,name', 'csr:id,f_name,l_name'])
                ->select('id', 'order_number', 'shipper_id', 'consignee_id', 'csr_id', 'revenue', 'status', 'ready_start_date', 'delivery_start_date', 'created_at', 'company_id')
                ->where('is_deleted', 0);

            if ($companyId) {
                $query->where('company_id', $companyId);
            }

            return $query->orderBy('created_at', 'desc')->limit(10)->get();
        } catch (\Exception $e) {
            Log::error('Recent Orders Error: ' . $e->getMessage());
            return collect();
        }
    }

    private function getCompanyId()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return null;
            }

            // If super admin, show all data
            if ($user->is_super_admin ?? false) {
                return null;
            }

            return $user->company_id ?? null;
        } catch (\Exception $e) {
            Log::error('Error getting company ID: ' . $e->getMessage());
            return null;
        }
    }

    private function getEmptyStats()
    {
        return [
            'total_orders' => 0,
            'orders_completion_rate' => 0,
            'total_revenue' => 0,
            'revenue_growth' => 0,
            'active_manifests' => 0,
            'manifest_change' => 0,
            'active_companies' => 0,
            'total_users' => 0,
        ];
    }

    private function getEmptyChartData()
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = Carbon::now()->subMonths($i)->format('M Y');
        }

        return [
            'months' => $months,
            'revenues' => array_fill(0, 6, 0),
            'margins' => array_fill(0, 6, 0),
        ];
    }

    // API endpoints for real-time updates with error handling
    public function getStatsApi()
    {
        try {
            $currentMonth = Carbon::now()->startOfMonth();
            $previousMonth = Carbon::now()->subMonth()->startOfMonth();

            return response()->json($this->getBasicStats($currentMonth, $previousMonth));
        } catch (\Exception $e) {
            Log::error('Stats API Error: ' . $e->getMessage());
            return response()->json($this->getEmptyStats(), 500);
        }
    }

    public function getChartDataApi()
    {
        try {
            return response()->json($this->getChartData());
        } catch (\Exception $e) {
            Log::error('Chart Data API Error: ' . $e->getMessage());
            return response()->json($this->getEmptyChartData(), 500);
        }
    }

    public function getOrderStatusApi()
    {
        try {
            return response()->json($this->getOrderStatusDistribution());
        } catch (\Exception $e) {
            Log::error('Order Status API Error: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    public function getEquipmentStatusApi()
    {
        try {
            return response()->json($this->getEquipmentStatus());
        } catch (\Exception $e) {
            Log::error('Equipment Status API Error: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    public function refreshDashboard()
    {
        try {
            return response()->json([
                'status' => 'success',
                'message' => 'Dashboard data refreshed',
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Refresh Dashboard Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to refresh dashboard',
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }
}
