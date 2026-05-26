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
            font-family: 'DejaVu Sans', 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }

        /* Main container */
        .report-container {
            max-width: 1280px;
            margin: 0 auto;
            background: white;
        }

        /* Header Section */
        .school-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1a3a5c;
            padding-bottom: 15px;
        }

        .school-name {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #1a3a5c;
            margin-bottom: 5px;
        }

        .school-motto {
            font-size: 12px;
            font-style: italic;
            color: #666;
        }

        .school-address {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }

        /* Report Title */
        .report-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 15px 0;
            text-transform: uppercase;
            color: #1a3a5c;
        }

        /* Student Info Section */
        .student-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }

        .student-info td {
            padding: 5px 8px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        .student-info td:first-child {
            font-weight: bold;
            width: 180px;
            background-color: #f9f9f9;
        }

        /* Results Table */
        .result-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }

        .result-table th,
        .result-table td {
            border: 1px solid #ddd;
            padding: 6px 4px;
            vertical-align: middle;
            text-align: center;
        }

        .result-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
            padding: 8px 4px;
        }

        .result-table td:first-child,
        .result-table th:first-child {
            width: 40px;
        }

        .result-table td:nth-child(2),
        .result-table th:nth-child(2) {
            text-align: left;
            min-width: 150px;
        }

        .fraction {
            display: inline-block;
            text-align: center;
            line-height: 1.3;
        }

        .fraction .numerator {
            border-bottom: 1px solid #000;
            padding: 0 3px;
        }

        .fraction .denominator {
            padding: 0 3px;
        }

        /* Score Highlighting */
        .highlight-red {
            background-color: #ffebee;
            color: #c62828;
            font-weight: bold;
        }

        .zero-text {
            color: #999;
            font-style: italic;
        }

        .abs-text {
            color: #ff9800;
            font-weight: bold;
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
            border: 1px solid #ddd;
            padding: 10px;
            vertical-align: top;
        }

        .remarks-table td:first-child {
            width: 200px;
            font-weight: bold;
            background-color: #f9f9f9;
        }

        /* Principal Section */
        .principal-section {
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }

        .principal-table {
            width: 100%;
            border-collapse: collapse;
        }

        .principal-table td {
            padding: 8px;
            vertical-align: top;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            margin-top: 30px;
            padding-top: 5px;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }

        /* Print Styles */
        @media print {
            body {
                padding: 0;
                margin: 0;
            }

            .result-table th {
                background-color: #f5f5f5 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .highlight-red {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-break {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    @php
        $isFirstTerm = ($metadata['term_id'] ?? $termid ?? 1) == 1;
        $isSecondOrThirdTerm = !$isFirstTerm;
        $student = $students->first();
    @endphp

    <div class="report-container">
        {{-- School Header --}}
        <div class="school-header">
            <div class="school-name">{{ $schoolInfo->school_name ?? 'TOPCLASS COLLEGE' }}</div>
            <div class="school-motto">{{ $schoolInfo->school_motto ?? 'Developing the total child' }}</div>
            <div class="school-address">{{ $schoolInfo->school_address ?? '39, Okegbala Street, Ondo.' }}</div>
            <div class="school-address">
                Phone: {{ $schoolInfo->school_phone ?? '+234806 770 6684' }} |
                Email: {{ $schoolInfo->school_email ?? 'info@topclasscollege.ng' }}
            </div>
        </div>

        {{-- Report Title --}}
        <div class="report-title">
            {{ $metadata['term'] ?? $schoolterm ?? 'Term' }} TERMINAL PROGRESS REPORT<br>
            {{ $metadata['session'] ?? $schoolsession ?? '2025/2026' }} ACADEMIC SESSION
        </div>

        {{-- Student Information --}}
        <table class="student-info">
            <tr>
                <td>Name:</td>
                <td>{{ $student->lastname ?? '' }} {{ $student->fname ?? '' }} {{ $student->othername ?? '' }}</td>
                <td>Session:</td>
                <td>{{ $metadata['session'] ?? $schoolsession ?? '-' }}</td>
            </tr>
            <tr>
                <td>Class:</td>
                <td>{{ $schoolclass->schoolclass ?? 'N/A' }} {{ $schoolclass->armRelation->arm ?? '' }}</td>
                <td>Term:</td>
                <td>{{ $metadata['term'] ?? $schoolterm ?? '-' }}</td>
            </tr>
            <tr>
                <td>DOB:</td>
                <td>{{ $student->dateofbirth ?? '-' }}</td>
                <td>Adm No:</td>
                <td>{{ $student->admissionNo ?? '-' }}</td>
            </tr>
            <tr>
                <td>Sex:</td>
                <td>{{ $student->gender ?? '-' }}</td>
                <td>Students in Class:</td>
                <td>{{ $numberOfStudents ?? 0 }}</td>
            </tr>
        </table>

        {{-- Results Table --}}
        <table class="result-table">
            <thead>
                {{-- First header row (letter labels) --}}
                <tr>
                    <th rowspan="2">S/N</th>
                    <th rowspan="2">Subjects</th>
                    <th colspan="3">Continuous Assessment</th>
                    <th rowspan="2">d</th>
                    <th rowspan="2">e</th>
                    <th rowspan="2">f</th>
                    @if($isSecondOrThirdTerm)
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
                    <th colspan="3" style="display: none;"></th>
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
                    @if($isSecondOrThirdTerm)
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
                    <td>{{ $index + 1 }}</td>
                    <td style="text-align: left;">{{ $score->subject_name ?? $score->subject ?? '-' }}</td>

                    {{-- Column a: T1 / CA1 --}}
                    <td class="@if(is_numeric($score->ca1_numeric) && $score->ca1_numeric < 50) highlight-red @endif">
                        {{ $score->ca1_display ?? '-' }}
                    </td>

                    {{-- Column b: T2 / CA2 --}}
                    <td class="@if(is_numeric($score->ca2_numeric) && $score->ca2_numeric < 50) highlight-red @endif">
                        {{ $score->ca2_display ?? '-' }}
                    </td>

                    {{-- Column c: T3 / CA3 --}}
                    <td class="@if(is_numeric($score->ca3_numeric) && $score->ca3_numeric < 50) highlight-red @endif">
                        {{ $score->ca3_display ?? '-' }}
                    </td>

                    {{-- Column d: CA Average = (a+b+c)/3 --}}
                    <td>{{ $score->ca_average ?? '-' }}</td>

                    {{-- Column e: Term Exams --}}
                    <td class="@if(is_numeric($score->exam_numeric) && $score->exam_numeric < 50) highlight-red @endif">
                        {{ $score->exam_display ?? '-' }}
                    </td>

                    {{-- Column f = (d+e)/2 --}}
                    <td class="@if(is_numeric($score->f_score) && $score->f_score < 50) highlight-red @endif">
                        {{ $score->f_score ?? '-' }}
                    </td>

                    @if($isSecondOrThirdTerm)
                        {{-- Column g = B/F (carryover from previous term) --}}
                        <td class="@if($score->bf_display == '0') zero-text @elseif($score->bf_display == 'ABS') abs-text @elseif(is_numeric($score->bf_display) && $score->bf_display < 50) highlight-red @endif">
                            {{ $score->bf_display ?? '-' }}
                        </td>

                        {{-- Column h = Cumulative = (f+g)/2 --}}
                        <td class="@if(is_numeric($score->cum_score) && $score->cum_score < 50) highlight-red @endif">
                            {{ $score->cum_score ?? '-' }}
                        </td>
                    @endif

                    {{-- Grade (based on f for Term 1, based on cum for Term 2/3) --}}
                    <td class="@if(in_array($score->grade ?? '', ['F', 'F9', 'E', 'E8'])) highlight-red @endif">
                        {{ $score->grade ?? '-' }}
                    </td>

                    {{-- Position (PSN) --}}
                    <td>{{ $score->position ?? '-' }}</td>

                    {{-- Class Average --}}
                    <td>{{ $score->class_average ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $isFirstTerm ? 11 : 13 }}" style="text-align: center;">
                        No subject records found for this student.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Teacher and Counselor Remarks --}}
        <table class="remarks-table">
            <tr>
                <td>Class Teacher's Remark:</td>
                <td>
                    {{ $studentpp->class_teacher_remark ?? 'No comment' }}
                    <div style="margin-top: 20px;">
                        Signature/Date: _______________________
                    </div>
                </td>
            </tr>
            <tr>
                <td>Guidance Counselor's Remark:</td>
                <td>
                    {{ $studentpp->guidance_counselor_remark ?? 'No comment' }}
                    <div style="margin-top: 20px;">
                        Signature/Date: _______________________
                    </div>
                </td>
            </tr>
        </table>

        {{-- Principal's Remark --}}
        <div class="principal-section">
            <table class="principal-table">
                <tr>
                    <td style="font-weight: bold; width: 180px;">Principal's Remark:</td>
                    <td>{{ $studentpp->principalscomment ?? 'No comment' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Promotion Status:</td>
                    <td>
                        <strong>{{ $promotionStatusValue ?? 'Not applicable for this term' }}</strong>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>
                        <div style="margin-top: 30px;">
                            <div style="float: right; text-align: center;">
                                <div class="signature-line"></div>
                                <div>Principal's Signature/Date</div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Footer --}}
        <div class="footer">
            Generated on {{ date('F j, Y g:i A') }}
        </div>
    </div>
</body>
</html>
