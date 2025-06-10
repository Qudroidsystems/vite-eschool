<?php

namespace App\Exports;

use App\Models\Broadsheets;
use App\Models\SchoolInformation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarksSheetExport
{
    protected $subjectclassid;
    protected $staffid;
    protected $termid;
    protected $sessionid;
    protected $schoolclassid;

    public function __construct($subjectclassid, $staffid, $termid, $sessionid, $schoolclassid)
    {
        $this->subjectclassid = $subjectclassid;
        $this->staffid = $staffid;
        $this->termid = $termid;
        $this->sessionid = $sessionid;
        $this->schoolclassid = $schoolclassid;
    }

    public function download()
    {
        try {
            $schoolInfo = SchoolInformation::getActiveSchool();
            Log::info('School Info:', ['schoolInfo' => $schoolInfo]);
    
            $classInfo = $this->getClassAndSubjectInfo();
            Log::info('Class Info:', ['classInfo' => $classInfo]);
    
            $students = $this->getStudentsList();
            Log::info('Students:', ['count' => $students->count()]);
    
            $data = [
                'schoolInfo' => $schoolInfo,
                'classInfo' => $classInfo,
                'students' => $students,
            ];
    
            $pdf = Pdf::loadView('subjectscoresheet.markssheet', $data);
            $pdf->setPaper('A4', 'portrait');
    
            $filename = $this->generateFilename($classInfo);
            Log::info('Generated Filename:', ['filename' => $filename]);
    
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('PDF Generation Error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e; // Rethrow to see the error in the browser for debugging
        }
    }

    protected function getClassAndSubjectInfo()
    {
        return DB::table('subjectclass')
            ->leftJoin('subject', 'subject.id', '=', 'subjectclass.subjectid')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
            ->leftJoin('schoolterm', function ($join) {
                $join->on('schoolterm.id', '=', DB::raw('?'))->addBinding($this->termid);
            })
            ->leftJoin('schoolsession', function ($join) {
                $join->on('schoolsession.id', '=', DB::raw('?'))->addBinding($this->sessionid);
            })
            ->leftJoin('classcategories', 'classcategories.id', '=', 'schoolclass.classcategoryid')
            ->where('subjectclass.id', $this->subjectclassid)
            ->where('subjectteacher.staffid', $this->staffid)
            ->select([
                'subject.subject',
                'subject.subject_code',
                'schoolclass.schoolclass',
                'schoolarm.arm',
                'users.name as teacher_name',
                'schoolterm.term',
                'schoolsession.session',
                'classcategories.ca1score as max_ca1',
                'classcategories.ca2score as max_ca2',
                'classcategories.ca3score as max_ca3',
                'classcategories.examscore as max_exam',
            ])
            ->first();
    }

    protected function getStudentsList()
    {
        return DB::table('broadsheet_records')
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
            ->where('broadsheet_records.schoolclass_id', $this->schoolclassid)
            ->where('broadsheet_records.subject_id', function($query) {
                $query->select('subject_id')
                      ->from('subjectclass')
                      ->where('id', $this->subjectclassid)
                      ->limit(1);
            })
            ->where('broadsheet_records.session_id', $this->sessionid)
            ->orderBy('studentRegistration.admissionNO')
            ->select([
                'studentRegistration.admissionNO as admission_no',
                'studentRegistration.firstname',
                'studentRegistration.lastname',
                'broadsheet_records.student_id'
            ])
            ->get();
    }

    protected function generateFilename($classInfo)
    {
        if (!$classInfo) {
            return 'marks_sheet.pdf';
        }

        return sprintf(
            'Marks_Sheet_%s_%s%s_%s_%s_%s.pdf',
            str_replace(' ', '_', $classInfo->teacher_name),
            $classInfo->subject,
            $classInfo->subject_code,
            $classInfo->schoolclass,
            $classInfo->arm,
            $classInfo->term
        );
    }
}
?>