<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Venue;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function index()
    {
        $departments = Department::all();
        $designations = Designation::all();
        $venues = Venue::all();
        return view('masters.index', compact('departments', 'designations', 'venues'));
    }

    public function storeDepartment(Request $request)
    {
        Department::create($request->validate(['name' => 'required|unique:departments', 'code' => 'nullable']));
        return back()->with('success', 'Department Added');
    }

    public function storeDesignation(Request $request)
    {
        Designation::create($request->validate(['name' => 'required|unique:designations']));
        return back()->with('success', 'Designation Added');
    }

    public function destroyDepartment(Department $department)
    {
        $department->delete();
        return back();
    }

    public function destroyDesignation(Designation $designation)
    {
        $designation->delete();
        return back();
    }

    public function showTrainers()
    {
        // Fetch only users marked as trainers, grouped by department
        $departments = Department::with([
            'users' => function ($query) {
                $query->where('is_trainer', 1)->with('designation');
            }
        ])->get()->filter(function ($dept) {
            return $dept->users->count() > 0;
        });

        return view('trainers.index', compact('departments'));
    }
    public function storeVenue(Request $request)
    {
        Venue::create($request->validate([
            'name' => 'required|unique:venues,name'
        ]));

        return back()->with('success', 'Venue Added');
    }

    public function destroyVenue(Venue $venue)
    {
        $venue->delete();
        return back();
    }
}
