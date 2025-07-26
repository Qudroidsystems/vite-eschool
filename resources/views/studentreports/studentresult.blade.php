<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Class Results - Enhanced Design</title>
    <style>
        /* Enhanced reset and modern font setup */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #1f2937;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 20px 0;
        }

        .student-section {
            width: 210mm;
            min-height: 277mm;
            page-break-after: always;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        .student-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6, #06b6d4);
        }

        .student-section:last-child {
            page-break-after: avoid;
        }

        /* Modern fraction styling */
        .fraction {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            font-family: 'Inter', sans-serif;
            font-size: 9px;
            font-weight: 500;
        }

        .fraction .numerator {
            border-bottom: 1.5px solid #4f46e5;
            padding: 2px 6px;
            color: #4f46e5;
            font-weight: 600;
        }

        .fraction .denominator {
            padding-top: 3px;
            color: #6b7280;
        }

        /* Enhanced dotted underlines */
        span.text-space-on-dots,
        span.text-dot-space2 {
            border-bottom: 2px dotted #d1d5db;
            display: inline-block;
            min-height: 18px;
            padding-bottom: 2px;
            transition: all 0.2s ease;
        }

        span.text-space-on-dots {
            width: 280px;
        }

        span.text-dot-space2 {
            width: 180px;
        }

        /* Modern header styling */
        .school-name1 {
            font-size: 32px;
            font-weight: 800;
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-align: center;
            margin-bottom: 8px;
        }

        .school-name2 {
            font-size: 22px;
            font-weight: 700;
            color: #1e40af;
            text-align: center;
            margin: 8px 0;
            text-shadow: 0 2px 4px rgba(30, 64, 175, 0.1);
        }

        .school-logo {
            width: 90px;
            height: 90px;
            border: 3px solid #e0e7ff;
            border-radius: 20px;
            overflow: hidden;
            margin: 0 auto 15px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.15);
            transition: transform 0.2s ease;
        }

        .school-logo:hover {
            transform: scale(1.02);
        }

        .header-divider {
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
            border-radius: 2px;
            margin: 12px 0;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }

        .header-divider2 {
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, #e2e8f0, #cbd5e1, #e2e8f0);
            border-radius: 1px;
            margin: 8px 0;
        }

        .report-title {
            background: linear-gradient(135deg, #1e293b, #334155);
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            font-size: 20px;
            font-weight: 700;
            text-align: center;
            margin: 15px 0;
            box-shadow: 0 10px 30px rgba(30, 41, 59, 0.3);
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }

        .report-title::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s ease;
        }

        .report-title:hover::before {
            left: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header-img {
            width: 100%;
            height: 100%;
            border-radius: 16px;
            object-fit: cover;
        }

        .school-motto, .school-address, .school-website {
            font-size: 11px;
            color: #64748b;
            margin: 4px 0;
            font-weight: 500;
        }

        .school-website {
            color: #3b82f6;
            text-decoration: none;
        }

        /* Enhanced student info section */
        .student-info-section {
            margin-bottom: 20px;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border-radius: 12px;
            padding: 16px;
            border: 1px solid #e2e8f0;
        }

        .result-details {
            font-size: 12px;
            font-weight: 600;
            color: #374151;
        }

        .rd1, .rd2, .rd3, .rd4, .rd5, .rd6, .rd7, .rd8, .rd9, .rd10 {
            border-bottom: 2px dotted #94a3b8;
            margin-left: 8px;
            min-width: 100px;
            display: inline-block;
            font-weight: 600;
            color: #1e40af;
            padding-bottom: 3px;
        }

        /* Enhanced photo frame */
        .photo-frame {
            border: 4px solid #fbbf24;
            border-radius: 16px;
            overflow: hidden;
            background: white;
            padding: 4px;
            width: 110px;
            height: 130px;
            margin: 0 auto;
            text-align: center;
            box-shadow: 0 8px 25px rgba(251, 191, 36, 0.2);
            position: relative;
        }

        .photo-frame::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 12px;
            background: linear-gradient(135deg, transparent 30%, rgba(251, 191, 36, 0.1));
            pointer-events: none;
        }

        .photo-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }

        /* Enhanced results table */
        .result-table table {
            width: 100%;
            border: none;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 20px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .result-table thead th {
            background: linear-gradient(135deg, #1e40af, #3730a3);
            color: white;
            font-weight: 600;
            padding: 12px 6px;
            text-align: center;
            font-size: 10px;
            border: none;
            position: relative;
        }

        .result-table thead th:first-child {
            border-top-left-radius: 12px;
        }

        .result-table thead th:last-child {
            border-top-right-radius: 12px;
        }

        .result-table tbody td {
            border: 1px solid #e2e8f0;
            padding: 10px 6px;
            text-align: center;
            font-size: 10px;
            background: white;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .result-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .result-table tbody tr:hover td {
            background: #eff6ff;
            transform: translateX(2px);
        }

        .result-table tbody td.subject-name {
            text-align: left !important;
            font-weight: 700;
            color: #1e40af;
        }

        /* Enhanced highlighting */
        .highlight-red {
            color: #dc2626 !important;
            font-weight: 700 !important;
            background: rgba(220, 38, 38, 0.1) !important;
            border-radius: 4px;
            padding: 2px 4px;
        }

        .highlight-bold {
            font-weight: 700 !important;
            color: #059669 !important;
            background: rgba(5, 150, 105, 0.1) !important;
            border-radius: 4px;
            padding: 2px 4px;
        }

        /* Enhanced assessment tables */
        .assessment-table {
            width: 100%;
            border: none;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 12px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .assessment-table thead th {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            font-weight: 600;
            padding: 10px 8px;
            text-align: center;
            font-size: 10px;
            border: none;
        }

        .assessment-table tbody td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            background: white;
            font-size: 9px;
            font-weight: 500;
        }

        .assessment-table tbody tr:nth-child(even) td {
            background: #fefce8;
        }

        /* Enhanced grade display */
        .grade-display {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            border-radius: 16px;
            padding: 16px;
            text-align: center;
            margin-bottom: 16px;
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
            position: relative;
            overflow: hidden;
        }

        .grade-display::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .grade-display:hover::before {
            transform: translateX(100%);
        }

        .grade-display span {
            font-size: 11px;
            font-weight: 600;
            margin: 0 8px;
            display: inline-block;
            padding: 4px 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            backdrop-filter: blur(10px);
        }

        /* Enhanced remarks table */
        .remarks-table {
            width: 100%;
            border: none;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 16px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.1);
        }

        .remarks-table td {
            border: 1px solid #e0e7ff;
            padding: 16px;
            background: white;
            vertical-align: top;
        }

        .remarks-table .h6 {
            color: #7c3aed;
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Enhanced footer */
        .footer-section {
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #cbd5e1;
            text-align: center;
            margin-top: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .h5 {
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #059669;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Layout improvements */
        .student-info-table {
            width: 100%;
            margin-bottom: 16px;
        }

        .student-info-table td {
            padding: 6px;
            vertical-align: top;
        }

        .assessment-layout-table {
            width: 100%;
            margin-bottom: 16px;
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
            padding: 8px;
            text-align: center;
        }

        .info-row {
            margin-bottom: 8px;
            line-height: 1.6;
        }

        /* Utility classes */
        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: 700;
        }

        .text-primary {
            color: #1e40af;
        }

        .student-section-inner {
            width: 100%;
            height: auto;
        }

        /* Responsive improvements */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .student-section {
                box-shadow: none;
                margin: 0;
                border-radius: 0;
            }
        }

        /* Animation for interactive elements */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .student-section {
            animation: fadeInUp 0.6s ease-out;
        }
    </style>
</head>
<body>
    <!-- Demo content for visualization -->
    <div class="student-section">
        <div class="student-section-inner">
            <!-- Header Section -->
            <div class="header">
                <div class="school-logo">
                    <img class="header-img" src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iNDAiIGN5PSI0MCIgcj0iNDAiIGZpbGw9IiMzQjgyRjYiLz4KPHN2ZyB4PSIyMCIgeT0iMjAiIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8cGF0aCBkPSJNMTIgM0w0IDlWMjFIMjBWOUwxMiAzWiIgZmlsbD0id2hpdGUiLz4KPHN2ZyB4PSI4IiB5PSIxMCIgd2lkdGg9IjgiIGhlaWdodD0iOCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8cGF0aCBkPSJNMTIgMkw0IDdWMTdIMjBWN0wxMiAyWiIgZmlsbD0iIzNCODJGNiIvPgo8L3N2Zz4KPC9zdmc+Cjwvc3ZnPgo=" alt="School Logo">
                </div>
                <p class="school-name2">QUODOROID CODING ACADEMY</p>
                <div class="school-motto">Excellence in Education</div>
                <div class="school-address">123 Education Street, Knowledge City</div>
                <div class="school-website">www.quodoroid.edu</div>
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
                                <span class="rd1">John Doe Smith</span>
                            </div>
                            <div class="info-row">
                                <span class="result-details">Session:</span>
                                <span class="rd2">2023/2024</span>
                                <span class="result-details">Term:</span>
                                <span class="rd3">First Term</span>
                                <span class="result-details">Class:</span>
                                <span class="rd4">SS 3A</span>
                            </div>
                            <div class="info-row">
                                <span class="result-details">Date of Birth:</span>
                                <span class="rd5">15/03/2006</span>
                                <span class="result-details">Admission No:</span>
                                <span class="rd6">QCA/2023/001</span>
                                <span class="result-details">Sex:</span>
                                <span class="rd7">Male</span>
                            </div>
                            <div class="info-row">
                                <span class="result-details">No. of Times School Opened:</span>
                                <span class="rd8">95</span>
                                <span class="result-details">No. of Times School Absent:</span>
                                <span class="rd9">5</span>
                                <span class="result-details">No. of Students in Class:</span>
                                <span class="rd10">45</span>
                            </div>
                        </td>
                        <td width="25%">
                            <div class="photo-frame">
                                <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEyMCIgdmlld0JveD0iMCAwIDEwMCAxMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMDAiIGhlaWdodD0iMTIwIiBmaWxsPSIjRjNGNEY2Ii8+CjxjaXJjbGUgY3g9IjUwIiBjeT0iNDAiIHI9IjIwIiBmaWxsPSIjOUI5Qjk5Ii8+CjxwYXRoIGQ9Ik0yMCA5MEMyMCA3NS44NTc5IDMxLjQzMTUgNjUgNTAgNjVDNjguNTY4NSA2NSA4MCA3NS44NTc5IDgwIDkwVjEyMEgyMFY5MFoiIGZpbGw9IiM5Qjk5OTkiLz4KPC9zdmc+" alt="Student Photo">
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
                        <tr>
                            <td>1</td>
                            <td class="subject-name">Mathematics</td>
                            <td class="highlight-bold">78</td>
                            <td class="highlight-bold">82</td>
                            <td class="highlight-bold">75</td>
                            <td class="highlight-bold">78.3</td>
                            <td class="highlight-bold">85</td>
                            <td class="highlight-bold">81.7</td>
                            <td class="highlight-bold">80</td>
                            <td class="highlight-bold">80.9</td>
                            <td class="highlight-bold">A</td>
                            <td class="highlight-bold">3rd</td>
                            <td class="highlight-bold">72.5</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td class="subject-name">English Language</td>
                            <td class="highlight-bold">70</td>
                            <td class="highlight-bold">68</td>
                            <td class="highlight-bold">72</td>
                            <td class="highlight-bold">70.0</td>
                            <td class="highlight-bold">75</td>
                            <td class="highlight-bold">72.5</td>
                            <td class="highlight-bold">71</td>
                            <td class="highlight-bold">71.8</td>
                            <td class="highlight-bold">B</td>
                            <td class="highlight-bold">8th</td>
                            <td class="highlight-bold">65.2</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td class="subject-name">Physics</td>
                            <td class="highlight-red">45</td>
                            <td class="highlight-red">48</td>
                            <td class="highlight-bold">52</td>
                            <td class="highlight-red">48.3</td>
                            <td class="highlight-red">40</td>
                            <td class="highlight-red">44.2</td>
                            <td class="highlight-red">46</td>
                            <td class="highlight-red">45.1</td>
                            <td class="highlight-red">F</td>
                            <td class="highlight-bold">35th</td>
                            <td class="highlight-bold">58.7</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td class="subject-name">Chemistry</td>
                            <td class="highlight-bold">65</td>
                            <td class="highlight-bold">62</td>
                            <td class="highlight-bold">68</td>
                            <td class="highlight-bold">65.0</td>
                            <td class="highlight-bold">70</td>
                            <td class="highlight-bold">67.5</td>
                            <td class="highlight-bold">66</td>
                            <td class="highlight-bold">66.8</td>
                            <td class="highlight-bold">B</td>
                            <td class="highlight-bold">12th</td>
                            <td class="highlight-bold">61.3</td>
                        </tr>
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
                                <tr><td>Class Attendance</td><td>VG</td><td></td></tr>
                                <tr><td>Attentiveness in Class</td><td>G</td><td></td></tr>
                                <tr><td>Class Participation</td><td>VG</td><td></td></tr>
                                <tr><td>Self Control</td><td>G</td><td></td></tr>
                                <tr><td>Relationship with Others</td><td>VG</td><td></td></tr>
                                <tr><td>Doing Assignment</td><td>G</td><td></td></tr>
                                <tr><td>Neatness</td><td>VG</td><td></td></tr>
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
                                <tr><td>Writing Skill</td><td>G</td><td></td></tr>
                                <tr><td>Reading Skill</td><td>VG</td><td></td></tr>
                                <tr><td>Spoken English/Communication</td><td>G</td><td></td></tr>
                                <tr><td>Hand Writing</td><td>AVG</td><td></td></tr>
                                <tr><td>Sports/Games</td><td>VG</td><td></td></tr>
                                <tr><td>Club</td><td>G</td><td></td></tr>
                                <tr><td>Music</td><td>AVG</td><td></td></tr>
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
                                <span class="text-space-on-dots">John is a dedicated student who shows great potential in academics. Keep up the good work!</span>
                            </div>
                        </td>
                        <td width="50%">
                            <div class="h6">Remark On Other Activities</div>
                            <div>
                                <span class="text-space-on-dots">Excellent participation in sports and extracurricular activities. Shows good leadership qualities.</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td width="50%">
                            <div class="h6">Guidance Counselor's Remark Signature/Date</div>
                            <div>
                                <span class="text-space-on-dots">Student displays good social behavior and positive attitude towards learning. Recommend continued support in Physics.</span>
                            </div>
                        </td>
                        <td width="50%">
                            <div class="h6">Principal's Remark Signature/Date</div>
                            <div>
                                <span class="text-space-on-dots">Commendable overall performance. Student should focus more on science subjects to improve grades. Well done!</span>
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
                            <span class="text-dot-space2">15th December, 2023</span>
                            <span class="font-bold">and collected by</span>
                            <span class="text-dot-space2">Parent/Guardian</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="font-bold text-primary">NEXT TERM BEGINS</span>
                            <span class="text-dot-space2">8th January, 2024</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>
</html>