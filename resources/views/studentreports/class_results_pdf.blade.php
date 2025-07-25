<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Class Results - {{ $metadata['class_name'] }} - {{ $metadata['session'] }} - {{ $metadata['term'] }}</title>
    <style>
        /* Basic reset */
        * {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #000;
            background: #fff;
        }

        .page {
            width: 100%;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }

        /* Header styles */
        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header-img {
            width: 150px;
            height: auto;
        }

        .school-name {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 24px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 10px 0;
        }

        .school-motto, .school-address, .school-website {
            font-size: 12px;
            color: #555;
            margin: 5px 0;
        }

        .header-divider {
            width: 100%;
            height: 4px;
            background: #1e40af;
            margin: 10px 0;
        }

        .header-divider2 {
            width: 100%;
            height: 2px;
            background: #555;
        }

        .report-title {
            font-size: 20px;
            font-weight: bold;
            color: #fff;
            background: #111827;
            padding: 10px;
            text-align: center;
            margin: 10px 0;
        }

        /* Student section */
        .student-section {
            margin-bottom: 30px;
            page-break-after: always;
        }

        .student-info-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .student-info-table td {
            padding: 5px;
            vertical-align: top;
        }

        .result-details {
            font-weight: bold;
            font-size: 14px;
            color: #374151;
        }

        .rd {
            border-bottom: 2px dotted #6b7280;
            display: inline-block;
            min-width: 150px;
            font-weight: bold;
            margin-left: 8px;
        }

        .photo-frame {
            border: 4px solid #1e40af;
            width: 150px;
            height: 170px;
            text-align: center;
        }

        .photo-frame img {
            width: 100%;
            height: 100%;
        }

        /* Results table */
        .result-table table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #1e40af;
            margin-bottom: 20px;
        }

        .result-table th, .result-table td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            text-align: center;
            font-size: 12px;
        }

        .result-table th {
            background: #333;
            color: #fff;
            font-weight: bold;
        }

        .result-table tbody tr:nth-child(even) {
            background: #f8f8f8;
        }

        .result-table td.subject {
            text-align: left;
            font-weight: bold;
        }

        .highlight-red {
            color: #dc2626;
            font-weight: bold;
        }

        .highlight-bold {
            font-weight: bold;
        }

        /* Fraction styles */
        .fraction {
            display: inline-block;
            text-align: center;
            font-size: 10px;
        }

        .fraction .numerator {
            border-bottom: 2px solid #333;
            padding: 0 5px;
            display: block;
        }

        .fraction .denominator {
            padding-top: 5px;
            display: block;
        }

        /* Assessment tables */
        .assessment-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #cbda77;
            margin-bottom: 20px;
        }

        .assessment-table th, .assessment-table td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
        }

        .assessment-table th {
            background: #d4d41d;
            color: #fff;
            font-weight: bold;
        }

        .assessment-table tbody tr:nth-child(even) td {
            background: #f0fdf4;
        }

        .grade-display {
            background: #d4d41d;
            color: #fff;
            padding: 10px;
            text-align: center;
            margin-bottom: 20px;
        }

        .grade-display span {
            font-size: 12px;
            margin: 0 10px;
        }

        /* Remarks table */
        .remarks-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #7c3aed;
            margin-bottom: 20px;
        }

        .remarks-table td {
            border: 1px solid #c4b5fd;
            padding: 10px;
            vertical-align: top;
        }

        .remarks-table .h6 {
            color: #6d28d9;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .text-space-on-dots {
            border-bottom: 1px dotted #666;
            display: inline-block;
            width: 300px;
        }

        /* Footer */
        .footer-section {
            background: #f1f5f9;
            padding: 15px;
            text-align: center;
            margin-top: 20px;
        }

        .text-dot-space2 {
            border-bottom: 1px dotted #666;
            display: inline-block;
            width: 200px;
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <div class="page">
        @foreach ($allStudentData as $studentData)
            <div class="student-section">
                <!-- Header Section -->
                <div class="header">
                    @php
                        $schoolInfo = $studentData['schoolInfo'];
                    @endphp
                    <img class="header-img" src="{{ $studentData['school_logo_path'] ?? public_path('storage/school_logos/default.jpg') }}" alt="School Logo">
                    <div class="school-name">{{ $schoolInfo->school_name ?? 'QUODOROID CODING ACADEMY' }}</div>
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
                <table class="student-info-table">
                    <tr>
                        <td width="75%">
                            @if ($studentData['students']->isNotEmpty())
                                @php $student = $studentData['students']->first(); @endphp
                                <div>
                                    <span class="result-details">Name of Student:</span>
                                    <span class="rd">{{ $student->fname }} {{ $student->lastname }} {{ $student->othername ?? '' }}</span>
                                </div>
                                <div>
                                    <span class="result-details">Session:</span>
                                    <span class="rd">{{ $studentData['schoolsession'] }}</span>
                                    <span class="result-details">Term:</span>
                                    <span class="rd">{{ $studentData['schoolterm'] }}</span>
                                    <span class="result-details">Class:</span>
                                    <span class="rd">{{ $studentData['schoolclass']->schoolclass ?? 'N/A' }} {{ $studentData['schoolclass']->armRelation->arm ?? '' }}</span>
                                </div>
                                <div>
                                    <span class="result-details">Date of Birth:</span>
                                    <span class="rd">{{ $student->dateofbirth ? \Carbon\Carbon::parse($student->dateofbirth)->format('d/m/Y') : 'N/A' }}</span>
                                    <span class="result-details">Admission No:</span>
                                    <span class="rd">{{ $student->admissionNo ?? 'N/A' }}</span>
                                    <span class="result-details">Sex:</span>
                                    <span class="rd">{{ $student->gender ?? 'N/A' }}</span>
                                </div>
                                <div>
                                    @if ($studentData['studentpp']->isNotEmpty())
                                        @php $profile = $studentData['studentpp']->first(); @endphp
                                        <span class="result-details">No. of Times School Opened:</span>
                                        <span class="rd">{{ $profile->attendance ?? 'N/A' }}</span>
                                        <span class="result-details">No. of Times School Absent:</span>
                                        <span class="rd">{{ $profile->attendance ? ($profile->attendance - ($profile->attendance ?? 0)) : 'N/A' }}</span>
                                    @else
                                        <span class="result-details">No. of Times School Opened:</span>
                                        <span class="rd">N/A</span>
                                        <span class="result-details">No. of Times School Absent:</span>
                                        <span class="rd">N/A</span>
                                    @endif
                                    <span class="result-details">No. of Students in Class:</span>
                                    <span class="rd">{{ $studentData['numberOfStudents'] ?? 'N/A' }}</span>
                                </div>
                            @else
                                <div>
                                    <span class="result-details">No student data available.</span>
                                </div>
                            @endif
                        </td>
                        <td width="25%" align="center">
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

                <!-- Results Table -->
                <div class="result-table">
                    <table>
                        <thead>
                            <tr>
                                <th></th>
                                <th>Subjects</th>
                                <th>T1</th>
                                <th>T2</th>
                                <th>T3</th>
                                <th>
                                    <div class="fraction">
                                        <div class="numerator">T1 + T2 + T3</div>
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
                                <th>Cum</th>
                                <th>Grade</th>
                                <th>PSN</th>
                                <th>Class Avg</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($studentData['scores'] as $index => $score)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="subject">{{ $score->subject_name }}</td>
                                    <td @if ($score->ca1 <= 50 && is_numeric($score->ca1)) class="highlight-red" @elseif ($score->ca1 > 50 && is_numeric($score->ca1)) class="highlight-bold" @endif>{{ $score->ca1 ?? '-' }}</td>
                                    <td @if ($score->ca2 <= 50 && is_numeric($score->ca2)) class="highlight-red" @elseif ($score->ca2 > 50 && is_numeric($score->ca2)) class="highlight-bold" @endif>{{ $score->ca2 ?? '-' }}</td>
                                    <td @if ($score->ca3 <= 50 && is_numeric($score->ca3)) class="highlight-red" @elseif ($score->ca3 > 50 && is_numeric($score->ca3)) class="highlight-bold" @endif>{{ $score->ca3 ?? '-' }}</td>
                                    <td @if ($score->ca1 && $score->ca2 && $score->ca3 && round(($score->ca1 + $score->ca2 + $score->ca3) / 3, 1) <= 50) class="highlight-red" @elseif ($score->ca1 && $score->ca2 && $score->ca3 && round(($score->ca1 + $score->ca2 + $score->ca3) / 3, 1) > 50) class="highlight-bold" @endif>
                                        {{ $score->ca1 && $score->ca2 && $score->ca3 ? round(($score->ca1 + $score->ca2 + $score->ca3) / 3, 1) : '-' }}
                                    </td>
                                    <td @if ($score->exam <= 50 && is_numeric($score->exam)) class="highlight-red" @elseif ($score->exam > 50 && is_numeric($score->exam)) class="highlight-bold" @endif>{{ $score->exam ?? '-' }}</td>
                                    <td @if ($score->total <= 50 && is_numeric($score->total)) class="highlight-red" @elseif ($score->total > 50 && is_numeric($score->total)) class="highlight-bold" @endif>{{ $score->total ?? '-' }}</td>
                                    <td @if ($score->bf <= 50 && is_numeric($score->bf)) class="highlight-red" @elseif ($score->bf > 50 && is_numeric($score->bf)) class="highlight-bold" @endif>{{ $score->bf ?? '-' }}</td>
                                    <td @if ($score->cum <= 50 && is_numeric($score->cum)) class="highlight-red" @elseif ($score->cum > 50 && is_numeric($score->cum)) class="highlight-bold" @endif>{{ $score->cum ?? '-' }}</td>
                                    <td @if (in_array($score->grade, ['F', 'F9', 'E', 'E8'])) class="highlight-red" @elseif ($score->grade && !in_array($score->grade, ['F', 'F9', 'E', 'E8'])) class="highlight-bold" @endif>{{ $score->grade ?? '-' }}</td>
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

                <!-- Assessment Tables -->
                <table width="100%">
                    <tr>
                        <td width="50%" style="padding-right: 10px;">
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
                        <td width="50%" style="padding-left: 10px;">
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
                    <tr>
                        <td width="50%">
                            <div class="h6">Class Teacher's Remark Signature/Date</div>
                            <span class="text-space-on-dots">
                                @if ($studentData['studentpp']->isNotEmpty())
                                    {{ $studentData['studentpp']->first()->classteachercomment ?? 'N/A' }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </td>
                        <td width="50%">
                            <div class="h6">Remark On Other Activities</div>
                            <span class="text-space-on-dots">
                                @if ($studentData['studentpp']->isNotEmpty())
                                    {{ $studentData['studentpp']->first()->cooperation ?? 'N/A' }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td width="50%">
                            <div class="h6">Guidance Counselor's Remark Signature/Date</div>
                            <span class="text-space-on-dots">
                                @if ($studentData['studentpp']->isNotEmpty())
                                    {{ $studentData['studentpp']->first()->guidancescomment ?? 'N/A' }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </td>
                        <td width="50%">
                            <div class="h6">Principal's Remark Signature/Date</div>
                            <span class="text-space-on-dots">
                                @if ($studentData['studentpp']->isNotEmpty())
                                    {{ $studentData['studentpp']->first()->principalscomment ?? 'N/A' }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </td>
                    </tr>
                </table>

                <!-- Footer Section -->
                <div class="footer-section">
                    <div>
                        <span class="fw-bold">This Result was issued on</span>
                        <span class="text-dot-space2"></span>
                        <span class="fw-bold">and collected by</span>
                        <span class="text-dot-space2"></span>
                    </div>
                    <div>
                        <span class="fw-bold">NEXT TERM BEGINS</span>
                        <span class="text-dot-space2"></span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>