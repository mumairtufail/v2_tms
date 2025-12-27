@extends('layouts.app')
@section('content')

<div class="container-fluid mt-xl-50 mt-sm-30 mt-15">
	<!-- Row -->
	
	<div class="row">
		<div class="col-xl-12">
			<!-- Stats Cards Row -->
			<div class="hk-row">
				<div class="col-lg-3 col-sm-6">
					<div class="card card-sm">
						<div class="card-body">
							<span class="d-block font-11 font-weight-500 text-dark text-uppercase mb-10">Total Orders</span>
							<div class="d-flex align-items-center justify-content-between position-relative">
								<div>
									<span class="d-block display-5 font-weight-400 text-dark">{{ number_format($stats['total_orders'] ?? 0) }}</span>
									<small class="text-muted">This Month</small>
								</div>
								<div class="position-absolute r-0">
									<span id="pie_chart_1" class="d-flex easy-pie-chart" data-percent="{{ $stats['orders_completion_rate'] ?? 0 }}">
										<span class="percent head-font">{{ $stats['orders_completion_rate'] ?? 0 }}</span>
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="col-lg-3 col-sm-6">
					<div class="card card-sm">
						<div class="card-body">
							<span class="d-block font-11 font-weight-500 text-dark text-uppercase mb-10">Total Revenue</span>
							<div class="d-flex align-items-center justify-content-between position-relative">
								<div>
									<span class="d-block">
										<span class="display-5 font-weight-400 text-dark">${{ number_format($stats['total_revenue'] ?? 0, 0) }}</span>
									</span>
									<small class="text-muted">This Month</small>
								</div>
								<div class="position-absolute r-0">
									@php
										$revenueGrowth = $stats['revenue_growth'] ?? 0;
										$growthPercent = abs($revenueGrowth) > 100 ? 100 : abs($revenueGrowth);
									@endphp
									<span id="pie_chart_2" class="d-flex easy-pie-chart" data-percent="{{ $growthPercent }}">
										<span class="percent head-font">{{ $revenueGrowth > 0 ? '+' : '' }}{{ $revenueGrowth }}</span>
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="col-lg-3 col-sm-6">
					<div class="card card-sm">
						<div class="card-body">
							<span class="d-block font-11 font-weight-500 text-dark text-uppercase mb-10">Active Manifests</span>
							<div class="d-flex align-items-end justify-content-between">
								<div>
									<span class="d-block">
										<span class="display-5 font-weight-400 text-dark">{{ number_format($stats['active_manifests'] ?? 0) }}</span>
									</span>
									<small class="text-muted">In Transit</small>
								</div>
								<div>
									@php $manifestChange = $stats['manifest_change'] ?? 0; @endphp
									<span class="font-12 font-weight-600 {{ $manifestChange >= 0 ? 'text-success' : 'text-danger' }}">
										{{ $manifestChange > 0 ? '+' : '' }}{{ $manifestChange }}%
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="col-lg-3 col-sm-6">
					<div class="card card-sm">
						<div class="card-body">
							<span class="d-block font-11 font-weight-500 text-dark text-uppercase mb-10">
								{{ (Auth::user()->is_super_admin ?? false) ? 'Active Companies' : 'Team Members' }}
							</span>
							<div class="d-flex align-items-end justify-content-between">
								<div>
									<span class="d-block">
										<span class="display-5 font-weight-400 text-dark">{{ number_format($stats['active_companies'] ?? 0) }}</span>
									</span>
									<small class="text-muted">{{ (Auth::user()->is_super_admin ?? false) ? 'Partners' : 'Active' }}</small>
								</div>
								<div>
									<span class="text-info font-12 font-weight-600">{{ number_format($stats['total_users'] ?? 0) }} Users</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<!-- Charts Row -->
			<div class="hk-row">
				<div class="col-lg-4">
					<div class="card">
						<div class="card-header card-header-action">
							<h6>Order Status Distribution</h6>
							<div class="d-flex align-items-center card-action-wrap">
								<div class="d-flex align-items-center card-action-wrap">
									<a href="#" class="inline-block refresh mr-15" onclick="refreshOrderStatusChart()">
										<i class="ion ion-md-refresh"></i>
									</a>
									<a class="inline-block card-close" href="#" data-effect="fadeOut">
										<i class="ion ion-md-close"></i>
									</a>
								</div>
							</div>
						</div>
						<div class="card-body">
							@if(isset($order_status_distribution) && !empty($order_status_distribution) && is_array($order_status_distribution))
								<div class="hk-legend-wrap mb-20">
									@php $colorIndex = 0; $colors = ['primary', 'indigo-light-1', 'indigo-light-2', 'indigo-light-3', 'success', 'warning', 'danger']; @endphp
									@foreach($order_status_distribution as $status => $count)
										<div class="hk-legend">
											<span class="d-10 bg-{{ $colors[$colorIndex % count($colors)] }} rounded-circle d-inline-block"></span>
											<span>{{ ucfirst(str_replace('_', ' ', $status)) }} ({{ $count }})</span>
										</div>
										@php $colorIndex++; @endphp
									@endforeach
								</div>
								<div id="order_status_chart" class="echart" style="height:291px;"></div>
							@else
								<div class="text-center text-muted py-5">
									<i class="ion ion-md-pie font-48 text-light"></i>
									<p class="mt-3">No order data available</p>
								</div>
							@endif
						</div>
					</div>
				</div>
				
				<div class="col-lg-4">
					<div class="card">
						<div class="card-header card-header-action">
							<h6>Equipment Status</h6>
							<div class="d-flex align-items-center card-action-wrap">
								<div class="inline-block dropdown">
									<a class="dropdown-toggle no-caret" data-toggle="dropdown" href="#" aria-expanded="false" role="button">
										<i class="ion ion-ios-more"></i>
									</a>
									<div class="dropdown-menu dropdown-menu-right">
										@if(Route::has('equipment.index'))
											<a class="dropdown-item" href="{{ route('equipment.index') }}" >View All Equipment</a>
										@endif
										@if(Route::has('equipment.create'))
											<a class="dropdown-item" href="{{ route('equipment.create') }}">Add Equipment</a>
										@endif
									</div>
								</div>
							</div>
						</div>
						<div class="card-body">
							@if(isset($equipment_status) && !empty($equipment_status) && is_array($equipment_status))
								<div class="hk-legend-wrap mb-20">
									@foreach($equipment_status as $status => $count)
										@php
											$statusColor = 'primary';
											switch(strtolower($status)) {
												case 'available': $statusColor = 'success'; break;
												case 'in_use': case 'in use': $statusColor = 'warning'; break;
												case 'maintenance': case 'repair': $statusColor = 'danger'; break;
												default: $statusColor = 'primary';
											}
										@endphp
										<div class="hk-legend">
											<span class="d-10 bg-{{ $statusColor }} rounded-circle d-inline-block"></span>
											<span>{{ ucfirst(str_replace('_', ' ', $status)) }} ({{ $count }})</span>
										</div>
									@endforeach
								</div>
								<div id="equipment_chart" class="echart" style="height:291px;"></div>
							@else
								<div class="text-center text-muted py-5">
									<i class="ion ion-md-cog font-48 text-light"></i>
									<p class="mt-3">No equipment data available</p>
								</div>
							@endif
						</div>
					</div>
				</div>
				
				<div class="col-lg-4">
					<div class="card">
						<div class="card-header card-header-action">
							<h6>Recent Activity</h6>
							<div class="d-flex align-items-center card-action-wrap">
								<div class="inline-block dropdown">
									<a class="dropdown-toggle no-caret" data-toggle="dropdown" href="#" aria-expanded="false" role="button">
										<i class="ion ion-ios-more"></i>
									</a>
									<div class="dropdown-menu dropdown-menu-right">
										@if(Route::has('activity-logs.index'))
											<a class="dropdown-item" href="{{ route('activity-logs.index') }}">View All Logs</a>
										@endif
									</div>
								</div>
							</div>
						</div>
						<div class="card-body" style="max-height: 350px; overflow-y: auto;">
							@if(isset($recent_activities) && $recent_activities->count() > 0)
								@foreach($recent_activities as $activity)
									<div class="d-flex align-items-center mb-15">
										<div class="avatar avatar-xs avatar-rounded">
											@if(isset($activity->user) && $activity->user && isset($activity->user->profile_image) && $activity->user->profile_image)
												<img src="{{ asset('storage/' . $activity->user->profile_image) }}" alt="user" class="avatar-img">
											@else
												<img src="{{ asset('dist/img/default-avatar.png') }}" alt="user" class="avatar-img">
											@endif
										</div>
										<div class="ml-15">
											<div class="font-13 font-weight-500 text-dark">
												@if(isset($activity->user) && $activity->user)
													{{ ($activity->user->f_name ?? 'Unknown') . ' ' . ($activity->user->l_name ?? 'User') }}
												@else
													Unknown User
												@endif
											</div>
											<div class="font-12 text-muted">
												{{ $activity->action ?? 'No action recorded' }}
											</div>
											<div class="font-11 text-light">
												@if(isset($activity->created_at))
													{{ $activity->created_at->diffForHumans() }}
												@else
													Unknown time
												@endif
											</div>
										</div>
									</div>
								@endforeach
							@else
								<div class="text-center text-muted py-4">
									<i class="ion ion-md-information-circle font-48 text-light"></i>
									<p class="mt-3">No recent activities</p>
								</div>
							@endif
						</div>
					</div>
				</div>
			</div>
			
			<!-- Revenue Chart and Customer Types -->
			<div class="hk-row">
				<div class="col-lg-8">
					<div class="card card-refresh">
						<div class="refresh-container">
							<div class="loader-pendulums"></div>
						</div>
						<div class="card-header card-header-action">
							<h6>Monthly Revenue Trends</h6>
							<div class="d-flex align-items-center card-action-wrap">
								<a href="#" class="inline-block refresh mr-15" onclick="refreshRevenueChart()">
									<i class="ion ion-md-refresh"></i>
								</a>
								<a href="#" class="inline-block full-screen">
									<i class="ion ion-md-expand"></i>
								</a>
							</div>
						</div>
						<div class="card-body">
							@if(isset($chart_data) && is_array($chart_data) && !empty($chart_data['months']))
								<div class="hk-legend-wrap mb-20">
									<div class="hk-legend">
										<span class="d-10 bg-primary rounded-circle d-inline-block"></span>
										<span>Revenue</span>
									</div>
									<div class="hk-legend">
										<span class="d-10 bg-indigo-light-3 rounded-circle d-inline-block"></span>
										<span>Margin</span>
									</div>
								</div>
								<div id="revenue_chart" class="echart" style="height: 350px;"></div>
							@else
								<div class="text-center text-muted py-5">
									<i class="ion ion-md-trending-up font-48 text-light"></i>
									<p class="mt-3">No revenue data available</p>
								</div>
							@endif
						</div>
					</div>
				</div>
				
				<div class="col-lg-4">
					<div class="card">
						<div class="card-header card-header-action">
							<h6>Customer Types</h6>
							<div class="d-flex align-items-center card-action-wrap">
								<div class="toggle toggle-sm toggle-simple toggle-light toggle-bg-primary">
								</div>
							</div>
						</div>
						<div class="card-body">
							@if(isset($customer_types) && !empty($customer_types) && is_array($customer_types))
								<div id="customer_types_chart" class="" style="height:325px;"></div>
								<div class="hk-legend-wrap mt-20 mb-5">
									@php $colorIndex = 0; $colors = ['primary', 'indigo-light-1', 'indigo-light-2', 'indigo-light-3']; @endphp
									@foreach($customer_types as $type => $count)
										<div class="hk-legend">
											<span class="d-10 bg-{{ $colors[$colorIndex % count($colors)] }} rounded-circle d-inline-block"></span>
											<span>{{ ucfirst($type) }} ({{ $count }})</span>
										</div>
										@php $colorIndex++; @endphp
									@endforeach
								</div>
							@else
								<div class="text-center text-muted py-5">
									<i class="ion ion-md-people font-48 text-light"></i>
									<p class="mt-3">No customer data available</p>
								</div>
							@endif
						</div>
					</div>
				</div>
			</div>

			<!-- Recent Orders Table -->
			<div class="card">
				<div class="card-header card-header-action">
					<h6>Recent Orders</h6>
					<div class="d-flex align-items-center card-action-wrap">
						@if(Route::has('orders.index'))
							<a href="{{ route('orders.index') }}" class="btn btn-primary btn-sm" style="color: #fff !important;">View All Orders</a>
						@endif
					</div>
				</div>
				<div class="card-body pa-0">
					<div class="table-wrap">
						<div class="table-responsive">
							<table class="table table-sm table-hover mb-0">
								<thead>
									<tr>
										<th>Order #</th>
										<th>Shipper</th>
										<th>Consignee</th>
										<th>CSR</th>
										<th>Revenue</th>
										<th>Status</th>
										<th>Ready Date</th>
										<th>Delivery Date</th>
									</tr>
								</thead>
								<tbody>
									@if(isset($recent_orders) && $recent_orders->count() > 0)
										@foreach($recent_orders as $order)
											<tr>
												<td>
													@if(Route::has('orders.show') && isset($order->id))
														<a href="{{ route('orders.show', $order->id) }}" class="text-primary font-weight-500">
															{{ $order->order_number ?? 'N/A' }}
														</a>
													@else
														<span class="font-weight-500">{{ $order->order_number ?? 'N/A' }}</span>
													@endif
												</td>
												<td>{{ isset($order->shipper) && $order->shipper ? $order->shipper->name : 'N/A' }}</td>
												<td>{{ isset($order->consignee) && $order->consignee ? $order->consignee->name : 'N/A' }}</td>
												<td>
													@if(isset($order->csr) && $order->csr)
														{{ ($order->csr->f_name ?? '') }} {{ ($order->csr->l_name ?? '') }}
													@else
														<span class="text-muted">Unassigned</span>
													@endif
												</td>
												<td>
													@if(isset($order->revenue) && $order->revenue)
														<span class="font-weight-500">${{ number_format($order->revenue, 2) }}</span>
													@else
														<span class="text-muted">-</span>
													@endif
												</td>
												<td>
													@php
														$statusColor = 'secondary';
														$orderStatus = $order->status ?? 'unknown';
														switch(strtolower($orderStatus)) {
															case 'completed': $statusColor = 'success'; break;
															case 'in_transit': case 'in transit': $statusColor = 'primary'; break;
															case 'pending': $statusColor = 'warning'; break;
															case 'cancelled': $statusColor = 'danger'; break;
															case 'new': $statusColor = 'info'; break;
															default: $statusColor = 'secondary';
														}
													@endphp
													<span class="badge badge-{{ $statusColor }}">{{ ucfirst(str_replace('_', ' ', $orderStatus)) }}</span>
												</td>
												<td>
													@if(isset($order->ready_start_date) && $order->ready_start_date)
														{{ \Carbon\Carbon::parse($order->ready_start_date)->format('M d, Y') }}
													@else
														<span class="text-muted">-</span>
													@endif
												</td>
												<td>
													@if(isset($order->delivery_start_date) && $order->delivery_start_date)
														{{ \Carbon\Carbon::parse($order->delivery_start_date)->format('M d, Y') }}
													@else
														<span class="text-muted">-</span>
													@endif
												</td>
											</tr>
										@endforeach
									@else
										<tr>
											<td colspan="8" class="text-center text-muted py-4">
												<i class="ion ion-md-document font-48 text-light"></i>
												<p class="mt-3">No recent orders found</p>
											</td>
										</tr>
									@endif
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /Row -->
</div>
<!-- /Container -->

