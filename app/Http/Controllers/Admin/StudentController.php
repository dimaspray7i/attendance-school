<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with(['user', 'schoolClass', 'academicYear'])->latest()->paginate(10);
        return view('admin.students.index', compact('students'));
    }

    public function create()
    {
        $classes = SchoolClass::with('major')->get();
        $academicYears = AcademicYear::all();
        return view('admin.students.create', compact('classes', 'academicYears'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nis' => 'required|string|max:50|unique:students,nis',
            'nisn' => 'nullable|string|max:50',
            'full_name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:100',
            'gender' => 'required|in:L,P',
            'birth_date' => 'required|date',
            'class_id' => 'required|exists:classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        DB::transaction(function () use ($validated) {
            // Create user account
            $user = User::create([
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'siswa',
            ]);

            // Create student record
            Student::create([
                'user_id' => $user->id,
                'nis' => $validated['nis'],
                'nisn' => $validated['nisn'] ?? null,
                'full_name' => $validated['full_name'],
                'nickname' => $validated['nickname'] ?? null,
                'gender' => $validated['gender'],
                'birth_date' => $validated['birth_date'],
                'class_id' => $validated['class_id'],
                'academic_year_id' => $validated['academic_year_id'],
                'address' => $validated['address'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'status' => 'active',
            ]);
        });

        return redirect()->route('admin.students.index')
            ->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function show(Student $student)
    {
        $student->load(['user', 'schoolClass.major', 'academicYear', 'parents']);
        return view('admin.students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        $classes = SchoolClass::with('major')->get();
        $academicYears = AcademicYear::all();
        return view('admin.students.edit', compact('student', 'classes', 'academicYears'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'nis' => 'required|string|max:50|unique:students,nis,' . $student->id,
            'nisn' => 'nullable|string|max:50',
            'full_name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:100',
            'gender' => 'required|in:L,P',
            'birth_date' => 'required|date',
            'class_id' => 'required|exists:classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive,alumni,transferred',
        ]);

        $student->update($validated);

        // Update user name if changed
        if ($student->user && $student->user->name !== $validated['full_name']) {
            $student->user->update(['name' => $validated['full_name']]);
        }

        return redirect()->route('admin.students.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student)
    {
        DB::transaction(function () use ($student) {
            $student->user->delete();
            $student->delete();
        });

        return redirect()->route('admin.students.index')
            ->with('success', 'Siswa berhasil dihapus.');
    }
}