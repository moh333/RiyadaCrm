@php
    $fieldName = $field->getFieldName();
    $columnName = $field->getColumnName();
    $uitype = $field->getUitype();
    $value = null;

    try {
        if ($field->isCustomField()) {
            $value = $contact->getCustomField($columnName);
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
        @php
            // Handle multiple images (stored as JSON array)
            $images = [];
            if (is_string($value) && str_starts_with($value, '[')) {
                $images = json_decode($value, true) ?? [$value];
            } else {
                $images = [$value];
            }
        @endphp
        <div class="mt-2">
            <div class="row g-2">
                @foreach($images as $index => $imagePath)
                    <div class="col-md-4" id="image-{{ $fieldName }}-{{ $index }}">
                        <div class="card border-0 shadow-sm position-relative">
                            <img src="{{ url('tenancy/assets/' . $imagePath) }}" class="card-img-top rounded-top-3"
                                style="height: 150px; object-fit: cover;" alt="Image">
                            <div class="card-body p-2">
                                <div class="d-flex gap-1">
                                    <a href="{{ url('tenancy/assets/' . $imagePath) }}" target="_blank"
                                        class="btn btn-sm btn-outline-primary rounded-2 flex-fill"
                                        title="{{ __('contacts::contacts.view') }}">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-2"
                                        onclick="deleteFile('{{ $contact->getId() }}', '{{ $columnName }}', '{{ $imagePath }}', 'image-{{ $fieldName }}-{{ $index }}')"
                                        title="{{ __('contacts::contacts.delete') }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif ($uitype == 28 && $value) {{-- File --}}
        @php
            // Handle multiple files (stored as JSON array)
            $files = [];
            if (is_string($value) && str_starts_with($value, '[')) {
                $files = json_decode($value, true) ?? [$value];
            } else {
                $files = [$value];
            }
        @endphp
        <div class="mt-2">
            <div class="d-flex flex-column gap-2">
                @foreach($files as $index => $filePath)
                    @php
                        $fileName = basename($filePath);
                        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $iconMap = [
                            'pdf' => 'file-earmark-pdf text-danger',
                            'doc' => 'file-earmark-word text-primary',
                            'docx' => 'file-earmark-word text-primary',
                            'xls' => 'file-earmark-excel text-success',
                            'xlsx' => 'file-earmark-excel text-success',
                            'ppt' => 'file-earmark-ppt text-warning',
                            'pptx' => 'file-earmark-ppt text-warning',
                            'zip' => 'file-earmark-zip text-secondary',
                            'rar' => 'file-earmark-zip text-secondary',
                        ];
                        $icon = $iconMap[$extension] ?? 'file-earmark text-muted';
                    @endphp
                    <div class="card border-0 shadow-sm" id="file-{{ $fieldName }}-{{ $index }}">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center flex-fill">
                                    <i class="bi bi-{{ $icon }} fs-3 me-3"></i>
                                    <div class="flex-fill">
                                        <h6 class="mb-0 fw-bold">{{ $fileName }}</h6>
                                        <small class="text-muted">{{ strtoupper($extension) }}</small>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ url('tenancy/assets/' . $filePath) }}" download
                                        class="btn btn-sm btn-outline-primary rounded-2"
                                        title="{{ __('contacts::contacts.download') }}">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <a href="{{ url('tenancy/assets/' . $filePath) }}" target="_blank"
                                        class="btn btn-sm btn-outline-info rounded-2"
                                        title="{{ __('contacts::contacts.view') }}">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-2"
                                        onclick="deleteFile('{{ $contact->getId() }}', '{{ $columnName }}', '{{ $filePath }}', 'file-{{ $fieldName }}-{{ $index }}')"
                                        title="{{ __('contacts::contacts.delete') }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
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