<!-- Chart.js CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

<script>
// Global chart instances
let orderStatusChart = null;
let equipmentChart = null;
let revenueChart = null;
let customerTypesChart = null;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all charts with error handling
    try {
        initializeCharts();
    } catch (error) {
        console.error('Error initializing charts:', error);
    }
});

function initializeCharts() {
    // Initialize Order Status Chart
    try {
        initOrderStatusChart();
    } catch (error) {
        console.error('Error initializing order status chart:', error);
    }
    
    // Initialize Equipment Chart
    try {
        initEquipmentChart();
    } catch (error) {
        console.error('Error initializing equipment chart:', error);
    }
    
    // Initialize Revenue Chart
    try {
        initRevenueChart();
    } catch (error) {
        console.error('Error initializing revenue chart:', error);
    }
    
    // Initialize Customer Types Chart
    try {
        initCustomerTypesChart();
    } catch (error) {
        console.error('Error initializing customer types chart:', error);
    }
}

function initOrderStatusChart() {
    const ctx = document.getElementById('order_status_chart');
    if (!ctx) return;
    
    try {
        const orderData = @json($order_status_distribution ?? []);
        
        if (!orderData || typeof orderData !== 'object' || Object.keys(orderData).length === 0) {
            console.log('No order status data available');
            return;
        }
        
        const labels = Object.keys(orderData).map(status => status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' '));
        const data = Object.values(orderData);
        const colors = ['#007bff', '#6f42c1', '#e83e8c', '#fd7e14', '#28a745', '#ffc107', '#dc3545'];
        
        if (orderStatusChart) {
            orderStatusChart.destroy();
        }
        
        orderStatusChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors.slice(0, labels.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error creating order status chart:', error);
    }
}

