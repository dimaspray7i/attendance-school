<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentProfile;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ParentController extends Controller
{
    public function index()
    {
        $parents = ParentProfile::with(['user', 'students'])->latest()->paginate(10);
        return view('admin.parents.index', compact('parents'));
    }

    public function create()
    {
        $students = Student::with('schoolClass')->active()->get();
        return view('admin.parents.create', compact('students'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'relationship' => 'required|in:ayah,ibu,wali',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'address' => 'nullable|string',
            'occupation' => 'nullable|string|max:100',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
        ]);

        DB::transaction(function () use ($validated) {
            // Create user account
            $user = User::create([
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'orang_tua',
            ]);

            // Create parent record
            $parent = ParentProfile::create([
                'user_id' => $user->id,
                'full_name' => $validated['full_name'],
                'relationship' => $validated['relationship'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'address' => $validated['address'] ?? null,
                'occupation' => $validated['occupation'] ?? null,
            ]);

            // Attach students
            $parent->students()->attach($validated['student_ids']);
        });

        return redirect()->route('admin.parents.index')
            ->with('success', 'Orang tua berhasil ditambahkan.');
    }

    public function show(ParentProfile $parent)
    {
        $parent->load(['user', 'students.schoolClass']);
        return view('admin.parents.show', compact('parent'));
    }

    public function edit(ParentProfile $parent)
    {
        $students = Student::with('schoolClass')->active()->get();
        return view('admin.parents.edit', compact('parent', 'students'));
    }

    public function update(Request $request, ParentProfile $parent)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'relationship' => 'required|in:ayah,ibu,wali',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'occupation' => 'nullable|string|max:100',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
        ]);

        DB::transaction(function () use ($validated, $parent) {
            $parent->update([
                'full_name' => $validated['full_name'],
                'relationship' => $validated['relationship'],
                'phone' => $validated['phone'],
                'address' => $validated['address'] ?? null,
                'occupation' => $validated['occupation'] ?? null,
            ]);

            // Sync students
            $parent->students()->sync($validated['student_ids']);

            // Update user name if changed
            if ($parent->user->name !== $validated['full_name']) {
                $parent->user->update(['name' => $validated['full_name']]);
            }
        });

        return redirect()->route('admin.parents.index')
            ->with('success', 'Data orang tua berhasil diperbarui.');
    }

    public function destroy(ParentProfile $parent)
    {
        DB::transaction(function () use ($parent) {
            $parent->students()->detach();
            $parent->user->delete();
            $parent->delete();
        });

        return redirect()->route('admin.parents.index')
            ->with('success', 'Orang tua berhasil dihapus.');
    }
}