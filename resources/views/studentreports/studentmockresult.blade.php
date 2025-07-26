<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Mock Result - {{ $data['students']->isNotEmpty() ? $data['students']->first()->fname . ' ' . $data['students']->first()->lastname : 'Student' }} - {{ $data['schoolsession'] }} - {{ $data['schoolterm'] }}</title>
    <style>
        /* Basic reset and font setup */
        * {
            margin: 0;
            padding: 0;
            /* box-sizing: border-box; */
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: #fff;
        }

        .student-section {
            width: 210mm; /* A4 width */
            min-height: 277mm; /* A4 height minus margins */
            background: #ffffff;
            border: 2px solid #1e40af;
            margin: 20px auto; /* Center horizontally with auto margins */
            padding: 15px; /* Consistent internal padding */
            position: relative;
            /* box-sizing: border-box; */
        }

        .fraction {
            display: inline-block;
            font-family: Arial, sans-serif;
            font-size: 10px;
            text-align: center;
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

        span.text-space-on-dots,
        span.text-dot-space2 {
            border-bottom: 1px dotted #666;
            display: inline-block;
            min-height: 16px;
        }

        span.text-space-on-dots {
            width: 280px;
        }

        span.text-dot-space2 {
            width: 180px;
        }

        .school-name1 {
            font-size: 28px;
            font-weight: 700;
            color: #1e3a8a;
            text-align: center;
        }

        .school-name2 {
            font-size: 20px;
            font-weight: 800;
            color: #1e40af;
            text-align: center;
            margin: 5px 0;
        }

        .school-logo {
            width: 80px;
            height: 80px;
            border: 0px solid #1e40af;
            border-radius: 5px;
            overflow: hidden;
            margin: 0 auto 10px;
            text-align: center;
        }

        .header-divider {
            width: 100%;
            height: 3px;
            background: #1e40af;
            margin: 6px 0;
        }

        .header-divider2 {
            width: 100%;
            height: 2px;
            background: #64748b;
            margin: 3px 0;
        }

        .report-title {
            background: #111827;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 700;
            text-align: center;
            margin: 10px 0;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
        }

        .header-img {
            width: 100%;
            height: 100%;
            border-radius: 35px;
        }

        .school-motto, .school-address, .school-website {
            font-size: 11px;
            color: #6b7280;
            margin: 3px 0;
        }

        .student-info-section {
            margin-bottom: 12px;
        }

        .result-details {
            font-size: 12px;
            font-weight: 700;
            color: #374151;
        }

        .rd1, .rd2, .rd3, .rd4, .rd5, .rd6, .rd7, .rd8, .rd9, .rd10 {
            border-bottom: 2px dotted #6b7280;
            margin-left: 6px;
            min-width: 100px;
            display: inline-block;
            font-weight: 700;
            padding-bottom: 2px;
        }

        .photo-frame {
            border: 3px solid #e3e568;
            border-radius: 8px;
            overflow: hidden;
            background: white;
            padding: 3px;
            width: 100px;
            height: 120px;
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
            margin-bottom: 12px;
        }

        .result-table thead th {
            background: #243f99;
            color: white;
            font-weight: 600;
            border: 1px solid #1d4ed8;
            padding: 8px 4px;
            text-align: center;
            font-size: 10px;
        }

        .result-table tbody td {
            border: 1px solid #cbd5e1;
            padding: 6px 4px;
            text-align: center;
            font-size: 10px;
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
            margin-bottom: 8px;
        }

        .assessment-table thead th {
            background: #fbbf24;
            color: white;
            font-weight: 600;
            border: 1px solid #047857;
            padding: 6px;
            text-align: center;
            font-size: 10px;
        }

        .assessment-table tbody td {
            border: 1px solid #d1d5db;
            padding: 4px 6px;
            background: white;
            font-size: 9px;
        }

        .assessment-table tbody tr:nth-child(even) td {
            background: #f0fdf4;
        }

        .grade-display {
            background: #fbbf24;
            color: white;
            border-radius: 12px;
            padding: 8px;
            text-align: center;
            margin-bottom: 10px;
        }

        .grade-display span {
            font-size: 11px;
            font-weight: 600;
            margin: 0 6px;
        }

        .remarks-table {
            width: 100%;
            border: 2px solid #7c3aed;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .remarks-table td {
            border: 1px solid #c4b5fd;
            padding: 8px;
            background: white;
            vertical-align: top;
        }

        .remarks-table .h6 {
            color: #6d28d9;
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 10px;
        }

        .footer-section {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 10px;
            border: 1px solid #cbd5e1;
            text-align: center;
            margin-top: 8px;
        }

        .h5 {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 6px;
            color: #047857;
        }

        .student-info-table {
            width: 100%;
            margin-bottom: 12px;
        }

        .student-info-table td {
            padding: 4px;
            vertical-align: top;
        }

        .assessment-layout-table {
            width: 100%;
            margin-bottom: 10px;
        }

        .assessment-layout-table td {
            width: 48%;
            vertical-align: top;
            padding: 0 1%;
        }

        .footer-layout-table {
            width: 100%;
        }

        .footer-layout-table td {
            padding: 4px;
            text-align: center;
        }

        .info-row {
            margin-bottom: 6px;
            line-height: 1.5;
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
    <div class="student-section">
        <div class="student-section-inner">
            <!-- Header Section -->
            <div class="header">
                @php
                    $schoolInfo = $data['schoolInfo'];
                @endphp
                <div class="school-logo">
                    <img class="header-img" src="{{ $data['school_logo_path'] ?? public_path('storage/school_logos/default.jpg') }}" alt="School Logo">
                </div>
                <p class="school-name2">{{ $schoolInfo->school_name ?? 'QUODOROID CODING ACADEMY' }}</p>
                <div class="school-motto">{{ $schoolInfo->school_motto ?? 'N/A' }}</div>
                <div class="school-address">{{ $schoolInfo->school_address ?? 'N/A' }}</div>
                @if ($schoolInfo->school_website)
                    <div class="school-website">{{ $schoolInfo->school_website }}</div>
                @endif
                <div class="header-divider"></div>
                <div class="header-divider2"></div>
                <div class="report-title">MOCK TERMINAL PROGRESS REPORT</div>
            </div>

            <!-- Student Information Section -->
            <div class="student-info-section">
                <table class="student-info-table">
                    <tr>
                        <td width="75%">
                            @if ($data['students']->isNotEmpty())
                                @php $student = $data['students']->first(); @endphp
                                <div class="info-row">
                                    <span class="result-details">Name of Student:</span>
                                    <span class="rd1">{{ $student->fname }} {{ $student->lastname }} {{ $student->othername ?? '' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="result-details">Session:</span>
                                    <span class="rd2">{{ $data['schoolsession'] }}</span>
                                    <span class="result-details">Term:</span>
                                    <span class="rd3">{{ $data['schoolterm'] }}</span>
                                    <span class="result-details">Class:</span>
                                    <span class="rd4">{{ $data['schoolclass']->schoolclass ?? 'N/A' }} {{ $data['schoolclass']->armRelation->arm ?? '' }}</span>
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
                                    @if ($data['studentpp']->isNotEmpty())
                                        @php $profile = $data['studentpp']->first(); @endphp
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
                                    <span class="result-details">No. of Students in Class:</span>
                                    <span class="rd10">{{ $data['numberOfStudents'] ?? 'N/A' }}</span>
                                </div>
                            @else
                                <div class="info-row">
                                    <span class="result-details">No student data available.</span>
                                </div>
                            @endif
                        </td>
                        <td width="25%">
                            <div class="photo-frame">
                                @if ($data['students']->isNotEmpty() && $student->picture)
                                    <img src="{{ $data['student_image_path'] ?? public_path('storage/student_avatars/unnamed.jpg') }}" alt="{{ $student->fname }}'s picture">
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
                            <th>S/N</th>
                            <th>Subjects</th>
                            <th>Mock Exam</th>
                            <th>Grade</th>
                            <th>Position</th>
                            <th>Class Average</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data['mockScores'] as $index => $score)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="subject-name">{{ $score->subject_name }}</td>
                                <td class="@if ($score->exam <= 50 && is_numeric($score->exam)) highlight-red @elseif ($score->exam > 50 && is_numeric($score->exam)) highlight-bold @endif">{{ $score->exam ?? '-' }}</td>
                                <td class="@if (in_array($score->grade, ['F', 'F9', 'E', 'E8'])) highlight-red @elseif ($score->grade && !in_array($score->grade, ['F', 'F9', 'E', 'E8'])) highlight-bold @endif">{{ $score->grade ?? '-' }}</td>
                                <td class="highlight-bold">{{ $score->position ?? '-' }}</td>
                                <td class="highlight-bold">{{ $score->class_average ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">No mock scores available.</td>
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
                                @if ($data['studentpp']->isNotEmpty())
                                    @php $profile = $data['studentpp']->first(); @endphp
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
                                @if ($data['studentpp']->isNotEmpty())
                                    @php $profile = $data['studentpp']->first(); @endphp
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
                                    @if ($data['studentpp']->isNotEmpty())
                                        {{ $data['studentpp']->first()->classteachercomment ?? 'N/A' }}
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
                                    @if ($data['studentpp']->isNotEmpty())
                                        {{ $data['studentpp']->first()->cooperation ?? 'N/A' }}
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
                                    @if ($data['studentpp']->isNotEmpty())
                                        {{ $data['studentpp']->first()->guidancescomment ?? 'N/A' }}
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
                                    @if ($data['studentpp']->isNotEmpty())
                                        {{ $data['studentpp']->first()->principalscomment ?? 'N/A' }}
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
</body>
</html>