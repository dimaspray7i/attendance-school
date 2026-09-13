<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Major;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::with('major')->latest()->paginate(10);
        return view('admin.classes.index', compact('classes'));
    }

    public function create()
    {
        $majors = Major::all();
        return view('admin.classes.create', compact('majors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:classes,code',
            'major_id' => 'required|exists:majors,id',
            'grade' => 'required|integer|in:10,11,12',
            'description' => 'nullable|string',
        ]);

        SchoolClass::create($validated);

        return redirect()->route('admin.classes.index')
            ->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function show(SchoolClass $class)
    {
        $class->load('major', 'students');
        return view('admin.classes.show', compact('class'));
    }

    public function edit(SchoolClass $class)
    {
        $majors = Major::all();
        return view('admin.classes.edit', compact('class', 'majors'));
    }

    public function update(Request $request, SchoolClass $class)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:classes,code,' . $class->id,
            'major_id' => 'required|exists:majors,id',
            'grade' => 'required|integer|in:10,11,12',
            'description' => 'nullable|string',
        ]);

        $class->update($validated);

        return redirect()->route('admin.classes.index')
            ->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(SchoolClass $class)
    {
        $class->delete();

        return redirect()->route('admin.classes.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }
}