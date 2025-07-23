<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal Progress Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        /* Watermark styles */
        .watermark {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
            overflow: hidden;
        }

        .watermark-text {
            position: absolute;
            font-family: 'Times New Roman', Times, serif;
            font-size: 14px;
            color: rgba(255, 215, 0, 0.15);
            font-weight: bold;
            transform: rotate(-45deg);
            white-space: nowrap;
            user-select: none;
        }

        /* Content wrapper to ensure content appears above watermark */
        .content-wrapper {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.98);
        }

        .fraction {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        .fraction .numerator {
            border-bottom: 2px solid #333;
            padding: 0 5px;
        }
        .fraction .denominator {
            padding-top: 5px;
        }
        tr.rt>th,
        tr.rt>td {
            text-align: center;
        }
        div.grade>span {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 16px;
            font-weight: bold;
        }
        span.text-space-on-dots {
            position: relative;
            width: 500px;
            border-bottom: 1px dotted #666;
            display: inline-block;
        }
        span.text-dot-space2 {
            position: relative;
            width: 300px;
            border-bottom: 1px dotted #666;
            display: inline-block;
        }

        /* Enhanced print styles */
        @media print {
            .watermark {
                position: absolute;
            }
            div.print-body {
                background-color: white;
            }
            @page {
                size: A4;
                margin: 15mm;
            }
            div.print-body {
                background-color: white;
            }
            html, body {
                width: 100%;
            }
            body {
                margin: 0;
            }
            nav {
                display: none;
            }
        }

        /* Enhanced header styles */
        p.school-name1 {
            font-family: 'Times New Roman', Times, serif;
            font-size: 42px;
            font-weight: 700;
            color: #1e3a8a;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        p.school-name2 {
            font-family: 'Times New Roman', Times, serif;
            font-size: 32px;
            font-weight: 800;
            color: #1e40af;
            letter-spacing: 2px;
        }
        div.school-logo {
            width: 90px;
            height: 70px;
            border-radius: 50%;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        div.header-divider {
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #1e40af, #3b82f6, #1e40af);
            margin-bottom: 4px;
            border-radius: 2px;
        }
        div.header-divider2 {
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, #64748b, #94a3b8, #64748b);
            border-radius: 1px;
        }

        /* Enhanced card styles */
        .main-card {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .print-sect {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }

        /* Enhanced result details */
        span.result-details {
            font-size: 16px;
            font-family: 'Times New Roman', Times, serif;
            font-weight: 600;
            color: #374151;
        }
        span.rd1, span.rd2, span.rd3, span.rd4, span.rd5, span.rd6, span.rd7, span.rd8, span.rd9, span.rd10 {
            border-bottom: 2px dotted #6b7280;
            margin-left: 8px;
        }

        /* Enhanced table styles */
        .result-table table {
            border: 2px solid #1e40af;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .result-table thead th {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: white;
            font-weight: 600;
            border: 1px solid #1d4ed8;
            padding: 12px 8px;
            text-align: center;
            font-size: 13px;
        }
        
        .result-table tbody td {
            border: 1px solid #cbd5e1;
            padding: 10px 8px;
            text-align: center;
            font-size: 14px;
            background: white;
            transition: background-color 0.2s;
        }
        
        .result-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }
        
        .result-table tbody tr:hover td {
            background: #e0f2fe;
        }

        /* Enhanced highlight class for scores less than 40 */
        .highlight-red {
            color: #dc2626 !important;
            font-weight: bold;
            background: rgba(239, 68, 68, 0.1) !important;
        }

        /* Enhanced assessment tables */
        .assessment-table {
            border: 2px solid #059669;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .assessment-table thead th {
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
            font-weight: 600;
            border: 1px solid #047857;
            padding: 10px;
            text-align: center;
        }
        
        .assessment-table tbody td {
            border: 1px solid #d1d5db;
            padding: 8px 12px;
            background: white;
        }
        
        .assessment-table tbody tr:nth-child(even) td {
            background: #f0fdf4;
        }

        /* Enhanced grade display */
        .grade-display {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
            border-radius: 15px;
            padding: 15px;
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
        }
        
        .grade-display span {
            font-family: 'Times New Roman', Times, serif;
            font-size: 16px;
            font-weight: 600;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }

        /* Enhanced remarks section */
        .remarks-table {
            border: 2px solid #7c3aed;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .remarks-table td {
            border: 1px solid #c4b5fd;
            padding: 15px;
            background: white;
        }
        
        .remarks-table .h6 {
            color: #6d28d9;
            font-weight: 600;
            margin-bottom: 10px;
        }

        /* Enhanced photo frame */
        .photo-frame {
            border: 4px solid #1e40af;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
            overflow: hidden;
            background: white;
            padding: 5px;
        }

        /* Title enhancement */
        .report-title {
            background: linear-gradient(135deg, #111827, #374151);
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 24px;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            box-shadow: 0 6px 15px rgba(0,0,0,0.3);
        }

        /* Footer enhancement */
        .footer-section {
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #cbd5e1;
        }
    </style>
</head>
<body>
    <!-- Watermark -->
    <div class="watermark">
        <!-- Generate watermark text across the page -->
    </div>

    <div class="card main-card">
        <div class="print-body w-100 h-100 content-wrapper">
            <div class="print-sect container-fluid" style="max-width: 1200px; margin: 0 auto; padding: 30px;">
                <!-- Header Section -->
                <div class="row mb-4">
                    <div class="col-md d-flex flex-column">
                        <div class="w-100 d-flex justify-content-center align-items-center pt-2">
                            <div class="school-logo me-3">
                                <img src="{{ asset('print-main/public/assets/tp.png') }}" class="w-100 h-100" alt="School Logo">
                            </div>
                            <div>
                                <p class="school-name1 m-0">TCC</p>
                            </div>
                        </div>
                        <div class="w-100 d-flex justify-content-center align-items-center">
                            <p class="school-name2 m-0">TOPCLASS COLLEGE</p>
                        </div>
                        <div class="w-100 d-flex flex-column justify-content-center align-items-center mt-2">
                            <p class="h5 m-0 text-secondary">Developing the total child</p>
                            <p class="h6 m-0 text-muted">39, Okegbala Street off Odojomu Road, Ondo.</p>
                        </div>
                        <div class="mt-3">
                            <div class="header-divider"></div>
                            <div class="header-divider2"></div>
                        </div>
                        <div class="w-100 d-flex flex-column justify-content-center align-items-center mt-3">
                            <h1 class="report-title m-0">
                                TERMINAL PROGRESS REPORT
                            </h1>
                        </div>
                    </div>
                </div>

                <!-- Student Information Section -->
                <div class="row mb-4">
                    <div class="col-lg-9 d-flex flex-column justify-content-center gap-3">
                        <div class="d-flex flex-row flex-wrap align-items-center gap-2">
                            <span class="result-details">Name of Student:</span><span class="rd1"></span>
                        </div>
                        <div class="d-flex flex-row flex-wrap align-items-center gap-3">
                            <span class="result-details">Session:</span><span class="rd2"></span>
                            <span class="result-details">Term:</span><span class="rd3"></span>
                            <span class="result-details">Class:</span><span class="rd4"></span>
                        </div>
                        <div class="d-flex flex-row flex-wrap align-items-center gap-3">
                            <span class="result-details">Date of Birth:</span><span class="rd5"></span>
                            <span class="result-details">Admission No:</span><span class="rd6"></span>
                            <span class="result-details">Sex:</span><span class="rd7"></span>
                        </div>
                        <div class="d-flex flex-row flex-wrap align-items-center gap-3">
                            <span class="result-details">No. of Times School Opened:</span><span class="rd8"></span>
                            <span class="result-details">No. of Times School Absent:</span><span class="rd9"></span>
                            <span class="result-details">No. of Student in Class:</span><span class="rd10"></span>
                        </div>
                    </div>
                    <div class="col-lg-3 d-flex justify-content-center align-items-center">
                        <div class="photo-frame" style="width: 140px; height: 160px;">
                            <img src="{{ asset('print-main/public/assets/siji.jpg') }}" class="w-100 h-100" alt="Student Photo" style="object-fit: cover;">
                        </div>
                    </div>
                </div>

                <!-- Results Table Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="result-table">
                            <table class="table table-hover table-responsive">
                                <thead>
                                    <tr class="rt">
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
                                    <tr class="rt">
                                        <th>S/N</th>
                                        <th></th>
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
                                        <th><span class="d-block">Cum</span> (f/g)/2</th>
                                        <th>Grade</th>
                                        <th>PSN</th>
                                        <th>Class Average</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Sample data for demonstration -->
                                    <tr>
                                        <td>1</td>
                                        <td>Mathematics</td>
                                        <td>45</td>
                                        <td>52</td>
                                        <td>48</td>
                                        <td>48.3</td>
                                        <td>67</td>
                                        <td>57.7</td>
                                        <td>55</td>
                                        <td>56.4</td>
                                        <td>B3</td>
                                        <td>12th</td>
                                        <td>52.1</td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>English Language</td>
                                        <td class="highlight-red">32</td>
                                        <td>42</td>
                                        <td class="highlight-red">38</td>
                                        <td class="highlight-red">37.3</td>
                                        <td>45</td>
                                        <td>41.2</td>
                                        <td class="highlight-red">39</td>
                                        <td>40.1</td>
                                        <td>C6</td>
                                        <td>25th</td>
                                        <td>44.5</td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>Physics</td>
                                        <td>58</td>
                                        <td>62</td>
                                        <td>55</td>
                                        <td>58.3</td>
                                        <td>72</td>
                                        <td>65.2</td>
                                        <td>63</td>
                                        <td>64.1</td>
                                        <td>B2</td>
                                        <td>8th</td>
                                        <td>59.8</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Assessment Tables Section -->
                <div class="row gap-3 mb-4">
                    <div class="col-lg assessment-section">
                        <div class="h5 mb-3 text-success fw-bold">Character Assessment</div>
                        <table class="table assessment-table">
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
                                <tr><td>Class Participation</td><td>AVG</td><td></td></tr>
                                <tr><td>Self Control</td><td>G</td><td></td></tr>
                                <tr><td>Relationship with Others</td><td>VG</td><td></td></tr>
                                <tr><td>Doing Assignment</td><td>G</td><td></td></tr>
                                <tr><td>Neatness</td><td>AVG</td><td></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-lg assessment-section">
                        <div class="h5 mb-3 text-success fw-bold">Skill Development</div>
                        <table class="table assessment-table">
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
                                <tr><td>Spoken English/Communication</td><td>AVG</td><td></td></tr>
                                <tr><td>Hand Writing</td><td>G</td><td></td></tr>
                                <tr><td>Sports/Games</td><td>VG</td><td></td></tr>
                                <tr><td>Club</td><td>G</td><td></td></tr>
                                <tr><td>Music</td><td>AVG</td><td></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Grade Legend -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="grade-display d-flex justify-content-around align-items-center flex-wrap">
                            <span>Grade: V.Good {VG}</span>
                            <span>Good {G}</span>
                            <span>Average {AVG}</span>
                            <span>Below Average {BA}</span>
                            <span>Poor {P}</span>
                        </div>
                    </div>
                </div>

                <!-- Remarks Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <table class="w-100 remarks-table">
                            <tbody>
                                <tr>
                                    <td class="w-50">
                                        <div class="h6">Class Teacher's Remark Signature/Date</div>
                                        <div class="w-100">
                                            <span class="text-space-on-dots">Excellent performance in most subjects</span>
                                        </div>
                                    </td>
                                    <td class="w-50">
                                        <div class="h6">Remark On Other Activities</div>
                                        <div class="">
                                            <span class="text-space-on-dots">Very active in extracurricular activities</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="w-50">
                                        <div class="h6">Guidance Counselor's Remark Signature/Date</div>
                                        <div class="">
                                            <span class="text-space-on-dots">Well-adjusted student with good behavior</span>
                                        </div>
                                    </td>
                                    <td class="w-50">
                                        <div class="h6">Principal's Remark Signature/Date</div>
                                        <div class="">
                                            <span class="text-space-on-dots">Keep up the good work</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer Section -->
                <div class="row mb-2">
                    <div class="col-12">
                        <div class="footer-section">
                            <div class="d-flex flex-row justify-content-between align-items-center p-2 flex-wrap gap-4">
                                <span class="fw-bold">This Result was issued on<span class="m-2 text-dot-space2">........................</span></span>
                                <span class="fw-bold">and collected by<span class="m-2 text-dot-space2">........................</span></span>
                            </div>
                            <div class="d-flex flex-row justify-content-center align-items-center p-2">
                                <span class="h5 fw-bold text-primary">NEXT TERM BEGINS<span class="m-2 text-dot-space2">........................</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Generate watermark text across the page
        function createWatermark() {
            const watermark = document.querySelector('.watermark');
            const text = 'TOPCLASS COLLEGE, ONDO';
            const windowHeight = window.innerHeight;
            const windowWidth = window.innerWidth;
            
            // Clear existing watermark
            watermark.innerHTML = '';
            
            // Calculate spacing
            const verticalSpacing = 150;
            const horizontalSpacing = 400;
            
            for (let y = 0; y < windowHeight + 200; y += verticalSpacing) {
                for (let x = -200; x < windowWidth + 200; x += horizontalSpacing) {
                    const span = document.createElement('span');
                    span.className = 'watermark-text';
                    span.textContent = text;
                    span.style.left = x + 'px';
                    span.style.top = y + 'px';
                    watermark.appendChild(span);
                }
            }
        }
        
        // Create watermark on load
        document.addEventListener('DOMContentLoaded', createWatermark);
        
        // Recreate watermark on resize
        window.addEventListener('resize', createWatermark);
    </script>
</body>
</html>