<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use App\Models\SchoolInformation;

class StudentReportExport implements WithMultipleSheets
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Group students by class name (fallback to 'Unassigned')
        $grouped = $this->data['students']->groupBy(function ($student) {
            return $student->currentClass?->schoolclass?->schoolclass ?? 'Unassigned';
        });

        // One sheet per class
        foreach ($grouped as $className => $students) {
            $sheets[$className] = new PerClassSheet($students, $this->data['columns'], $className);
        }

        // Final summary sheet
        $sheets['Summary'] = new SummarySheet($this->data);

        return $sheets;
    }
}

class PerClassSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents
{
    protected $students;
    protected $columns;
    protected $className;

    public function __construct(Collection $students, array $columns, string $className)
    {
        $this->students = $students;
        $this->columns = $columns;
        $this->className = $className;
    }

    public function collection()
    {
        return $this->students;
    }

    public function headings(): array
    {
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

        return array_map(fn($col) => $map[$col] ?? ucwords(str_replace('_', ' ', $col)), $this->columns);
    }

    public function map($student): array
    {
        $row = [];

        foreach ($this->columns as $col) {
            switch ($col) {
                case 'photo':
                    $row[] = $student->picture && $student->picture !== 'unnamed.jpg' ? 'Yes' : 'No';
                    break;

                case 'fullname':
                    $row[] = trim("{$student->lastname} {$student->firstname} {$student->othername}");
                    break;

                case 'class':
                    $cls = $student->currentClass?->schoolclass?->schoolclass ?? '-';
                    $arm = $student->currentClass?->schoolclass?->armRelation?->arm ?? '';
                    $row[] = $arm ? "$cls - $arm" : $cls;
                    break;

                case 'guardian_phone':
                    $row[] = $student->parent ? ($student->parent->father_phone ?: $student->parent->mother_phone ?: '-') : '-';
                    break;

                case 'admission_date':
                    $row[] = $student->admission_date ? $student->admission_date->format('d/m/Y') : '-';
                    break;

                case 'dateofbirth':
                    $row[] = $student->dateofbirth ? $student->dateofbirth->format('d/m/Y') : '-';
                    break;

                default:
                    $value = $student->$col ?? '-';
                    $row[] = is_object($value) && method_exists($value, 'format') ? $value->format('d/m/Y') : $value;
            }
        }

        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E40AF'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();

                // Insert title rows
                $sheet->insertNewRowBefore(1, 4);

                $sheet->setCellValue('A1', 'Student List - ' . $this->className);
                $sheet->mergeCells("A1:{$highestColumn}1");
                $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);

                $sheet->setCellValue('A2', 'Generated: ' . now()->format('d M Y h:i A'));
                $sheet->mergeCells("A2:{$highestColumn}2");

                $sheet->setCellValue('A3', 'Total Students in Class: ' . $this->students->count());
                $sheet->mergeCells("A3:{$highestColumn}3");

                // Auto-size all columns
                foreach (range('A', $highestColumn) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Freeze header + title rows
                $sheet->freezePane('A5');

                // Light zebra striping on data rows
                $sheet->getStyle("A5:{$highestColumn}{$highestRow}")
                    ->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFFAFAFA'],
                        ],
                    ]);
            },
        ];
    }
}

class SummarySheet implements WithEvents, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $school = SchoolInformation::where('is_active', true)->first();

                $sheet->setCellValue('A1', $school ? $school->school_name : 'School Management System');
                $sheet->mergeCells('A1:F1');
                $sheet->getStyle('A1')->getFont()->setSize(18)->setBold(true);

                $sheet->setCellValue('A2', $school ? $school->school_motto : 'Student Summary Report');
                $sheet->mergeCells('A2:F2');
                $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(12);

                $sheet->setCellValue('A3', 'Generated: ' . $this->data['generated']);
                $sheet->mergeCells('A3:F3');

                $row = 5;
                $sheet->setCellValue("A{$row}", 'Total Students');
                $sheet->setCellValue("B{$row}", $this->data['total']);
                $row++;

                $sheet->setCellValue("A{$row}", 'Male Students');
                $sheet->setCellValue("B{$row}", $this->data['males']);
                $row++;

                $sheet->setCellValue("A{$row}", 'Female Students');
                $sheet->setCellValue("B{$row}", $this->data['females']);
                $row += 2;

                $sheet->setCellValue("A{$row}", 'Students per Class');
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                $row++;

                $classes = $this->data['students']->groupBy(function ($s) {
                    return $s->currentClass?->schoolclass?->schoolclass ?? 'Unassigned';
                });

                foreach ($classes as $class => $group) {
                    $sheet->setCellValue("A{$row}", $class);
                    $sheet->setCellValue("B{$row}", $group->count());
                    $row++;
                }

                $row += 2;

                $sheet->setCellValue("A{$row}", 'Students per Religion');
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                $row++;

                $religions = $this->data['students']->groupBy('religion');
                foreach ($religions as $rel => $group) {
                    $sheet->setCellValue("A{$row}", $rel ?: 'Not Specified');
                    $sheet->setCellValue("B{$row}", $group->count());
                    $row++;
                }

                $row += 2;

                $sheet->setCellValue("A{$row}", 'Students per Blood Group');
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                $row++;

                $bloods = $this->data['students']->groupBy('blood_group');
                foreach ($bloods as $bg => $group) {
                    $sheet->setCellValue("A{$row}", $bg ?: 'Not Specified');
                    $sheet->setCellValue("B{$row}", $group->count());
                    $row++;
                }

                $sheet->getStyle("A5:B" . ($row - 1))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFCBD5E1'],
                        ],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFF1F5F9'],
                    ],
                ]);

                $sheet->getStyle("A5:A" . ($row - 1))->getFont()->setBold(true);

                $sheet->getColumnDimension('A')->setWidth(30);
                $sheet->getColumnDimension('B')->setWidth(15);

                // School logo fallback
                if (file_exists(public_path('images/school-logo.png'))) {
                    $drawing = new Drawing();
                    $drawing->setName('School Logo');
                    $drawing->setPath(public_path('images/school-logo.png'));
                    $drawing->setCoordinates('D1');
                    $drawing->setHeight(80);
                    $drawing->setWorksheet($sheet);
                } else {
                    $sheet->setCellValue('D1', 'School Logo');
                    $sheet->getStyle('D1')->getFont()->setSize(12)->setBold(true);
                }
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['size' => 18, 'bold' => true]],
            2 => ['font' => ['italic' => true, 'size' => 12]],
        ];
    }
}