function initEquipmentChart() {
    const ctx = document.getElementById('equipment_chart');
    if (!ctx) return;
    
    try {
        const equipmentData = @json($equipment_status ?? []);
        
        if (!equipmentData || typeof equipmentData !== 'object' || Object.keys(equipmentData).length === 0) {
            console.log('No equipment status data available');
            return;
        }
        
        const labels = Object.keys(equipmentData).map(status => status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' '));
        const data = Object.values(equipmentData);
        const colors = ['#28a745', '#ffc107', '#dc3545', '#007bff'];
        
        if (equipmentChart) {
            equipmentChart.destroy();
        }
        
        equipmentChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors.slice(0, labels.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error creating equipment chart:', error);
    }
}

function initRevenueChart() {
    const ctx = document.getElementById('revenue_chart');
    if (!ctx) return;
    
    try {
        const chartData = @json($chart_data ?? []);
        
        if (!chartData || typeof chartData !== 'object' || !chartData.months || !Array.isArray(chartData.months)) {
            console.log('No revenue chart data available');
            return;
        }
        
        if (revenueChart) {
            revenueChart.destroy();
        }
        
        revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.months || [],
                datasets: [{
                    label: 'Revenue',
                    data: chartData.revenues || [],
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Margin',
                    data: chartData.margins || [],
                    borderColor: '#6f42c1',
                    backgroundColor: 'rgba(111, 66, 193, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + (value || 0).toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': $' + (context.parsed.y || 0).toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error creating revenue chart:', error);
    }
}

function initCustomerTypesChart() {
    const ctx = document.getElementById('customer_types_chart');
    if (!ctx) return;
    
    try {
        const customerData = @json($customer_types ?? []);
        
        if (!customerData || typeof customerData !== 'object' || Object.keys(customerData).length === 0) {
            console.log('No customer types data available');
            return;
        }
        
        const labels = Object.keys(customerData).map(type => type.charAt(0).toUpperCase() + type.slice(1));
        const data = Object.values(customerData);
        const colors = ['#007bff', '#6f42c1', '#e83e8c', '#fd7e14'];
        
        if (customerTypesChart) {
            customerTypesChart.destroy();
        }
        
        customerTypesChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors.slice(0, labels.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error creating customer types chart:', error);
    }
}

// Refresh functions with error handling
function refreshOrderStatusChart() {
    try {
        fetch('/dashboard/order-status-api')
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                console.log('Order status data refreshed', data);
                // You can update the chart here if needed
            })
            .catch(error => {
                console.error('Error refreshing order status:', error);
                // Show user-friendly message
                showErrorMessage('Failed to refresh order status data');
            });
    } catch (error) {
        console.error('Error in refreshOrderStatusChart:', error);
    }
}

