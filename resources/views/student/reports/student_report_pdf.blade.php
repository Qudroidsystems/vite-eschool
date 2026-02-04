<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header .subtitle {
            margin-top: 5px;
            color: #666;
            font-size: 12px;
        }
        .summary {
            margin-bottom: 15px;
            padding: 8px;
            background-color: #f8f9fa;
            border-radius: 4px;
            font-size: 11px;
        }
        .summary table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary td {
            padding: 4px;
            border: 1px solid #dee2e6;
        }
        .summary .label {
            font-weight: bold;
            background-color: #e9ecef;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 9px;
        }
        th {
            background-color: #405189;
            color: white;
            padding: 6px;
            text-align: left;
            border: 1px solid #dee2e6;
            font-weight: bold;
        }
        td {
            padding: 5px;
            border: 1px solid #dee2e6;
            line-height: 1.3;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 8px;
        }
        .student-photo {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #ddd;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .nowrap {
            white-space: nowrap;
        }
        .page-number:after {
            content: counter(page);
        }
        .page-count:after {
            content: counter(pages);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="subtitle">
            Class: {{ $className }} | Generated: {{ $generated }}
        </div>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td class="label" width="20%">Total Students:</td>
                <td width="30%">{{ $total }}</td>
                <td class="label" width="20%">Males:</td>
                <td width="30%">{{ $males }}</td>
            </tr>
            <tr>
                <td class="label">Females:</td>
                <td>{{ $females }}</td>
                <td class="label">Orientation:</td>
                <td>{{ ucfirst($orientation) }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                @if(in_array('photo', $columns))
                <th width="5%" class="text-center">Photo</th>
                @endif
                @if(in_array('admissionNo', $columns))
                <th width="10%" class="text-left">Admission No</th>
                @endif
                @if(in_array('fullname', $columns))
                <th width="20%" class="text-left">Full Name</th>
                @endif
                @if(in_array('gender', $columns))
                <th width="5%" class="text-center">Gender</th>
                @endif
                @if(in_array('dateofbirth', $columns))
                <th width="8%" class="text-center">Date of Birth</th>
                @endif
                @if(in_array('age', $columns))
                <th width="4%" class="text-center">Age</th>
                @endif
                @if(in_array('class', $columns))
                <th width="12%" class="text-left">Class</th>
                @endif
                @if(in_array('status', $columns))
                <th width="8%" class="text-center">Status</th>
                @endif
                @if(in_array('admission_date', $columns))
                <th width="8%" class="text-center">Admission Date</th>
                @endif
                @if(in_array('phone_number', $columns))
                <th width="10%" class="text-left">Phone Number</th>
                @endif
                @if(in_array('state', $columns))
                <th width="10%" class="text-left">State</th>
                @endif
                @if(in_array('local', $columns))
                <th width="10%" class="text-left">LGA</th>
                @endif
                @if(in_array('religion', $columns))
                <th width="8%" class="text-center">Religion</th>
                @endif
                @if(in_array('blood_group', $columns))
                <th width="6%" class="text-center">Blood Group</th>
                @endif
                @if(in_array('father_name', $columns))
                <th width="15%" class="text-left">Father's Name</th>
                @endif
                @if(in_array('mother_name', $columns))
                <th width="15%" class="text-left">Mother's Name</th>
                @endif
                @if(in_array('guardian_phone', $columns))
                <th width="10%" class="text-left">Guardian Phone</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
            <tr>
                @if(in_array('photo', $columns))
                <td class="text-center">
                    <div style="width: 30px; height: 30px; background-color: #ddd; border-radius: 50%; margin: 0 auto;"></div>
                </td>
                @endif
                @if(in_array('admissionNo', $columns))
                <td class="text-left">{{ $student->admissionNo ?? 'N/A' }}</td>
                @endif
                @if(in_array('fullname', $columns))
                <td class="text-left">
                    {{ ($student->lastname ?? '') . ' ' . ($student->firstname ?? '') . ' ' . ($student->othername ?? '') }}
                </td>
                @endif
                @if(in_array('gender', $columns))
                <td class="text-center">{{ $student->gender ?? 'N/A' }}</td>
                @endif
                @if(in_array('dateofbirth', $columns))
                <td class="text-center nowrap">
                    @if($student->dateofbirth)
                    {{ \Carbon\Carbon::parse($student->dateofbirth)->format('d/m/Y') }}
                    @else
                    N/A
                    @endif
                </td>
                @endif
                @if(in_array('age', $columns))
                <td class="text-center">{{ $student->age ?? 'N/A' }}</td>
                @endif
                @if(in_array('class', $columns))
                <td class="text-left">
                    {{ $student->schoolclass ?? 'N/A' }}{{ $student->arm_name ? ' - ' . $student->arm_name : '' }}
                </td>
                @endif
                @if(in_array('status', $columns))
                <td class="text-center">
                    @if($student->statusId == 1)
                    Old
                    @elseif($student->statusId == 2)
                    New
                    @else
                    N/A
                    @endif
                    @if($student->student_status)
                    <br><small>({{ $student->student_status }})</small>
                    @endif
                </td>
                @endif
                @if(in_array('admission_date', $columns))
                <td class="text-center nowrap">
                    @if($student->admission_date)
                    {{ \Carbon\Carbon::parse($student->admission_date)->format('d/m/Y') }}
                    @else
                    N/A
                    @endif
                </td>
                @endif
                @if(in_array('phone_number', $columns))
                <td class="text-left">{{ $student->phone_number ?? 'N/A' }}</td>
                @endif
                @if(in_array('state', $columns))
                <td class="text-left">{{ $student->state ?? 'N/A' }}</td>
                @endif
                @if(in_array('local', $columns))
                <td class="text-left">{{ $student->local ?? 'N/A' }}</td>
                @endif
                @if(in_array('religion', $columns))
                <td class="text-center">{{ $student->religion ?? 'N/A' }}</td>
                @endif
                @if(in_array('blood_group', $columns))
                <td class="text-center">{{ $student->blood_group ?? 'N/A' }}</td>
                @endif
                @if(in_array('father_name', $columns))
                <td class="text-left">{{ $student->father_name ?? 'N/A' }}</td>
                @endif
                @if(in_array('mother_name', $columns))
                <td class="text-left">{{ $student->mother_name ?? 'N/A' }}</td>
                @endif
                @if(in_array('guardian_phone', $columns))
                <td class="text-left">
                    {{ $student->father_phone ?? $student->mother_phone ?? 'N/A' }}
                </td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on {{ date('d/m/Y H:i:s') }}</p>
        <p>Page <span class="page-number"></span> of <span class="page-count"></span></p>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
            $size = 8;
            $font = $fontMetrics->getFont("DejaVu Sans");
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 35;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
</body>
</html>
