<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterDocument;
use Illuminate\Support\Facades\Storage;


class MasterDocumentController extends Controller
{
    public function index()
    {
        $documents = MasterDocument::withCount('questions')->get();
        return view('documents.index', compact('documents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'doc_name'   => 'required',
            'doc_number' => 'required|unique:master_documents',
            'file'       => 'required|mimes:pdf,doc,docx,ppt,pptx|max:10000',
        ]);

        $path = $request->file('file')->store('master_docs', 'public');

        MasterDocument::create([
            'doc_name'   => $request->doc_name,
            'doc_number' => $request->doc_number,
            'version'    => $request->version ?? '1.0',
            'doc_type'   => $request->doc_type,
            'file_path'  => $path,
        ]);

        return back()->with('success', 'Master Document added to Global Pool.');
    }

    public function destroy($id)
    {
        $doc = MasterDocument::findOrFail($id);
        Storage::disk('public')->delete($doc->file_path);
        $doc->delete();
        return back()->with('success', 'Document removed.');
    }
}
