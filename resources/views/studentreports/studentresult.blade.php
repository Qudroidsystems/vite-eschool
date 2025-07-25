<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal Progress Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Simplified CSS for PDF compatibility */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.4;
            background: #f8fafc;
        }

        .content-wrapper {
            background: #ffffff;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Fraction styles for scores */
        .fraction {
            display: inline-block;
            text-align: center;
            font-size: 10px;
            line-height: 1.2;
        }
        .fraction .numerator {
            border-bottom: 2px solid #333;
            padding: 0 5px;
            display: block;
        }
        .fraction .denominator {
            padding-top: 2px;
            display: block;
        }

        /* Center table headers and cells */
        tr.rt > th,
        tr.rt > td {
            text-align: center;
        }

        /* Dotted underline for remarks */
        .text-space-on-dots {
            border-bottom: 1px dotted #666;
            display: inline-block;
            width: 400px;
        }
        .text-dot-space2 {
            border-bottom: 1px dotted #666;
            display: inline-block;
            width: 200px;
        }

        /* Header styles */
        .school-name1 {
            font-size: 36px;
            font-weight: bold;
            color: #1e3a8a;
            text-align: center;
        }
        .school-name2 {
            font-size: 28px;
            font-weight: bold;
            color: #1e40af;
            text-align: center;
            margin: 5px 0;
        }
        .school-logo {
            width: 100px;
            height: 80px;
            margin: 0 auto;
            border: 2px solid #1e40af;
            border-radius: 10px;
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
            background: #64748b;
            margin-bottom: 10px;
        }

        /* Main card */
        .main-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
        }

        .print-sect {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
        }

        /* Result details */
        .result-details {
            font-size: 16px;
            font-weight: bold;
            color: #374151;
        }
        .rd1, .rd2, .rd3, .rd4, .rd5, .rd6, .rd7, .rd8, .rd9, .rd10 {
            border-bottom: 2px dotted #6b7280;
            margin-left: 8px;
            min-width: 120px;
            display: inline-block;
            font-weight: bold;
        }

        /* Table styles */
        .result-table table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #1e40af;
            background: #ffffff;
        }
        .result-table thead th {
            background: #1e40af;
            color: #ffffff;
            font-weight: bold;
            border: 1px solid #1d4ed8;
            padding: 10px;
            font-size: 12px;
            text-align: center;
        }
        .result-table tbody td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            text-align: center;
            font-size: 13px;
            background: #ffffff;
        }
        .result-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }
        .result-table tbody td:nth-child(2) {
            text-align: left !important;
            font-weight: bold;
        }
        .highlight-red {
            color: #dc2626;
            font-weight: bold;
        }
        .highlight-bold {
            font-weight: bold;
        }

        /* Assessment tables */
        .assessment-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #047857;
        }
        .assessment-table thead th {
            background: #f59e0b;
            color: #ffffff;
            font-weight: bold;
            border: 1px solid #047857;
            padding: 8px;
            text-align: center;
        }
        .assessment-table tbody td {
            border: 1px solid #d1d5db;
            padding: 8px;
            background: #ffffff;
        }
        .assessment-table tbody tr:nth-child(even) td {
            background: #f0fdf4;
        }

        /* Grade display */
        .grade-display {
            background: #f59e0b;
            color: #ffffff;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
        }
        .grade-display span {
            font-size: 14px;
            font-weight: bold;
            margin: 0 10px;
        }

        /* Remarks table */
        .remarks-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #7c3aed;
        }
        .remarks-table td {
            border: 1px solid #c4b5fd;
            padding: 10px;
            background: #ffffff;
        }
        .remarks-table .h6 {
            color: #6d28d9;
            font-weight: bold;
            margin-bottom: 8px;
        }

        /* Photo frame */
        .photo-frame {
            width: 120px;
            height: 140px;
            border: 3px solid #1e40af;
            border-radius: 8px;
            background: #ffffff;
            padding: 5px;
            margin: 0 auto;
        }

        /* Report title */
        .report-title {
            background: #374151;
            color: #ffffff;
            padding: 10px;
            border-radius: 8px;
            font-size: 22px;
            font-weight: bold;
            text-align: center;
        }

        /* Footer section */
        .footer-section {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }

        /* Print button (hidden in PDF) */
        .print-button {
            display: none; /* Hidden in PDF output */
        }
    </style>
