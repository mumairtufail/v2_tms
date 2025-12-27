{{--
    This file represents the content for the "Sequence" tab.
    It's used for creating an order with a linear sequence of stops.
--}}

<!-- Informational Header -->
<div class="alert alert-secondary border-0 bg-light small mb-4">
This order has multiple shippers and consignees with different stops.
</div>

<!-- Stops Section -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="card-title mb-0 fw-bold">STOPS</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-vcenter">
                <thead>
                    <tr>
                        <th>Stop #</th>
                        <th style="width: 25%;">Shipper <span class="text-danger">*</span></th>
                        <th style="width: 25%;">Consignee <span class="text-danger">*</span></th>
                        <th>Manifest</th>
                        <th>Dims <span class="text-danger">*</span></th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Example Stop Row -->
                    <tr>
                        <td>1</td>
                        <td><a href="#" class="text-decoration-none">Add</a></td>
                        <td><a href="#" class="text-decoration-none">Add</a></td>
                        <td>--</td>
                        <td>0 LF</td>
                        <td><span class="badge bg-info">NEW</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="text-center mt-3">
            <button type="button" class="btn btn-primary">Add stop</button>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <div>
            <strong>Total Stops: 1</strong>
        </div>
        <div class="text-end">
            <div class="text-muted small">Total Revenue (CAD) <span class="ms-3">$0.00</span></div>
            <div class="text-muted small">Total Costs (CAD) <span class="ms-4">$0.00</span></div>
            <hr class="my-1">
            <div class="fw-bold">Margin (CAD) <span class="ms-5">$0.00 (N/A)</span></div>
        </div>
    </div>
</div>

<!-- Commodities Section -->
<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="card-title mb-0 fw-bold">COMMODITIES (1)</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th style="width: 25%;">Description</th>
                        <th>QTY</th>
                        <th>Type</th>
                        <th>LG</th>
                        <th>WD</th>
                        <th>HT</th>
                        <th>PCS</th>
                        <th>LF</th>
                        <th>Total WT</th>
                        <th>Class</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Example Commodity Row -->
                    <tr>
                        <td><input type="text" name="seq_commodities[0][description]" class="form-control form-control-sm"></td>
                        <td><input type="number" name="seq_commodities[0][qty]" value="1" class="form-control form-control-sm" style="min-width: 70px;"></td>
                        <td><input type="text" name="seq_commodities[0][type]" value="Skid" class="form-control form-control-sm" style="min-width: 100px;"></td>
                        <td><input type="text" name="seq_commodities[0][length]" value="in/lb" class="form-control form-control-sm" style="min-width: 80px;"></td>
                        <td><input type="text" name="seq_commodities[0][width]" value="in/lb" class="form-control form-control-sm" style="min-width: 80px;"></td>
                        <td><input type="text" name="seq_commodities[0][height]" value="in/lb" class="form-control form-control-sm" style="min-width: 80px;"></td>
                        <td><input type="text" name="seq_commodities[0][pcs]" value="LF" class="form-control form-control-sm" style="min-width: 80px;"></td>
                        <td><input type="text" name="seq_commodities[0][lf]" value="0 lbs" class="form-control form-control-sm" style="min-width: 80px;"></td>
                        <td><input type="text" name="seq_commodities[0][weight]" value="None" class="form-control form-control-sm" style="min-width: 80px;"></td>
                        <td><input type="text" name="seq_commodities[0][class]" class="form-control form-control-sm" style="min-width: 100px;"></td>
                        <td><a href="#" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></a></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <a href="#" class="btn btn-outline-primary mt-2">
            <i class="bi bi-plus"></i> Add commodity
        </a>
    </div>
</div>