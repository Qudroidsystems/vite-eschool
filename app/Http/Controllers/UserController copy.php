<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BioModel;
use App\Models\User;
use DB;
use Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use App\Models\Student;

class UserController extends Controller
{


    function __construct()
    {
         $this->middleware('permission:user-list|user-create|user-edit|user-delete', ['only' => ['index','store']]);
         $this->middleware('permission:user-create', ['only' => ['create','store']]);
         $this->middleware('permission:user-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:user-delete', ['only' => ['destroy']]);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): View
    {
        $data = User::latest()->paginate(5);
        $roles = Role::pluck('name','name')->all();
        $role_permissions = Role::with('permissions')->get();

            // Add this line to fetch students
        $students = Student::select('id', 'admissionNo', 'firstname', 'lastname')
        ->where('statusId', 1) // Assuming 1 is for active students
        ->orderBy('admissionNo')
        ->get();

        return view('users.index',compact('data', 'students'))
            ->with('i', ($request->input('page', 1) - 1) * 5)
            ->with('roles',$roles)
            ->with('role_perm',$role_permissions);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): View
    {
        $roles = Role::pluck('name','name')->all();
        return view('users.create',compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): RedirectResponse
    {
        // $this->validate($request, [
        //     'name' => 'required',
        //     'email' => 'required|email|unique:users,email',
        //     'password' => 'required|same:confirm-password',
        //     'roles' => 'required'
        // ]);

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        $user = User::create($input);
        BioModel::updateOrCreate(['user_id'=>$user->id],
                                 ['firstname' =>'',
                                   'lastname' => '',
                                   'othernames' => '',
                                   'phone' => '',
                                   'address' => '',
                                   'gender' =>'',
                                   'maritalstatus' =>'',
                                    'nationality' =>'',
                                    'dob' => '']);

        $user->assignRole($request->input('roles'));

        return redirect()->route('users.index')
                        ->with('success','User created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id): View
    {
        $user = User::find($id);
        $userroles = $user->roles->all();
        $userbio = $user->bio;
        return view('users.overview',compact('user'),
        compact('userroles'))
        ->with("userbio",$userbio);
    }




    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id): View
    {
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name','name')->all();

        return view('users.edit',compact('user','roles','userRole'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'same:confirm-password',
            'roles' => 'required'
        ]);


        $input = $request->all();
        if(!empty($input['password'])){
            $input['password'] = Hash::make($input['password']);
        }else{
            $input = Arr::except($input,array('password'));
        }

        $user = User::find($id);
        $user->update($input);
        DB::table('model_has_roles')->where('model_id',$id)->delete();

        $user->assignRole($request->input('roles'));

        return redirect()->route('users.index')
                        ->with('success','User updated successfully');
    }


        /**
     * Show the form for creating a new user from student.
     *
     * @return \Illuminate\Http\Response
     */
    public function createFromStudentForm(): View
    {
        $roles = Role::pluck('name','name')->all();
        $students = Student::select('id', 'admissionNo', 'firstname', 'lastname')
                        ->where('statusId', 1) // Assuming 1 is for active students
                        ->orderBy('admissionNo')
                        ->get();
        
        return view('users.add-student-user', compact('roles', 'students'));
    }

    /**
     * Store a newly created user from student in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createFromStudent(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'student_id' => 'required|exists:studentregistration,id',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'roles' => 'required'
        ]);

        // Get student data
        $student = Student::findOrFail($request->student_id);
        
        // Create user
        $user = new User();
        $user->name = $student->firstname . ' ' . $student->lastname;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->student_id = $student->id; // Link to student record
        $user->save();
        
        // Assign role
        $user->assignRole($request->input('roles'));
        
        // Also create or update the BioModel entry if necessary
        BioModel::updateOrCreate(
            ['user_id' => $user->id],
            [
                'firstname' => $student->firstname,
                'lastname' => $student->lastname,
                'othernames' => $student->othername ?? '',
                'phone' => '', // You could add these fields to your form if needed
                'address' => $student->home_address ?? '',
                'gender' => $student->gender ?? '',
                'maritalstatus' => '',
                'nationality' => $student->nationlity ?? '',
                'dob' => $student->dateofbirth ?? ''
            ]
        );

        return redirect()->route('users.index')
                        ->with('success', 'Student added as user successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): RedirectResponse
    {
        // User::find($id)->delete();
        // return redirect()->route('users.index')
        //                 ->with('success','User deleted successfully');


        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);
            $user->delete();
            DB::commit();
            return redirect()->route('users.index')->with('success', 'User deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('users.index')->with('error', 'Error deleting user: ' . $e->getMessage());
        }
    }

     // delete user
     public function delete($id)
     {
         $delete = User::destroy($id);

         // check data deleted or not
         if ($delete == 1) {
             $success = true;
             $message = "User deleted successfully";
         } else {
             $success = true;
             $message = "User not found";
         }

         //  return response
         return response()->json([
             'success' => $success,
             'message' => $message,
         ]);
     }
}
