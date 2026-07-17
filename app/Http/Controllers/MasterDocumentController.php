<?php

namespace App\Http\Controllers;

use App\Models\SubDepartment;
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
        $subdepartments = SubDepartment::all();

        return view('documents.index', compact(
            'documents',
            'departments',
            'sections',
            'subdepartments'
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
        try {
            $request->validate([
                'doc_name' => 'required',
                'doc_number' => 'required|unique:master_documents',
                'file' => 'required|mimes:pdf,doc,docx,ppt,pptx|max:10000',
                'department_id' => 'required',
                'subdepartment_id' => 'required|array|min:1',
                'subdepartment_id.*' => 'integer|exists:sub_departments,id',
                'section_id' => 'required|array|min:1',
                'section_id.*' => 'integer|exists:sections,sec_id',
                'read_time' => 'nullable|string|max:50',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withInput()->with('error', implode(' ', $e->validator->errors()->all()));
        }

        try {
            $path = $request->file('file')->store('master_docs', 'public');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Failed to upload the file: ' . $e->getMessage());
        }

        $subdepartmentId = $this->normalizeSelection($request->input('subdepartment_id'));
        $sectionId = $this->normalizeSelection($request->input('section_id'));

        try {
            MasterDocument::create([
                'doc_name' => $request->doc_name,
                'doc_number' => $request->doc_number,
                'version' => $request->version ?? '1.0',
                'doc_type' => $request->doc_type,
                'file_path' => $path,
                //  added this line to track who uploaded the document
                'uploaded_by' => auth()->id(),
                'department_id' => $request->department_id,
                'subdepartment_id' => $subdepartmentId,
                'section_id' => $sectionId,
                'read_time' => $request->filled('read_time') ? trim($request->read_time) : null,
            ]);
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($path);
            return back()->withInput()->with('error', 'Failed to save the document: ' . $e->getMessage());
        }

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


    public function view($id)
    {
        $doc = MasterDocument::findOrFail($id);
        $path = storage_path('app/public/' . $doc->file_path);

        abort_unless(file_exists($path), 404, 'Document file not found.');

        $headers = [
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ];

        if (request()->boolean('secure')) {
            $headers = array_merge($headers, [
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0',
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'SAMEORIGIN',
                'Referrer-Policy' => 'no-referrer',
            ]);
        }

        return response()->file($path, $headers);
    }

    private function normalizeSelection($value)
    {
        if (is_array($value)) {
            $value = array_values(array_filter($value, fn ($item) => filled($item)));

            return $value[0] ?? null;
        }

        return filled($value) ? $value : null;
    }
}
