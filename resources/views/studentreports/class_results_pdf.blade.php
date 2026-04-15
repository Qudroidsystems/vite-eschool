<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Class Results - {{ $metadata['class_name'] }} - {{ $metadata['session'] }} - {{ $metadata['term'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            background: #fff;
            margin: 10mm 0 0 0;
            padding: 0;
            text-align: center;
        }

        .student-section {
            width: 190mm;
            max-height: 287mm;
            page-break-after: always;
            background: #ffffff;
            border: 3px double #000000;
            margin: 0 auto;
            padding: 12px;
            position: relative;
            overflow: hidden;
            text-align: left;
        }

        .student-section:last-child {
            page-break-after: avoid;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                text-align: center;
            }
            .student-section {
                width: 190mm;
                max-height: 287mm;
                margin: 0 auto;
                padding: 10mm;
                page-break-after: always;
                text-align: left;
            }
            .student-section:last-child {
                page-break-after: avoid;
            }
        }

        .fraction {
            display: inline-block;
            font-family: Arial, sans-serif;
            font-size: 8px;
            text-align: center;
            font-weight: bold;
        }

        .fraction .numerator {
            border-bottom: 2px solid #333;
            padding: 0 3px;
            display: block;
        }

        .fraction .denominator {
            padding-top: 3px;
            display: block;
        }

        .school-name2 {
            font-size: 22px;
            font-weight: 900;
            color: #000000;
            text-align: left;
            margin: 1px 0;
            line-height: 1.2;
        }

        .school-logo {
            width: 100px;
            height: 70px;
            border: 0px solid #1e40af;
            border-radius: 1px;
            overflow: hidden;
            text-align: center;
        }

        .header-divider {
            width: 100%;
            height: 2px;
            background: #1e40af;
            margin: 4px 0;
        }

        .header-divider2 {
            width: 100%;
            height: 1px;
            background: #64748b;
            margin: 2px 0;
        }

        .report-title {
            background: #111827;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 700;
            text-align: center;
            margin: 8px 0;
        }

        .header-table {
            width: 100%;
            table-layout: fixed;
        }

        .header-table td {
            vertical-align: middle;
            padding: 0;
        }

        .header-img {
            width: 100%;
            height: 100%;
            border-radius: 1px;
        }

        .photo-frame {
            border: 3px solid #090909;
            border-radius: 8px;
            overflow: hidden;
            background: white;
            padding: 2px;
            width: 80px;
            height: 100px;
            margin: 0 auto;
            text-align: center;
        }

        .photo-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .student-info-section {
            margin-bottom: 4px;
        }

        .result-details {
            font-size: 10px;
            font-weight: 800;
            color: #000000;
        }

        .info-value {
            font-size: 11px;
            font-weight: 900;
            color: #000000;
        }

        .info-row {
            margin-bottom: 2px;
            line-height: 1.2;
        }

        .result-table table {
            width: 100%;
            border: 2px solid #000000;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .result-table thead th {
            background: #0d1a3d;
            color: white;
            font-weight: 800;
            border: 1px solid #000000;
            padding: 6px 3px;
            text-align: center;
            font-size: 8px;
        }

        .result-table tbody td {
            border: 1px solid #000000;
            padding: 4px 3px;
            text-align: center;
            font-size: 10px;
            background: white;
            font-weight: 900;
        }

        .result-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .result-table tbody td.subject-name {
            text-align: left !important;
            font-weight: 600;
        }

        .highlight-red {
            color: #dc2626 !important;
            font-weight: 900;
        }

        .abs-text {
            color: #f59e0b !important;
            font-weight: bold;
        }

        .zero-text {
            color: #dc2626 !important;
            font-weight: bold;
        }

        .remarks-table {
            width: 100%;
            border: 2px solid #000000;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .remarks-table td {
            border: 1px solid #000000;
            padding: 8px;
            background: white;
            vertical-align: top;
        }

        .remarks-table .h6 {
            color: #050505;
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 12px;
        }

        .full-width-dots {
            display: block;
            width: 100%;
            border-bottom: 1px dotted #666;
            min-height: 18px;
            padding: 4px 0 8px 0;
            font-weight: bold;
            font-size: 12px;
            line-height: 1.5;
            word-wrap: break-word;
        }

        .promotion-status {
            font-weight: 900 !important;
            margin-left: 5px;
            font-size: 10px !important;
        }

        .promotion-status.promotion-promoted {
            color: #1e40af !important;
        }

        .promotion-status.promotion-repeat {
            color: #dc2626 !important;
        }

        .promotion-status.promotion-parents {
            color: #dc2626 !important;
        }

        .promotion-status.promotion-default {
            color: #6b7280 !important;
        }

        .footer-section {
            background: #f1f5f9;
            border-radius: 6px;
            padding: 8px;
            border: 1px solid #cbd5e1;
            text-align: center;
            margin-top: 6px;
        }

        .text-dot-space2 {
            border-bottom: 1px dotted #666;
            display: inline-block;
            min-height: 14px;
            font-weight: bold;
            font-size: 12px;
            width: 150px;
        }

        .font-bold {
            font-weight: 900;
        }

        .text-primary {
            color: #02175e;
        }

        .powered-by {
            font-size: 12px;
            color: #000000;
            font-weight: 700;
            margin-top: 6px;
        }
    </style>
</head>
<body>
    @foreach ($allStudentData as $index => $studentData)
        <div class="student-section">
            <div class="student-section-inner">
                @php
                    $schoolInfo = $studentData['schoolInfo'] ?? null;
                    $student = $studentData['students'] && $studentData['students']->isNotEmpty() ? $studentData['students']->first() : null;
                    $profile = $studentData['studentpp'] && $studentData['studentpp']->isNotEmpty() ? $studentData['studentpp']->first() : null;
                @endphp

                <!-- Header -->
                <table class="header-table">
                    <tr>
                        <td width="25%">
                            <div class="school-logo">
                                <img class="header-img" src="{{ $studentData['school_logo_path'] ?? public_path('storage/school_logos/default.jpg') }}" alt="School Logo">
                            </div>
                        </td>
                        <td width="50%">
                            <div class="info-row"><p class="school-name2">{{ $schoolInfo->school_name ?? 'TOPCLASS COLLEGE' }}</p></div>
                            <div class="info-row"><span class="result-details">Motto:</span> <span class="info-value font-bold">{{ $schoolInfo->school_motto ?? 'Developing the total child' }}</span></div>
                            <div class="info-row"><span class="result-details">Address:</span> <span class="info-value font-bold">{{ $schoolInfo->school_address ?? '39, Okegbala Street, Ondo.' }}</span></div>
                            <div class="info-row"><span class="result-details">Phone:</span> <span class="info-value font-bold">{{ $schoolInfo->school_phone ?? '+234806 770 6684' }}</span></div>
                        </td>
                        <td width="25%">
                            <div class="photo-frame">
                                @if ($student && $student->picture)
                                    <img src="{{ $studentData['student_image_path'] ?? public_path('storage/student_avatars/unnamed.jpg') }}" alt="Student Photo">
                                @else
                                    <img src="{{ public_path('storage/student_avatars/unnamed.jpg') }}" alt="Default Photo">
                                @endif
                            </div>
                        </td>
                    </tr>
                    <div class="header-divider"></div>
                    <div class="header-divider2"></div>
                    <div class="report-title">{{ strtoupper($metadata['term']) }} {{ strtoupper($metadata['session']) }} ACADEMIC SESSION TERMINAL PROGRESS REPORT</div>

                <!-- Student Info -->
                <div class="student-info-section">
                    <table style="width: 100%;">
                        <tr>
                            <td width="33%">
                                <div class="info-row"><span class="result-details">Name:</span> <span class="info-value font-bold">{{ strtoupper($student->lastname ?? '') }} {{ $student->fname ?? '' }} {{ $student->othername ?? '' }}</span></div>
                                <div class="info-row"><span class="result-details">Session:</span> <span class="info-value font-bold">{{ $studentData['schoolsession'] ?? 'N/A' }}</span></div>
                                <div class="info-row"><span class="result-details">Term:</span> <span class="info-value font-bold">{{ $studentData['schoolterm'] ?? 'N/A' }}</span></div>
                            </td>
                            <td width="33%">
                                <div class="info-row"><span class="result-details">Class:</span> <span class="info-value font-bold">{{ $studentData['schoolclass']->schoolclass ?? 'N/A' }} {{ $studentData['schoolclass']->armRelation->arm ?? '' }}</span></div>
                                <div class="info-row"><span class="result-details">DOB:</span> <span class="info-value font-bold">{{ $student->dateofbirth ?? 'N/A' }}</span></div>
                                <div class="info-row"><span class="result-details">Adm No:</span> <span class="info-value font-bold">{{ $student->admissionNo ?? 'N/A' }}</span></div>
                            </td>
                            <td width="33%">
                                <div class="info-row"><span class="result-details">Sex:</span> <span class="info-value font-bold">{{ $student->gender ?? 'N/A' }}</span></div>
                                <div class="info-row"><span class="result-details">Students in Class:</span> <span class="info-value font-bold">{{ $studentData['numberOfStudents'] ?? 'N/A' }}</span></div>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Results Table -->
                <div class="result-table">
                    <table>
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Subjects</th>
                                <th>T1</th>
                                <th>T2</th>
                                <th>T3</th>
                                <th>d<br><small>(a+b+c)/3</small></th>
                                <th>e<br><small>Exam</small></th>
                                <th>f<br><small>(d+e)/2</small></th>
                                <th>g<br><small>B/F</small></th>
                                <th>h<br><small>Cum<br>(f+g)/2</small></th>
                                <th>Grade</th>
                                <th>PSN</th>
                                <th>Class Avg</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($studentData['scores'] as $idx => $score)
                                @php
                                    // Get numeric values for calculations
                                    $numCa1 = $score->ca1_numeric ?? null;
                                    $numCa2 = $score->ca2_numeric ?? null;
                                    $numCa3 = $score->ca3_numeric ?? null;
                                    $numExam = $score->exam_numeric ?? null;
                                    $numBf = $score->bf_numeric ?? null;

                                    // Calculate column d (average of available CAs)
                                    $caValues = [];
                                    if ($numCa1 !== null) $caValues[] = $numCa1;
                                    if ($numCa2 !== null) $caValues[] = $numCa2;
                                    if ($numCa3 !== null) $caValues[] = $numCa3;
                                    $colD = count($caValues) > 0 ? round(array_sum($caValues) / count($caValues), 1) : null;

                                    // Calculate column f (average of d and exam)
                                    $fComponents = [];
                                    if ($colD !== null) $fComponents[] = $colD;
                                    if ($numExam !== null) $fComponents[] = $numExam;
                                    $colF = count($fComponents) > 0 ? round(array_sum($fComponents) / count($fComponents), 1) : null;

                                    // Calculate column h (average of f and bf)
                                    $hComponents = [];
                                    if ($colF !== null) $hComponents[] = $colF;
                                    if ($numBf !== null) $hComponents[] = $numBf;
                                    $colH = count($hComponents) > 0 ? round(array_sum($hComponents) / count($hComponents), 1) : null;

                                    // Determine grade
                                    $grade = '-';
                                    if ($colH !== null) {
                                        if ($colH >= 70) $grade = 'A';
                                        elseif ($colH >= 60) $grade = 'B';
                                        elseif ($colH >= 50) $grade = 'C';
                                        elseif ($colH >= 40) $grade = 'D';
                                        else $grade = 'F';
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td class="subject-name">{{ $score->subject_name ?? 'N/A' }}</td>
                                    <td class="@if($score->ca1_display == '0') zero-text @elseif($score->ca1_display == 'ABS') abs-text @elseif(is_numeric($score->ca1_display) && $score->ca1_display < 50) highlight-red @endif">{{ $score->ca1_display ?? '-' }}</td>
                                    <td class="@if($score->ca2_display == '0') zero-text @elseif($score->ca2_display == 'ABS') abs-text @elseif(is_numeric($score->ca2_display) && $score->ca2_display < 50) highlight-red @endif">{{ $score->ca2_display ?? '-' }}</td>
                                    <td class="@if($score->ca3_display == '0') zero-text @elseif($score->ca3_display == 'ABS') abs-text @elseif(is_numeric($score->ca3_display) && $score->ca3_display < 50) highlight-red @endif">{{ $score->ca3_display ?? '-' }}</td>
                                    <td class="@if($colD !== null && $colD < 50) highlight-red @endif">{{ $colD !== null ? $colD : '-' }}</td>
                                    <td class="@if($score->exam_display == '0') zero-text @elseif($score->exam_display == 'ABS') abs-text @elseif(is_numeric($score->exam_display) && $score->exam_display < 50) highlight-red @endif">{{ $score->exam_display ?? '-' }}</td>
                                    <td class="@if($colF !== null && $colF < 50) highlight-red @endif">{{ $colF !== null ? $colF : '-' }}</td>
                                    <td class="@if($score->bf_display == '0') zero-text @elseif($score->bf_display == 'ABS') abs-text @elseif(is_numeric($score->bf_display) && $score->bf_display < 50) highlight-red @endif">{{ $score->bf_display ?? '-' }}</td>
                                    <td class="@if($colH !== null && $colH < 50) highlight-red @endif">{{ $colH !== null ? $colH : '-' }}</td>
                                    <td class="@if(in_array($grade, ['F', 'F9'])) highlight-red @endif">{{ $grade }}</td>
                                    <td>{{ $score->position ?? '-' }}</td>
                                    <td>{{ $score->class_average ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="13" class="text-center">No scores available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Remarks -->
                <table class="remarks-table">
                    <tbody>
                        <tr>
                            <td width="50%"><div class="h6">Class Teacher's Remark Signature/Date</div><div class="full-width-dots">{{ $profile ? ($profile->classteachercomment ?? 'N/A') : 'N/A' }}</div></td>
                            <td width="50%"><div class="h6">Guidance Counselor's Remark Signature/Date</div><div class="full-width-dots">{{ $profile ? ($profile->guidancescomment ?? 'N/A') : 'N/A' }}</div></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div class="h6">Principal's Remark & Promotion Status</div>
                                <div class="full-width-dots">
                                    {{ $profile ? ($profile->principalscomment ?? 'N/A') : 'N/A' }}
                                    @php
                                        $status = $studentData['promotionStatusValue'] ?? null;
                                        $statusUpper = strtoupper(trim($status ?? ''));
                                        $statusClass = 'promotion-default';
                                        if (str_contains($statusUpper, 'PROMOTED') && !str_contains($statusUpper, 'TRIAL')) $statusClass = 'promotion-promoted';
                                        elseif (str_contains($statusUpper, 'TRIAL')) $statusClass = 'promotion-repeat';
                                        elseif (str_contains($statusUpper, 'REPEAT')) $statusClass = 'promotion-repeat';
                                        elseif (str_contains($statusUpper, 'PRINCIPAL') || str_contains($statusUpper, 'PARENTS')) $statusClass = 'promotion-parents';
                                        $statusText = $status ?? 'Not applicable for this term';
                                    @endphp
                                    <br><br>
                                    <span class="promotion-status {{ $statusClass }}">PROMOTION STATUS: {{ $statusText }}</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Footer -->
                <div class="footer-section">
                    <div><span class="font-bold">This Result was issued on </span><span class="text-dot-space2">18th December, 2025</span><span class="font-bold"> and collected by</span><span>.......................................</span></div>
                    <div><span class="font-bold text-primary">Next Term Begins:</span><span class="text-dot-space2">........................</span></div>
                    <div class="powered-by">Powered by Qudroid Systems | www.qudroid.co | +2349057522004</div>
                </div>
            </div>
        </div>
    @endforeach
</body>
</html>
