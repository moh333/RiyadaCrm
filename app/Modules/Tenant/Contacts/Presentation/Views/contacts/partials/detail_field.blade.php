@php
    $fieldName = $field->getFieldName();
    $uitype = $field->getUitype();
    $value = null;

    try {
        if ($field->isCustomField()) {
            $value = $contact->getCustomField($fieldName);
        } else {
            // Property map for Contact entity getters
            $propMap = [
                'firstname' => 'FirstName',
                'lastname' => 'LastName',
                'salutation' => 'Salutation',
                'email' => 'Email',
                'phone' => 'OfficePhone',
                'mobile' => 'MobilePhone',
                'homephone' => 'HomePhone',
                'fax' => 'Fax',
                'title' => 'Title',
                'department' => 'Department',
                'account_id' => 'AccountId',
                'leadsource' => 'LeadSource',
                'smownerid' => 'OwnerId',
            ];

            $prop = $propMap[$fieldName] ?? str_replace('_', '', ucwords($fieldName, '_'));

            // Handle Salutation which is in FullName VO
            if ($fieldName === 'salutation') {
                $value = $contact->getFullName()->getSalutation();
            } elseif ($fieldName === 'firstname') {
                $value = $contact->getFullName()->getFirstName();
            } elseif ($fieldName === 'lastname') {
                $value = $contact->getFullName()->getLastName();
            } else {
                $method = 'get' . $prop;
                if (method_exists($contact, $method)) {
                    $val = $contact->$method();
                    // Handle Value Objects
                    if (is_object($val)) {
                        if (method_exists($val, 'getEmail')) {
                            $value = $val->getEmail();
                        } elseif (method_exists($val, 'getNumber')) {
                            $value = $val->getNumber();
                        } elseif (method_exists($val, '__toString')) {
                            $value = (string) $val;
                        } else {
                            $value = $val;
                        }
                    } else {
                        $value = $val;
                    }
                }
            }
        }
    } catch (\Exception $e) {
        $value = null;
    }
@endphp

<div class="col-md-6 mb-4">
    <h6 class="text-muted small fw-bold mb-2">
        {{ strtoupper($field->getLabel()) }}
    </h6>
    @if ($uitype == 69 && $value) {{-- Image --}}
        <div class="mt-2">
            <img src="{{ asset('storage/' . $value) }}" class="img-thumbnail rounded-3 shadow-sm"
                style="max-height: 150px;">
        </div>
    @elseif ($uitype == 28 && $value) {{-- File --}}
        <div class="mt-2">
            <a href="{{ asset('storage/' . $value) }}" target="_blank"
                class="text-primary text-decoration-none bg-light px-3 py-2 rounded-3 border d-inline-block">
                <i class="bi bi-file-earmark-arrow-down me-2"></i> {{ basename($value) }}
            </a>
        </div>
    @elseif ($uitype == 56) {{-- Checkbox --}}
        <p class="fw-500">
            @if($value == '1' || $value === true)
                <span class="badge bg-soft-success text-success">{{ __('contacts::contacts.yes') }}</span>
            @else
                <span class="badge bg-soft-danger text-danger">{{ __('contacts::contacts.no') }}</span>
            @endif
        </p>
    @elseif ($fieldName == 'account_id' && $value)
        @php
            $accountName = \DB::connection('tenant')->table('vtiger_account')->where('accountid', $value)->value('accountname');
        @endphp
        <p class="fw-500"><i class="bi bi-building me-1 text-primary"></i> {{ $accountName ?? $value }}</p>
    @elseif ($uitype == 13 && $value) {{-- Email --}}
        <p class="fw-500"><a href="mailto:{{ $value }}" class="text-decoration-none">{{ $value }}</a></p>
    @else
        <p class="fw-500">{{ $value ?: '-' }}</p>
    @endif
</div>