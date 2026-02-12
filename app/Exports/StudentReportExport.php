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
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Carbon\Carbon;

class StudentReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents
{
    protected $data;
    protected $groupedColumns = [];

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->groupedColumns = $this->determineColumnGroups();
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
            'lastname'       => 'Last Name',
            'firstname'      => 'First Name',
            'othername'      => 'Other Name',
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
            'term'           => 'Term',
            'session'        => 'Session',
            'email'          => 'Email Address',
            'city'           => 'City',
            'nationality'    => 'Nationality',
            'placeofbirth'   => 'Place of Birth',
            'mother_tongue'  => 'Mother Tongue',
            'student_category' => 'Student Category',
            'future_ambition' => 'Future Ambition',
            'last_school'    => 'Last School',
            'last_class'     => 'Last Class',
            'father_occupation' => 'Father\'s Occupation',
            'mother_occupation' => 'Mother\'s Occupation',
            'father_city'    => 'Father\'s City',
            'parent_email'   => 'Parent Email',
            'parent_address' => 'Parent Address',
            'nin_number'     => 'NIN Number',
            'school_house'   => 'School House',
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
                    if (isset($student->picture) && $student->picture !== 'unnamed.jpg') {
                        $row[] = '✓ Photo';
                    } else {
                        $row[] = $this->getStudentInitials($student) ?: 'No Photo';
                    }
                    break;

                case 'admissionNo':
                    $row[] = $student->admissionNo ?? 'N/A';
                    break;

                case 'class':
                    $class = $student->schoolclass ?? $student->current_class_name ?? 'N/A';
                    $arm = $student->arm_name ?? $student->current_arm ?? '';
                    $row[] = $arm ? "$class - $arm" : $class;
                    break;

                case 'guardian_phone':
                    $phone = $student->father_phone ?? $student->mother_phone ?? $student->guardian_phone ?? '';
                    $row[] = $phone ?: 'N/A';
                    break;

                case 'admission_date':
                case 'dateofbirth':
                    $dateField = $col === 'admission_date' ? $student->admission_date : $student->dateofbirth;
                    if ($dateField) {
                        try {
                            $row[] = Carbon::parse($dateField)->format('d/m/Y');
                        } catch (\Exception $e) {
                            $row[] = $dateField;
                        }
                    } else {
                        $row[] = 'N/A';
                    }
                    break;

                case 'age':
                    if (isset($student->age) && $student->age) {
                        $row[] = $student->age;
                    } elseif (isset($student->dateofbirth) && $student->dateofbirth) {
                        try {
                            $dob = Carbon::parse($student->dateofbirth);
                            $row[] = $dob->age;
                        } catch (\Exception $e) {
                            $row[] = 'N/A';
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
                    if (isset($student->student_status) && $student->student_status) {
                        $status .= ($status ? ' - ' . $student->student_status : $student->student_status);
                    }
                    $row[] = $status ?: 'N/A';
                    break;

                case 'term':
                    $row[] = $student->current_term_name ?? $student->term ?? $this->data['termName'] ?? 'N/A';
                    break;

                case 'session':
                    $row[] = $student->current_session_name ?? $student->session ?? $this->data['sessionName'] ?? 'N/A';
                    break;

                default:
                    $value = $this->getStudentProperty($student, $col);
                    $row[] = $value !== null && $value !== '' ? $value : 'N/A';
                    break;
            }
        }

