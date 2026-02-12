<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Student Report' }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid {{ $confidential ?? false ? '#DC3545' : '#1E40AF' }};
            padding-bottom: 15px;
        }
        .school-name {
            font-size: 22px;
            font-weight: bold;
            color: {{ $confidential ?? false ? '#DC3545' : '#1E40AF' }};
            margin-bottom: 5px;
        }
        .school-motto {
            font-size: 13px;
            font-style: italic;
            color: #6B7280;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
            color: #374151;
        }
        .report-meta {
            background: #F3F4F6;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 11px;
        }
        .confidential-badge {
            background: #DC3545;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
            margin-left: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background: {{ $confidential ?? false ? '#DC3545' : '#1E40AF' }};
            color: white;
            font-weight: 600;
            padding: 12px 8px;
            text-align: left;
            font-size: 11px;
        }
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #E5E7EB;
            vertical-align: middle;
        }
        tr:nth-child(even) {
            background-color: #F9FAFB;
        }
        .photo-placeholder {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #E5E7EB;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #6B7280;
        }
        .photo-img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
        }
        .status-active {
            color: #10B981;
            font-weight: 600;
        }
        .status-inactive {
            color: #6B7280;
            font-weight: 600;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #E5E7EB;
            font-size: 10px;
            color: #6B7280;
            text-align: center;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60px;
            font-weight: bold;
            color: rgba(220, 53, 69, 0.1);
            z-index: 1000;
            pointer-events: none;
            text-transform: uppercase;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

@if($confidential ?? false)
    <div class="watermark">CONFIDENTIAL</div>
@endif

<div class="header">
    @if($include_header ?? true && isset($school_info))
        <div class="school-name">{{ $school_info->school_name ?? 'SCHOOL NAME' }}</div>
        @if(isset($school_info->school_motto))
            <div class="school-motto">{{ $school_info->school_motto }}</div>
        @endif
    @endif

    <div class="report-title">
        STUDENT MASTER LIST REPORT
        @if($confidential ?? false)
            <span class="confidential-badge">CONFIDENTIAL</span>
        @endif
    </div>
</div>

<div class="report-meta">
    <table style="width: 100%; border: none; background: transparent;">
        <tr>
            <td style="border: none; padding: 5px; background: transparent;"><strong>Class:</strong> {{ $className }}</td>
            <td style="border: none; padding: 5px; background: transparent;"><strong>Term:</strong> {{ $termName }}</td>
            <td style="border: none; padding: 5px; background: transparent;"><strong>Session:</strong> {{ $sessionName }}</td>
        </tr>
        <tr>
            <td style="border: none; padding: 5px; background: transparent;"><strong>Generated:</strong> {{ $generated }}</td>
            <td style="border: none; padding: 5px; background: transparent;"><strong>By:</strong> {{ $generated_by }}</td>
            <td style="border: none; padding: 5px; background: transparent;"><strong>Total Students:</strong> {{ $total }}</td>
        </tr>
        @if(isset($males) && isset($females))
        <tr>
            <td style="border: none; padding: 5px; background: transparent;"><strong>Males:</strong> {{ $males }}</td>
            <td style="border: none; padding: 5px; background: transparent;"><strong>Females:</strong> {{ $females }}</td>
            <td style="border: none; padding: 5px; background: transparent;"></td>
        </tr>
        @endif
        @if($warning ?? false)
        <tr>
            <td colspan="3" style="border: none; padding: 8px; background: #FEF2F2; color: #991B1B; border-radius: 4px;">
                ⚠️ {{ $warning }}
            </td>
        </tr>
        @endif
    </table>
</div>

<table>
    <thead>
        <tr>
            @foreach($columns as $column)
                @php
                    $headerMap = [
                        'photo' => 'Photo',
                        'admissionNo' => 'Adm. No.',
                        'lastname' => 'Last Name',
                        'firstname' => 'First Name',
                        'othername' => 'Other Name',
                        'gender' => 'Gender',
                        'dateofbirth' => 'DOB',
                        'age' => 'Age',
                        'class' => 'Class',
                        'status' => 'Status',
                        'admission_date' => 'Adm. Date',
                        'phone_number' => 'Phone',
                        'state' => 'State',
                        'local' => 'LGA',
                        'religion' => 'Religion',
                        'blood_group' => 'Blood',
                        'father_name' => 'Father',
                        'mother_name' => 'Mother',
                        'guardian_phone' => 'Guardian',
                        'term' => 'Term',
                        'session' => 'Session',
                        'email' => 'Email',
                        'city' => 'City',
                        'nationality' => 'Nationality',
                        'placeofbirth' => 'Birth Place',
                        'mother_tongue' => 'Lang',
                        'student_category' => 'Category',
                        'future_ambition' => 'Ambition',
                        'last_school' => 'Prev School',
                        'last_class' => 'Prev Class',
                        'father_occupation' => 'Father Occ',
                        'mother_occupation' => 'Mother Occ',
                        'father_city' => 'Father City',
                        'parent_email' => 'Parent Email',
                        'parent_address' => 'Parent Address',
                        'nin_number' => 'NIN',
                        'school_house' => 'House',
                    ];
                @endphp
                <th>{{ $headerMap[$column] ?? ucwords(str_replace('_', ' ', $column)) }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse($students as $student)
            <tr>
                @foreach($columns as $column)
                    <td>
                        @switch($column)
                            @case('photo')
                                @if(isset($student->picture_base64) && $student->picture_base64 && $student->has_photo)
                                    <img src="{{ $student->picture_base64 }}" class="photo-img" alt="Photo">
                                @elseif(isset($student->picture) && $student->picture && $student->picture !== 'unnamed.jpg')
                                    <span class="photo-placeholder">✓</span>
                                @else
                                    <span class="photo-placeholder">{{ $student->photo_initials ?? 'ST' }}</span>
                                @endif
                                @break

                            @case('admissionNo')
                                {{ $student->admissionNo ?? 'N/A' }}
                                @break

                            @case('class')
                                {{ $student->schoolclass ?? '' }} {{ $student->arm_name ?? '' }}
                                @break

                            @case('gender')
                                {{ $student->gender ?? 'N/A' }}
                                @break

                            @case('status')
                                @if($student->student_status === 'Active')
                                    <span class="status-active">Active</span>
                                @elseif($student->student_status === 'Inactive')
                                    <span class="status-inactive">Inactive</span>
                                @else
                                    @if($student->statusId == 1) Old @elseif($student->statusId == 2) New @else N/A @endif
                                @endif
                                @break

                            @case('dateofbirth')
                                {{ $student->dateofbirth ? \Carbon\Carbon::parse($student->dateofbirth)->format('d/m/Y') : 'N/A' }}
                                @break

                            @case('age')
                                {{ $student->age ?? \Carbon\Carbon::parse($student->dateofbirth)->age ?? 'N/A' }}
                                @break

                            @case('admission_date')
                                {{ $student->admission_date ? \Carbon\Carbon::parse($student->admission_date)->format('d/m/Y') : 'N/A' }}
                                @break

                            @case('term')
                                {{ $student->current_term_name ?? $termName }}
                                @break

                            @case('session')
                                {{ $student->current_session_name ?? $sessionName }}
                                @break

                            @case('guardian_phone')
                                {{ $student->father_phone ?? $student->mother_phone ?? 'N/A' }}
                                @break

                            @default
                                @php
                                    $value = $student->$column ?? 'N/A';
                                @endphp
                                {{ is_array($value) ? json_encode($value) : $value }}
                        @endswitch
                    </td>
                @endforeach
            </tr>
        @empty
            <tr>
                <td colspan="{{ count($columns) }}" style="text-align: center; padding: 30px;">
                    No students found matching the criteria.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    <div>Generated by: {{ $generated_by ?? 'System' }} | Template: {{ ucfirst($template ?? 'default') }}</div>
    <div>Generated on: {{ $generated ?? now()->format('d/m/Y H:i:s') }}</div>
    <div style="margin-top: 5px;">Page <span class="page-number"></span> of <span class="total-pages"></span></div>
</div>

<script type="text/php">
    if (isset($pdf)) {
        $x = 520;
        $y = 820;
        $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $font = null;
        $size = 10;
        $color = array(0,0,0);
        $word_space = 0.0;
        $char_space = 0.0;
        $angle = 0.0;
        $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
    }
</script>

</body>
</html>
