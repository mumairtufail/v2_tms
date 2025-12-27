@extends('layouts.app')

@section('content')
<div class="container-fluid mt-xl-50 mt-sm-30 mt-15">
	@include('partials.message')
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
									<canvas id="pie_chart_1" width="80" height="80"></canvas>
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
									<canvas id="pie_chart_2" width="80" height="80"></canvas>
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
									<span class="font-12 font-weight-600 {{ ($stats['manifest_change'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
										{{ ($stats['manifest_change'] ?? 0) > 0 ? '+' : '' }}{{ $stats['manifest_change'] ?? 0 }}%
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
								<a href="#" class="inline-block refresh mr-15" onclick="refreshOrderStatusChart(); return false;">
									<i class="ion ion-md-refresh"></i>
								</a>
							</div>
						</div>
						<div class="card-body">
							@if(!empty($order_status_distribution))
								<div class="hk-legend-wrap mb-20">
									@php 
										$colorIndex = 0; 
										$colors = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6f42c1', '#fd7e14']; 
									@endphp
									@foreach($order_status_distribution as $status => $count)
										<div class="hk-legend">
											<span class="d-10 rounded-circle d-inline-block" style="background-color: {{ $colors[$colorIndex % count($colors)] }};"></span>
											<span>{{ ucfirst(str_replace('_', ' ', $status)) }} ({{ $count }})</span>
										</div>
										@php $colorIndex++; @endphp
									@endforeach
								</div>
								<div style="position: relative; height: 250px;">
									<canvas id="order_status_chart"></canvas>
								</div>
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
						</div>
						<div class="card-body">
							@if(!empty($equipment_status))
								<div class="hk-legend-wrap mb-20">
									@php $equipColors = ['#28a745', '#ffc107', '#dc3545', '#007bff']; $eqIndex = 0; @endphp
									@foreach($equipment_status as $status => $count)
										<div class="hk-legend">
											<span class="d-10 rounded-circle d-inline-block" style="background-color: {{ $equipColors[$eqIndex % count($equipColors)] }};"></span>
											<span>{{ ucfirst(str_replace('_', ' ', $status)) }} ({{ $count }})</span>
										</div>
										@php $eqIndex++; @endphp
									@endforeach
								</div>
								<div style="position: relative; height: 250px;">
									<canvas id="equipment_chart"></canvas>
								</div>
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
						</div>
						<div class="card-body" style="max-height: 350px; overflow-y: auto;">
							@forelse($recent_activities ?? [] as $activity)
								<div class="d-flex align-items-center mb-15">
									<div class="avatar avatar-xs avatar-rounded">
										@if($activity->user && $activity->user->profile_image)
											<img src="{{ asset('storage/' . $activity->user->profile_image) }}" alt="user" class="avatar-img">
										@else
											<div class="avatar-initial bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 12px;">
												{{ $activity->user ? strtoupper(substr($activity->user->f_name ?? 'U', 0, 1)) : 'U' }}
											</div>
										@endif
									</div>
									<div class="ml-15">
										<div class="font-13 font-weight-500 text-dark">
											{{ $activity->user ? ($activity->user->f_name . ' ' . $activity->user->l_name) : 'Unknown User' }}
										</div>
										<div class="font-12 text-muted">
											{{ $activity->action }}
										</div>
										<div class="font-11 text-light">
											{{ $activity->created_at->diffForHumans() }}
										</div>
									</div>
								</div>
							@empty
								<div class="text-center text-muted py-4">
									<i class="ion ion-md-information-circle font-48 text-light"></i>
									<p class="mt-3">No recent activities</p>
								</div>
							@endforelse
						</div>
					</div>
				</div>
			</div>
			
			<!-- Revenue Chart and Customer Types -->
			<div class="hk-row">
				<div class="col-lg-8">
					<div class="card">
						<div class="card-header card-header-action">
							<h6>Monthly Revenue Trends</h6>
							<div class="d-flex align-items-center card-action-wrap">
								<a href="#" class="inline-block refresh mr-15" onclick="refreshRevenueChart(); return false;">
									<i class="ion ion-md-refresh"></i>
								</a>
							</div>
						</div>
						<div class="card-body">
							<div class="hk-legend-wrap mb-20">
								<div class="hk-legend">
									<span class="d-10 rounded-circle d-inline-block" style="background-color: #007bff;"></span>
									<span>Revenue</span>
								</div>
								<div class="hk-legend">
									<span class="d-10 rounded-circle d-inline-block" style="background-color: #6f42c1;"></span>
									<span>Margin</span>
								</div>
							</div>
							<div style="position: relative; height: 350px;">
								<canvas id="revenue_chart"></canvas>
							</div>
						</div>
					</div>
				</div>
				
				<div class="col-lg-4">
					<div class="card">
						<div class="card-header card-header-action">
							<h6>Customer Types</h6>
						</div>
						<div class="card-body">
							@if(!empty($customer_types))
								<div style="position: relative; height: 280px;">
									<canvas id="customer_types_chart"></canvas>
								</div>
								<div class="hk-legend-wrap mt-20 mb-5">
									@php $custColors = ['#007bff', '#6f42c1', '#e83e8c', '#fd7e14']; $custIndex = 0; @endphp
									@foreach($customer_types as $type => $count)
										<div class="hk-legend">
											<span class="d-10 rounded-circle d-inline-block" style="background-color: {{ $custColors[$custIndex % count($custColors)] }};"></span>
											<span>{{ ucfirst($type) }} ({{ $count }})</span>
										</div>
										@php $custIndex++; @endphp
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
						<a href="#" class="btn btn-primary btn-sm">View All Orders</a>
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
									@forelse($recent_orders ?? [] as $order)
										<tr>
											<td>
												<span class="font-weight-500 text-primary">{{ $order->order_number ?? 'N/A' }}</span>
											</td>
											<td>{{ $order->shipper->name ?? 'N/A' }}</td>
											<td>{{ $order->consignee->name ?? 'N/A' }}</td>
											<td>
												@if($order->csr ?? false)
													{{ $order->csr->f_name }} {{ $order->csr->l_name }}
												@else
													<span class="text-muted">Unassigned</span>
												@endif
											</td>
											<td>
												@if($order->revenue ?? false)
													<span class="font-weight-500">${{ number_format($order->revenue, 2) }}</span>
												@else
													<span class="text-muted">-</span>
												@endif
											</td>
											<td>
												@php
													$statusColor = 'secondary';
													$status = strtolower($order->status ?? 'unknown');
													switch($status) {
														case 'completed': $statusColor = 'success'; break;
														case 'in_transit': case 'in transit': $statusColor = 'primary'; break;
														case 'pending': $statusColor = 'warning'; break;
														case 'cancelled': $statusColor = 'danger'; break;
														case 'new': $statusColor = 'info'; break;
														default: $statusColor = 'secondary';
													}
												@endphp
												<span class="badge badge-{{ $statusColor }}">{{ ucfirst(str_replace('_', ' ', $order->status ?? 'Unknown')) }}</span>
											</td>
											<td>
												@if($order->ready_start_date ?? false)
													{{ \Carbon\Carbon::parse($order->ready_start_date)->format('M d, Y') }}
												@else
													<span class="text-muted">-</span>
												@endif
											</td>
											<td>
												@if($order->delivery_start_date ?? false)
													{{ \Carbon\Carbon::parse($order->delivery_start_date)->format('M d, Y') }}
												@else
													<span class="text-muted">-</span>
												@endif
											</td>
										</tr>
									@empty
										<tr>
											<td colspan="8" class="text-center text-muted py-4">
												<i class="ion ion-md-document font-48 text-light"></i>
												<p class="mt-3">No recent orders found</p>
											</td>
										</tr>
									@endforelse
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

