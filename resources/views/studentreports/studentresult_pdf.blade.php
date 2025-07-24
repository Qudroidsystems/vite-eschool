<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal Progress Report</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333333;
        }

        @page {
            size: A4;
            margin: 15mm;
        }

        .page {
            width: 100%;
            min-height: 100%;
            page-break-after: always;
            padding: 10px;
        }

        .page:last-child {
            page-break-after: avoid;
        }

        .content-wrapper {
            background: #ffffff;
            border: 1px solid #cccccc;
            padding: 10px;
        }

        /* Header Styles */
        .header-section {
            text-align: center;
            margin-bottom: 10px;
        }

        .school-logo {
            width: 80px;
            height: 80px;
            border: 2px solid #1e40af;
            display: block;
            margin: 0 auto;
        }

        .school-name {
            font-size: 20px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }

        .school-motto, .school-address {
            font-size: 10px;
            color: #555555;
            margin-bottom: 3px;
        }

        .header-divider {
            width: 100%;
            height: 2px;
            background: #1e40af;
            margin: 5px 0;
        }

        .report-title {
            background: #374151;
            color: #ffffff;
            padding: 8px;
            font-size: 16px;
            font-weight: bold;
            margin: 5px 0;
        }

        /* Student Info Section */
        .student-info-section {
            display: flex;
            width: 100%;
            margin-bottom: 10px;
        }

        .student-details {
            flex: 0 0 70%;
            padding-right: 10px;
        }

        .student-photo-container {
            flex: 0 0 30%;
            height: 120px;
            border: 2px solid #1e40af;
            background: #ffffff;
        }

        .student-photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .info-item {
            margin-bottom: 5px;
        }

        .info-label {
            font-weight: bold;
            color: #333333;
            margin-right: 5px;
            font-size: 12px;
            display: inline-block;
            width: 120px;
        }

        .info-value {
            border-bottom: 1px dotted #666666;
            font-size: 12px;
            display: inline-block;
            width: calc(100% - 130px);
        }

        /* Results Table */
        .results-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #1e40af;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .results-table th {
            background: #1e40af;
            color: #ffffff;
            font-weight: bold;
            border: 1px solid #1e40af;
            padding: 5px;
            text-align: center;
            font-size: 10px;
        }

        .results-table td {
            border: 1px solid #cccccc;
            padding: 5px;
            text-align: center;
            font-size: 10px;
        }

        .results-table td:nth-child(2) {
            text-align: left !important;
            font-weight: bold;
        }

        .highlight-red {
            color: #cc0000 !important;
            font-weight: bold;
        }

        .highlight-bold {
            font-weight: bold;
        }

        /* Assessment Tables */
        .assessment-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #f59e0b;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .assessment-table th {
            background: #f59e0b;
            color: #ffffff;
            font-weight: bold;
            border: 1px solid #f59e0b;
            padding: 5px;
            text-align: center;
            font-size: 10px;
        }

        .assessment-table td {
            border: 1px solid #cccccc;
            padding: 5px;
            font-size: 10px;
        }

        /* Grading Scale */
        .grading-scale {
            background: #f59e0b;
            color: #ffffff;
            padding: 5px;
            margin-bottom: 10px;
            font-size: 10px;
            text-align: center;
            font-weight: bold;
        }

        /* Remarks Table */
        .remarks-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #7c3aed;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .remarks-table td {
            border: 1px solid #c4b5fd;
            padding: 5px;
            font-size: 10px;
        }

        .remarks-label {
            color: #6d28d9;
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        /* Footer Section */
        .footer-section {
            background: #f1f5f9;
            padding: 5px;
            border: 1px solid #cccccc;
            font-size: 10px;
        }

        .signature-row {
            display: flex;
            width: 100%;
        }

        .signature-item {
            flex: 0 0 50%;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .page {
                page-break-inside: avoid;
            }

            .results-table,
            .assessment-table,
            .remarks-table {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="content-wrapper">
            <!-- Header Section -->
            <div class="header-section">
                @php
                    $logoPath = public_path($data['schoolInfo']->getLogoUrlAttribute() ?? 'assets/tp.png');
                @endphp
                <img class="school-logo" src="{{ $logoPath }}" alt="School Logo" onerror="this.src='{{ public_path('assets/tp.png') }}';">
                <div class="school-name">{{ $data['schoolInfo']->school_name ?? 'QUODOROID CODING ACADEMY' }}</div>
                <div class="school-motto">{{ $data['schoolInfo']->school_motto ?? 'N/A' }}</div>
                <div class="school-address">
                    {{ $data['schoolInfo']->school_address ?? 'N/A' }}
                    @if ($data['schoolInfo']->school_website)
                        <br>{{ $data['schoolInfo']->school_website }}
                    @endif
                </div>
                <div class="header-divider"></div>
                <div class="report-title">TERMINAL PROGRESS REPORT</div>
            </div>

            <!-- Student Info Section -->
            <div class="student-info-section">
                <div class="student-details">
                    @if (!empty($data['students']) && $data['students']->isNotEmpty())
                        @php $student = $data['students']->first(); @endphp
                        <div class="info-item">
                            <span class="info-label">Name of Student:</span>
                            <span class="info-value">{{ $student->fname }} {{ $student->lastname }} {{ $student->othername ?? '' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Session:</span>
                            <span class="info-value">{{ $data['schoolsession'] }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Term:</span>
                            <span class="info-value">{{ $data['schoolterm'] }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Class:</span>
                            <span class="info-value">{{ $data['schoolclass']->schoolclass ?? 'N/A' }} {{ $data['schoolclass']->armRelation->arm ?? '' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Date of Birth:</span>
                            <span class="info-value">{{ $student->dateofbirth ? \Carbon\Carbon::parse($student->dateofbirth)->format('d/m/Y') : 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Admission No:</span>
                            <span class="info-value">{{ $student->admissionNo ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Sex:</span>
                            <span class="info-value">{{ $student->gender ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Students in Class:</span>
                            <span class="info-value">{{ $data['numberOfStudents'] ?? 'N/A' }}</span>
                        </div>
                    @else
                        <div class="info-item">
                            <span class="info-label">Error:</span>
                            <span class="info-value">No student data available for this report.</span>
                        </div>
                    @endif
                </div>
                <div class="student-photo-container">
                    @if (!empty($data['students']) && $data['students']->isNotEmpty() && $student->picture)
                        @php
                            $studentImagePath = public_path('storage/' . $student->picture);
                        @endphp
                        <img src="{{ $studentImagePath }}" alt="{{ $student->fname ?? 'Student' }}'s picture" onerror="this.src='{{ public_path('storage/student_avatars/unnamed.jpg') }}';">
                    @else
                        <img src="{{ public_path('storage/student_avatars/unnamed.jpg') }}" alt="Default Student Photo">
                    @endif
                </div>
            </div>

            <!-- Results Table -->
            <div class="results-section">
                <table class="results-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">S/N</th>
                            <th style="width: 20%;">Subjects</th>
                            <th style="width: 8%;">T1</th>
                            <th style="width: 8%;">T2</th>
                            <th style="width: 8%;">T3</th>
                            <th style="width: 8%;">Term Exams</th>
                            <th style="width: 8%;">Total</th>
                            <th style="width: 8%;">Grade</th>
                            <th style="width: 8%;">PSN</th>
                            <th style="width: 8%;">Class Avg</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data['scores'] as $scoreIndex => $score)
                            <tr>
                                <td>{{ $scoreIndex + 1 }}</td>
                                <td style="text-align: left;">{{ $score->subject_name }}</td>
                                <td @if ($score->ca1 <= 50 && is_numeric($score->ca1)) class="highlight-red" @endif>{{ $score->ca1 ?? '-' }}</td>
                                <td @if ($score->ca2 <= 50 && is_numeric($score->ca2)) class="highlight-red" @endif>{{ $score->ca2 ?? '-' }}</td>
                                <td @if ($score->ca3 <= 50 && is_numeric($score->ca3)) class="highlight-red" @endif>{{ $score->ca3 ?? '-' }}</td>
                                <td @if ($score->exam <= 50 && is_numeric($score->exam)) class="highlight-red" @endif>{{ $score->exam ?? '-' }}</td>
                                <td @if ($score->total <= 50 && is_numeric($score->total)) class="highlight-red" @endif>{{ $score->total ?? '-' }}</td>
                                <td @if (in_array($score->grade, ['F', 'F9', 'E', 'E8'])) class="highlight-red" @endif>{{ $score->grade ?? '-' }}</td>
                                <td>{{ $score->position ?? '-' }}</td>
                                <td>{{ $score->class_average ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10">No scores available for this student.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Grading Scale -->
            <div class="grading-scale">
                <strong>Academic Grading Scale:</strong> 80-100 (A), 70-79 (B), 60-69 (C), 50-59 (D), 40-49 (E), 0-39 (F) | 
                Senior: A1 (75-100), B2 (70-74), B3 (65-69), C4 (60-64), C5 (55-59), C6 (50-54), E8 (40-49), F9 (0-39)
            </div>

            <!-- Remarks Section -->
            <div class="remarks-section">
                <table class="remarks-table">
                    <tbody>
                        <tr>
                            <td style="width: 50%;">
                                <span class="remarks-label">Principal's Remark:</span>
                                <span style="border-bottom: 1px dotted #666666; display: block;">
                                    {{ $data['studentpp']->isNotEmpty() ? $data['studentpp']->first()->principalscomment ?? 'N/A' : 'N/A' }}
                                </span>
                            </td>
                            <td style="width: 50%;">
                                <span class="remarks-label">Promotion Status:</span>
                                <span style="border-bottom: 1px dotted #666666; display: block;">
                                    {{ \App\Models\PromotionStatus::where('studentId', $data['studentid'])->where('schoolclassid', $data['schoolclassid'])->where('sessionid', $data['sessionid'])->where('termid', $data['termid'])->first()->promotionStatus ?? 'N/A' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>