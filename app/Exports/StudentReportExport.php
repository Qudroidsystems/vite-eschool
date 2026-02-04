<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class StudentReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data['students'];
    }

    public function headings(): array
    {
        $headings = [];
        $columns = $this->data['columns'];

        $map = [
            'photo'          => 'Photo',
            'admissionNo'    => 'Admission Number',
            'fullname'       => 'Full Name',
            'gender'         => 'Gender',
            'dateofbirth'    => 'Date of Birth',
            'age'            => 'Age',
            'class'          => 'Class / Arm',
            'status'         => 'Student Status',
            'admission_date' => 'Admission Date',
            'phone_number'   => 'Phone Number',
            'state'          => 'State of Origin',
            'local'          => 'Local Government Area (LGA)',
            'religion'       => 'Religion',
            'blood_group'    => 'Blood Group',
            'father_name'    => "Father's Name",
            'mother_name'    => "Mother's Name",
            'guardian_phone' => 'Guardian Contact Phone',
        ];

        foreach ($columns as $col) {
            $headings[] = $map[$col] ?? ucwords(str_replace('_', ' ', $col));
        }

        return $headings;
    }

    public function map($student): array
    {
        $row = [];
        $columns = $this->data['columns'];

        foreach ($columns as $col) {
            switch ($col) {
                case 'photo':
                    $row[] = ($student->picture && $student->picture !== 'unnamed.jpg') ? 'Yes' : 'No';
                    break;

                case 'fullname':
                    $fullname = trim(($student->lastname ?? '') . ' ' . ($student->firstname ?? '') . ' ' . ($student->othername ?? ''));
                    $row[] = $fullname ?: 'N/A';
                    break;

                case 'class':
                    $class = $student->schoolclass ?? 'N/A';
                    $arm = $student->arm_name ?? '';
                    $row[] = $arm ? "$class - $arm" : $class;
                    break;

                case 'guardian_phone':
                    $phone = $student->father_phone ?? $student->mother_phone ?? '';
                    $row[] = $phone ?: 'N/A';
                    break;

                case 'admission_date':
                    if ($student->admission_date) {
                        try {
                            $row[] = Carbon::parse($student->admission_date)->format('d/m/Y');
                        } catch (\Exception $e) {
                            $row[] = $student->admission_date;
                        }
                    } else {
                        $row[] = 'N/A';
                    }
                    break;

                case 'dateofbirth':
                    if ($student->dateofbirth) {
                        try {
                            $row[] = Carbon::parse($student->dateofbirth)->format('d/m/Y');
                        } catch (\Exception $e) {
                            $row[] = $student->dateofbirth;
                        }
                    } else {
                        $row[] = 'N/A';
                    }
                    break;

                case 'status':
                    $status = '';
                    if (isset($student->statusId)) {
                        if ($student->statusId == 1) $status = 'Old Student';
                        if ($student->statusId == 2) $status = 'New Student';
                    }
                    if ($student->student_status) {
                        $status .= ($status ? ' (' . $student->student_status . ')' : $student->student_status);
                    }
                    $row[] = $status ?: 'N/A';
                    break;

                default:
                    if (property_exists($student, $col)) {
                        $value = $student->$col;
                        $row[] = $value !== null && $value !== '' ? $value : 'N/A';
                    } else {
                        $row[] = 'N/A';
                    }
                    break;
            }
        }

        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        $totalRows = $this->data['students']->count() + 4;

        return [
            5 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E40AF']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],

            'A6:A' . $totalRows => [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F8FAFC']
                ]
            ],

            'A5:' . $sheet->getHighestColumn() . $totalRows => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB']
                    ]
                ]
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Insert title rows at the top
                $sheet->insertNewRowBefore(1, 4);

                // School header if included
                if ($this->data['include_header'] ?? true) {
                    $schoolInfo = $this->data['school_info'] ?? null;

                    if ($schoolInfo) {
                        $sheet->setCellValue('A1', $schoolInfo->school_name ?? 'STUDENT MASTER LIST REPORT');
                        $sheet->mergeCells('A1:' . $sheet->getHighestColumn() . '1');
                        $sheet->getStyle('A1')->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 16,
                                'color' => ['rgb' => '1E40AF']
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER
                            ]
                        ]);

                        if ($schoolInfo->school_motto) {
                            $sheet->setCellValue('A2', $schoolInfo->school_motto);
                            $sheet->mergeCells('A2:' . $sheet->getHighestColumn() . '2');
                            $sheet->getStyle('A2')->applyFromArray([
                                'font' => [
                                    'italic' => true,
                                    'size' => 12
                                ],
                                'alignment' => [
                                    'horizontal' => Alignment::HORIZONTAL_CENTER
                                ]
                            ]);
                        }

                        $details = 'Class: ' . $this->data['className'] .
                                  ' | Generated: ' . $this->data['generated'] .
                                  ' | Total Students: ' . $this->data['total'];
                        $sheet->setCellValue('A3', $details);
                        $sheet->mergeCells('A3:' . $sheet->getHighestColumn() . '3');
                        $sheet->getStyle('A3')->applyFromArray([
                            'font' => ['size' => 10],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                        ]);
                    } else {
                        $sheet->setCellValue('A1', 'STUDENT MASTER LIST REPORT');
                        $sheet->mergeCells('A1:' . $sheet->getHighestColumn() . '1');
                        $sheet->getStyle('A1')->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 16,
                                'color' => ['rgb' => '1E40AF']
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER
                            ]
                        ]);

                        $details = 'Class: ' . $this->data['className'] .
                                  ' | Generated: ' . $this->data['generated'] .
                                  ' | Total Students: ' . $this->data['total'];
                        $sheet->setCellValue('A2', $details);
                        $sheet->mergeCells('A2:' . $sheet->getHighestColumn() . '2');
                        $sheet->getStyle('A2')->applyFromArray([
                            'font' => ['size' => 11],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                        ]);
                    }
                } else {
                    $sheet->setCellValue('A1', 'STUDENT MASTER LIST REPORT - ' . $this->data['className']);
                    $sheet->mergeCells('A1:' . $sheet->getHighestColumn() . '1');
                    $sheet->getStyle('A1')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 16,
                            'color' => ['rgb' => '1E40AF']
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER
                        ]
                    ]);

                    $details = 'Generated: ' . $this->data['generated'] .
                              ' | Total Students: ' . $this->data['total'] .
                              ' | Males: ' . $this->data['males'] .
                              ' | Females: ' . $this->data['females'];
                    $sheet->setCellValue('A2', $details);
                    $sheet->mergeCells('A2:' . $sheet->getHighestColumn() . '2');
                    $sheet->getStyle('A2')->applyFromArray([
                        'font' => ['size' => 11],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                    ]);
                }

                // Empty row before header
                $sheet->setCellValue('A4', '');

                // Auto-size columns
                foreach (range('A', $sheet->getHighestColumn()) as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                // Set row height for header
                $sheet->getRowDimension(5)->setRowHeight(30);

                // Freeze header row (row 5)
                $sheet->freezePane('A6');

                // Add filter to header row
                $sheet->setAutoFilter('A5:' . $sheet->getHighestColumn() . '5');
            }
        ];
    }
}
