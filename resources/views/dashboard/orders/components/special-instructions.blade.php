@props([
    'namePrefix' => 'order',
    'instructions' => '',
    'title' => 'SPECIAL INSTRUCTIONS',
    'titleColor' => 'text-secondary',
    'showTitle' => true,
    'rows' => 3,
    'placeholder' => 'Enter special instructions for this order...'
])

<div class="special-instructions-section border rounded p-2 h-100">
    @if($showTitle)
    <h6 class="fw-bold mb-2 {{ $titleColor }} small">{{ $title }}</h6>
    @endif
    
    <textarea class="form-control form-control-sm" 
              name="{{ $namePrefix }}[special_instructions]" 
              rows="{{ $rows }}" 
              placeholder="{{ $placeholder }}">{{ $instructions }}</textarea>
              
    <div class="text-muted small mt-1">
        <i class="bi bi-info-circle"></i> 
        Include any special handling requirements, delivery instructions, or important notes.
    </div>
</div>