function refreshRevenueChart() {
    try {
        fetch('/dashboard/chart-data-api')
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (revenueChart && data && data.months && data.revenues && data.margins) {
                    revenueChart.data.labels = data.months;
                    revenueChart.data.datasets[0].data = data.revenues;
                    revenueChart.data.datasets[1].data = data.margins;
                    revenueChart.update();
                }
            })
            .catch(error => {
                console.error('Error refreshing revenue chart:', error);
                showErrorMessage('Failed to refresh revenue chart data');
            });
    } catch (error) {
        console.error('Error in refreshRevenueChart:', error);
    }
}

// Auto-refresh dashboard every 5 minutes with error handling
setInterval(function() {
    try {
        fetch('/dashboard/refresh')
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data && data.status === 'success') {
                    console.log('Dashboard auto-refreshed at:', data.timestamp);
                }
            })
            .catch(error => {
                console.error('Error auto-refreshing dashboard:', error);
                // Don't show message for auto-refresh errors to avoid spam
            });
    } catch (error) {
        console.error('Error in auto-refresh:', error);
    }
}, 300000); // 5 minutes

// Helper function to show error messages
function showErrorMessage(message) {
    // You can implement a toast notification or other user feedback here
    console.warn('User message:', message);
}

// Easy Pie Chart initialization with error handling
$(document).ready(function() {
    try {
        if (typeof $.fn.easyPieChart !== 'undefined') {
            $('.easy-pie-chart').easyPieChart({
                barColor: '#007bff',
                trackColor: '#f8f9fa',
                scaleColor: false,
                lineWidth: 8,
                size: 80,
                animate: 2000
            });
        }
    } catch (error) {
        console.error('Error initializing easy pie charts:', error);
    }
});
</script>

@endsection