<script>
// Global chart instances
let orderStatusChart = null;
let equipmentChart = null;
let revenueChart = null;
let customerTypesChart = null;
let pieChart1 = null;
let pieChart2 = null;

// Chart data from backend
const chartData = {
    orderStatus: @json($order_status_distribution ?? []),
    equipmentStatus: @json($equipment_status ?? []),
    customerTypes: @json($customer_types ?? []),
    revenueData: @json($chart_data ?? ['months' => [], 'revenues' => [], 'margins' => []]),
    stats: @json($stats ?? [])
};

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all charts
    initializeCharts();
});

function initializeCharts() {
    // Initialize small pie charts in stats cards
    initSmallPieCharts();
    
    // Initialize main charts
    initOrderStatusChart();
    initEquipmentChart();
    initRevenueChart();
    initCustomerTypesChart();
}

function initSmallPieCharts() {
    // Completion Rate Pie Chart
    const ctx1 = document.getElementById('pie_chart_1');
    if (ctx1 && chartData.stats.orders_completion_rate !== undefined) {
        const completionRate = chartData.stats.orders_completion_rate;
        pieChart1 = new Chart(ctx1, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [completionRate, 100 - completionRate],
                    backgroundColor: ['#007bff', '#e9ecef'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: false,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                cutout: '70%'
            }
        });
    }

    // Revenue Growth Pie Chart
    const ctx2 = document.getElementById('pie_chart_2');
    if (ctx2 && chartData.stats.revenue_growth !== undefined) {
        const revenueGrowth = Math.abs(chartData.stats.revenue_growth);
        const displayGrowth = Math.min(revenueGrowth, 100);
        pieChart2 = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [displayGrowth, 100 - displayGrowth],
                    backgroundColor: [chartData.stats.revenue_growth >= 0 ? '#28a745' : '#dc3545', '#e9ecef'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: false,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                cutout: '70%'
            }
        });
    }
}

