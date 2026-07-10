<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Order;
use App\Services\Portal\CustomerPortalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected CustomerPortalService $portalService
    ) {}

    public function index(Request $request, Company $company): View
    {
        $customer = Auth::guard('customer')->user();

        $orders = $this->portalService->getOrders($customer, [
            'search' => $request->search,
            'status' => $request->status,
        ]);

        return view('portal.orders.index', compact('company', 'customer', 'orders'));
    }

    public function show(Company $company, Order $order): View
    {
        $customer = Auth::guard('customer')->user();

        $this->portalService->assertOrderBelongsToCustomer($order, $customer);

        $order = $this->portalService->getOrderDetail($customer, $order->id);

        return view('portal.orders.show', compact('company', 'customer', 'order'));
    }
}
