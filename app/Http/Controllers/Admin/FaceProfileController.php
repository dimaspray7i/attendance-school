<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewEnrollmentRequest;
use App\Models\FaceEmbedding;
use App\Models\FaceProfile;
use App\Services\FaceEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FaceProfileController extends Controller
{
    public function __construct(
        private FaceEnrollmentService $enrollmentService
    ) {}

    /**
     * List semua face profiles dengan filter.
     */
    public function index(Request $request)
    {
        $query = FaceProfile::with(['student.user', 'student.schoolClass']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $profiles = $query->latest()->paginate(15);

        $stats = [
            'total' => FaceProfile::count(),
            'pending' => FaceProfile::pending()->count(),
            'approved' => FaceProfile::approved()->count(),
            'rejected' => FaceProfile::where('status', 'rejected')->count(),
            're_enroll' => FaceProfile::where('status', 're_enroll')->count(),
            'disabled' => FaceProfile::where('status', 'disabled')->count(),
        ];

        return view('admin.face-profiles.index', compact('profiles', 'stats'));
    }

    /**
     * Detail face profile untuk review.
     */
    public function show(FaceProfile $faceProfile)
    {
        $this->authorize('review', $faceProfile);

        $faceProfile->load([
            'student.user',
            'student.schoolClass.major',
            'embeddings',
            'logs.performedByUser',
            'approvedByUser',
        ]);

        return view('admin.face-profiles.show', compact('faceProfile'));
    }

    /**
     * Proses review (approve/reject/re_enroll/disable).
     */
    public function review(ReviewEnrollmentRequest $request, FaceProfile $faceProfile)
    {
        $this->authorize('review', $faceProfile);

        $validated = $request->validated();
        $admin = $request->user();

        try {
            switch ($validated['action']) {
                case 'approve':
                    $this->enrollmentService->approve($faceProfile, $admin, $validated['notes'] ?? null);
                    $message = 'Face enrollment berhasil disetujui.';
                    break;

                case 'reject':
                    $this->enrollmentService->reject($faceProfile, $admin, $validated['reason']);
                    $message = 'Face enrollment ditolak.';
                    break;

                case 're_enroll':
                    $this->enrollmentService->requestReEnroll($faceProfile, $admin, $validated['reason']);
                    $message = 'Siswa diminta melakukan enrollment ulang.';
                    break;

                case 'disable':
                    $this->enrollmentService->disable($faceProfile, $admin, $validated['reason']);
                    $message = 'Face profile dinonaktifkan.';
                    break;

                default:
                    return back()->withErrors(['action' => 'Aksi tidak valid.']);
            }

            return redirect()->route('admin.face-profiles.index')
                ->with('success', $message);

        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Gagal memproses: ' . $e->getMessage()]);
        }
    }

    /**
     * Serve image embedding (dengan authorization).
     * Gambar biometrik TIDAK boleh diakses publik.
     */
    public function image(FaceEmbedding $embedding)
    {
        $this->authorize('review', $embedding->faceProfile);

        if (!$embedding->image_path || !Storage::disk('local')->exists($embedding->image_path)) {
            abort(404);
        }

        return Storage::disk('local')->response($embedding->image_path);
    }
}