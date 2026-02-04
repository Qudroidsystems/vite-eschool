<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Student Master List Report' }} - {{ now()->format('d M Y') }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.5;
            margin: {{ request()->query('orientation', 'portrait') === 'landscape' ? '1cm 0.8cm' : '1.5cm' }};
        }
        .header-table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }
        .logo-cell {
            width: 140px;
            vertical-align: top;
            padding-right: 25px;
        }
        .logo {
            max-width: 130px;
            max-height: 130px;
            object-fit: contain;
        }
        .school-name {
            font-size: 24pt;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 6px;
        }
        .school-motto {
            font-size: 13pt;
            font-style: italic;
            color: #4b5563;
            margin-bottom: 10px;
        }
        .school-details {
            font-size: 10.5pt;
            color: #4b5563;
            line-height: 1.6;
        }
        .report-title {
            text-align: center;
            font-size: 18pt;
            color: #1e40af;
            margin: 25px 0 10px;
            font-weight: bold;
        }
        .report-meta {
            text-align: center;
            font-size: 11pt;
            color: #4b5563;
            margin-bottom: 25px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 9px 10px;
            text-align: left;
            vertical-align: middle;
        }
        th {
            background: linear-gradient(to bottom, #eff6ff, #dbeafe);
            color: #1e40af;
            font-weight: bold;
            font-size: 10pt;
            text-transform: uppercase;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .photo-cell {
            width: 70px;
            text-align: center;
        }
        .student-photo {
            width: 56px;
            height: 56px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #d1d5db;
        }
        .totals-row {
            background: #e0f2fe;
            font-weight: bold;
        }
        .watermark {
            position: fixed;
            top: 45%;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 72pt;
            color: rgba(200, 200, 200, 0.12);
            transform: rotate(-45deg);
            z-index: -1;
            pointer-events: none;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 35px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9.5pt;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }
        @page {
            margin: {{ request()->query('orientation', 'portrait') === 'landscape' ? '1cm 0.8cm' : '1.5cm' }};
        }
    </style>
</head>
<body>

    <div class="watermark">CONFIDENTIAL</div>

    <?php $school = \App\Models\SchoolInformation::where('is_active', true)->first(); ?>

    <!-- Letterhead -->
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if($school && $school->school_logo && file_exists(storage_path('app/public/' . $school->school_logo)))
                    <img src="{{ storage_path('app/public/' . $school->school_logo) }}" alt="School Logo" class="logo">
                @else
                    <!-- SVG fallback logo -->
                    <svg width="130" height="130" viewBox="0 0 130 130" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="65" cy="65" r="60" fill="#e5e7eb"/>
                        <text x="65" y="75" font-family="Arial" font-size="28" font-weight="bold" fill="#6b7280" text-anchor="middle">LOGO</text>
                    </svg>
                @endif
            </td>
            <td style="text-align:center; vertical-align:top;">
                <div class="school-name">
                    {{ $school ? $school->school_name : config('app.name', 'School Management System') }}
                </div>
                @if($school && $school->school_motto)
                    <div class="school-motto">"{{ $school->school_motto }}"</div>
                @endif
                @if($school)
                    <div class="school-details">
                        {{ $school->school_address }}<br>
                        Phone: {{ $school->school_phone }} • Email: {{ $school->school_email }}
                        @if($school->school_website)<br>Website: {{ $school->school_website }}@endif
                    </div>
                @endif
            </td>
            <td style="width:140px;"></td>
        </tr>
    </table>

    <div class="report-title">{{ $title ?? 'Student Master List Report' }}</div>
    <div class="report-meta">
        Class: <strong>{{ $className ?? 'All Classes' }}</strong> •
        Generated: <strong>{{ $generated }}</strong> •
        Orientation: <strong>{{ request()->query('orientation', 'portrait') === 'landscape' ? 'Landscape' : 'Portrait' }}</strong>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                @if(in_array('photo', $columns ?? [])) <th class="photo-cell">Photo</th> @endif
                @if(in_array('admissionNo', $columns ?? [])) <th>Admission Number</th> @endif
                @if(in_array('fullname', $columns ?? [])) <th>Full Name</th> @endif
                @if(in_array('gender', $columns ?? [])) <th>Gender</th> @endif
                @if(in_array('dateofbirth', $columns ?? [])) <th>Date of Birth</th> @endif
                @if(in_array('age', $columns ?? [])) <th>Age</th> @endif
                @if(in_array('class', $columns ?? [])) <th>Class / Arm</th> @endif
                @if(in_array('status', $columns ?? [])) <th>Student Status</th> @endif
                @if(in_array('admission_date', $columns ?? [])) <th>Admission Date</th> @endif
                @if(in_array('phone_number', $columns ?? [])) <th>Phone Number</th> @endif
                @if(in_array('state', $columns ?? [])) <th>State of Origin</th> @endif
                @if(in_array('local', $columns ?? [])) <th>Local Government Area (LGA)</th> @endif
                @if(in_array('religion', $columns ?? [])) <th>Religion</th> @endif
                @if(in_array('blood_group', $columns ?? [])) <th>Blood Group</th> @endif
                @if(in_array('father_name', $columns ?? [])) <th>Father's Name</th> @endif
                @if(in_array('mother_name', $columns ?? [])) <th>Mother's Name</th> @endif
                @if(in_array('guardian_phone', $columns ?? [])) <th>Guardian Contact Phone</th> @endif
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
                <tr>
                    @if(in_array('photo', $columns ?? []))
                        <td class="photo-cell">
                            @if($student->picture && $student->picture !== 'unnamed.jpg' && file_exists(storage_path('app/public/images/student_avatars/' . $student->picture)))
                                <img src="{{ storage_path('app/public/images/student_avatars/' . $student->picture) }}" class="student-photo" alt="Photo">
                            @else
                                <div style="width:56px;height:56px;background:#e5e7eb;border-radius:6px;display:flex;align-items:center;justify-content:center;font-weight:bold;color:#6b7280;">
                                    {{ substr($student->firstname ?? '', 0, 1) . substr($student->lastname ?? '', 0, 1) }}
                                </div>
                            @endif
                        </td>
                    @endif

                    @if(in_array('admissionNo', $columns ?? [])) <td>{{ $student->admissionNo ?? '-' }}</td> @endif
                    @if(in_array('fullname', $columns ?? []))
                        <td>{{ trim("{$student->lastname} {$student->firstname} {$student->othername}") }}</td>
                    @endif
                    @if(in_array('gender', $columns ?? [])) <td>{{ $student->gender ?? '-' }}</td> @endif
                    @if(in_array('dateofbirth', $columns ?? [])) <td>{{ $student->dateofbirth ? $student->dateofbirth->format('d/m/Y') : '-' }}</td> @endif
                    @if(in_array('age', $columns ?? [])) <td>{{ $student->age ?? '-' }}</td> @endif
                    @if(in_array('class', $columns ?? []))
                        <td>
                            {{ $student->currentClass?->schoolclass?->schoolclass ?? '-' }}
                            {{ $student->currentClass?->schoolclass?->armRelation?->arm ? ' - ' . $student->currentClass->schoolclass->armRelation->arm : '' }}
                        </td>
                    @endif
                    @if(in_array('status', $columns ?? [])) <td>{{ $student->student_status ?? '-' }}</td> @endif
                    @if(in_array('admission_date', $columns ?? [])) <td>{{ $student->admission_date ? $student->admission_date->format('d/m/Y') : '-' }}</td> @endif
                    @if(in_array('phone_number', $columns ?? [])) <td>{{ $student->phone_number ?? '-' }}</td> @endif
                    @if(in_array('state', $columns ?? [])) <td>{{ $student->state ?? '-' }}</td> @endif
                    @if(in_array('local', $columns ?? [])) <td>{{ $student->local ?? '-' }}</td> @endif
                    @if(in_array('religion', $columns ?? [])) <td>{{ $student->religion ?? '-' }}</td> @endif
                    @if(in_array('blood_group', $columns ?? [])) <td>{{ $student->blood_group ?? '-' }}</td> @endif
                    @if(in_array('father_name', $columns ?? [])) <td>{{ $student->parent?->father ?? '-' }}</td> @endif
                    @if(in_array('mother_name', $columns ?? [])) <td>{{ $student->parent?->mother ?? '-' }}</td> @endif
                    @if(in_array('guardian_phone', $columns ?? []))
                        <td>{{ $student->parent ? ($student->parent->father_phone ?: $student->parent->mother_phone ?: '-') : '-' }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns ?? []) }}" class="text-center py-5 text-muted">
                        No students found matching the selected criteria.
                    </td>
                </tr>
            @endforelse

            <!-- Totals Row -->
            <tr class="totals-row">
                <td colspan="{{ in_array('photo', $columns ?? []) ? 2 : 1 }}" class="text-end fw-bold">Totals:</td>
                <td class="fw-bold">{{ $students->count() }}</td>
                <td class="fw-bold">{{ $males ?? 0 }}</td>
                <td class="fw-bold">{{ $females ?? 0 }}</td>
                <td colspan="{{ count($columns ?? []) - 5 }}" class="text-start fw-bold">Students ({{ $total ?? 0 }})</td>
            </tr>
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        © {{ date('Y') }} {{ $school ? $school->school_name : config('app.name') }} • Confidential Document
        <br>
        Page <span class="pageNumber"></span> of <span class="totalPages"></span>
    </div>

    <!-- Page numbering script -->
    <script type="text/php">
        if (isset($pdf)) {
            $font = Font_Metrics::get_font("helvetica", "normal");
            $pdf->page_script('
                if ($PAGE_COUNT > 1) {
                    $text = "Page " . $PAGE_NUM . " of " . $PAGE_COUNT;
                    $width = Font_Metrics::get_text_width($text, $font, 9.5);
                    $pdf->text(520 - $width, 765, $text, $font, 9.5, array(0.4, 0.4, 0.4));
                }
            ');
        }
    </script>

</body>
</html>
