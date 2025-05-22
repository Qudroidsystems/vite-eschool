<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schoolhouse;
use App\Models\Schoolterm;
use App\Models\Schoolsession;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SchoolHouseController extends Controller
{

    function __construct()
    {
         $this->middleware('permission:schoolhouse-list|schoolhouse-create|schoolhouse-edit|schoolhouse-delete', ['only' => ['index','store']]);
         $this->middleware('permission:schoolhouse-create', ['only' => ['create','store']]);
         $this->middleware('permission:schoolhouse-edit', ['only' => ['edit','update','updatehouse']]);
         $this->middleware('permission:schoolhouse-delete', ['only' => ['destroy','deletehouse']]);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function index()
    {
        //

        $schoolterm = Schoolterm::all();
        $schoolsession = Schoolsession::all();
        $schoolhouses = Schoolhouse::leftJoin('users', 'users.id','=','schoolhouses.housemasterid')
        ->leftJoin('staffpicture', 'staffpicture.staffid','=','users.id')
        ->leftJoin('schoolterm', 'schoolterm.id','=','schoolhouses.termid')
        ->leftJoin('schoolsession', 'schoolsession.id','=','schoolhouses.sessionid')
        ->get(['schoolhouses.id as id','users.id as userid','users.name as housemaster',
        'staffpicture.picture as pic','schoolhouses.house','schoolhouses.housecolour','schoolterm.term as term',
            'schoolsession.session as session','schoolhouses.updated_at as updated_at']);

        $staff = User::whereHas('roles', function($q){ $q->where('name', '!=','Student'); })
        ->leftJoin('staffbioinfo', 'staffbioinfo.userid','=','users.id')
        ->leftJoin('staffpicture', 'staffpicture.staffid','=','users.id')
        ->get(['users.id as userid','users.name as name','staffpicture.picture as pic']);



        return view('schoolhouse.index')->with('schoolhouses',$schoolhouses)
                                        ->with('schoolterm',$schoolterm)
                                        ->with('staff',$staff)
                                        ->with('schoolsession',$schoolsession);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
        $schoolhouse =  Schoolhouse::where('house',$request->housename)
                                    ->where('housemasterid',$request->housemasterid)
                                    ->where('housecolour',$request->housecolour)
                                    ->where('termid',$request->termid)
                                    ->where('sessionid',$request->sessionid)
                                    ->exists();

        if($schoolhouse){
            return redirect()->back()->with('danger', 'Record already exists');
        }else{

            $input = $request->all();
            Schoolhouse::create($input);
            return redirect()->back()->with('success', 'Record has been successfully created!');

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

        $schoolhouses = Schoolhouse::where('schoolhouses.id', $id)
        ->leftJoin('users', 'users.id','=','schoolhouses.housemasterid')
        ->leftJoin('schoolterm', 'schoolterm.id','=','schoolhouses.termid')
        ->leftJoin('schoolsession', 'schoolsession.id','=','schoolhouses.sessionid')
        ->get(['schoolhouses.id as shid','users.id as userid','users.name as housemaster',
        'schoolhouses.house','schoolhouses.housecolour','schoolterm.term as term',
        'schoolhouses.termid as termid','schoolhouses.sessionid as sessionid',
        'schoolsession.session as session','schoolhouses.updated_at as updated_at']);

        $staff = User::whereHas('roles', function($q){ $q->where('name', '!=','Student'); })
        ->leftJoin('staffbioinfo', 'staffbioinfo.userid','=','users.id')
        ->leftJoin('staffpicture', 'staffpicture.staffid','=','users.id')
        ->get(['users.id as userid','users.name as name','staffpicture.picture as pic']);

        $schoolterm = Schoolterm::all();
        $schoolsession = Schoolsession::all();
        return view('schoolhouse.edit')->with('schoolhouses',$schoolhouses)
                                      ->with('staff',$staff )
                                        ->with('schoolterm',$schoolterm)
                                        ->with('schoolsession',$schoolsession);
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
        $input = $request->all();


        $sclass = Schoolhouse::find($id);
        $sclass->update($input);

        return redirect()->back()->with('success', 'Record has been successfully updated!');
    }

    public function updatehouse(Request $request)
    {

         $this->validate($request, [
            'house' => 'required',
            'housecolour' => 'required'
        ]);

        DB::table('schoolhouses')->updateOrInsert(
            ['id'=>$request->id],
            ['house'=>$request->house,
                    'housecolour'=>$request->housecolour,
                    'housemasterid'=>$request->update_housemasterid,
                    'termid'=>$request->update_termid,
                    'sessionid'=>$request->update_sessionid,]);

        return redirect()->back()->with('success', 'Record has been successfully updated!');
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
    }






    public function deletehouse(Request $request)
    {
        Schoolterm::find($request->houseid)->delete();
        //check data deleted or not
        if ($request->houseid) {
            $success = true;
            $message = "House has been removed";
        } else {
            $success = true;
            $message = "House not found";
        }

        //  return response
        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);

    }

    }

