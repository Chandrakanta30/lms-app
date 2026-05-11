<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Venue;
use App\Models\Section;
use Illuminate\Http\Request;
use App\Traits\MasterCheck;

class MasterController extends Controller
{
    use MasterCheck;
    public function index()
    {
        $departments = Department::all();
        $designations = Designation::all();
        $venues = Venue::all();
        $sections = Section::all();
        return view('masters.index', compact('departments', 'designations', 'venues', 'sections'));
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
        $mapping = [
            ' master_documents' => 'department_id',

        ];

        if ($this->isMasterUsed($department->id, $mapping)) {
            return back()->with('error', 'Department is already in use');
        }

        $department->delete();
        return back()->with('success', 'Deleted successfully');
    }

    public function destroyDesignation(Designation $designation)
    {
        $mapping = [

        ];

        if ($this->isMasterUsed($designation->id, $mapping)) {
            return back()->with('error', 'Designation is already in use');
        }

        $designation->delete();
        return back()->with('success', 'Deleted successfully');
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
        // $venue->delete();
        // return back();
        $mapping = [
            'module_venue' => 'venue_id',

        ];

        if ($this->isMasterUsed($venue->venue_id, $mapping)) {
            return back()->with('error', 'venue is already in use');
        }

        $venue->delete();
        return back()->with('success', 'Deleted successfully');
    }
    public function storeSection(Request $request)
    {
        Section::create($request->validate([
            'name' => 'required|unique:sections,name'
        ]));

        return back()->with('success', 'Section Added');
    }

    public function destroySection(Section $section)
    {
        $mapping = [
            'master_documents' => 'section_id'

        ];

        if ($this->isMasterUsed($section->sec_id, $mapping)) {
            return back()->with('error', 'Section is already in use');
        }

        $section->delete();
        return back()->with('success', 'Deleted successfully');
    }
}
