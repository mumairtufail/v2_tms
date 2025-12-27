@props([
    'index' => 0,
    'namePrefix' => 'stops',
    'data' => null,
    'required' => false,
    'showSchedule' => true,
    'title' => 'SHIPPER',
    'icon' => 'bi-box-arrow-up',
    'iconColor' => 'text-success'
])

<div class="pickup-section border rounded p-2 h-100">
    <div class="d-flex align-items-center mb-2">
        <div class="pickup-icon me-2">
            <i class="bi {{ $icon }} {{ $iconColor }}"></i>
        </div>
        <h6 class="fw-bold mb-0 {{ $iconColor }} small">{{ $title }}</h6>
    </div>
    
    <div class="mb-2">
        <label class="form-label small">Company name @if($required)<span class="text-danger">*</span>@endif</label>
        <input type="text" class="form-control form-control-sm" 
               name="{{ $namePrefix }}[{{ $index }}][shipper_company_name]" 
               value="{{ $data?->company_name ?? '' }}"
               @if($required) required @endif>
    </div>
    
    <div class="mb-2">
        <label class="form-label small">Address 1 @if($required)<span class="text-danger">*</span>@endif</label>
        <input type="text" class="form-control form-control-sm" 
               name="{{ $namePrefix }}[{{ $index }}][shipper_address_1]" 
               value="{{ $data?->address_1 ?? '' }}"
               @if($required) required @endif>
    </div>
    
    <div class="mb-2">
        <label class="form-label small">Address 2</label>
        <input type="text" class="form-control form-control-sm" 
               name="{{ $namePrefix }}[{{ $index }}][shipper_address_2]" 
               value="{{ $data?->address_2 ?? '' }}">
    </div>
    
    <div class="row g-1 mb-2">
        <div class="col-4">
            <label class="form-label small">City @if($required)<span class="text-danger">*</span>@endif</label>
            <input type="text" class="form-control form-control-sm" 
                   name="{{ $namePrefix }}[{{ $index }}][shipper_city]" 
                   value="{{ $data?->city ?? '' }}"
                   @if($required) required @endif>
        </div>
        <div class="col-4">
            <label class="form-label small">State @if($required)<span class="text-danger">*</span>@endif</label>
            <input type="text" class="form-control form-control-sm" 
                   name="{{ $namePrefix }}[{{ $index }}][shipper_state]" 
                   value="{{ $data?->state ?? '' }}"
                   @if($required) required @endif>
        </div>
        <div class="col-4">
            <label class="form-label small">ZIP @if($required)<span class="text-danger">*</span>@endif</label>
            <input type="text" class="form-control form-control-sm" 
                   name="{{ $namePrefix }}[{{ $index }}][shipper_zip]" 
                   value="{{ $data?->postal_code ?? '' }}"
                   @if($required) required @endif>
        </div>
    </div>
    
    <div class="mb-2">
        <label class="form-label small">Country</label>
        <input type="text" class="form-control form-control-sm" 
               name="{{ $namePrefix }}[{{ $index }}][shipper_country]" 
               value="{{ $data?->country ?? 'USA' }}">
    </div>
    
    <div class="mb-2">
        <label class="form-label small">Contact name</label>
        <input type="text" class="form-control form-control-sm" 
               name="{{ $namePrefix }}[{{ $index }}][shipper_contact_name]" 
               value="{{ $data?->contact_name ?? '' }}">
    </div>
    
    <div class="row g-1 mb-2">
        <div class="col-6">
            <label class="form-label small">Phone</label>
            <input type="text" class="form-control form-control-sm" 
                   name="{{ $namePrefix }}[{{ $index }}][shipper_phone]" 
                   value="{{ $data?->contact_phone ?? '' }}">
        </div>
        <div class="col-6">
            <label class="form-label small">Email</label>
            <input type="email" class="form-control form-control-sm" 
                   name="{{ $namePrefix }}[{{ $index }}][shipper_contact_email]" 
                   value="{{ $data?->contact_email ?? '' }}">
        </div>
    </div>
    
    <div class="mb-2">
        <label class="form-label small">Notes</label>
        <textarea class="form-control form-control-sm" 
                  name="{{ $namePrefix }}[{{ $index }}][shipper_notes]" 
                  rows="2">{{ $data?->notes ?? '' }}</textarea>
    </div>
    
    <!-- Operating Hours -->
    <div class="row g-1 mb-2">
        <div class="col-6">
            <label class="form-label small">Opening</label>
            <input type="time" class="form-control form-control-sm" 
                   name="{{ $namePrefix }}[{{ $index }}][shipper_opening_time]" 
                   value="{{ $data?->opening_time ?? '' }}">
        </div>
        <div class="col-6">
            <label class="form-label small">Closing</label>
            <input type="time" class="form-control form-control-sm" 
                   name="{{ $namePrefix }}[{{ $index }}][shipper_closing_time]" 
                   value="{{ $data?->closing_time ?? '' }}">
        </div>
    </div>
    
    @if($showSchedule)
    <!-- Ready Schedule -->
    <div class="mb-2">
        <label class="form-label small">Start time</label>
        <input type="datetime-local" class="form-control form-control-sm" 
               name="{{ $namePrefix }}[{{ $index }}][ready_start_time]" 
               value="{{ $data?->start_time?->format('Y-m-d\TH:i') ?? '' }}">
    </div>
    <div class="mb-2">
        <label class="form-label small">End time</label>
        <input type="datetime-local" class="form-control form-control-sm" 
               name="{{ $namePrefix }}[{{ $index }}][ready_end_time]" 
               value="{{ $data?->end_time?->format('Y-m-d\TH:i') ?? '' }}">
    </div>
    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" value="1" 
               id="ready_appointment_{{ $index }}" 
               name="{{ $namePrefix }}[{{ $index }}][ready_appointment]" 
               {{ ($data?->is_appointment ?? false) ? 'checked' : '' }}>
        <label class="form-check-label small" for="ready_appointment_{{ $index }}">
            Make this an appointment
        </label>
    </div>
    @endif
</div>