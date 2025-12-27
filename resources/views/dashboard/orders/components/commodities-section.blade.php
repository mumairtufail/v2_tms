@props([
    'index' => 0,
    'namePrefix' => 'stops',
    'commodities' => collect(),
    'title' => 'COMMODITIES',
    'titleColor' => 'text-info',
    'showAddButton' => true
])

<div class="commodities-section border rounded p-2 h-100">
    <h6 class="fw-bold mb-2 {{ $titleColor }} small">{{ $title }}</h6>
    <div class="commodities-container">
        @forelse($commodities as $commodityIndex => $commodity)
        <div class="commodity-item mb-2 p-2 bg-light rounded">
            <div class="mb-2">
                <label class="form-label small">Description</label>
                <input type="text" class="form-control form-control-sm" 
                       name="{{ $namePrefix }}[{{ $index }}][commodities][{{ $commodityIndex }}][description]" 
                       value="{{ $commodity->description ?? '' }}">
            </div>
            <div class="row g-1 mb-1">
                <div class="col-4">
                    <label class="form-label small">QTY</label>
                    <input type="number" class="form-control form-control-sm" 
                           name="{{ $namePrefix }}[{{ $index }}][commodities][{{ $commodityIndex }}][quantity]" 
                           value="{{ $commodity->quantity ?? 1 }}">
                </div>
                <div class="col-4">
                    <label class="form-label small">Weight</label>
                    <input type="number" class="form-control form-control-sm" 
                           name="{{ $namePrefix }}[{{ $index }}][commodities][{{ $commodityIndex }}][weight]" 
                           value="{{ $commodity->weight ?? 0 }}">
                </div>
                <div class="col-4">
                    @if($commodityIndex > 0 || $commodities->count() > 1)
                    <button type="button" class="btn btn-sm btn-outline-danger remove-commodity-btn" style="margin-top: 23px;" title="Remove commodity">
                        <i class="bi bi-trash"></i>
                    </button>
                    @endif
                </div>
            </div>
            <div class="row g-1">
                <div class="col-4">
                    <label class="form-label small">L</label>
                    <input type="number" class="form-control form-control-sm" 
                           name="{{ $namePrefix }}[{{ $index }}][commodities][{{ $commodityIndex }}][length]" 
                           value="{{ $commodity->length ?? '' }}">
                </div>
                <div class="col-4">
                    <label class="form-label small">W</label>
                    <input type="number" class="form-control form-control-sm" 
                           name="{{ $namePrefix }}[{{ $index }}][commodities][{{ $commodityIndex }}][width]" 
                           value="{{ $commodity->width ?? '' }}">
                </div>
                <div class="col-4">
                    <label class="form-label small">H</label>
                    <input type="number" class="form-control form-control-sm" 
                           name="{{ $namePrefix }}[{{ $index }}][commodities][{{ $commodityIndex }}][height]" 
                           value="{{ $commodity->height ?? '' }}">
                </div>
            </div>
        </div>
        @empty
        <div class="commodity-item mb-2 p-2 bg-light rounded">
            <div class="mb-2">
                <label class="form-label small">Description</label>
                <input type="text" class="form-control form-control-sm" 
                       name="{{ $namePrefix }}[{{ $index }}][commodities][0][description]">
            </div>
            <div class="row g-1 mb-1">
                <div class="col-6">
                    <label class="form-label small">QTY</label>
                    <input type="number" class="form-control form-control-sm" 
                           name="{{ $namePrefix }}[{{ $index }}][commodities][0][quantity]" 
                           value="1">
                </div>
                <div class="col-6">
                    <label class="form-label small">Weight</label>
                    <input type="number" class="form-control form-control-sm" 
                           name="{{ $namePrefix }}[{{ $index }}][commodities][0][weight]" 
                           value="0">
                </div>
            </div>
            <div class="row g-1">
                <div class="col-4">
                    <label class="form-label small">L</label>
                    <input type="number" class="form-control form-control-sm" 
                           name="{{ $namePrefix }}[{{ $index }}][commodities][0][length]">
                </div>
                <div class="col-4">
                    <label class="form-label small">W</label>
                    <input type="number" class="form-control form-control-sm" 
                           name="{{ $namePrefix }}[{{ $index }}][commodities][0][width]">
                </div>
                <div class="col-4">
                    <label class="form-label small">H</label>
                    <input type="number" class="form-control form-control-sm" 
                           name="{{ $namePrefix }}[{{ $index }}][commodities][0][height]">
                </div>
            </div>
        </div>
        @endforelse
    </div>
    
    @if($showAddButton)
    <button type="button" class="btn btn-sm btn-outline-info add-commodity-btn">
        <i class="bi bi-plus"></i> Add Commodity
    </button>
    @endif
</div>