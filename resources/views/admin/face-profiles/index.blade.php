<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Face Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-6">
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-2xl font-bold">{{ $stats['total'] }}</div>
                    <div class="text-xs text-gray-600">Total</div>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg shadow-sm border-l-4 border-yellow-500">
                    <div class="text-2xl font-bold text-yellow-700">{{ $stats['pending'] }}</div>
                    <div class="text-xs text-gray-600">Pending</div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg shadow-sm border-l-4 border-green-500">
                    <div class="text-2xl font-bold text-green-700">{{ $stats['approved'] }}</div>
                    <div class="text-xs text-gray-600">Approved</div>
                </div>
                <div class="bg-red-50 p-4 rounded-lg shadow-sm border-l-4 border-red-500">
                    <div class="text-2xl font-bold text-red-700">{{ $stats['rejected'] }}</div>
                    <div class="text-xs text-gray-600">Rejected</div>
                </div>
                <div class="bg-orange-50 p-4 rounded-lg shadow-sm border-l-4 border-orange-500">
                    <div class="text-2xl font-bold text-orange-700">{{ $stats['re_enroll'] }}</div>
                    <div class="text-xs text-gray-600">Re-Enroll</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg shadow-sm border-l-4 border-gray-500">
                    <div class="text-2xl font-bold text-gray-700">{{ $stats['disabled'] }}</div>
                    <div class="text-xs text-gray-600">Disabled</div>
                </div>
            </div>

            <!-- Filter -->
            <div class="bg-white p-4 rounded-lg shadow-sm mb-4">
                <form method="GET" class="flex gap-2">
                    <select name="status" class="border rounded px-3 py-2">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="re_enroll" {{ request('status') == 're_enroll' ? 'selected' : '' }}>Re-Enroll</option>
                        <option value="disabled" {{ request('status') == 'disabled' ? 'selected' : '' }}>Disabled</option>
                    </select>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Cari nama/NIS..." class="border rounded px-3 py-2 flex-1">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Filter
                    </button>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Siswa</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Kelas</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Sampel</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Kualitas</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($profiles as $profile)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-semibold">{{ $profile->student->full_name }}</div>
                                    <div class="text-xs text-gray-500">NIS: {{ $profile->student->nis }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm">{{ $profile->student->schoolClass->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm">{{ $profile->sample_count }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($profile->overall_quality_score)
                                        {{ number_format($profile->overall_quality_score * 100, 1) }}%
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($profile->isPending())
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs">Pending</span>
                                    @elseif($profile->isApproved())
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Approved</span>
                                    @elseif($profile->isRejected())
                                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Rejected</span>
                                    @elseif($profile->needsReEnroll())
                                        <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded text-xs">Re-Enroll</span>
                                    @else
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">{{ $profile->status }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $profile->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.face-profiles.show', $profile) }}" 
                                       class="text-blue-600 hover:underline text-sm">Review</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    Belum ada data face profile.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $profiles->links() }}
            </div>
        </div>
    </div>