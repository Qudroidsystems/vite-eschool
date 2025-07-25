<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Terminal Progress Report</title>
    <style>
        /* Reset for consistent rendering */
        * {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333333;
            background: #ffffff;
        }

        @page {
            size: A4;
            margin: 15mm;
        }

        .page {
            width: 100%;
            padding: 10px;
            page-break-after: always;
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
            margin: 0 auto 5px;
            background: #f8fafc;
            text-align: center;
            line-height: 80px;
            color: #1e40af;
            font-weight: bold;
            font-size: 10px;
        }

        .school-logo img {
            width: 100%;
            height: 100%;
            vertical-align: middle;
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
            width: 100%;
            margin-bottom: 10px;
            overflow: hidden; /* Clearfix for floats */
        }

        .student-details {
            float: left;
            width: 70%;
            padding-right: 10px;
        }

        .student-photo-container {
            float: left;
            width: 30%;
            height: 120px;
            border: 2px solid #1e40af;
            background: #f8fafc;
            text-align: center;
            line-height: 120px;
            color: #1e40af;
            font-size: 10px;
        }

        .student-photo-container img {
            width: 100%;
            height: 100%;
            vertical-align: middle;
        }

        .info-item {
            margin-bottom: 5px;
            overflow: hidden;
        }

        .info-label {
            font-weight: bold;
            color: #333333;
            font-size: 12px;
            display: inline-block;
            width: 120px;
        }

        .info-value {
            border-bottom: 1px dotted #666666;
            font-size: 12px;
            display: inline-block;
            width: calc(100% - 130px);
            min-height: 16px;
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

        .results-table td.subject-name {
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
            vertical-align: top;
        }

        .remarks-label {
            color: #6d28d9;
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        .remarks-content {
            border-bottom: 1px dotted #666666;
            display: block;
            min-height: 20px;
            padding-bottom: 2px;
        }

        /* Footer Section */
        .footer-section {
            background: #f1f5f9;
            padding: 5px;
            border: 1px solid #cccccc;
            font-size: 10px;
            overflow: hidden; /* Clearfix for floats */
        }

        .signature-row {
            width: 100%;
            overflow: hidden;
        }

        .signature-item {
            float: left;
            width: 50%;
        }

        /* Clearfix for floated elements */
        .clear {
            clear: both;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="content-wrapper">
            <!-- Header Section -->
            <div class="header-section">
                <div class="school-logo">
                    @if (!empty($data['school_logo_path']) && file_exists($data['school_logo_path']))
                        <img src="{{ $data['school_logo_path'] }}" alt="School Logo">
                    @else
                        LOGO
                    @endif
                </div>
                <div class="school-name">{{ $data['schoolInfo']->school_name ?? 'QUODOROID CODING ACADEMY' }}</div>
                <div class="school-motto">{{ $data['schoolInfo']->school_motto ?? 'Excellence in Education' }}</div>
                <div class="school-address">
                    {{ $data['schoolInfo']->school_address ?? '123 Education Street, Lagos, Nigeria' }}<br>
                    {{ $data['schoolInfo']->school_website ?? 'www.quodoroid.edu.ng' }}
                </div>
                <div class="header-divider"></div>
                <div class="report-title">TERMINAL PROGRESS REPORT</div>
            </div>

            <!-- Student Info Section -->
            <div class="student-info-section">
                <div class="student-details">
                    <div class="info-item">
                        <span class="info-label">Name of Student:</span>
                        <span class="info-value">
                            {{ $data['students']->first()->firstname ?? '' }} 
                            {{ $data['students']->first()->lastname ?? '' }} 
                            {{ $data['students']->first()->othername ?? '' }}
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Session:</span>
                        <span class="info-value">{{ $data['schoolsession'] ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Term:</span>
                        <span class="info-value">{{ $data['schoolterm'] ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Class:</span>
                        <span class="info-value">
                            {{ $data['schoolclass']->schoolclass ?? 'N/A' }} 
                            {{ $data['schoolclass']->armRelation->arm ?? '' }}
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Date of Birth:</span>
                        <span class="info-value">{{ $data['students']->first()->dateofbirth ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Admission No:</span>
                        <span class="info-value">{{ $data['students']->first()->admissionNo ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Sex:</span>
                        <span class="info-value">{{ $data['students']->first()->gender ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Students in Class:</span>
                        <span class="info-value">{{ $data['numberOfStudents'] ?? 'N/A' }}</span>
                    </div>
                </div>
                <div class="student-photo-container">
                    @if (!empty($data['student_image_path']) && file_exists($data['student_image_path']))
                        <img src="{{ $data['student_image_path'] }}" alt="Student Photo">
                    @else
                        Student Photo
                    @endif
                </div>
                <div class="clear"></div>
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
                        @if ($data['scores']->isNotEmpty())
                            @foreach ($data['scores'] as $index => $score)
                                <tr>
                                    <td>{{ $index }}</td>
                                    <td class="subject-name">{{ $data['score']}} $score->subject_name ?? 'N/A' }}</td>
                                    <td>{{ $data['score']['ca1'] ?? '-' }}</td>
                                    <td>{{ $data['score']['ca2'] ?? '-' }}</td>
                                    <td>{{ $data['score']['ca3'] ?? '-' }}</td>
                                    <td>{{ $data['score']['exam'] ?? '-' }}</td>
                                    <td class="{{ $score['total'] < 40 ? 'highlight-red' : '' }}">{{ $data['score']['total'] ?? '-' }}</td>
                                    <td class="{{ $score['grade'] == 'F' || $score['grade'] == 'F9' ? 'highlight-red' : '' }}">{{ $data['score']['grade'] ?? '-' }}</td>
                                    <td>{{ $data['score']['position'] ?? '-' }}</td>
                                    <td>{{ $data['score']['class_average'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="10">No scores available</td>
                            </tr>
                        @endif
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
                                <span class="remarks-content">{{ $data['studentpp']->first()->principalscomment ?? 'N/A' }}</span>
                            </td>
                            <td style="width: 50%;">
                                <span class="remarks-label">Promotion Status:</span>
                                <span class="remarks-content">
                                    @php
                                        $promotion = \App\Models\PromotionStatus::where([
                                            'studentId' => $data['studentid'],
                                            'schoolclassid' => $data['schoolclassid'],
                                            'sessionid' => $data['sessionid'],
                                            'termid' => $data['termid']
                                        ])->first();
                                    @endphp
                                    {{ $promotion->promotionStatus ?? 'N/A' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer Section -->
            <div class="footer-section">
                <div class="signature-row">
                    <div class="signature-item">
                        <span class="remarks-label">Principal's Signature:</span>
                        <span class="remarks-content">________________________</span>
                    </div>
                    <div class="signature-item">
                        <span class="remarks-label">Date:</span>
                        <span class="remarks-content">{{ now()->format('d/m/Y') }}</span>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>