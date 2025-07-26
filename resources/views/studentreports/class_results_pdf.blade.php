<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Class Results - {{ $metadata['class_name'] }} - {{ $metadata['session'] }} - {{ $metadata['term'] }}</title>
    <style>
        /* Basic reset and font setup */
        * {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: #fff;
            margin: 10mm 0 0 0; /* Add top margin */
            padding: 0;
            display: block;
            text-align: center; /* Center all content */
        }

        .student-section {
            width: 190mm; /* Adjusted width for better balance */
            max-height: 287mm; /* Adjusted height accounting for top margin */
            page-break-after: always;
            background: #ffffff;
            border: 2px solid #1e40af;
            margin: 0 auto; /* Center horizontally */
            padding: 12px; /* Reduced padding to fit more content */
            position: relative;
            overflow: hidden; /* Prevent content overflow */
            text-align: left; /* Reset text alignment for content */
            display: block;
        }

        .student-section:last-child {
            page-break-after: avoid;
        }

        /* Print-specific styles */
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
                display: block;
            }
            
            .student-section:last-child {
                page-break-after: avoid;
            }
        }

        .fraction {
            display: inline-block;
            font-family: Arial, sans-serif;
            font-size: 9px; /* Slightly smaller */
            text-align: center;
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

        span.text-space-on-dots,
        span.text-dot-space2 {
            border-bottom: 1px dotted #666;
            display: inline-block;
            min-height: 14px; /* Reduced height */
        }

        span.text-space-on-dots {
            width: 250px; /* Reduced width */
        }

        span.text-dot-space2 {
            width: 150px; /* Reduced width */
        }

        .school-name1 {
            font-size: 24px; /* Reduced size */
            font-weight: 700;
            color: #1e3a8a;
            text-align: center;
        }

        .school-name2 {
            font-size: 18px; /* Reduced size */
            font-weight: 800;
            color: #1e40af;
            text-align: center;
            margin: 3px 0; /* Reduced margin */
        }

        .school-logo {
            width: 70px; /* Reduced size */
            height: 70px;
            border: 0px solid #1e40af;
            border-radius: 5px;
            overflow: hidden;
            margin: 0 auto 8px; /* Reduced margin */
            text-align: center;
        }

        .header-divider {
            width: 100%;
            height: 2px; /* Reduced height */
            background: #1e40af;
            margin: 4px 0; /* Reduced margin */
        }

        .header-divider2 {
            width: 100%;
            height: 1px; /* Reduced height */
            background: #64748b;
            margin: 2px 0; /* Reduced margin */
        }

        .report-title {
            background: #111827;
            color: white;
            padding: 8px 16px; /* Reduced padding */
            border-radius: 6px;
            font-size: 16px; /* Reduced size */
            font-weight: 700;
            text-align: center;
            margin: 8px 0; /* Reduced margin */
        }

        .header {
            text-align: center;
            margin-bottom: 10px; /* Reduced margin */
        }

        .header-img {
            width: 100%;
            height: 100%;
            border-radius: 35px;
        }

        .school-motto, .school-address, .school-website {
            font-size: 10px; /* Reduced size */
            color: #6b7280;
            margin: 2px 0; /* Reduced margin */
        }

        .student-info-section {
            margin-bottom: 10px; /* Reduced margin */
        }

        .result-details {
            font-size: 11px; /* Reduced size */
            font-weight: 700;
            color: #374151;
        }

        .rd1, .rd2, .rd3, .rd4, .rd5, .rd6, .rd7, .rd8, .rd9, .rd10 {
            border-bottom: 2px dotted #6b7280;
            margin-left: 6px;
            min-width: 80px; /* Reduced width */
            display: inline-block;
            font-weight: 700;
            padding-bottom: 2px;
            font-size: 11px;
        }

        .photo-frame {
            border: 3px solid #1e40af; /* Changed to blue border */
            border-radius: 8px;
            overflow: hidden;
            background: white;
            padding: 2px; /* Reduced padding */
            width: 80px; /* Reduced size */
            height: 100px;
            margin: 0 auto;
            text-align: center;
        }

        .photo-frame img {
            width: 100%;
            height: 100%;
        }

        .result-table table {
            width: 100%;
            border: 2px solid #1e40af;
            border-collapse: collapse;
            margin-bottom: 8px; /* Reduced margin */
        }

        .result-table thead th {
            background: #243f99;
            color: white;
            font-weight: 600;
            border: 1px solid #1d4ed8;
            padding: 6px 3px; /* Reduced padding */
            text-align: center;
            font-size: 9px; /* Reduced size */
        }

        .result-table tbody td {
            border: 1px solid #cbd5e1;
            padding: 4px 3px; /* Reduced padding */
            text-align: center;
            font-size: 9px; /* Reduced size */
            background: white;
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
            font-weight: bold;
        }

        .highlight-bold {
            font-weight: 700 !important;
        }

        .assessment-table {
            width: 100%;
            border: 2px solid #cbda77;
            border-collapse: collapse;
            margin-bottom: 6px; /* Reduced margin */
        }

        .assessment-table thead th {
            background: #fbbf24;
            color: white;
            font-weight: 600;
            border: 1px solid #047857;
            padding: 4px; /* Reduced padding */
            text-align: center;
            font-size: 9px; /* Reduced size */
        }

        .assessment-table tbody td {
            border: 1px solid #d1d5db;
            padding: 3px 4px; /* Reduced padding */
            background: white;
            font-size: 8px; /* Reduced size */
        }

        .assessment-table tbody tr:nth-child(even) td {
            background: #f0fdf4;
        }

        .grade-display {
            background: #fbbf24;
            color: white;
            border-radius: 10px;
            padding: 6px; /* Reduced padding */
            text-align: center;
            margin-bottom: 8px; /* Reduced margin */
        }

        .grade-display span {
            font-size: 10px; /* Reduced size */
            font-weight: 600;
            margin: 0 4px; /* Reduced margin */
        }

        .remarks-table {
            width: 100%;
            border: 2px solid #7c3aed;
            border-collapse: collapse;
            margin-bottom: 8px; /* Reduced margin */
        }

        .remarks-table td {
            border: 1px solid #c4b5fd;
            padding: 6px; /* Reduced padding */
            background: white;
            vertical-align: top;
        }

        .remarks-table .h6 {
            color: #6d28d9;
            font-weight: 600;
            margin-bottom: 4px; /* Reduced margin */
            font-size: 9px; /* Reduced size */
        }

        .footer-section {
            background: #f1f5f9;
            border-radius: 6px;
            padding: 8px; /* Reduced padding */
            border: 1px solid #cbd5e1;
            text-align: center;
            margin-top: 6px; /* Reduced margin */
        }

        .h5 {
            font-size: 10px; /* Reduced size */
            font-weight: bold;
            margin-bottom: 4px; /* Reduced margin */
            color: #047857;
        }

        .student-info-table {
            width: 100%;
            margin-bottom: 10px; /* Reduced margin */
            table-layout: fixed; /* Ensure consistent column widths */
        }

        .student-info-table td {
            padding: 3px; /* Reduced padding */
            vertical-align: top;
        }

        .assessment-layout-table {
            width: 100%;
            margin-bottom: 8px; /* Reduced margin */
            table-layout: fixed; /* Ensure equal column distribution */
        }

        .assessment-layout-table td {
            width: 50%; /* Exact 50% for each column */
            vertical-align: top;
            padding: 0 1%;
        }

        .footer-layout-table {
            width: 100%;
        }

        .footer-layout-table td {
            padding: 3px; /* Reduced padding */
            text-align: center;
        }

        .info-row {
            margin-bottom: 8px; /* Increased margin for better spacing */
            line-height: 1.6; /* Increased line height */
        }

        .info-row .result-details {
            margin-right: 8px;
        }

        .info-row .rd1, .info-row .rd2, .info-row .rd3, .info-row .rd4, 
        .info-row .rd5, .info-row .rd6, .info-row .rd7, .info-row .rd8, 
        .info-row .rd9, .info-row .rd10 {
            margin-right: 20px; /* Add space between elements */
        }

        .info-row.students-count {
            margin-top: 8px; /* Add space before "No. of Students in Class" */
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .text-primary {
            color: #1e40af;
        }

        .student-section-inner {
            width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    @foreach ($allStudentData as $index => $studentData)
        <div class="student-section">
            <div class="student-section-inner">
                <!-- Header Section -->
                <div class="header">
                    @php
                        $schoolInfo = $studentData['schoolInfo'];
                    @endphp
                    <div class="school-logo">
                        <img class="header-img" src="{{ $studentData['school_logo_path'] ?? public_path('storage/school_logos/default.jpg') }}" alt="School Logo">
                    </div>
                    <p class="school-name2">{{ $schoolInfo->school_name ?? 'QUODOROID CODING ACADEMY' }}</p>
                    <div class="school-motto">{{ $schoolInfo->school_motto ?? 'N/A' }}</div>
                    <div class="school-address">{{ $schoolInfo->school_address ?? 'N/A' }}</div>
                    @if ($schoolInfo->school_website)
                        <div class="school-website">{{ $schoolInfo->school_website }}</div>
                    @endif
                    <div class="header-divider"></div>
                    <div class="header-divider2"></div>
                    <div class="report-title">TERMINAL PROGRESS REPORT</div>
                </div>

                <!-- Student Information Section -->
                <div class="student-info-section">
                    <table class="student-info-table">
                        <tr>
                            <td width="75%">
                                @if ($studentData['students']->isNotEmpty())
                                    @php $student = $studentData['students']->first(); @endphp
                                    <div class="info-row">
                                        <span class="result-details">Name of Student:</span>
                                        <span class="rd1">{{ $student->fname }} {{ $student->lastname }} {{ $student->othername ?? '' }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="result-details">Session:</span>
                                        <span class="rd2">{{ $studentData['schoolsession'] }}</span>
                                        <span class="result-details">Term:</span>
                                        <span class="rd3">{{ $studentData['schoolterm'] }}</span>
                                        <span class="result-details">Class:</span>
                                        <span class="rd4">{{ $studentData['schoolclass']->schoolclass ?? 'N/A' }} {{ $studentData['schoolclass']->armRelation->arm ?? '' }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="result-details">Date of Birth:</span>
                                        <span class="rd5">
                                            @php
                                                $dob = $student->dateofbirth;
                                                $formattedDob = 'N/A';
                                                
                                                if ($dob) {
                                                    try {
                                                        // Check if it's a numeric value (Excel date serial)
                                                        if (is_numeric($dob)) {
                                                            // Convert Excel date serial to Unix timestamp
                                                            // Excel epoch starts at 1900-01-01, but has a leap year bug
                                                            $unixTimestamp = ($dob - 25569) * 86400;
                                                            $formattedDob = date('d/m/Y', $unixTimestamp);
                                                        } else {
                                                            // Try to parse as regular date
                                                            $formattedDob = \Carbon\Carbon::parse($dob)->format('d/m/Y');
                                                        }
                                                    } catch (\Exception $e) {
                                                        // If all parsing fails, just display the raw value
                                                        $formattedDob = $dob;
                                                    }
                                                }
                                            @endphp
                                            {{ $formattedDob }}
                                        </span>
                                        <span class="result-details">Admission No:</span>
                                        <span class="rd6">{{ $student->admissionNo ?? 'N/A' }}</span>
                                        <span class="result-details">Sex:</span>
                                        <span class="rd7">{{ $student->gender ?? 'N/A' }}</span>
                                    </div>
                                    <div class="info-row">
                                        @if ($studentData['studentpp']->isNotEmpty())
                                            @php $profile = $studentData['studentpp']->first(); @endphp
                                            <span class="result-details">No. of Times School Opened:</span>
                                            <span class="rd8">{{ $profile->attendance ?? 'N/A' }}</span>
                                            <span class="result-details">No. of Times School Absent:</span>
                                            <span class="rd9">{{ $profile->attendance ? ($profile->attendance - ($profile->attendance ?? 0)) : 'N/A' }}</span>
                                        @else
                                            <span class="result-details">No. of Times School Opened:</span>
                                            <span class="rd8">N/A</span>
                                            <span class="result-details">No. of Times School Absent:</span>
                                            <span class="rd9">N/A</span>
                                        @endif
                                    </div>
                                    <div class="info-row students-count">
                                        <span class="result-details">No. of Students in Class:</span>
                                        <span class="rd10">{{ $studentData['numberOfStudents'] ?? 'N/A' }}</span>
                                    </div>
                                @else
                                    <div class="info-row">
                                        <span class="result-details">No student data available.</span>
                                    </div>
                                @endif
                            </td>
                            <td width="25%">
                                <div class="photo-frame">
                                    @if ($studentData['students']->isNotEmpty() && $student->picture)
                                        <img src="{{ $studentData['student_image_path'] ?? public_path('storage/student_avatars/unnamed.jpg') }}" alt="{{ $student->fname }}'s picture">
                                    @else
                                        <img src="{{ public_path('storage/student_avatars/unnamed.jpg') }}" alt="Default Photo">
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Results Table -->
                <div class="result-table">
                    <table>
                        <thead>
                            <tr>
                                <th></th>
                                <th>Subjects</th>
                                <th>a</th>
                                <th>b</th>
                                <th>c</th>
                                <th>d</th>
                                <th>e</th>
                                <th>f</th>
                                <th>g</th>
                                <th>h</th>
                                <th>i</th>
                                <th>j</th>
                                <th>k</th>
                            </tr>
                            <tr>
                                <th>S/N</th>
                                <th>Subjects</th>
                                <th>T1</th>
                                <th>T2</th>
                                <th>T3</th>
                                <th>
                                    <div class="fraction">
                                        <div class="numerator">a + b + c</div>
                                        <div class="denominator">3</div>
                                    </div>
                                </th>
                                <th>Term Exams</th>
                                <th>
                                    <div class="fraction">
                                        <div class="numerator">d + f</div>
                                        <div class="denominator">2</div>
                                    </div>
                                </th>
                                <th>B/F</th>
                                <th>Cum (f/g)/2</th>
                                <th>Grade</th>
                                <th>PSN</th>
                                <th>Class Average</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($studentData['scores'] as $index => $score)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="subject-name">{{ $score->subject_name }}</td>
                                    <td class="@if ($score->ca1 <= 50 && is_numeric($score->ca1)) highlight-red @elseif ($score->ca1 > 50 && is_numeric($score->ca1)) highlight-bold @endif">{{ $score->ca1 ?? '-' }}</td>
                                    <td class="@if ($score->ca2 <= 50 && is_numeric($score->ca2)) highlight-red @elseif ($score->ca2 > 50 && is_numeric($score->ca2)) highlight-bold @endif">{{ $score->ca2 ?? '-' }}</td>
                                    <td class="@if ($score->ca3 <= 50 && is_numeric($score->ca3)) highlight-red @elseif ($score->ca3 > 50 && is_numeric($score->ca3)) highlight-bold @endif">{{ $score->ca3 ?? '-' }}</td>
                                    <td class="@if ($score->ca1 && $score->ca2 && $score->ca3 && round(($score->ca1 + $score->ca2 + $score->ca3) / 3, 1) <= 50) highlight-red @elseif ($score->ca1 && $score->ca2 && $score->ca3 && round(($score->ca1 + $score->ca2 + $score->ca3) / 3, 1) > 50) highlight-bold @endif">
                                        {{ $score->ca1 && $score->ca2 && $score->ca3 ? round(($score->ca1 + $score->ca2 + $score->ca3) / 3, 1) : '-' }}
                                    </td>
                                    <td class="@if ($score->exam <= 50 && is_numeric($score->exam)) highlight-red @elseif ($score->exam > 50 && is_numeric($score->exam)) highlight-bold @endif">{{ $score->exam ?? '-' }}</td>
                                    <td class="@if ($score->total <= 50 && is_numeric($score->total)) highlight-red @elseif ($score->total > 50 && is_numeric($score->total)) highlight-bold @endif">{{ $score->total ?? '-' }}</td>
                                    <td class="@if ($score->bf <= 50 && is_numeric($score->bf)) highlight-red @elseif ($score->bf > 50 && is_numeric($score->bf)) highlight-bold @endif">{{ $score->bf ?? '-' }}</td>
                                    <td class="@if ($score->cum <= 50 && is_numeric($score->cum)) highlight-red @elseif ($score->cum > 50 && is_numeric($score->cum)) highlight-bold @endif">{{ $score->cum ?? '-' }}</td>
                                    <td class="@if (in_array($score->grade, ['F', 'F9', 'E', 'E8'])) highlight-red @elseif ($score->grade && !in_array($score->grade, ['F', 'F9', 'E', 'E8'])) highlight-bold @endif">{{ $score->grade ?? '-' }}</td>
                                    <td class="highlight-bold">{{ $score->position ?? '-' }}</td>
                                    <td class="highlight-bold">{{ $score->class_average ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13">No scores available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Assessment Tables Section -->
                <table class="assessment-layout-table">
                    <tr>
                        <td>
                            <div class="h5">Character Assessment</div>
                            <table class="assessment-table">
                                <thead>
                                    <tr>
                                        <th>Criteria</th>
                                        <th>Grade</th>
                                        <th>Sign</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($studentData['studentpp']->isNotEmpty())
                                        @php $profile = $studentData['studentpp']->first(); @endphp
                                        <tr><td>Class Attendance</td><td>{{ $profile->attendance ?? 'N/A' }}</td><td></td></tr>
                                        <tr><td>Attentiveness in Class</td><td>{{ $profile->attentiveness_in_class ?? 'N/A' }}</td><td></td></tr>
                                        <tr><td>Class Participation</td><td>{{ $profile->class_participation ?? 'N/A' }}</td><td></td></tr>
                                        <tr><td>Self Control</td><td>{{ $profile->selfcontrol ?? 'N/A' }}</td><td></td></tr>
                                        <tr><td>Relationship with Others</td><td>{{ $profile->relationship_with_others ?? 'N/A' }}</td><td></td></tr>
                                        <tr><td>Doing Assignment</td><td>{{ $profile->doing_assignment ?? 'N/A' }}</td><td></td></tr>
                                        <tr><td>Neatness</td><td>{{ $profile->neatness ?? 'N/A' }}</td><td></td></tr>
                                    @else
                                        <tr><td colspan="3">No character assessment data available.</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </td>
                        <td>
                            <div class="h5">Skill Development</div>
                            <table class="assessment-table">
                                <thead>
                                    <tr>
                                        <th>Skills</th>
                                        <th>Grade</th>
                                        <th>Sign</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($studentData['studentpp']->isNotEmpty())
                                        @php $profile = $studentData['studentpp']->first(); @endphp
                                        <tr><td>Writing Skill</td><td>{{ $profile->writing_skill ?? 'N/A' }}</td><td></td></tr>
                                        <tr><td>Reading Skill</td><td>{{ $profile->reading_skill ?? 'N/A' }}</td><td></td></tr>
                                        <tr><td>Spoken English/Communication</td><td>{{ $profile->spoken_english_communication ?? 'N/A' }}</td><td></td></tr>
                                        <tr><td>Hand Writing</td><td>{{ $profile->hand_writing ?? 'N/A' }}</td><td></td></tr>
                                        <tr><td>Sports/Games</td><td>{{ $profile->gamesandsports ?? 'N/A' }}</td><td></td></tr>
                                        <tr><td>Club</td><td>{{ $profile->club ?? 'N/A' }}</td><td></td></tr>
                                        <tr><td>Music</td><td>{{ $profile->music ?? 'N/A' }}</td><td></td></tr>
                                    @else
                                        <tr><td colspan="3">No skill development data available.</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Grade Legend -->
                <div class="grade-display">
                    <span>Grade: V.Good {VG}</span>
                    <span>Good {G}</span>
                    <span>Average {AVG}</span>
                    <span>Below Average {BA}</span>
                    <span>Poor {P}</span>
                </div>

                <!-- Remarks Section -->
                <table class="remarks-table">
                    <tbody>
                        <tr>
                            <td width="50%">
                                <div class="h6">Class Teacher's Remark Signature/Date</div>
                                <div>
                                    <span class="text-space-on-dots">
                                        @if ($studentData['studentpp']->isNotEmpty())
                                            {{ $studentData['studentpp']->first()->classteachercomment ?? 'N/A' }}
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td width="50%">
                                <div class="h6">Remark On Other Activities</div>
                                <div>
                                    <span class="text-space-on-dots">
                                        @if ($studentData['studentpp']->isNotEmpty())
                                            {{ $studentData['studentpp']->first()->cooperation ?? 'N/A' }}
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                <div class="h6">Guidance Counselor's Remark Signature/Date</div>
                                <div>
                                    <span class="text-space-on-dots">
                                        @if ($studentData['studentpp']->isNotEmpty())
                                            {{ $studentData['studentpp']->first()->guidancescomment ?? 'N/A' }}
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td width="50%">
                                <div class="h6">Principal's Remark Signature/Date</div>
                                <div>
                                    <span class="text-space-on-dots">
                                        @if ($studentData['studentpp']->isNotEmpty())
                                            {{ $studentData['studentpp']->first()->principalscomment ?? 'N/A' }}
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Footer Section -->
                <div class="footer-section">
                    <table class="footer-layout-table">
                        <tr>
                            <td>
                                <span class="font-bold">This Result was issued on</span>
                                <span class="text-dot-space2">........................</span>
                                <span class="font-bold">and collected by</span>
                                <span class="text-dot-space2">........................</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="font-bold text-primary">NEXT TERM BEGINS</span>
                                <span class="text-dot-space2">........................</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</body>
</html>