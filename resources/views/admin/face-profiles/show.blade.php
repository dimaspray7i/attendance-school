<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Review Face Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-4">
                <a href="{{ route('admin.face-profiles.index') }}" class="text-blue-600 hover:underline">
                    ← Kembali ke daftar
                </a>
            </div>

            <!-- Info Siswa -->
            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-bold mb-4">Data Siswa</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <div class="text-gray-500">Nama</div>
                        <div class="font-semibold">{{ $faceProfile->student->full_name }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500">NIS</div>
                        <div class="font-semibold">{{ $faceProfile->student->nis }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500">Kelas</div>
                        <div class="font-semibold">{{ $faceProfile->student->schoolClass->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500">Status</div>
                        <div class="font-semibold">
                            @if($faceProfile->isPending())
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs">Pending</span>
                            @elseif($faceProfile->isApproved())
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Approved</span>
                            @elseif($faceProfile->isRejected())
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Rejected</span>
                            @elseif($faceProfile->needsReEnroll())
                                <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded text-xs">Re-Enroll</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">{{ $faceProfile->status }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mt-4 pt-4 border-t">
                    <div>
                        <div class="text-gray-500">Jumlah Sampel</div>
                        <div class="font-semibold">{{ $faceProfile->sample_count }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500">Kualitas Rata-rata</div>
                        <div class="font-semibold">
                            {{ $faceProfile->overall_quality_score ? number_format($faceProfile->overall_quality_score * 100, 1) . '%' : '-' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-500">Diajukan</div>
                        <div class="font-semibold">{{ $faceProfile->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    @if($faceProfile->approvedByUser)
                    <div>
                        <div class="text-gray-500">Direview Oleh</div>
                        <div class="font-semibold">{{ $faceProfile->approvedByUser->name }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Sampel Wajah -->
            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-bold mb-4">Sampel Wajah</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($faceProfile->embeddings as $embedding)
                        <div class="border rounded-lg overflow-hidden">
                            <img src="{{ $embedding->image_url }}" 
                                 alt="Sample {{ $embedding->sample_type }}"
                                 class="w-full aspect-square object-cover">
                            <div class="p-2 text-xs">
                                <div class="font-semibold uppercase">{{ $embedding->sample_type }}</div>
                                <div class="text-gray-600">
                                    Kualitas: {{ number_format($embedding->quality_score * 100, 1) }}%
                                </div>
                                @if($embedding->is_primary)
                                    <span class="text-blue-600">★ Primary</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Form Review -->
            @if($faceProfile->isPending() || $faceProfile->needsReEnroll())
            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-bold mb-4">Aksi Review</h3>
                
                <form action="{{ route('admin.face-profiles.review', $faceProfile) }}" method="POST">
                    @csrf
                    <input type="hidden" name="action" id="action-input" value="">

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-4">
                        <button type="button" onclick="setAction('approve')" 
                                class="px-4 py-3 bg-green-600 text-white rounded hover:bg-green-700 font-semibold">
                            ✓ Approve
                        </button>
                        <button type="button" onclick="setAction('reject')" 
                                class="px-4 py-3 bg-red-600 text-white rounded hover:bg-red-700 font-semibold">
                            ✕ Reject
                        </button>
                        <button type="button" onclick="setAction('re_enroll')" 
                                class="px-4 py-3 bg-orange-600 text-white rounded hover:bg-orange-700 font-semibold">
                            ↻ Re-Enroll
                        </button>
                        <button type="button" onclick="setAction('disable')" 
                                class="px-4 py-3 bg-gray-600 text-white rounded hover:bg-gray-700 font-semibold">
                            ⊘ Disable
                        </button>
                    </div>

                    <div id="action-label" class="mb-3 font-semibold text-gray-700"></div>

                    <div id="reason-group" class="mb-3 hidden">
                        <label class="block text-sm font-medium mb-1">Alasan <span class="text-red-500">*</span></label>
                        <textarea name="reason" rows="3" class="w-full border rounded px-3 py-2"></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Catatan Admin (opsional)</label>
                        <textarea name="notes" rows="2" class="w-full border rounded px-3 py-2"></textarea>
                    </div>

                    <button type="submit" id="submit-btn" disabled
                            class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:bg-gray-400">
                        Kirim Review
                    </button>
                </form>
            </div>

            <script>
                function setAction(action) {
                    document.getElementById('action-input').value = action;
                    document.getElementById('submit-btn').disabled = false;
                    
                    const labels = {
                        approve: 'Anda akan menyetujui face enrollment ini.',
                        reject: 'Anda akan menolak face enrollment ini. Alasan wajib diisi.',
                        re_enroll: 'Anda akan meminta siswa melakukan enrollment ulang. Alasan wajib diisi.',
                        disable: 'Anda akan menonaktifkan face profile ini. Alasan wajib diisi.'
                    };
                    document.getElementById('action-label').textContent = labels[action];
                    
                    const needsReason = ['reject', 're_enroll', 'disable'].includes(action);
                    document.getElementById('reason-group').classList.toggle('hidden', !needsReason);
                }
            </script>
            @endif

            <!-- Audit Log -->
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4">Audit Log</h3>
                <div class="space-y-2 text-sm">
                    @foreach($faceProfile->logs as $log)
                        <div class="flex justify-between border-b pb-2">
                            <div>
                                <span class="font-semibold">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</span>
                                @if($log->performedByUser)
                                    <span class="text-gray-500">oleh {{ $log->performedByUser->name }}</span>
                                @endif
                                @if($log->notes)
                                    <div class="text-gray-600 text-xs mt-1">{{ $log->notes }}</div>
                                @endif
                            </div>
                            <span class="text-gray-500">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>