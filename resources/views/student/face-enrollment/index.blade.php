<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Face Enrollment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('info'))
                <div class="mb-4 bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4">
                    {{ session('info') }}
                </div>
            @endif

            <!-- Status Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-bold mb-4">Status Face Enrollment Anda</h3>
                    
                    @if($profile)
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span class="font-semibold">
                                    @if($profile->isPending())
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">Menunggu Review</span>
                                    @elseif($profile->isApproved())
                                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">Disetujui</span>
                                    @elseif($profile->isRejected())
                                        <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm">Ditolak</span>
                                    @elseif($profile->needsReEnroll())
                                        <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm">Perlu Enrollment Ulang</span>
                                    @elseif($profile->isDisabled())
                                        <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm">Dinonaktifkan</span>
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Jumlah Sampel:</span>
                                <span class="font-semibold">{{ $profile->sample_count }}</span>
                            </div>
                            @if($profile->overall_quality_score)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Kualitas:</span>
                                <span class="font-semibold">{{ number_format($profile->overall_quality_score * 100, 1) }}%</span>
                            </div>
                            @endif
                            @if($profile->rejection_reason)
                            <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded">
                                <p class="text-sm font-semibold text-red-800">Alasan:</p>
                                <p class="text-sm text-red-700">{{ $profile->rejection_reason }}</p>
                            </div>
                            @endif
                        </div>

                        @if($profile->isRejected() || $profile->needsReEnroll())
                            <div class="mt-6">
                                <a href="{{ route('siswa.face-enrollment.capture') }}" 
                                   class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    Enrollment Ulang
                                </a>
                            </div>
                        @endif
                    @else
                        <p class="text-gray-600 mb-4">Anda belum melakukan face enrollment.</p>
                        <a href="{{ route('siswa.face-enrollment.capture') }}" 
                           class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Mulai Enrollment
                        </a>
                    @endif
                </div>
            </div>

            <!-- Log Aktivitas -->
            @if($logs->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-bold mb-4">Riwayat Aktivitas</h3>
                    <div class="space-y-2">
                        @foreach($logs as $log)
                            <div class="flex justify-between text-sm border-b pb-2">
                                <span>
                                    <span class="font-semibold">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</span>
                                    @if($log->performedByUser)
                                        <span class="text-gray-500">oleh {{ $log->performedByUser->name }}</span>
                                    @endif
                                </span>
                                <span class="text-gray-500">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>