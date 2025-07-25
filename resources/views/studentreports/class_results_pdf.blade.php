<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Class Results - {{ $metadata['class_name'] ?? 'Unknown Class' }} - {{ $metadata['session'] ?? 'Unknown Session' }} - {{ $metadata['term'] ?? 'Unknown Term' }}</title>
    <style>
        /* Basic reset and font setup */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Times New Roman', Times, serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: #fff;
        }

        .page {
            width: 210mm; /* A4 width */
            min-height: 297mm; /* A4 height */
            margin: 0 auto;
            padding: 10mm;
        }

        /* Student section */
        .student-section {
            width: 100%;
            page-break-after: always;
            background: #ffffff;
            border: 2px solid #1e40af;
            padding: 15px;
            break-inside: avoid; /* Prevent splitting across pages */
        }

        .student-section:last-child {
            page-break-after: auto;
        }

        /* Fraction styles */
        .fraction {
            display: inline-block;
            font-size: 10px;
            text-align: center;
            vertical-align: middle;
        }
        .fraction .numerator {
            border-bottom: 1px solid #333;
            padding: 0 3px;
            display: block;
        }
        .fraction .denominator {
            padding-top: 2px;
            display: block;
        }

        /* Dotted lines for forms */
        .text-space-on-dots {
            width: 250px;
            border-bottom: 1px dotted #666;
            display: inline-block;
        }
        .text-dot-space2 {
            width: 150px;
            border-bottom: 1px dotted #666;
            display: inline-block;
        }

        /* Header styles */
        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .school-name1 {
            font-size: 28px;
            font-weight: bold;
            color: #1e3a8a;
        }

        .school-name2 {
            font-size: 22px;
            font-weight: bold;
            color: #1e40af;
        }

        .school-logo {
            width: 80px;
            height: 60px;
            border: 2px solid #1e40af;
            border-radius: 50%;
            margin: 0 auto 5px;
        }

        .school-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .header-divider {
            width: 100%;
            height: 3px;
            background: #1e40af;
            margin: 5px 0;
        }

        .header-divider2 {
            width: 100%;
            height: 1px;
            background: #64748b;
        }

        .report-title {
            background: #111827;
            color: white;
            padding: 10px;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
        }

        .school-motto, .school-address, .school-website {
            font-size: 11px;
            color: #6b7280;
            margin: 2px 0;
        }

        /* Student info styles */
        .student-info-section {
            margin-bottom: 10px;
        }

        .student-info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .student-info-table td {
            vertical-align: top;
            padding: 5px;
        }

        .result-details {
            font-size: 12px;
            font-weight: bold;
            color: #374151;
        }

        .rd1, .rd2, .rd3, .rd4, .rd5, .rd6, .rd7, .rd8, .rd9, .rd10 {
            border-bottom: 1px dotted #6b7280;
            margin-left: 5px;
            min-width: 100px;
            display: inline-block;
        }

        .photo-frame {
            border: 3px solid #1e40af;
            border-radius: 8px;
            padding: 3px;
            width: 100px;
            height: 120px;
            margin: 0 auto;
        }

        .photo-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Results table */
        .result-table table {
            width: 100%;
            border: 2px solid #1e40af;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10px;
        }

        .result-table th {
            background: #243f99;
            color: white;
            font-weight: bold;
            border: 1px solid #1d4ed8;
            padding: 6px;
            text-align: center;
        }

        .result-table td {
            border: 1px solid #cbd5e1;
            padding: 6px;
            text-align: center;
            background: white;
        }

        .result-table tr:nth-child(even) td {
            background: #f8fafc;
        }

        .subject-name {
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
            border: 2px solid #cbda77;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10px;
        }

        .assessment-table th {
            background: #fbbf24;
            color: white;
            font-weight: bold;
            border: 1px solid #047857;
            padding: 6px;
            text-align: center;
        }

        .assessment-table td {
            border: 1px solid #d1d5db;
            padding: 6px;
            background: white;
        }

        .assessment-table tr:nth-child(even) td {
            background: #f0fdf4;
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

        .grade-display {
            background: #fbbf24;
            color: white;
            border-radius: 10px;
            padding: 8px;
            text-align: center;
            margin-bottom: 10px;
            font-size: 11px;
        }

        .grade-display span {
            margin: 0 5px;
        }

        /* Remarks table */
        .remarks-table {
            width: 100%;
            border: 2px solid #7c3aed;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10px;
        }

        .remarks-table td {
            border: 1px solid #c4b5fd;
            padding: 8px;
            background: white;
            vertical-align: top;
        }

        .remarks-table .h6 {
            color: #6d28d9;
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 11px;
        }

        /* Footer section */
        .footer-section {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 10px;
            border: 1px solid #cbd5e1;
            text-align: center;
            margin-top: 10px;
            font-size: 10px;
        }

        .footer-layout-table {
            width: 100%;
        }

        .footer-layout-table td {
            padding: 3px;
            text-align: center;
        }

        .h5 {
            font-size: 11px;
            font-weight: bold;
            color: #047857;
            margin-bottom: 5px;
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
    </style>
</head>
<body>
    <div class="page">
        @foreach ($allStudentData as $index => $studentData)
            <div class="student-section">
                <!-- Header Section -->
                <div class="header">
                    <div class="school-logo">
                        <img src="{{ $studentData['school_logo_path'] ?? public_path('storage/school_logos/default.jpg') }}" alt="School Logo">
                    </div>
                    <p class="school-name2">{{ $studentData['schoolInfo']['school_name'] ?? 'QUODOROID CODING ACADEMY' }}</p>
                    <div class="school-motto">{{ $studentData['schoolInfo']['school_motto'] ?? 'N/A' }}</div>
                    <div class="school-address">{{ $studentData['schoolInfo']['school_address'] ?? 'N/A' }}</div>
                    @if (!empty($studentData['schoolInfo']['school_website']))
                        <div class="school-website">{{ $studentData['schoolInfo']['school_website'] }}</div>
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
                                <div class="info-row">
                                    <span class="result-details">Name of Student:</span>
                                    <span class="rd1">{{ $studentData['firstname'] ?? '' }} {{ $studentData['lastname'] ?? '' }} {{ $studentData['othername'] ?? '' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="result-details">Session:</span>
                                    <span class="rd2">{{ $metadata['session'] ?? 'N/A' }}</span>
                                    <span class="result-details">Term:</span>
                                    <span class="rd3">{{ $metadata['term'] ?? 'N/A' }}</span>
                                    <span class="result-details">Class:</span>
                                    <span class="rd4">{{ $metadata['class_name'] ?? 'N/A' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="result-details">Date of Birth:</span>
                                    <span class="rd5">{{ !empty($studentData['dateofbirth']) ? \Carbon\Carbon::parse($studentData['dateofbirth'])->format('d/m/Y') : 'N/A' }}</span>
                                    <span class="result-details">Admission No:</span>
                                    <span class="rd6">{{ $studentData['admission_no'] ?? 'N/A' }}</span>
                                    <span class="result-details">Sex:</span>
                                    <span class="rd7">{{ $studentData['gender'] ?? 'N/A' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="result-details">No. of Times School Opened:</span>
                                    <span class="rd8">{{ $studentData['attendance'] ?? 'N/A' }}</span>
                                    <span class="result-details">No. of Times School Absent:</span>
                                    <span class="rd9">{{ isset($studentData['attendance']) ? ($studentData['attendance'] - ($studentData['attendance'] ?? 0)) : 'N/A' }}</span>
                                    <span class="result-details">No. of Students in Class:</span>
                                    <span class="rd10">{{ $metadata['student_count'] ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td width="25%">
                                <div class="photo-frame">
                                    <img src="{{ $studentData['student_image_path'] ?? public_path('storage/student_avatars/unnamed.jpg') }}" alt="Student Photo">
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
                                <th>T1</th>
                                <th>T2</th>
                                <th>T3</th>
                                <th>
                                    <div class="fraction">
                                        <div class="numerator">(T1+T2+T3)/3</div>
                                        <div class="denominator"></div>
                                    </div>
                                </th>
                                <th>Term Exams</th>
                                <th>
                                    <div class="fraction">
                                        <div class="numerator">d + e</div>
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
                            @forelse ($studentData['subjects'] as $index => $subject)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="subject-name">{{ $subject['name'] ?? '-' }}</td>
                                    <td class="{{ ($subject['ca1'] ?? 0) <= 50 && is_numeric($subject['ca1']) ? 'highlight-red' : 'highlight-bold' }}">{{ $subject['ca1'] ?? '-' }}</td>
                                    <td class="{{ ($subject['ca2'] ?? 0) <= 50 && is_numeric($subject['ca2']) ? 'highlight-red' : '' }}">{{ $subject['ca2'] ?? '-' }}</td>
                                    <td class="{{ ($subject['ca3'] ?? 0) <= 50 && is_numeric($subject['ca3']) ? 'highlight-red' : '' }}">{{ $subject['ca3'] ?? '-' }}</td>
                                    <td class="{{ ($subject['ca_avg'] ?? 0) <= 50 && is_numeric($subject['ca_avg']) ? 'highlight-red' : 'highlight-bold' }}">{{ $subject['ca_avg'] ?? '-' }}</td>
                                    <td class="{{ ($subject['exam'] ?? 0) <= 50 && is_numeric($subject['exam']) ? 'highlight-red' : 'highlight-bold' }}">{{ $subject['exam'] ?? '-' }}</td>
                                    <td class="{{ ($subject['total'] ?? 0) <= 50 && is_numeric($subject['total']) ? 'highlight-red' : 'highlight-bold' }}">{{ $subject['total'] ?? '-' }}</td>
                                    <td class="{{ ($subject['bf'] ?? 0) <= 50 && is_numeric($subject['bf']) ? 'highlight-red' : 'highlight-bold' }}">{{ $subject['bf'] ?? '-' }}</td>
                                    <td class="{{ ($subject['cum'] ?? 0) <= 50 && is_numeric($subject['cum']) ? 'highlight-red' : 'highlight-bold' }}">{{ $subject['cum'] ?? '-' }}</td>
                                    <td class="{{ in_array($subject['grade'] ?? '', ['F', 'F9', 'E', 'E8']) ? 'highlight-red' : 'highlight-bold' }}">{{ $subject['grade'] ?? '-' }}</td>
                                    <td class="highlight-bold">{{ $subject['position'] ?? '-' }}</td>
                                    <td class="highlight-bold">{{ $subject['class_average'] ?? '-' }}</td>
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
                                    <tr><td>Class Attendance</td><td>{{ $studentData['attendance'] ?? 'N/A' }}</td><td></td></tr>
                                    <tr><td>Attentiveness in Class</td><td>{{ $studentData['attentiveness_in_class'] ?? 'N/A' }}</td><td></td></tr>
                                    <tr><td>Class Participation</td><td>{{ $studentData['class_participation'] ?? 'N/A' }}</td><td></td></tr>
                                    <tr><td>Self Control</td><td>{{ $studentData['selfcontrol'] ?? 'N/A' }}</td><td></td></tr>
                                    <tr><td>Relationship with Others</td><td>{{ $studentData['relationship_with_others'] ?? 'N/A' }}</td><td></td></tr>
                                    <tr><td>Doing Assignment</td><td>{{ $studentData['doing_assignment'] ?? 'N/A' }}</td><td></td></tr>
                                    <tr><td>Neatness</td><td>{{ $studentData['neatness'] ?? 'N/A' }}</td><td></td></tr>
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
                                    <tr><td>Writing Skill</td><td>{{ $studentData['writing_skill'] ?? 'N/A' }}</td><td></td></tr>
                                    <tr><td>Reading Skill</td><td>{{ $studentData['reading_skill'] ?? 'N/A' }}</td><td></td></tr>
                                    <tr><td>Spoken English/Communication</td><td>{{ $studentData['spoken_english_communication'] ?? 'N/A' }}</td><td></td></tr>
                                    <tr><td>Hand Writing</td><td>{{ $studentData['hand_writing'] ?? 'N/A' }}</td><td></td></tr>
                                    <tr><td>Sports/Games</td><td>{{ $studentData['gamesandsports'] ?? 'N/A' }}</td><td></td></tr>
                                    <tr><td>Club</td><td>{{ $studentData['club'] ?? 'N/A' }}</td><td></td></tr>
                                    <tr><td>Music</td><td>{{ $studentData['music'] ?? 'N/A' }}</td><td></td></tr>
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
                                <div><span class="text-space-on-dots">{{ $studentData['classteachercomment'] ?? 'N/A' }}</span></div>
                            </td>
                            <td width="50%">
                                <div class="h6">Remark On Other Activities</div>
                                <div><span class="text-space-on-dots">{{ $studentData['cooperation'] ?? 'N/A' }}</span></div>
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                <div class="h6">Guidance Counselor's Remark Signature/Date</div>
                                <div><span class="text-space-on-dots">{{ $studentData['guidancescomment'] ?? 'N/A' }}</span></div>
                            </td>
                            <td width="50%">
                                <div class="h6">Principal's Remark Signature/Date</div>
                                <div><span class="text-space-on-dots">{{ $studentData['principalscomment'] ?? 'N/A' }}</span></div>
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
        @endforeach
    </div>
</body>
</html>