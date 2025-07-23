<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal Progress Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            /* box-sizing: border-box;
            margin: 0;
            padding: 0; */
        }
        .fraction {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        .fraction .numerator {
            border-bottom: 2px solid black;
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
            border-bottom-style: dotted;
        }
        span.text-dot-space2 {
            position: relative;
            width: 300px;
            border-bottom-style: dotted;
        }
        @media print {
            div.print-body {
                background-color: white;
            }
            @page {
                size: 940px;
                margin: 0px;
            }
            div.print-body {
                background-color: white;
            }
            html,
            body {
                width: 940px;
            }
            body {
                margin: 0;
            }
            nav {
                display: none;
            }
        }
        p.school-name1 {
            font-family: 'Times New Roman', Times, serif;
            font-size: 40px;
            font-weight: 500;
        }
        p.school-name2 {
            font-family: 'Times New Roman', Times, serif;
            font-size: 30px;
            font-weight: bolder;
        }
        div.school-logo {
            width: 80px;
            height: 60px;
        }
        div.header-divider {
            width: 100%;
            height: 3px;
            background-color: black;
            margin-bottom: 3px;
        }
        div.header-divider2 {
            width: 100%;
            height: 1px;
            background-color: black;
        }
        span.result-details {
            font-size: 16px;
            font-family: 'Times New Roman', Times, serif;
            font-weight: lighter;
            font-style: italic;
        }
        span.rd1 {
            position: relative;
            width: 86.1%;
            border-bottom-style: dotted;
        }
        span.rd2 {
            position: relative;
            width: 30%;
            border-bottom-style: dotted;
        }
        span.rd3 {
            position: relative;
            width: 30%;
            border-bottom-style: dotted;
        }
        span.rd4 {
            position: relative;
            width: 30%;
            border-bottom-style: dotted;
        }
        span.rd5 {
            position: relative;
            width: 25%;
            border-bottom-style: dotted;
        }
        span.rd6 {
            position: relative;
            width: 28%;
            border-bottom-style: dotted;
        }
        span.rd7 {
            position: relative;
            width: 17.2%;
            border-bottom-style: dotted;
        }
        span.rd8 {
            position: relative;
            width: 12%;
            border-bottom-style: dotted;
        }
        span.rd9 {
            position: relative;
            width: 11%;
            border-bottom-style: dotted;
        }
        span.rd10 {
            position: relative;
            width: 11%;
            border-bottom-style: dotted;
        }
        /* Updated table border styles */
        table, tr, td, th, thead, tbody {
            border: 2px solid blue !important;
        }
        /* Updated highlight class for scores less than 40 */
        .highlight-red {
            color: red !important;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="print-body bg-light w-100 h-100">
            <div class="print-sect container-fluid border bg-white" style="width: 1200px;">
                <div class="row mb-2">
                    <div class="col-md d-flex flex-column">
                        <div class="w-100 d-flex justify-content-center align-items-center pt-1">
                            <div class="school-logo">
                                <img src="{{ asset('print-main/public/assets/tp.png') }}" class="w-100 h-100" alt="">
                            </div>
                            <div>
                                <p class="school-name1 m-0">TCC</p>
                            </div>
                        </div>
                        <div class="w-100 d-flex justify-content-center align-items-center">
                            <p class="school-name2 m-0">TOPCLASS COLLEGE</p>
                        </div>
                        <div class="w-100 d-flex flex-column justify-content-center align-items-center">
                            <p class="h4 m-0">Developing the total child</p>
                            <p class="h4 m-0">39, Okegbala Street off Odojomu Road, Ondo.</p>
                        </div>
                        <div class="header-divider"></div>
                        <div class="header-divider2"></div>
                        <div class="w-100 d-flex flex-column justify-content-center align-items-center">
                            <p class="h1 m-0 bg-black text-white px-1 rounded" style="font-family: 'Times New Roman', Times, serif;">
                                TERMINAL PROGRESS REPORT FOR SSS 1-3
                            </p>
                        </div>
                    </div>
                </div>
                <div class="row mb-2 d-flex flex-row">
                    <div class="col-sm-10 bg-white d-flex flex-column justify-content-left gap-3">
                        <div class="d-flex flex-row flex-nowrap align-items-center gap-2">
                            <span class="result-details">Name of Student:</span><span class="rd1"></span>
                        </div>
                        <div class="d-flex flex-row align-items-center gap-2">
                            <span class="result-details">Session:</span><span class="rd2"></span>
                            <span class="result-details">Term:</span><span class="rd3"></span>
                            <span class="result-details">Class:</span><span class="rd4"></span>
                        </div>
                        <div class="d-flex flex-row align-items-center gap-2">
                            <span class="result-details">Date of Birth:</span><span class="rd5"></span>
                            <span class="result-details">Admission No:</span><span class="rd6"></span>
                            <span class="result-details">Sex:</span><span class="rd7"></span>
                        </div>
                        <div class="d-flex flex-row align-items-center gap-2">
                            <span class="result-details">No. of Times School Opened:</span><span class="rd8"></span>
                            <span class="result-details">No. of Times School Absent:</span><span class="rd9"></span>
                            <span class="result-details">No. of Student in Class:</span><span class="rd10"></span>
                        </div>
                    </div>
                    <div class="col bg-white d-flex justify-content-center align-items-center">
                        <div class="h-100 bg-light" style="width: 90%; box-shadow: 50px">
                            <img src="{{ asset('print-main/public/assets/siji.jpg') }}" class="w-100 h-100" alt="">
                        </div>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm bg-white">
                        <div class="mt-3 result-table">
                            <table class="table table-bordered table-hover table-responsive-sm" style="border: 1px solid black;">
                                                            <thead style="border: 1px solid black;">
                                                                <tr class="rt">
                                                                    <th>S/N</th>
                                                                    <th>Subjects</th>
                                                                    <th>Term Exam</th>
                                                                    <th>Grade</th>
                                                                    <th>Position</th>
                                                                    <th>Class Average</th>
                                                                  
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse ($mockScores as $index => $score)
                                                                    <tr>
                                                                        <td align="center" style="font-size: 14px;">{{ $index + 1 }}</td>
                                                                        <td align="center" style="font-size: 14px;">{{ $score->subject_name }}</td>
                                                                        <td align="center" style="font-size: 14px;" @if ($score->exam <= 50 && is_numeric($score->exam)) class="highlight-red" @endif>{{ $score->exam ?? '-' }}</td>
                                                                        <td align="center" style="font-size: 14px;" @if (in_array($score->grade, ['F', 'F9','E','E8'])) class="highlight-red" @endif>{{ $score->grade ?? '-' }}</td>
                                                                        <td align="center" style="font-size: 14px;">{{ $score->position ?? '-' }}</td>
                                                                        <td align="center" style="font-size: 14px;" @if ($score->class_average <= 50 && is_numeric($score->class_average)) class="highlight-red" @endif>{{ $score->class_average ?? '-' }}</td>
                                                                   
                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="7" align="center">No mock scores available for this student.</td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                        </div>
                    </div>
                </div>
                 <div class="row gap-2 mb-2 flex flex-row">
                                                <div class="col bg-white rounded">
                                                    <div class="mt-2">
                                                        <div class="h5">Character Assessment</div>
                                                        <table class="table table-bordered table-hover table-responsive-sm" style="border: 1px solid black;">
                                                            <thead style="border: 1px solid black;">
                                                                <tr>
                                                                    <th></th>
                                                                    <th>Grade</th>
                                                                    <th>Sign</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($studentpp as $s)
                                                                    <tr>
                                                                        <td>Class Attendance</td>
                                                                        <td>{{ $s->attendance ?? '-' }}</td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Attentiveness in Class</td>
                                                                        <td>{{ $s->attentiveness_in_class ?? '-' }}</td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Class Participation</td>
                                                                        <td>{{ $s->class_participation ?? '-' }}</td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Self Control</td>
                                                                        <td>{{ $s->selfcontrol ?? '-' }}</td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Relationship with Others</td>
                                                                        <td>{{ $s->relationship_with_others ?? '-' }}</td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Doing Assignment</td>
                                                                        <td>{{ $s->doing_assignment ?? '-' }}</td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Neatness</td>
                                                                        <td>{{ $s->neatness ?? '-' }}</td>
                                                                        <td></td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="col bg-white rounded">
                                                    <div class="mt-2">
                                                        <div class="h5">Skill Development</div>
                                                        <table class="table table-bordered table-hover table-responsive-sm" style="border: 1px solid black;">
                                                            <thead style="border: 1px solid black;">
                                                                <tr>
                                                                    <th></th>
                                                                    <th>Grade</th>
                                                                    <th>Sign</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($studentpp as $s)
                                                                    <tr>
                                                                        <td>Writing Skill</td>
                                                                        <td>{{ $s->writing_skill ?? '-' }}</td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Reading Skill</td>
                                                                        <td>{{ $s->reading_skill ?? '-' }}</td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Spoken English/Communication</td>
                                                                        <td>{{ $s->spoken_english_communication ?? '-' }}</td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Hand Writing</td>
                                                                        <td>{{ $s->hand_writing ?? '-' }}</td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Sports/Games</td>
                                                                        <td>{{ $s->gamesandsports ?? '-' }}</td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Club</td>
                                                                        <td>{{ $s->club ?? '-' }}</td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Music</td>
                                                                        <td>{{ $s->music ?? '-' }}</td>
                                                                        <td></td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-md bg-white rounded grade d-flex justify-content-around align-items-center">
                                                    <span>Grade: V.Good {VG}</span>
                                                    <span>Good {G}</span>
                                                    <span>Average {AVG}</span>
                                                    <span>Below Average {BA}</span>
                                                    <span>Poor {P}</span>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-md bg-white rounded">
                                                    <div class="m-2">
                                                        <table class="w-100 table-bordered" style="border: 1px solid black;">
                                                            <tbody class="w-100">
                                                                <tr class="w-100">
                                                                    <td class="p-2 w-50">
                                                                        <div class="h6">Class Teacher's Remark Signature/Date</div>
                                                                        <div class="w-100">
                                                                            <span class="text-space-on-dots">{{ $studentpp[0]->classteachercomment ?? 'N/A' }}</span>
                                                                        </div>
                                                                    </td>
                                                                    <td class="p-2 w-50">
                                                                        <div class="h6">Remark On Other Activities</div>
                                                                        <div class="">
                                                                            <span class="text-space-on-dots">N/A</span>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr class="w-50">
                                                                    <td class="p-2 w-50">
                                                                        <div class="h6">Guidance Counselor's Remark Signature/Date</div>
                                                                        <div class="">
                                                                            <span class="text-space-on-dots">N/A</span>
                                                                        </div>
                                                                    </td>
                                                                    <td class="p-2 w-50">
                                                                        <div class="h6">Principal's Remark Signature/Date</div>
                                                                        <div class="">
                                                                            <span class="text-space-on-dots">{{ $studentpp[0]->principalscomment ?? 'N/A' }}</span>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-md bg-white rounded px-4">
                                                    <div class="d-flex flex-row justify-content-left align-items-center p-2 gap-4">
                                                        <span>This Result was issued on<span class="m-2 text-dot-space2">N/A</span></span>
                                                        <span>and collected by<span class="m-2 text-dot-space2">N/A</span></span>
                                                    </div>
                                                    <div class="d-flex flex-row justify-content-left align-items-center p-2 gap-4">
                                                        <span class="h6">NEXT TERM BEGINS<span class="m-2 text-dot-space2">N/A</span></span>
                                                    </div>
                                                </div>
                                            </div>
            </div>
        </div>
    </div>
</body>
</html>