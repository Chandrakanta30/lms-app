<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterDocument;
use App\Models\Section;
use Illuminate\Support\Facades\Storage;
use App\Models\Department;
use App\Models\SectionMaster;


class MasterDocumentController extends Controller
{
    public function index()
    {
        $documents = MasterDocument::with([
            'uploader',
            'reviewer',
            'department',
            'section'
        ])
            ->withCount('questions')
            ->get();

        $departments = Department::all();

        $sections = Section::all();

        return view('documents.index', compact(
            'documents',
            'departments',
            'sections'
        ));
    }



    public function show($id)
    {
        $document = MasterDocument::with(['uploader', 'reviewer', 'modules'])
            ->withCount('questions')
            ->findOrFail($id);

        $extension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
        $isPreviewable = in_array($extension, ['pdf']);

        return view('documents.show', compact('document', 'isPreviewable'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'doc_name' => 'required',
            'doc_number' => 'required|unique:master_documents',
            'file' => 'required|mimes:pdf,doc,docx,ppt,pptx|max:10000',
            'department_id' => 'required',
            'section_id' => 'required',
        ]);

        $path = $request->file('file')->store('master_docs', 'public');

        MasterDocument::create([
            'doc_name' => $request->doc_name,
            'doc_number' => $request->doc_number,
            'version' => $request->version ?? '1.0',
            'doc_type' => $request->doc_type,
            'file_path' => $path,
            //  added this line to track who uploaded the document
            'uploaded_by' => auth()->id(),
            'department_id' => $request->department_id,
            'section_id' => $request->section_id,
        ]);

        return back()->with('success', 'Master Document added to Global Pool.');
    }

    public function review($id)
    {
        $document = MasterDocument::findOrFail($id);
        $document->update([
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Document review submitted successfully.');
    }

    public function destroy($id)
    {
        $doc = MasterDocument::findOrFail($id);
        Storage::disk('public')->delete($doc->file_path);
        $doc->delete();
        return back()->with('success', 'Document removed.');
    }
}
