<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mock Results - {{ $metadata['class_name'] }} - {{ $metadata['session'] }} - {{ $metadata['term'] }}</title>
    <style>
        /* [Existing CSS styles unchanged] */
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
            font-size: 9px;
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
            min-height: 14px;
        }

        span.text-space-on-dots {
            width: 250px;
        }

        span.text-dot-space2 {
            width: 150px;
        }

        .school-name1 {
            font-size: 24px;
            font-weight: 700;
            color: #1e3a8a;
            text-align: center;
        }

        .school-name2 {
            font-size: 24px;
            font-weight: 900;
            color: #000000;
            text-align: left;
            margin: 1px 0;
            line-height: 1.2;
        }

        .school-logo {
            width: 100px;
            height: 100px;
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
            font-size: 16px;
            font-weight: 700;
            text-align: center;
            margin: 8px 0;
        }

        .header {
            margin-bottom: 6px;
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

        .school-motto, .school-address, .school-website {
            font-size: 12px;
            font-weight: 900;
            color: #000000;
            margin: 1px 0;
            text-align: left;
            line-height: 1.2;
        }

        .student-info-section {
            margin-bottom: 4px;
        }

        .result-details {
            font-size: 11px;
            font-weight: 800;
            color: #000000;
        }

        .info-value {
            font-size: 12px;
            font-weight: bold;
            color: #000000;
        }

        .rd1, .rd2, .rd3, .rd4, .rd5, .rd6, .rd7, .rd8, .rd9, .rd10 {
            border-bottom: 2px dotted #6b7280;
            margin-left: 6px;
            min-width: 60px;
            display: inline-block;
            font-weight: 800;
            padding-bottom: 1px;
            font-size: 10px;
            color: #000000;
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
            font-size: 9px;
        }

        .result-table thead th:nth-child(3),
        .result-table thead th:nth-child(4) {
            width: 50px;
        }

        .result-table thead th:nth-child(5),
        .result-table thead th:nth-child(6) {
            width: 60px;
        }

        .result-table tbody tr {
            font-weight: 800;
        }

        .result-table tbody td {
            border: 1px solid #000000;
            padding: 4px 3px;
            text-align: center;
            font-size: 11px;
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

        .highlight-bold {
            font-weight: 900 !important;
        }

        .assessment-table {
            width: 100%;
            border: 2px solid #000000;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .assessment-table thead th {
            background: #fbbf24;
            color: white;
            font-weight: 600;
            border: 1px solid #000000;
            padding: 4px;
            text-align: center;
            font-size: 9px;
        }

        .assessment-table tbody td {
            border: 1px solid #000000;
            padding: 3px 4px;
            background: white;
            font-size: 8px;
            color: #000000;
            font-weight: bold;
        }

        .assessment-table tbody tr:nth-child(even) td {
            background: #f0fdf4;
        }

        .grade-display {
            background: #fbbf24;
            color: white;
            border-radius: 10px;
            padding: 6px;
            text-align: center;
            margin-bottom: 8px;
        }

        .grade-display span {
            font-size: 10px;
            font-weight: 600;
            margin: 0 4px;
        }

        .remarks-table {
            width: 100%;
            border: 2px solid #000000;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .remarks-table td {
            border: 1px solid #000000;
            padding: 6px;
            background: white;
            vertical-align: top;
        }

        .remarks-table .h6 {
            color: #050505;
            font-weight: 600;
            margin-bottom: 4px;
            font-size: 9px;
        }

        .remarks-table .text-space-on-dots {
            color: #000000;
            font-weight: bold;
        }

        .remarks-table .promotion-status {
            color: #000000;
            font-weight: bold;
        }

        .footer-section {
            background: #f1f5f9;
            border-radius: 6px;
            padding: 8px;
            border: 1px solid #cbd5e1;
            text-align: center;
            margin-top: 6px;
        }

        .h5 {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 4px;
            color: #047857;
        }

        .student-info-table {
            width: 100%;
            margin-bottom: 4px;
            table-layout: fixed;
        }

        .student-info-table td {
            padding: 1px;
            vertical-align: top;
        }

        .assessment-layout-table {
            width: 100%;
            margin-bottom: 8px;
            table-layout: fixed;
        }

        .assessment-layout-table td {
            width: 50%;
            vertical-align: top;
            padding: 0 1%;
        }

        .footer-layout-table {
            width: 100%;
        }

        .footer-layout-table td {
            padding: 3px;
            text-align: center;
        }

        .info-row {
            margin-bottom: 2px;
            line-height: 1.2;
        }

        .info-row .result-details {
            margin-right: 4px;
        }

        .info-row.students-count {
            margin-top: 2px;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .text-primary {
            color: #02175e;
        }

        .student-section-inner {
            width: 100%;
            height: auto;
        }

        .powered-by {
            font-size: 9px;
            color: #000000;
            font-weight: 700;
            margin-top: 6px;
        }

        .promotion-status {
            font-weight: bold;
            margin-left: 5px;
        }

        .promotion-promoted {
            color: #22c55e;
        }

        .promotion-repeat {
            color: #dc2626;
        }

        .promotion-parents {
            color: #f97316;
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
                        $schoolInfo = $studentData['schoolInfo'] ?? null;
                        $student = $studentData['students'] && $studentData['students']->isNotEmpty() ? $studentData['students']->first() : null;
                    @endphp
                    <table class="header-table">
                        <tr>
                            <td width="25%">
                                <div class="school-logo">
                                    <img class="header-img" src="{{ $studentData['school_logo_path'] ?? public_path('storage/school_logos/default.jpg') }}" alt="School Logo">
                                </div>
                            </td>
                            <td width="50%">
                                <p class="school-name2">{{ $schoolInfo->school_name ?? 'QUODOROID CODING ACADEMY' }}</p>
                                <div class="school-motto">{{ $schoolInfo->school_motto ?? 'NO INFO' }}</div>
                                <div class="school-address">{{ $schoolInfo->school_address ?? 'NO INFO' }}</div>
                                @if ($schoolInfo && $schoolInfo->school_website)
                                    <div class="school-website">{{ $schoolInfo->school_website }}</div>
                                @else
                                    <div class="school-website">NO INFO</div>
                                @endif
                            </td>
                            <td width="25%">
                                <div class="photo-frame">
                                    @if ($studentData['students'] && $studentData['students']->isNotEmpty() && $student->picture)
                                        <img src="{{ $studentData['student_image_path'] ?? public_path('storage/student_avatars/unnamed.jpg') }}" alt="{{ $student->fname ?? 'Student' }}'s picture">
                                    @else
                                        <img src="{{ public_path('storage/student_avatars/unnamed.jpg') }}" alt="Default Photo">
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </table>
                    <div class="header-divider"></div>
                    <div class="header-divider2"></div>
                    <div class="report-title">{{ strtoupper($metadata['term']) }} {{ strtoupper($metadata['session']) }} MOCK PROGRESS REPORT</div>
                </div>

                <!-- Student Information Section -->
                <div class="student-info-section">
                    <table class="student-info-table">
                        <tr>
                            <td width="100%">
                                @if ($studentData['students'] && $studentData['students']->isNotEmpty())
                                    @php 
                                        $student = $studentData['students']->first();
                                        $profile = $studentData['studentpp'] && $studentData['studentpp']->isNotEmpty() ? $studentData['studentpp']->first() : null;
                                    @endphp
                                    <table style="width: 100%; table-layout: fixed;">
                                        <tr>
                                            <td width="41%">
                                                <div class="info-row">
                                                    <span class="result-details">Name:</span>
                                                    <span class="info-value font-bold">{{ strtoupper($student->lastname ?? 'ILEMOBAYOEEEE') }} {{ $student->fname ?? 'Eliabeeeee' }} {{ $student->othername ?? 'eeeee' }}</span>
                                                </div>
                                                <div class="info-row">
                                                    <span class="result-details">Session:</span>
                                                    <span class="info-value font-bold">{{ $studentData['schoolsession'] ?? 'NO INFO' }}</span>
                                                </div>
                                                <div class="info-row">
                                                    <span class="result-details">Term:</span>
                                                    <span class="info-value font-bold">{{ $studentData['schoolterm'] ?? 'NO INFO' }}</span>
                                                </div>
                                            </td>
                                            <td width="29%">
                                                <div class="info-row">
                                                    <span class="result-details">Class:</span>
                                                    <span class="info-value font-bold">{{ $studentData['schoolclass']->schoolclass ?? 'NO INFO' }} {{ $studentData['schoolclass']->armRelation->arm ?? 'NO INFO' }}</span>
                                                </div>
                                                <div class="info-row">
                                                    <span class="result-details">DOB:</span>
                                                    <span class="info-value font-bold">
                                                        @php
                                                            $dob = $student->dateofbirth ?? null;
                                                            $formattedDob = 'NO INFO';
                                                            if ($dob) {
                                                                try {
                                                                    if (is_numeric($dob)) {
                                                                        $unixTimestamp = ($dob - 25569) * 86400;
                                                                        $formattedDob = date('d/m/Y', $unixTimestamp);
                                                                    } else {
                                                                        $formattedDob = \Carbon\Carbon::parse($dob)->format('d/m/Y');
                                                                    }
                                                                } catch (\Exception $e) {
                                                                    $formattedDob = $dob;
                                                                }
                                                            }
                                                        @endphp
                                                        {{ $formattedDob }}
                                                    </span>
                                                </div>
                                                <div class="info-row">
                                                    <span class="result-details">Adm No:</span>
                                                    <span class="info-value font-bold">{{ $student->admissionNo ?? 'NO INFO' }}</span>
                                                </div>
                                            </td>
                                            <td width="30%">
                                                <div class="info-row">
                                                    <span class="result-details">Sex:</span>
                                                    <span class="info-value font-bold">{{ $student->gender ?? 'NO INFO' }}</span>
                                                </div>
                                                <div class="info-row">
                                                    <span class="result-details">School Opened:</span>
                                                    <span class="info-value font-bold">{{ $profile ? ($profile->attendance ?? 'NO INFO') : 'NO INFO' }}</span>
                                                </div>
                                                <div class="info-row">
                                                    <span class="result-details">Absent:</span>
                                                    <span class="info-value font-bold">{{ $profile && $profile->attendance ? ($profile->attendance - ($profile->attendance ?? 0)) : 'NO INFO' }}</span>
                                                </div>
                                                <div class="info-row students-count">
                                                    <span class="result-details">Students in Class:</span>
                                                    <span class="info-value font-bold">{{ $studentData['numberOfStudents'] ?? 'NO INFO' }}</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                @else
                                    <div class="info-row">
                                        <span class="result-details">No student data available.</span>
                                    </div>
                                @endif
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
                                <th>Exam Score</th>
                                <th>Total Score</th>
                                <th>Grade</th>
                                <th>Position</th>
                                <th>Class Average</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($studentData['mockScores'] as $index => $score)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="subject-name">{{ $score->subject_name ?? 'NO INFO' }}</td>
                                    <td class="@if ($score->exam < 50 && is_numeric($score->exam)) highlight-red @endif">{{ $score->exam ?? '-' }}</td>
                                    <td class="@if ($score->total < 50 && is_numeric($score->total)) highlight-red @endif">{{ $score->total ?? '-' }}</td>
                                    <td class="@if (in_array($score->grade ?? '', ['F', 'F9', 'E', 'E8'])) highlight-red @endif">{{ $score->grade ?? '-' }}</td>
                                    <td>{{ $score->position ?? '-' }}</td>
                                    <td>{{ $score->class_average ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">No scores available.</td>
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
                                    @if ($studentData['studentpp'] && $studentData['studentpp']->isNotEmpty())
                                        @php $profile = $studentData['studentpp']->first(); @endphp
                                        <tr><td>Class Attendance</td><td>{{ $profile->attendance ?? 'NO INFO' }}</td><td></td></tr>
                                        <tr><td>Attentiveness in Class</td><td>{{ $profile->attentiveness_in_class ?? 'NO INFO' }}</td><td></td></tr>
                                        <tr><td>Class Participation</td><td>{{ $profile->class_participation ?? 'NO INFO' }}</td><td></td></tr>
                                        <tr><td>Self Control</td><td>{{ $profile->selfcontrol ?? 'NO INFO' }}</td><td></td></tr>
                                        <tr><td>Relationship with Others</td><td>{{ $profile->relationship_with_others ?? 'NO INFO' }}</td><td></td></tr>
                                        <tr><td>Doing Assignment</td><td>{{ $profile->doing_assignment ?? 'NO INFO' }}</td><td></td></tr>
                                        <tr><td>Neatness</td><td>{{ $profile->neatness ?? 'NO INFO' }}</td><td></td></tr>
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
                                    @if ($studentData['studentpp'] && $studentData['studentpp']->isNotEmpty())
                                        @php $profile = $studentData['studentpp']->first(); @endphp
                                        <tr><td>Writing Skill</td><td>{{ $profile->writing_skill ?? 'NO INFO' }}</td><td></td></tr>
                                        <tr><td>Reading Skill</td><td>{{ $profile->reading_skill ?? 'NO INFO' }}</td><td></td></tr>
                                        <tr><td>Spoken English/Communication</td><td>{{ $profile->spoken_english_communication ?? 'NO INFO' }}</td><td></td></tr>
                                        <tr><td>Hand Writing</td><td>{{ $profile->hand_writing ?? 'NO INFO' }}</td><td></td></tr>
                                        <tr><td>Sports/Games</td><td>{{ $profile->gamesandsports ?? 'NO INFO' }}</td><td></td></tr>
                                        <tr><td>Club</td><td>{{ $profile->club ?? 'NO INFO' }}</td><td></td></tr>
                                        <tr><td>Music</td><td>{{ $profile->music ?? 'NO INFO' }}</td><td></td></tr>
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
                                        @if ($studentData['studentpp'] && $studentData['studentpp']->isNotEmpty())
                                            {{ $studentData['studentpp']->first()->classteachercomment ?? 'NO INFO' }}
                                        @else
                                            NO INFO
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td width="50%">
                                <div class="h6">Remark On Other Activities</div>
                                <div>
                                    <span class="text-space-on-dots">
                                        @if ($studentData['studentpp'] && $studentData['studentpp']->isNotEmpty())
                                            {{ $studentData['studentpp']->first()->cooperation ?? 'NO INFO' }}
                                        @else
                                            NO INFO
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
                                        @if ($studentData['studentpp'] && $studentData['studentpp']->isNotEmpty())
                                            {{ $studentData['studentpp']->first()->guidancescomment ?? 'NO INFO' }}
                                        @else
                                            NO INFO
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td width="50%">
                                <div class="h6">Principal's Remark & Promotion Status</div>
                                <div>
                                    <span class="text-space-on-dots">
                                        @if ($studentData['studentpp'] && $studentData['studentpp']->isNotEmpty())
                                            {{ $studentData['studentpp']->first()->principalscomment ?? 'NO INFO' }}
                                            @if ($studentData['studentpp']->first()->promotionStatus)
                                                | <span class="promotion-status @if ($studentData['studentpp']->first()->promotionStatus === 'PROMOTED') promotion-promoted @elseif ($studentData['studentpp']->first()->promotionStatus === 'REPEAT') promotion-repeat @elseif ($studentData['studentpp']->first()->promotionStatus === 'PARENTS TO SEE PRINCIPAL') promotion-parents @endif">
                                                    Promotion Status: {{ $studentData['studentpp']->first()->promotionStatus }}
                                                </span>
                                            @else
                                                | <span class="promotion-status">Promotion Status: NO INFO</span>
                                            @endif
                                        @else
                                            NO INFO | <span class="promotion-status">Promotion Status: NO INFO</span>
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
                    <div class="powered-by">Powered by Qudroid Systems | www.qudroid.co | +2349057522004</div>
                </div>
            </div>
        </div>
    @endforeach
</body>
</html>
