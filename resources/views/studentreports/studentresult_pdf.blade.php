<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal Progress Report</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333333;
            background: #ffffff;
        }

        @page {
            size: A4;
            margin: 15mm;
        }

        .page {
            width: 100%;
            min-height: 100%;
            page-break-after: always;
            padding: 10px;
        }

        .page:last-child {
            page-break-after: avoid;
        }

        .content-wrapper {
            background: #ffffff;
            border: 1px solid #cccccc;
            padding: 10px;
        }

        /* Header Styles */
        .header-section {
            text-align: center;
            margin-bottom: 10px;
        }

        .school-logo {
            width: 80px;
            height: 80px;
            border: 2px solid #1e40af;
            display: block;
            margin: 0 auto;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e40af;
            font-weight: bold;
        }

        .school-name {
            font-size: 20px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }

        .school-motto, .school-address {
            font-size: 10px;
            color: #555555;
            margin-bottom: 3px;
        }

        .header-divider {
            width: 100%;
            height: 2px;
            background: #1e40af;
            margin: 5px 0;
        }

        .report-title {
            background: #374151;
            color: #ffffff;
            padding: 8px;
            font-size: 16px;
            font-weight: bold;
            margin: 5px 0;
        }

        /* Student Info Section */
        .student-info-section {
            display: flex;
            width: 100%;
            margin-bottom: 10px;
        }

        .student-details {
            flex: 0 0 70%;
            padding-right: 10px;
        }

        .student-photo-container {
            flex: 0 0 30%;
            height: 120px;
            border: 2px solid #1e40af;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e40af;
            font-size: 10px;
        }

        .student-photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .info-item {
            margin-bottom: 5px;
        }

        .info-label {
            font-weight: bold;
            color: #333333;
            margin-right: 5px;
            font-size: 12px;
            display: inline-block;
            width: 120px;
        }

        .info-value {
            border-bottom: 1px dotted #666666;
            font-size: 12px;
            display: inline-block;
            width: calc(100% - 130px);
            min-height: 16px;
        }

        /* Results Table */
        .results-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #1e40af;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .results-table th {
            background: #1e40af;
            color: #ffffff;
            font-weight: bold;
            border: 1px solid #1e40af;
            padding: 5px;
            text-align: center;
            font-size: 10px;
        }

        .results-table td {
            border: 1px solid #cccccc;
            padding: 5px;
            text-align: center;
            font-size: 10px;
        }

        .results-table td:nth-child(2) {
            text-align: left !important;
            font-weight: bold;
        }

        .highlight-red {
            color: #cc0000 !important;
            font-weight: bold;
        }

        .highlight-bold {
            font-weight: bold;
        }

        /* Assessment Tables */
        .assessment-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #f59e0b;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .assessment-table th {
            background: #f59e0b;
            color: #ffffff;
            font-weight: bold;
            border: 1px solid #f59e0b;
            padding: 5px;
            text-align: center;
            font-size: 10px;
        }

        .assessment-table td {
            border: 1px solid #cccccc;
            padding: 5px;
            font-size: 10px;
        }

        /* Grading Scale */
        .grading-scale {
            background: #f59e0b;
            color: #ffffff;
            padding: 5px;
            margin-bottom: 10px;
            font-size: 10px;
            text-align: center;
            font-weight: bold;
        }

        /* Remarks Table */
        .remarks-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #7c3aed;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .remarks-table td {
            border: 1px solid #c4b5fd;
            padding: 5px;
            font-size: 10px;
            vertical-align: top;
        }

        .remarks-label {
            color: #6d28d9;
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        .remarks-content {
            border-bottom: 1px dotted #666666;
            display: block;
            min-height: 20px;
            padding-bottom: 2px;
        }

        /* Footer Section */
        .footer-section {
            background: #f1f5f9;
            padding: 5px;
            border: 1px solid #cccccc;
            font-size: 10px;
        }

        .signature-row {
            display: flex;
            width: 100%;
        }

        .signature-item {
            flex: 0 0 50%;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .page {
                page-break-inside: avoid;
            }

            .results-table,
            .assessment-table,
            .remarks-table {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="content-wrapper">
            <!-- Header Section -->
            <div class="header-section">
                <div class="school-logo">LOGO</div>
                <div class="school-name">QUODOROID CODING ACADEMY</div>
                <div class="school-motto">Excellence in Education</div>
                <div class="school-address">
                    123 Education Street, Lagos, Nigeria<br>
                    www.quodoroid.edu.ng
                </div>
                <div class="header-divider"></div>
                <div class="report-title">TERMINAL PROGRESS REPORT</div>
            </div>

            <!-- Student Info Section -->
            <div class="student-info-section">
                <div class="student-details">
                    <div class="info-item">
                        <span class="info-label">Name of Student:</span>
                        <span class="info-value">John Doe Smith</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Session:</span>
                        <span class="info-value">2024/2025</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Term:</span>
                        <span class="info-value">First Term</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Class:</span>
                        <span class="info-value">JSS 1A</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Date of Birth:</span>
                        <span class="info-value">15/03/2010</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Admission No:</span>
                        <span class="info-value">QCA/2024/001</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Sex:</span>
                        <span class="info-value">Male</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Students in Class:</span>
                        <span class="info-value">35</span>
                    </div>
                </div>
                <div class="student-photo-container">
                    Student Photo
                </div>
            </div>

            <!-- Results Table -->
            <div class="results-section">
                <table class="results-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">S/N</th>
                            <th style="width: 20%;">Subjects</th>
                            <th style="width: 8%;">T1</th>
                            <th style="width: 8%;">T2</th>
                            <th style="width: 8%;">T3</th>
                            <th style="width: 8%;">Term Exams</th>
                            <th style="width: 8%;">Total</th>
                            <th style="width: 8%;">Grade</th>
                            <th style="width: 8%;">PSN</th>
                            <th style="width: 8%;">Class Avg</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td style="text-align: left;">Mathematics</td>
                            <td>18</td>
                            <td>17</td>
                            <td>19</td>
                            <td>65</td>
                            <td>85</td>
                            <td>A</td>
                            <td>2nd</td>
                            <td>72</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td style="text-align: left;">English Language</td>
                            <td>16</td>
                            <td>18</td>
                            <td>17</td>
                            <td>58</td>
                            <td>78</td>
                            <td>B</td>
                            <td>5th</td>
                            <td>69</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td style="text-align: left;">Basic Science</td>
                            <td>15</td>
                            <td>16</td>
                            <td>18</td>
                            <td>52</td>
                            <td>72</td>
                            <td>B</td>
                            <td>8th</td>
                            <td>65</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td style="text-align: left;">Social Studies</td>
                            <td>12</td>
                            <td class="highlight-red">8</td>
                            <td>14</td>
                            <td class="highlight-red">28</td>
                            <td class="highlight-red">48</td>
                            <td class="highlight-red">E</td>
                            <td>25th</td>
                            <td>55</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td style="text-align: left;">Computer Studies</td>
                            <td>19</td>
                            <td>20</td>
                            <td>18</td>
                            <td>68</td>
                            <td>92</td>
                            <td>A</td>
                            <td>1st</td>
                            <td>78</td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td style="text-align: left;">French</td>
                            <td>14</td>
                            <td>15</td>
                            <td>16</td>
                            <td>45</td>
                            <td>62</td>
                            <td>C</td>
                            <td>12th</td>
                            <td>58</td>
                        </tr>
                        <tr>
                            <td>7</td>
                            <td style="text-align: left;">Physical Education</td>
                            <td>17</td>
                            <td>18</td>
                            <td>19</td>
                            <td>56</td>
                            <td>75</td>
                            <td>B</td>
                            <td>6th</td>
                            <td>71</td>
                        </tr>
                        <tr>
                            <td>8</td>
                            <td style="text-align: left;">Creative Arts</td>
                            <td>16</td>
                            <td>17</td>
                            <td>15</td>
                            <td>52</td>
                            <td>68</td>
                            <td>C</td>
                            <td>10th</td>
                            <td>63</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Grading Scale -->
            <div class="grading-scale">
                <strong>Academic Grading Scale:</strong> 80-100 (A), 70-79 (B), 60-69 (C), 50-59 (D), 40-49 (E), 0-39 (F) | 
                Senior: A1 (75-100), B2 (70-74), B3 (65-69), C4 (60-64), C5 (55-59), C6 (50-54), E8 (40-49), F9 (0-39)
            </div>

            <!-- Remarks Section -->
            <div class="remarks-section">
                <table class="remarks-table">
                    <tbody>
                        <tr>
                            <td style="width: 50%;">
                                <span class="remarks-label">Principal's Remark:</span>
                                <span class="remarks-content">Good performance overall. Needs improvement in Social Studies.</span>
                            </td>
                            <td style="width: 50%;">
                                <span class="remarks-label">Promotion Status:</span>
                                <span class="remarks-content">Promoted to JSS 2</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>