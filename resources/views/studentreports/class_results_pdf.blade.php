<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Result Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            padding: 20px;
        }

        /* Header Styles */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }

        .school-info {
            text-align: center;
            flex: 1;
        }

        .school-name {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .school-motto {
            font-size: 11px;
            font-style: italic;
        }

        .school-address {
            font-size: 10px;
        }

        .logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }

        /* Report Title */
        .report-title {
            text-align: center;
            margin: 15px 0;
            font-size: 14px;
            font-weight: bold;
            text-decoration: underline;
        }

        /* Student Info Table */
        .student-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }

        .student-info td {
            padding: 4px 8px;
            border: 1px solid #000;
        }

        .student-info td.label {
            font-weight: bold;
            width: 180px;
            background-color: #f5f5f5;
        }

        /* Results Table */
        .results-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 20px;
        }

        .results-table th,
        .results-table td {
            border: 1px solid #000;
            padding: 5px 3px;
            vertical-align: middle;
        }

        .results-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .results-table td {
            text-align: center;
        }

        .results-table td.subject-name {
            text-align: left;
            font-weight: 500;
        }

        .fraction {
            display: inline-block;
            text-align: center;
            vertical-align: middle;
            line-height: 1.2;
        }

        .fraction .numerator {
            border-bottom: 1px solid #000;
            padding: 0 2px;
        }

        .fraction .denominator {
            padding: 0 2px;
        }

        /* Color Classes */
        .highlight-red {
            background-color: #ffcccc;
            color: #cc0000;
            font-weight: bold;
        }

        .abs-text {
            color: #ff6600;
            font-style: italic;
        }

        .zero-text {
            color: #999999;
        }

        /* Remarks Section */
        .remarks-section {
            margin-top: 20px;
        }

        .remarks-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .remarks-table td {
            padding: 8px;
            border: 1px solid #000;
            vertical-align: top;
        }

        .remarks-table td.label {
            font-weight: bold;
            width: 180px;
            background-color: #f5f5f5;
        }

        .signature-line {
            display: inline-block;
            width: 150px;
            border-bottom: 1px solid #000;
            margin-left: 10px;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }

        /* Utilities */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .mb-10 {
            margin-bottom: 10px;
        }

        .mt-20 {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    @php
        $isFirstTerm = ($metadata['term_id'] ?? $termid ?? 1) == 1;
        $student = $students->first();
    @endphp

    {{-- Header --}}
    <div class="header">
        @if(isset($school_logo_path) && $school_logo_path)
            <img src="{{ $school_logo_path }}" class="logo" alt="School Logo">
        @endif
        <div class="school-info">
            <div class="school-name">{{ $schoolInfo->school_name ?? 'TOPCLASS COLLEGE' }}</div>
            <div class="school-motto">{{ $schoolInfo->school_motto ?? 'Developing the total child' }}</div>
            <div class="school-address">{{ $schoolInfo->school_address ?? '39, Okegbala Street, Ondo.' }}</div>
            <div class="school-contact">Phone: {{ $schoolInfo->school_phone ?? '+234806 770 6684' }} | Email: {{ $schoolInfo->school_email ?? 'info@topclasscollege.ng' }}</div>
        </div>
        @if(isset($student_image_path) && $student_image_path)
            <img src="{{ $student_image_path }}" class="logo" alt="Student Photo">
        @else
            <div style="width:80px;"></div>
        @endif
    </div>

    {{-- Report Title --}}
    <div class="report-title">
        {{ $metadata['term'] ?? $schoolterm ?? 'FIRST' }} TERM {{ $metadata['session'] ?? $schoolsession ?? '' }} ACADEMIC SESSION TERMINAL PROGRESS REPORT
    </div>

    {{-- Student Information --}}
    <table class="student-info">
        <tr>
            <td class="label">Name:</td>
            <td>{{ $student->lastname ?? '' }} {{ $student->fname ?? '' }} {{ $student->othername ?? '' }}</td>
            <td class="label">Session:</td>
            <td>{{ $metadata['session'] ?? $schoolsession ?? '' }}</td>
            <td class="label">Term:</td>
            <td>{{ $metadata['term'] ?? $schoolterm ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Class:</td>
            <td>{{ $schoolclass->schoolclass ?? '' }} {{ $schoolclass->armRelation->arm ?? '' }}</td>
            <td class="label">DOB:</td>
            <td>{{ $student->dateofbirth ?? '' }}</td>
            <td class="label">Adm No:</td>
            <td>{{ $student->admissionNo ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Sex:</td>
            <td>{{ $student->gender ?? '' }}</td>
            <td class="label">Date School Opened:</td>
            <td>5th January, 2026</td>
            <td class="label">Times School Opened:</td>
            <td>102</td>
        </tr>
        <tr>
            <td class="label">Students in Class:</td>
            <td colspan="5">{{ $numberOfStudents ?? 0 }}</td>
        </tr>
    </table>

    {{-- Results Table --}}
    <table class="results-table">
        <thead>
            {{-- First header row (letter labels) --}}
            <tr>
                <th rowspan="2">S/N</th>
                <th rowspan="2">Subjects</th>
                <th colspan="3">Continuous Assessment</th>
                <th rowspan="2">d</th>
                <th rowspan="2">e</th>
                <th rowspan="2">f</th>
                @if(!$isFirstTerm)
                    <th rowspan="2">g</th>
                    <th rowspan="2">h</th>
                @endif
                <th rowspan="2">{{ $isFirstTerm ? 'g' : 'i' }}</th>
                <th rowspan="2">{{ $isFirstTerm ? 'h' : 'j' }}</th>
                <th rowspan="2">{{ $isFirstTerm ? 'i' : 'k' }}</th>
            </tr>
            <tr>
                <th>a</th>
                <th>b</th>
                <th>c</th>
            </tr>

            {{-- Second header row (descriptive labels) --}}
            <tr>
                <th></th>
                <th></th>
                <th>T1</th>
                <th>T2</th>
                <th>T3</th>
                <th>
                    <div class="fraction">
                        <div class="numerator">a+b+c</div>
                        <div class="denominator">3</div>
                    </div>
                </th>
                <th>Term Exams</th>
                <th>
                    <div class="fraction">
                        <div class="numerator">d+e</div>
                        <div class="denominator">2</div>
                    </div>
                </th>
                @if(!$isFirstTerm)
                    <th>B/F</th>
                    <th>
                        <div class="fraction">
                            <div class="numerator">f+g</div>
                            <div class="denominator">2</div>
                        </div>
                    </th>
                @endif
                <th>Grade</th>
                <th>PSN</th>
                <th>Class Avg</th>
            </tr>
        </thead>
        <tbody>
            @forelse($scores as $index => $score)
                <tr>
                    {{-- S/N --}}
                    <td class="text-center">{{ $index + 1 }}</td>

                    {{-- Subject Name --}}
                    <td class="subject-name">{{ $score->subject_name ?? 'N/A' }}</td>

                    {{-- Column a: T1 (CA1) --}}
                    <td class="@if($score->ca1_display == 'ABS') abs-text @elseif(is_numeric($score->ca1_display) && $score->ca1_display < 50) highlight-red @endif">
                        {{ $score->ca1_display ?? '-' }}
                    </td>

                    {{-- Column b: T2 (CA2) --}}
                    <td class="@if($score->ca2_display == 'ABS') abs-text @elseif(is_numeric($score->ca2_display) && $score->ca2_display < 50) highlight-red @endif">
                        {{ $score->ca2_display ?? '-' }}
                    </td>

                    {{-- Column c: T3 (CA3) --}}
                    <td class="@if($score->ca3_display == 'ABS') abs-text @elseif(is_numeric($score->ca3_display) && $score->ca3_display < 50) highlight-red @endif">
                        {{ $score->ca3_display ?? '-' }}
                    </td>

                    {{-- Column d: Average of available CAs (a+b+c)/3 --}}
                    <td class="@if(is_numeric($score->ca_average) && $score->ca_average < 50) highlight-red @endif">
                        {{ $score->ca_average ?? '-' }}
                    </td>

                    {{-- Column e: Term Exams --}}
                    <td class="@if($score->exam_display == 'ABS') abs-text @elseif(is_numeric($score->exam_display) && $score->exam_display < 50) highlight-red @endif">
                        {{ $score->exam_display ?? '-' }}
                    </td>

                    {{-- Column f: (d+e)/2 --}}
                    <td class="@if(is_numeric($score->f_score) && $score->f_score < 50) highlight-red @endif">
                        {{ $score->f_score ?? '-' }}
                    </td>

                    {{-- Columns g & h: Only for Term 2 and Term 3 --}}
                    @if(!$isFirstTerm)
                        {{-- Column g: B/F (carryover from previous term) --}}
                        <td class="@if($score->bf_display == '0') zero-text @elseif($score->bf_display == 'ABS') abs-text @elseif(is_numeric($score->bf_display) && $score->bf_display < 50) highlight-red @endif">
                            {{ $score->bf_display ?? '-' }}
                        </td>

                        {{-- Column h: Cumulative = (f+g)/2 --}}
                        <td class="@if(is_numeric($score->cum_score) && $score->cum_score < 50) highlight-red @endif">
                            {{ $score->cum_score ?? '-' }}
                        </td>
                    @endif

                    {{-- Grade Column --}}
                    <td class="@if(in_array($score->grade ?? '', ['F', 'F9', 'E', 'E8'])) highlight-red @endif">
                        {{ $score->grade ?? '-' }}
                    </td>

                    {{-- Position/PSN Column --}}
                    <td class="text-center">
                        {{ $score->position ?? '-' }}
                    </td>

                    {{-- Class Average Column --}}
                    <td class="text-center">
                        {{ $score->class_average ?? '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $isFirstTerm ? 11 : 13 }}" class="text-center">
                        No results found for this student.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Remarks Section --}}
    <div class="remarks-section">
        <table class="remarks-table">
            <tr>
                <td class="label">Class Teacher's Remark:</td>
                <td colspan="3">
                    {{ $studentpp->class_teacher_remark ?? 'NO INFO' }}
                    <span class="signature-line"></span> Signature/Date
                </td>
            </tr>
            <tr>
                <td class="label">Guidance Counselor's Remark:</td>
                <td colspan="3">
                    {{ $studentpp->guidance_counselor_remark ?? 'NO INFO' }}
                    <span class="signature-line"></span> Signature/Date
                </td>
            </tr>
            <tr>
                <td class="label">Principal's Remark & Promotion Status:</td>
                <td colspan="3">
                    {{ $studentpp->principalscomment ?? 'NO INFO' }}
                </td>
            </tr>
            @if($isFirstTerm || $isSecondTerm)
                <tr>
                    <td class="label">Promotion Status:</td>
                    <td colspan="3">
                        Not applicable for this term
                    </td>
                </tr>
            @else
                <tr>
                    <td class="label">Promotion Status:</td>
                    <td colspan="3">
                        <strong>{{ $promotionStatusValue ?? 'PENDING' }}</strong>
                    </td>
                </tr>
            @endif
        </table>
    </div>

    {{-- Footer --}}
    <div class="footer">
        Generated on {{ date('d-m-Y H:i:s') }} | Printed from TopClass College Portal
    </div>
</body>
</html>
