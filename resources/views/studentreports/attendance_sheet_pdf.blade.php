<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Examination Attendance Sheet - {{ $schoolclass->schoolclass }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11px;
            color: #000;
            padding: 10mm;
        }
        .header-table { width: 100%; margin-bottom: 6px; }
        .header-table td { vertical-align: middle; padding: 0; }
        .school-logo { width: 70px; height: 70px; text-align: center; }
        .school-logo img { width: 100%; height: 100%; }
        .school-name { font-size: 18px; font-weight: 900; text-align: center; }
        .school-sub { font-size: 10px; font-weight: bold; text-align: center; }
        .header-divider { width: 100%; height: 2px; background: #000; margin: 6px 0; }
        .report-title {
            background: #111827; color: #fff; padding: 6px;
            text-align: center; font-size: 14px; font-weight: 700; margin: 8px 0;
        }
        .info-grid { width: 100%; margin-bottom: 10px; }
        .info-grid td { padding: 3px 6px; font-size: 11px; font-weight: bold; }
        .info-label { color: #333; }
        .info-value { border-bottom: 1px dotted #666; }
        table.attendance {
            width: 100%; border-collapse: collapse; margin-top: 8px;
        }
        table.attendance th {
            background: #0d1a3d; color: #fff; border: 1px solid #000;
            padding: 6px 4px; font-size: 10px; text-align: center;
        }
        table.attendance td {
            border: 1px solid #000; padding: 8px 4px; font-size: 10px;
        }
        table.attendance td.sn, table.attendance td.admno { text-align: center; }
        table.attendance td.name { text-align: left; }
        .box { width: 16px; height: 16px; border: 1px solid #000; display: inline-block; }
        .signature-line {
            margin-top: 30px; width: 100%;
        }
        .signature-line td { padding-top: 30px; font-size: 10px; text-align: center; }
        .sig-space { border-top: 1px solid #000; display: block; margin-top: 20px; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td width="15%">
                <div class="school-logo">
                    @if($schoolLogoPath)
                        <img src="{{ $schoolLogoPath }}" alt="Logo">
                    @endif
                </div>
            </td>
            <td width="70%">
                <div class="school-name">{{ $schoolInfo->school_name ?? 'TOPCLASS COLLEGE' }}</div>
                <div class="school-sub">{{ $schoolInfo->school_address ?? '' }}</div>
                <div class="school-sub">{{ $schoolInfo->school_phone ?? '' }} | {{ $schoolInfo->school_email ?? '' }}</div>
            </td>
            <td width="15%"></td>
        </tr>
    </table>
    <div class="header-divider"></div>
    <div class="report-title">EXAMINATION ATTENDANCE SHEET</div>

    <table class="info-grid">
        <tr>
            <td width="25%"><span class="info-label">Class:</span> <span class="info-value">{{ $schoolclass->schoolclass }} {{ $schoolclass->armRelation->arm ?? '' }}</span></td>
            <td width="25%"><span class="info-label">Session:</span> <span class="info-value">{{ $session->session }}</span></td>
            <td width="25%"><span class="info-label">Term:</span> <span class="info-value">{{ $term->term ?? 'N/A' }}</span></td>
            <td width="25%"><span class="info-label">No. of Students:</span> <span class="info-value">{{ $students->count() }}</span></td>
        </tr>
        <tr>
            <td colspan="2"><span class="info-label">Subject/Set By:</span> <span class="info-value">{{ $subjectTeacherLabel }}</span></td>
            <td><span class="info-label">Date:</span> <span class="info-value">{{ $examdate }}</span></td>
            <td><span class="info-label">Time:</span> <span class="info-value">{{ $examtime }}</span></td>
        </tr>
        <tr>
            <td colspan="4"><span class="info-label">Examiner(s):</span> <span class="info-value">{{ implode(', ', $examiners) }}</span></td>
        </tr>
    </table>

    <table class="attendance">
        <thead>
            <tr>
                <th style="width:5%">SN</th>
                <th style="width:15%">Admin No</th>
                <th style="width:30%">Full Name</th>
                <th style="width:10%">Present</th>
                <th style="width:10%">Absent</th>
                <th style="width:15%">Sign In</th>
                <th style="width:15%">Sign Out</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($students as $index => $student)
                <tr>
                    <td class="sn">{{ $index + 1 }}</td>
                    <td class="admno">{{ $student->admissionNo }}</td>
                    <td class="name">{{ strtoupper($student->lastname) }} {{ $student->firstname }} {{ $student->othername }}</td>
                    <td class="text-center"><span class="box"></span></td>
                    <td class="text-center"><span class="box"></span></td>
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="signature-line">
        <tr>
            <td width="50%"><span class="sig-space">Examiner's Signature</span></td>
            <td width="50%"><span class="sig-space">Invigilator's Signature</span></td>
        </tr>
    </table>
</body>
</html>
