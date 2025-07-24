<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Terminal Progress Report</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
    </style>
</head>
<body>
    @foreach ($allStudentData as $index => $data)
        @include('studentreports.studentresult_pdf', ['data' => $data])
    @endforeach
</body>
</html>