</head>
<body>
    <div class="main-card">
        <div class="content-wrapper">
            <div class="print-sect">
                <!-- Header Section -->
                @php
                    $schoolInfo = \App\Models\SchoolInformation::getActiveSchool();
                @endphp
                <div class="mb-4">
                    <div style="text-align: center;">
                        <img class="school-logo" src="{{ $schoolInfo->getLogoUrlAttribute() ?? asset('print-main/public/assets/tp.png') }}" alt="School Logo">
                    </div>
                    <p class="school-name2">{{ $schoolInfo->school_name }}</p>
                    <div style="text-align: center;">
                        <p class="h5 text-secondary">{{ $schoolInfo->school_motto }}</p>
                        <p class="h6 text-muted">{{ $schoolInfo->school_address }}</p>
                        @if ($schoolInfo->school_website)
                            <p class="h6 text-muted">{{ $schoolInfo->school_website }}</p>
                        @endif
                    </div>
                    <div class="header-divider"></div>
                    <div class="header-divider2"></div>
                    <h1 class="report-title">TERMINAL PROGRESS REPORT</h1>
                </div>

                <!-- Student Information Section -->
                <div class="mb-4">
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 70%; vertical-align: top;">
                                @if ($students->isNotEmpty())
                                    @php $student = $students->first(); @endphp
                                    <div style="margin-bottom: 10px;">
                                        <span class="result-details">Name of Student:</span>
                                        <span class="rd1">{{ $student->fname }} {{ $student->lastname }} {{ $student->othername ?? '' }}</span>
                                    </div>
                                    <div style="margin-bottom: 10px;">
                                        <span class="result-details">Session:</span>
                                        <span class="rd2">{{ $schoolsession }}</span>
                                        <span class="result-details" style="margin-left: 20px;">Term:</span>
                                        <span class="rd3">{{ $schoolterm }}</span>
                                        <span class="result-details" style="margin-left: 20px;">Class:</span>
                                        <span class="rd4">{{ $schoolclass->schoolclass ?? 'N/A' }} {{ $schoolclass->armRelation->arm ?? '' }}</span>
                                    </div>
                                    <div style="margin-bottom: 10px;">
                                        <span class="result-details">Date of Birth:</span>
                                        <span class="rd5">{{ $student->dateofbirth ? \Carbon\Carbon::parse($student->dateofbirth)->format('d/m/Y') : 'N/A' }}</span>
                                        <span class="result-details" style="margin-left: 20px;">Admission No:</span>
                                        <span class="rd6">{{ $student->admissionNo ?? 'N/A' }}</span>
                                        <span class="result-details" style="margin-left: 20px;">Sex:</span>
                                        <span class="rd7">{{ $student->gender ?? 'N/A' }}</span>
                                    </div>
                                    <div style="margin-bottom: 10px;">
                                        @if ($studentpp->isNotEmpty())
                                            @php $profile = $studentpp->first(); @endphp
                                            <span class="result-details">No. of Times School Opened:</span>
                                            <span class="rd8">{{ $profile->attendance ?? 'N/A' }}</span>
                                            <span class="result-details" style="margin-left: 20px;">No. of Times School Absent:</span>
                                            <span class="rd9">{{ $profile->attendance ? ($profile->attendance - ($profile->attendance ?? 0)) : 'N/A' }}</span>
                                        @else
                                            <span class="result-details">No. of Times School Opened:</span>
                                            <span class="rd8">N/A</span>
                                            <span class="result-details" style="margin-left: 20px;">No. of Times School Absent:</span>
                                            <span class="rd9">N/A</span>
                                        @endif
                                        <span class="result-details" style="margin-left: 20px;">No. of Students in Class:</span>
                                        <span class="rd10">{{ $numberOfStudents ?? 'N/A' }}</span>
                                    </div>
                                @else
                                    <div>
                                        <span class="result-details">No student data available.</span>
                                    </div>
                                @endif
                            </td>
                            <td style="width: 30%; text-align: center; vertical-align: top;">
                                <div class="photo-frame">
                                    @if ($students->isNotEmpty() && $student->picture)
                                        <img src="{{ asset('storage/' . $student->picture) }}" style="width: 100%; height: 100%; object-fit: cover;" alt="{{ $student->fname }}'s picture" onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}';">
                                    @else
                                        <img src="{{ asset('storage/student_avatars/unnamed.jpg') }}" style="width: 100%; height: 100%; object-fit: cover;" alt="Default Student Photo">
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Results Table Section -->
                <div class="mb-4">
                    <div class="result-table">
                        <table class="table">
                            <thead>
                                <tr class="rt">
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
                                    <th><span>Cum</span> (f/g)/2</th>
                                    <th>Grade</th>
                                    <th>PSN</th>
                                    <th>Class Average</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($scores as $index => $score)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td style="text-align: left;">{{ $score->subject_name }}</td>
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
                                        <td colspan="13" align="center">No scores available for this student.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Assessment Tables Section -->
                <div style="margin-bottom: 20px;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 50%; padding-right: 10px; vertical-align: top;">
                                <div class="h5 mb-3 text-success fw-bold">Character Assessment</div>
                                <table class="assessment-table">
                                    <thead>
                                        <tr>
                                            <th>Criteria</th>
                                            <th>Grade</th>
                                            <th>Sign</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if ($studentpp->isNotEmpty())
                                            @php $profile = $studentpp->first(); @endphp
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
                            <td style="width: 50%; padding-left: 10px; vertical-align: top;">
                                <div class="h5 mb-3 text-success fw-bold">Skill Development</div>
                                <table class="assessment-table">
                                    <thead>
                                        <tr>
                                            <th>Skills</th>
                                            <th>Grade</th>
                                            <th>Sign</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if ($studentpp->isNotEmpty())
                                            <tr><td>Writing Skill</td><td>{{ $profile->writing_skill ?? 'N/A' }}</td><td></td></tr>
                                            <tr><td>Reading Skill</td><td>{{ $profile->reading_skill ?? 'N/A' }}</td><td></td></tr>
                                            <tr><td>Spoken English/Communication</td><td>{{ $profile->spoken_english_communication ?? 'N/A' }}</td><td></td></tr>
                                            <tr><td>Hand Writing</td><td>{{ $profile->hand_writing ?? 'N/A' }}</td><td></td></tr>
                                            <tr><td>Sports/Games</td><td>{{ $profile->gamesandsports ?? 'N/A' }}</td><td></Ny/Atd></tr>
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
                </div>

                <!-- Grade Legend -->
                <div class="mb-4">
                    <div class="grade-display">
                        <span>Grade: V.Good {VG}</span>
                        <span>Good {G}</span>
                        <span>Average {AVG}</span>
                        <span>Below Average {BA}</span>
                        <span>Poor {P}</span>
                    </div>
                </div>

                <!-- Remarks Section -->
                <div class="mb-4">
                    <table class="remarks-table">
                        <tbody>
                            <tr>
                                <td style="width: 50%;">
                                    <div class="h6">Class Teacher's Remark Signature/Date</div>
                                    <span class="text-space-on-dots">{{ $studentpp->isNotEmpty() ? ($profile->classteachercomment ?? 'N/A') : 'N/A' }}</span>
                                </td>
                                <td style="width: 50%;">
                                    <div class="h6">Remark On Other Activities</div>
                                    <span class="text-space-on-dots">{{ $studentpp->isNotEmpty() ? ($profile->cooperation ?? 'N/A') : 'N/A' }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 50%;">
                                    <div class="h6">Guidance Counselor's Remark Signature/Date</div>
                                    <span class="text-space-on-dots">{{ $studentpp->isNotEmpty() ? ($profile->guidancescomment ?? 'N/A') : 'N/A' }}</span>
                                </td>
                                <td style="width: 50%;">
                                    <div class="h6">Principal's Remark Signature/Date</div>
                                    <span class="text-space-on-dots">{{ $studentpp->isNotEmpty() ? ($profile->principalscomment ?? 'N/A') : 'N/A' }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Section -->
                <div class="footer-section">
                    <div style="display: flex; justify-content: space-between; padding: 10px;">
                        <span class="fw-bold">This Result was issued on<span class="text-dot-space2">........................</span></span>
                        <span class="fw-bold">and collected by<span class="text-dot-space2">........................</span></span>
                    </div>
                    <div style="text-align: center; padding: 10px;">
                        <span class="h5 fw-bold text-primary">NEXT TERM BEGINS<span class="text-dot-space2">........................</span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>