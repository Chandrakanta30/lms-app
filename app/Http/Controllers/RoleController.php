<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index() {
        $roles = Role::with('permissions')->get();
        return view('roles.index', compact('roles'));
    }

    public function create() {
        $permissions = Permission::all();
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'description' => 'nullable|string',
            'training_required' => 'nullable|string',
            'permissions' => 'required|array'
        ]);
    
        $role = Role::create([
            'name' => $request->name,
            'description' => $request->description,
            'training_required' => $request->training_required,
        ]);
        
        $role->syncPermissions($request->permissions); // Syncs array of permission IDs
        return redirect()->route('roles.index')->with('success', 'Role Created');
    }

    public function edit(Role $role) {
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }


    public function update(Request $request, Role $role) 
{
    $request->validate([
        // The 'ignore' ensures validation doesn't fail because the name already exists for THIS record
        'name' => 'required|unique:roles,name,' . $role->id,
        'description' => 'nullable|string',
        'training_required' => 'nullable|string',
        'permissions' => 'required|array'
    ]);

    // Update the role attributes
    $role->update([
        'name' => $request->name,
        'description' => $request->description,
        'training_required' => $request->training_required,
    ]);
    
    // syncPermissions handles adding new and removing old permissions automatically
    $role->syncPermissions($request->permissions); 

    return redirect()->route('roles.index')->with('success', 'Role Updated Successfully');
}
}