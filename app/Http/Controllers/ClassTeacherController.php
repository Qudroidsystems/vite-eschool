<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ClassTeacher;
use App\Models\Schoolclass;
use App\Models\SubjectTeacher;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\User;


class ClassTeacherController extends Controller
{

    function __construct()
    {
         $this->middleware('permission:class_teacher-list|class_teacher-create|class_teacher-edit|class_teacher-delete', ['only' => ['index','store']]);
         $this->middleware('permission:class_teacher-create', ['only' => ['create','store']]);
         $this->middleware('permission:class_teacher-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:class_teacher-delete', ['only' => ['destroy','deleteclassteacher']]);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $schoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id','=','schoolclass.arm')
        ->get(['schoolclass.id as id','schoolarm.arm as schoolarm','schoolclass.schoolclass as schoolclass',]);
       // $subjectteachers = Subjectteacher::all();
        $subjectteachers = User::whereHas('roles', function($q){ $q->where('name', '!=','Student'); })
        ->get(['users.id as userid','users.name as name']);

        $schoolterm = Schoolterm::all();
        $schoolsession = Schoolsession::all();
        $classteachers = ClassTeacher::leftJoin('users', 'users.id','=','classteacher.staffid')
        ->leftJoin('schoolclass', 'schoolclass.id','=','classteacher.schoolclassid')
        ->leftJoin('schoolarm', 'schoolarm.id','=','schoolclass.arm')
        ->leftJoin('schoolterm', 'schoolterm.id','=','classteacher.termid')
        ->leftJoin('schoolsession', 'schoolsession.id','=','classteacher.sessionid')
        ->get(['classteacher.id as id','users.id as userid','users.name as staffname','users.avatar as avatar',
             'schoolclass.schoolclass as schoolclass','schoolarm.arm as schoolarm',
            'schoolclass.description as classcategory','schoolterm.term as term',
            'schoolsession.session as session','classteacher.updated_at as updated_at']);

            return view('classteacher.index')->with('classteachers',$classteachers)
                                             ->with('schoolclass',$schoolclass)
                                             ->with('subjectteachers',$subjectteachers)
                                             ->with('schoolterms',$schoolterm)
                                             ->with('schoolsessions',$schoolsession);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $schoolclass = Schoolclass::all();
       // $subjectteachers = Subjectteacher::all();
        $subjectteachers = User::whereHas('roles', function($q){ $q->where('name', '!=','Student'); })
        ->get(['users.id as userid','users.name as name']);

        $schoolterm = Schoolterm::all();
        $schoolsession = Schoolsession::all();

        return view('classteacher.create')->with('schoolclass',$schoolclass)
                                        ->with('subjectteachers',$subjectteachers)
                                        ->with('schoolterms',$schoolterm)
                                        ->with('schoolsessions',$schoolsession);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //

        $validator = Validator::make($request->all(), [
            'staffid' => 'required',
            'schoolclassid' => 'required',
            'termid' => 'required',
            'sessionid'=>'required',
        ],
        ['staffid.required'=>'Select Teacher!',
        'schoolclassid.required'=>'Select a Class Please!',
        'termid.required'=>'Select School Term',
        'session.required'=>'Select School Session!',
        ]
    );
    if ($validator->fails()) {

        return redirect()->back()->withErrors($validator)
                                 ->withInput();

     }

     $ct = ClassTeacher::where('schoolclassid',$request->input('schoolclassid'))
                      -> where('termid',$request->input('termid'))
                      -> where('sessionid',$request->input('sessionid'))
                      ->exists();
        if($ct){
            return redirect()->route('classteacher.index')
            ->with('danger', 'Hello. It seems you are assigning a teacher to class more than one');


        }else{

            $input = $request->all();
            classteacher::create($input);

        return redirect()->route('classteacher.index')
        ->with('success', 'Teacher Assigned to Class successfully.');
        }


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $schoolclass = Schoolclass::all();
        $subjectteachers = User::whereHas('roles', function($q){ $q->where('name', '!=','Student'); })
        ->get(['users.id as userid','users.name as name']);
        $schoolterm = Schoolterm::all();
        $schoolsession = Schoolsession::all();

        $classteachers = ClassTeacher::where('classteacher.id',$id)
        ->leftJoin('users', 'users.id','=','classteacher.staffid')
        ->leftJoin('schoolclass', 'schoolclass.id','=','classteacher.schoolclassid')
        ->leftJoin('schoolterm', 'schoolterm.id','=','classteacher.termid')
        ->leftJoin('schoolsession', 'schoolsession.id','=','classteacher.sessionid')
        ->get(['classteacher.id as ctid','users.id as userid','users.name as staffname',
             'schoolclass.schoolclass as schoolclass','schoolclass.arm as schoolarm',
             'schoolclass.id as classid','schoolsession.id as sessionid','schoolterm.id as termid',
            'schoolclass.description as classcategory','schoolterm.term as term',
            'schoolsession.session as session','classteacher.updated_at as updated_at']);


       return view('classteacher.edit')->with('classteachers',$classteachers)
                                         ->with('teachers',$subjectteachers)
                                         ->with('schoolclasses',$schoolclass)
                                         ->with('schoolterms',$schoolterm)
                                         ->with('schoolsessions',$schoolsession);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //

       $ct = ClassTeacher::where('staffid',$request->input('staffid'))
                      -> where('schoolclassid',$request->input('schoolclassid'))
                      -> where('termid',$request->input('termid'))
                      -> where('sessionid',$request->input('sessionid'))
                      ->exists();
        if($ct){
            return redirect()->route('classteacher.index')
            ->with('danger', 'Hello. It seems you are assigning a teacher to class more than once');


        }else{
            $input = $request->all();
            $classteacher = ClassTeacher::find($id);
            $classteacher->update($input);
           return redirect()->route('classteacher.edit',[$id])
             ->with('success', 'Class Teacher updated successfully.');

        }




    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
              Classteacher::find($id)->delete();

        return redirect()->route('classteacher.index')
            ->with('success', 'Class Teacher deleted successfully.');
    }

    public function deleteclassteacher(Request $request)
    {
        classteacher::find($request->classteacherid)->delete();
        //check data deleted or not
        if ($request->classteacherid) {
            $success = true;
            $message = "Class Teacher has been removed";
        } else {
            $success = true;
            $message = "Class Teacher not found";
        }

        //  return response
        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);

    }
}