function initOrderStatusChart() {
    const ctx = document.getElementById('order_status_chart');
    if (!ctx || Object.keys(chartData.orderStatus).length === 0) return;
    
    const labels = Object.keys(chartData.orderStatus).map(status => 
        status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ')
    );
    const data = Object.values(chartData.orderStatus);
    const colors = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6f42c1', '#fd7e14'];
    
    if (orderStatusChart) orderStatusChart.destroy();
    
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
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

function initEquipmentChart() {
    const ctx = document.getElementById('equipment_chart');
    if (!ctx || Object.keys(chartData.equipmentStatus).length === 0) return;
    
    const labels = Object.keys(chartData.equipmentStatus).map(status => 
        status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ')
    );
    const data = Object.values(chartData.equipmentStatus);
    const colors = ['#28a745', '#ffc107', '#dc3545', '#007bff'];
    
    if (equipmentChart) equipmentChart.destroy();
    
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
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed;
                        }
                    }
                }
            }
        }
    });
}

function initRevenueChart() {
    const ctx = document.getElementById('revenue_chart');
    if (!ctx || !chartData.revenueData.months.length) return;
    
    if (revenueChart) revenueChart.destroy();
    
    revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.revenueData.months,
            datasets: [{
                label: 'Revenue',
                data: chartData.revenueData.revenues,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Margin',
                data: chartData.revenueData.margins,
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
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

function initCustomerTypesChart() {
    const ctx = document.getElementById('customer_types_chart');
    if (!ctx || Object.keys(chartData.customerTypes).length === 0) return;
    
    const labels = Object.keys(chartData.customerTypes).map(type => 
        type.charAt(0).toUpperCase() + type.slice(1)
    );
    const data = Object.values(chartData.customerTypes);
    const colors = ['#007bff', '#6f42c1', '#e83e8c', '#fd7e14'];
    
    if (customerTypesChart) customerTypesChart.destroy();
    
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
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed;
                        }
                    }
                }
            }
        }
    });
}

// Refresh functions
function refreshOrderStatusChart() {
    console.log('Refreshing order status chart...');
    // You can implement AJAX refresh here
}

function refreshRevenueChart() {
    console.log('Refreshing revenue chart...');
    // You can implement AJAX refresh here
}

// Auto-refresh dashboard every 5 minutes
setInterval(function() {
    console.log('Auto-refreshing dashboard data...');
    // You can implement periodic refresh here
}, 300000); // 5 minutes

// Console log for debugging
console.log('Dashboard loaded with data:', chartData);
</script>

@endsection