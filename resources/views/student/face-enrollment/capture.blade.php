<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Capture Wajah untuk Enrollment') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="faceEnrollment()">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            @if($errors->any())
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('siswa.face-enrollment.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Instruksi -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-bold mb-3">Instruksi</h3>
                    <ol class="list-decimal list-inside space-y-2 text-gray-700">
                        <li>Pastikan pencahayaan cukup dan wajah terlihat jelas</li>
                        <li>Ambil 3 sampel: <strong>menghadap depan</strong>, <strong>menoleh kiri</strong>, <strong>menoleh kanan</strong></li>
                        <li>Setiap sampel akan divalidasi kualitasnya secara otomatis</li>
                        <li>Setelah semua sampel valid, klik "Kirim Enrollment"</li>
                    </ol>
                </div>

                <!-- Capture Area -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Webcam -->
                        <div>
                            <h4 class="font-semibold mb-2">Kamera</h4>
                            <div class="relative bg-gray-900 rounded-lg overflow-hidden aspect-video">
                                <video x-ref="video" autoplay playsinline class="w-full h-full object-cover"></video>
                                <canvas x-ref="canvas" class="hidden"></canvas>
                                
                                <template x-if="!cameraReady">
                                    <div class="absolute inset-0 flex items-center justify-center text-white">
                                        <button type="button" @click="startCamera()" 
                                                class="px-4 py-2 bg-blue-600 rounded hover:bg-blue-700">
                                            Aktifkan Kamera
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <div class="mt-3 flex gap-2">
                                <button type="button" @click="captureSample('front')" 
                                        :disabled="!cameraReady || capturing"
                                        class="flex-1 px-3 py-2 bg-green-600 text-white rounded text-sm hover:bg-green-700 disabled:bg-gray-400">
                                    📷 Depan
                                </button>
                                <button type="button" @click="captureSample('left')" 
                                        :disabled="!cameraReady || capturing"
                                        class="flex-1 px-3 py-2 bg-green-600 text-white rounded text-sm hover:bg-green-700 disabled:bg-gray-400">
                                    📷 Kiri
                                </button>
                                <button type="button" @click="captureSample('right')" 
                                        :disabled="!cameraReady || capturing"
                                        class="flex-1 px-3 py-2 bg-green-600 text-white rounded text-sm hover:bg-green-700 disabled:bg-gray-400">
                                    📷 Kanan
                                </button>
                            </div>
                        </div>

                        <!-- Preview Samples -->
                        <div>
                            <h4 class="font-semibold mb-2">Sampel Diambil (<span x-text="samples.length"></span>/3+)</h4>
                            <div class="grid grid-cols-3 gap-2">
                                <template x-for="(sample, idx) in samples" :key="idx">
                                    <div class="relative aspect-square bg-gray-100 rounded overflow-hidden border-2 border-green-500">
                                        <img :src="sample.preview" class="w-full h-full object-cover">
                                        <span class="absolute top-1 left-1 bg-black bg-opacity-60 text-white text-xs px-2 py-0.5 rounded"
                                              x-text="sample.type"></span>
                                        <button type="button" @click="removeSample(idx)"
                                                class="absolute top-1 right-1 bg-red-600 text-white w-6 h-6 rounded-full text-xs">
                                            ✕
                                        </button>
                                    </div>
                                </template>
                                <template x-if="samples.length < 3">
                                    <div x-for="i in (3 - samples.length)" :key="'empty-'+i"
                                         class="aspect-square bg-gray-100 rounded border-2 border-dashed border-gray-300 flex items-center justify-center text-gray-400 text-xs">
                                        Kosong
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hidden inputs untuk samples -->
                <template x-for="(sample, idx) in samples" :key="'input-'+idx">
                    <div>
                        <input type="hidden" :name="`samples[${idx}][type]`" :value="sample.type">
                        <input type="file" :name="`samples[${idx}][image]`" :id="`file-${idx}`" class="hidden" accept="image/*">
                    </div>
                </template>

                <!-- Submit -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <button type="submit" 
                            :disabled="samples.length < 3 || submitting"
                            class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 disabled:bg-gray-400">
                        <span x-show="!submitting">Kirim Enrollment</span>
                        <span x-show="submitting">Mengirim...</span>
                    </button>
                    <p class="text-xs text-gray-500 mt-2 text-center">
                        Minimal 3 sampel wajah diperlukan.
                    </p>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function faceEnrollment() {
            return {
                cameraReady: false,
                capturing: false,
                submitting: false,
                samples: [],
                stream: null,

                async startCamera() {
                    try {
                        this.stream = await navigator.mediaDevices.getUserMedia({ 
                            video: { width: 640, height: 480, facingMode: 'user' } 
                        });
                        this.$refs.video.srcObject = this.stream;
                        this.cameraReady = true;
                    } catch (err) {
                        alert('Gagal mengakses kamera: ' + err.message);
                    }
                },

                captureSample(type) {
                    if (!this.cameraReady) return;
                    this.capturing = true;

                    const video = this.$refs.video;
                    const canvas = this.$refs.canvas;
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0);

                    canvas.toBlob((blob) => {
                        const preview = URL.createObjectURL(blob);
                        const file = new File([blob], `${type}_${Date.now()}.jpg`, { type: 'image/jpeg' });

                        // Tambahkan ke samples array
                        const idx = this.samples.length;
                        this.samples.push({ type, preview, file });

                        // Inject file ke hidden input
                        this.$nextTick(() => {
                            const input = document.getElementById(`file-${idx}`);
                            if (input) {
                                const dt = new DataTransfer();
                                dt.items.add(file);
                                input.files = dt.files;
                            }
                        });

                        this.capturing = false;
                    }, 'image/jpeg', 0.9);
                },

                removeSample(idx) {
                    URL.revokeObjectURL(this.samples[idx].preview);
                    this.samples.splice(idx, 1);
                    
                    // Rebuild hidden inputs
                    this.$nextTick(() => {
                        document.querySelectorAll('input[name^="samples["]').forEach((el, i) => {
                            const sampleIdx = Math.floor(i / 2);
                            if (this.samples[sampleIdx]) {
                                if (el.type === 'hidden') el.value = this.samples[sampleIdx].type;
                                if (el.type === 'file') {
                                    const dt = new DataTransfer();
                                    dt.items.add(this.samples[sampleIdx].file);
                                    el.files = dt.files;
                                }
                            }
                        });
                    });
                }
            }
        }
    </script>
    @endpush
</x-app-layout>