        return $row;
    }

    private function getStudentInitials($student)
    {
        $first = $student->firstname ?? $student->first_name ?? '';
        $last = $student->lastname ?? $student->last_name ?? '';
        return strtoupper(substr($first, 0, 1) . substr($last, 0, 1)) ?: 'ST';
    }

    private function getStudentProperty($student, $property)
    {
        if (property_exists($student, $property)) {
            return $student->$property;
        }

        $snakeCase = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $property));
        if (property_exists($student, $snakeCase)) {
            return $student->$snakeCase;
        }

        $mappings = [
            'admissionNo' => ['admissionNo', 'admission_no', 'admission_number'],
            'phone_number' => ['phone_number', 'phone', 'mobile'],
            'blood_group' => ['blood_group', 'bloodgroup'],
            'father_name' => ['father_name', 'father'],
            'mother_name' => ['mother_name', 'mother'],
            'parent_email' => ['parent_email', 'parentemail'],
            'parent_address' => ['parent_address', 'parentaddress'],
            'school_house' => ['school_house', 'schoolhouse', 'sport_house', 'house'],
        ];

        if (isset($mappings[$property])) {
            foreach ($mappings[$property] as $mappedProperty) {
                if (property_exists($student, $mappedProperty)) {
                    return $student->$mappedProperty;
                }
            }
        }

        return null;
    }

    private function determineColumnGroups()
    {
        $columns = $this->data['columns'];
        $groups = [];

        $groupDefinitions = [
            'Student Information' => ['photo', 'admissionNo', 'firstname', 'lastname', 'othername', 'gender', 'dateofbirth', 'age'],
            'Academic Information' => ['class', 'status', 'term', 'session', 'admission_date', 'student_category'],
            'Contact Information' => ['phone_number', 'email', 'parent_email', 'parent_address', 'guardian_phone'],
            'Geographical Information' => ['state', 'local', 'city', 'nationality', 'placeofbirth'],
            'Parent Information' => ['father_name', 'mother_name', 'father_phone', 'mother_phone', 'father_occupation', 'mother_occupation', 'father_city'],
            'Personal Information' => ['blood_group', 'religion', 'mother_tongue', 'nin_number', 'school_house'],
            'Additional Information' => ['future_ambition', 'last_school', 'last_class', 'reason_for_leaving'],
        ];

        foreach ($groupDefinitions as $groupName => $groupColumns) {
            $foundColumns = array_intersect($columns, $groupColumns);
            if (!empty($foundColumns)) {
                $groups[$groupName] = $foundColumns;
            }
        }

        return $groups;
    }

    public function styles(Worksheet $sheet)
    {
        $totalRows = $this->data['students']->count() + 6;
        $startRow = 7;

        $styles = [
            $startRow => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => isset($this->data['confidential']) && $this->data['confidential'] ? 'DC3545' : '1E40AF']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ]
            ],
            'A' . $startRow . ':' . $sheet->getHighestColumn() . $totalRows => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB']
                    ]
                ]
            ]
        ];

        for ($i = $startRow + 1; $i <= $totalRows; $i++) {
            if ($i % 2 == 0) {
                $styles['A' . $i . ':' . $sheet->getHighestColumn() . $i] = [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8FAFC']
                    ]
                ];
            }
        }

        return $styles;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->insertNewRowBefore(1, 6);

                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

                $this->addHeaderSection($sheet, $highestColumn);
                $this->addColumnGroups($sheet, 6, $highestColumnIndex);

                foreach (range('A', $highestColumn) as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                $sheet->getRowDimension(7)->setRowHeight(30);
                $sheet->freezePane('A8');
                $sheet->setAutoFilter('A7:' . $highestColumn . '7');

                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageSetup()->setOrientation(
                    ($this->data['orientation'] ?? 'portrait') === 'landscape'
                        ? \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
                        : \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT
                );
            }
        ];
    }

    private function addHeaderSection($sheet, $highestColumn)
    {
        $title = 'STUDENT MASTER LIST REPORT';
        if (isset($this->data['confidential']) && $this->data['confidential']) {
            $title = 'CONFIDENTIAL - ' . $title;
        }

        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:' . $highestColumn . '1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => isset($this->data['confidential']) && $this->data['confidential'] ? 'DC3545' : '1E40AF']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);

        $details = 'Class: ' . ($this->data['className'] ?? 'All Classes') .
                  ' | Term: ' . ($this->data['termName'] ?? 'All Terms') .
                  ' | Session: ' . ($this->data['sessionName'] ?? 'All Sessions') .
                  ' | Generated: ' . ($this->data['generated'] ?? now()->format('d/m/Y H:i:s')) .
                  ' | Total Students: ' . ($this->data['total'] ?? 0);

        $sheet->setCellValue('A2', $details);
        $sheet->mergeCells('A2:' . $highestColumn . '2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        if ($this->data['is_large_report'] ?? false) {
            $sheet->setCellValue('A3', '⚠️ Large report detected. Photos excluded for performance.');
            $sheet->mergeCells('A3:' . $highestColumn . '3');
            $sheet->getStyle('A3')->applyFromArray([
                'font' => ['italic' => true, 'color' => ['rgb' => '721C24']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8D7DA']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
        }

        $sheet->setCellValue('A4', 'Generated by: ' . ($this->data['generated_by'] ?? 'System'));
        $sheet->mergeCells('A4:' . $highestColumn . '4');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $sheet->setCellValue('A5', 'Generated on: ' . date('d/m/Y H:i:s'));
        $sheet->mergeCells('A5:' . $highestColumn . '5');
        $sheet->getStyle('A5')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $sheet->setCellValue('A6', '');
    }

    private function addColumnGroups($sheet, $rowIndex, $highestColumnIndex)
    {
        $groupRow = $rowIndex;

        foreach ($this->groupedColumns as $groupName => $columns) {
            $groupColumns = $this->getColumnIndicesForGroup($columns);
            if (empty($groupColumns)) continue;

            $startColumn = $groupColumns[0];
            $endColumn = end($groupColumns);

            if ($startColumn <= $endColumn) {
                $startCell = Coordinate::stringFromColumnIndex($startColumn);
                $endCell = Coordinate::stringFromColumnIndex($endColumn);

                $sheet->mergeCells($startCell . $groupRow . ':' . $endCell . $groupRow);
                $sheet->setCellValue($startCell . $groupRow, $groupName);
                $sheet->getStyle($startCell . $groupRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '333333']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E5E7EB']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'borders' => [
                        'top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '9CA3AF']],
                        'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]
                    ]
                ]);
            }
        }

        $sheet->getRowDimension($groupRow)->setRowHeight(20);
    }

    private function getColumnIndicesForGroup($columns)
    {
        $indices = [];
        $headerColumns = $this->data['columns'];

        foreach ($columns as $column) {
            $index = array_search($column, $headerColumns);
            if ($index !== false) {
                $indices[] = $index + 1;
            }
        }

        sort($indices);
        return $indices;
    }
}
