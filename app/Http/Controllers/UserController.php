<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Designation;
use App\Models\Department;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    // 1. LIST ALL USERS
    public function index(Request $request) {
        // $users = User::with('roles')->latest()->paginate(10);



        $query = User::with(['department', 'designation', 'roles'])->orderBy('id','desc');

    // Filter by Keyword (Name, User ID, or Email)
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name', 'LIKE', "%$search%")
              ->orWhere('corporate_id', 'LIKE', "%$search%")
              ->orWhere('email', 'LIKE', "%$search%");
        });
    }

    // Filter by Department
    if ($request->filled('department_id')) {
        $query->where('department_id', $request->department_id);
    }

    // Filter by Role (Spatie Permissions)
    if ($request->filled('role')) {
        $query->role($request->role);
    }

    $users = $query->paginate(10)->withQueryString(); // withQueryString keeps filters in pagination links
    
    // You'll need to pass these to the view for the dropdowns
    $departments = \App\Models\Department::all();
    $roles = \Spatie\Permission\Models\Role::all();


        return view('users.index', compact('users','departments','roles'));
    }

    // 2. SHOW CREATE FORM
    public function create() {
        $roles = Role::all();

        $departments = Department::all();
        $designations = Designation::all();

        return view('users.create', compact('roles','departments','designations'));
    }

    // 3. STORE NEW USER
    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',   // make email nullable
            'corporate_id'  => 'required|string|unique:users,corporate_id',
            'internal_id' => 'nullable|string|unique:users,internal_id',  // add interanl id
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',  //for internalid validation
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'qualification' => 'nullable|string',
            'job_description'=>'nullable|string',
            'experience_years' => 'nullable|integer',
        ]);


        $userData = $request->only(['name',
                                    'email',
                                    'department_id', 
                                    'designation_id', 
                                    'qualification', 
                                    'job_description',
                                    'experience_years',
                                    'corporate_id',
                                    'internal_id']);

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }
        $userData['is_trainer'] = $request->has('is_trainer') ? 1 : 0;
        $user=User::create($userData);
        // $user = User::create([
        //     'name' => $request->name,
        //     'email' => $request->email,
        //     'password' => Hash::make($request->password),
        // ]);

        $user->assignRole($request->roles);
        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    // 4. SHOW EDIT FORM
    public function edit(User $user) {
        $roles = Role::all();
        $departments = Department::all();
        $designations = Designation::all();
        $userRoles = $user->roles->pluck('name')->toArray();
        return view('users.create', compact('user', 'roles', 'userRoles','departments','designations'));
    }

    // 5. UPDATE USER
    public function update(Request $request, User $user) 
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,'.$user->id,  // make email nullable and allow current user's email
            'corporate_id'  => 'required|string|unique:users,corporate_id,' . ($user->id ?? ''),
            'internal_id' => 'nullable|string|unique:users,internal_id,' . $user->id,   // add internal id updataion
            'roles' => 'required|array',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'qualification' => 'nullable|string',
            'job_description'=>'nullable|string',
            'experience_years' => 'nullable|integer|min:0',
        ]);
    
        // 1. Prepare the data for update
        $userData = $request->only([
            'name', 
            'email', 
            'department_id', 
            'designation_id', 
            'qualification', 
            'job_description',
            'experience_years',
            'corporate_id',
        ]);
    
        // 2. Handle password only if it's provided
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }
    
        $userData['is_trainer'] = $request->has('is_trainer') ? 1 : 0;
        // 3. Update the User Model (CRITICAL: This was missing)
        $user->update($userData);
    
        // 4. Sync Spatie Roles
        $user->syncRoles($request->roles);
    
        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    // 6. DELETE USER
    public function destroy(User $user) {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted.');
    }

    
}