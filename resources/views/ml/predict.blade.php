@extends('layouts.app')
@section('title', __('ml.title'))

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="card card-hover relative overflow-hidden p-6 sm:p-7">
        <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-cassava-100 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-4">
                <div class="hidden h-13 w-13 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-cassava-500 to-cassava-800 p-3.5 text-white shadow-lg sm:flex">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="6" width="12" height="12" rx="2"/><path d="M9 2v3M15 2v3M9 19v3M15 19v3M2 9h3M2 15h3M19 9h3M19 15h3"/></svg>
                </div>
                <div>
                    <p class="eyebrow text-cassava-600">{{ __('ml.eyebrow') }}</p>
                    <h2 class="mt-1 text-xl font-extrabold tracking-tight text-gray-900 sm:text-2xl">{{ __('ml.title') }}</h2>
                    <p class="mt-1 text-[13px] text-gray-500">{{ __('ml.subtitle') }}</p>
                </div>
            </div>
            <div class="flex w-fit items-center gap-2.5 rounded-2xl border px-4 py-2.5 text-sm font-bold
                {{ $isServiceOnline ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700' }}">
                <span class="relative flex h-2.5 w-2.5">
                    @if ($isServiceOnline)
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    @else
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-red-500"></span>
                    @endif
                </span>
                Python ML Service: {{ $isServiceOnline ? __('ml.service_on') : __('ml.service_off') }}
            </div>
        </div>
    </div>

    @if (!$isServiceOnline)
    <div class="rounded-3xl border border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50 p-6 text-sm text-amber-900 shadow-[var(--shadow-card)]">
        <p class="mb-2 flex items-center gap-2 font-bold">
            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-500 text-white">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 8v5M12 16.5v.5"/><circle cx="12" cy="12" r="9"/></svg>
            </span>
            {{ __('ml.service_down_title') }}
        </p>
        <p>{{ __('ml.service_down_desc') }}</p>
        <code class="mt-2 block rounded-2xl bg-gray-900 px-4 py-3 font-mono text-xs leading-relaxed text-lime-300">
            cd ml<br>
            pip install -r requirements.txt<br>
            uvicorn main:app --reload --port 8001
        </code>
        <p class="mt-2 text-xs text-amber-700">{{ __('ml.service_down_hint') }}</p>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Real-time Prediction Panel -->
        <div class="card card-hover p-6 sm:p-7">
            <div class="mb-5 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-cassava-950 text-lime-300">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M19 5l-2 2M7 17l-2 2"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900">{{ __('ml.manual_title') }}</h3>
                    <p class="text-xs text-gray-500">{{ __('ml.manual_sub', ['n' => $windowSize]) }}</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="eyebrow mb-1.5 block text-gray-500">{{ __('ml.choose_device') }}</label>
                    <select id="predict-device"
                            class="w-full rounded-2xl border border-gray-200 bg-gray-50/50 px-3.5 py-2.5 text-sm outline-none transition focus:border-cassava-400 focus:bg-white focus:ring-4 focus:ring-cassava-100">
                        @foreach ($devices as $device)
                            <option value="{{ $device->device_id }}">{{ $device->device_id }} – {{ $device->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button onclick="runPrediction()" class="btn-primary w-full !py-3">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                    {{ __('ml.analyze') }}
                </button>
            </div>

            <!-- Result Box -->
            <div id="result-box" class="hidden mt-6 p-5 rounded-3xl border-2">
                <p class="eyebrow mb-2 text-gray-400">{{ __('ml.result') }}</p>
                <div id="result-condition" class="text-4xl font-extrabold tracking-tight mb-2"></div>
                <div id="result-confidence" class="text-sm text-gray-500 mb-4"></div>

                <!-- Probability Bars -->
                <div id="result-probs" class="space-y-2.5"></div>

                <div id="result-features" class="mt-4 pt-4 border-t border-gray-100">
                    <p class="eyebrow mb-2 text-gray-400">{{ __('ml.features_in', ['n' => $windowSize]) }}</p>
                    <div class="grid grid-cols-3 gap-2 text-xs font-mono text-gray-600" id="feature-values"></div>
                </div>
            </div>

            <div id="result-error" class="hidden mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-700"></div>
        </div>

        <!-- Info Panel -->
        <div class="space-y-4">
            <div class="card p-6 sm:p-7">
                <h3 class="mb-4 font-bold text-gray-900">{{ __('ml.how_title') }}</h3>
                <div class="space-y-4 text-sm text-gray-600">
                    @php
                        $steps = [
                            ['1', 'Input Sensor', $windowSize . ' pembacaan terakhir dari MQ-2, MQ-3, MQ-135', 'bg-orange-100 text-orange-700'],
                            ['2', 'Feature Extraction', 'Mean, Std Dev, Min, Max per sensor → 12 fitur', 'bg-sky-100 text-sky-700'],
                            ['3', 'Klasifikasi', 'SVM (StandardScaler + SVC): Segar / Mulai Busuk / Busuk', 'bg-violet-100 text-violet-700'],
                            ['4', 'Output Probabilitas', 'Nilai kepercayaan per kelas (0–100%)', 'bg-emerald-100 text-emerald-700'],
                        ];
                    @endphp
                    @foreach ($steps as [$n, $title, $desc, $chip])
                    <div class="flex gap-3.5">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-sm font-extrabold {{ $chip }}">{{ $n }}</span>
                        <div><p class="font-bold text-gray-900">{{ $title }}</p><p class="mt-0.5 text-xs text-gray-500">{{ $desc }}</p></div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-3xl border border-sky-200 bg-gradient-to-br from-sky-50 to-blue-50 p-6 text-sm text-sky-900 shadow-[var(--shadow-card)]">
                <p class="mb-2 font-bold">Status Model (SVM)</p>
                @if ($isServiceOnline)
                    <p class="text-[13px] leading-relaxed">Model SVM aktif dan siap digunakan. Klasifikasi berjalan otomatis setiap ada data sensor baru (window {{ $windowSize }}). Cek <code class="rounded bg-sky-100 px-1.5 py-0.5 font-mono text-[11px]">/ml/health</code> untuk detail versi model.</p>
                @else
                    <p class="text-[13px] leading-relaxed">Model belum tersedia. Data sensor tetap tersimpan; klasifikasi otomatis dilanjutkan setelah service pulih. Anda perlu:<br/>
                    1. Mengumpulkan data training sensor<br/>
                    2. Melatih model SVM di Python (<code class="rounded bg-sky-100 px-1.5 py-0.5 font-mono text-[11px]">ml/train.py</code>)<br/>
                    3. Menjalankan Python API service</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Hasil klasifikasi otomatis terbaru (dari database) -->
    <div class="card p-6 sm:p-7">
        <div class="mb-4 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8Z"/></svg>
            </div>
            <div>
                <h3 class="font-bold text-gray-900">{{ __('ml.auto_latest') }}</h3>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('ml.auto_latest_sub', ['n' => $windowSize]) }}</p>
            </div>
        </div>
        @if ($latestPrediction)
            <div class="rounded-2xl border border-gray-100 bg-gradient-to-br from-gray-50/80 to-white p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <span class="inline-flex w-fit items-center gap-2 px-4 py-2 rounded-full text-sm font-bold ring-1 ring-inset
                        {{ $latestPrediction->predicted_label->value === 'fresh' ? 'bg-emerald-100 text-emerald-800 ring-emerald-200' : ($latestPrediction->predicted_label->value === 'stale' ? 'bg-amber-100 text-amber-800 ring-amber-200' : 'bg-red-100 text-red-800 ring-red-200') }}">
                        {{ $latestPrediction->predicted_label->label() }}
                    </span>
                    <div class="text-sm text-gray-600">
                        Kepercayaan: <strong class="text-gray-900">{{ number_format(($latestPrediction->confidence ?? 0) * 100, 1) }}%</strong>
                        <span class="mx-1 text-gray-400">|</span> {{ $latestPrediction->predicted_at?->translatedFormat('d M Y H:i:s') }}
                        <span class="mx-1 text-gray-400">|</span> <span class="font-mono rounded-md bg-gray-900 px-2 py-0.5 text-[11px] text-lime-300">{{ $latestPrediction->device?->device_id }}</span>
                        <span class="mx-1 text-gray-400">|</span> <span class="text-xs">model {{ $latestPrediction->model_name }}</span>
                    </div>
                </div>
                @if (!empty($latestPrediction->probabilities))
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-5">
                        @foreach (['fresh' => ['Segar', 'from-emerald-400 to-emerald-600', 'text-emerald-700'], 'stale' => ['Mulai Busuk', 'from-amber-400 to-amber-600', 'text-amber-700'], 'rotten' => ['Busuk', 'from-red-400 to-red-600', 'text-red-700']] as $key => [$label, $bar, $txt])
                            @php $pct = round((($latestPrediction->probabilities[$key] ?? 0)) * 100, 1); @endphp
                            <div class="rounded-2xl border border-gray-100 bg-white p-3.5 shadow-sm">
                                <div class="flex justify-between text-xs mb-2"><span class="font-bold {{ $txt }}">{{ $label }}</span><span class="font-mono font-bold text-gray-700">{{ $pct }}%</span></div>
                                <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden"><div class="h-2.5 bg-gradient-to-r {{ $bar }} rounded-full" style="width:{{ $pct }}%"></div></div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50/60 px-5 py-8 text-center">
                <p class="text-sm font-semibold text-gray-500">{{ __('ml.auto_empty') }}</p>
                <p class="mt-1 text-xs text-gray-400">{{ __('ml.auto_empty_hint', ['n' => $windowSize]) }}</p>
            </div>
        @endif
    </div>

    <!-- Riwayat prediksi dari database -->
    <div class="card overflow-hidden">
        <div class="p-6 pb-4">
            <h3 class="font-bold text-gray-900">{{ __('ml.history_db') }}</h3>
            <p class="text-xs text-gray-500 mt-0.5">{{ __('ml.history_db_sub') }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="table-shell min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th>Waktu</th>
                        <th>Perangkat</th>
                        <th>Label</th>
                        <th class="!text-right">Confidence</th>
                        <th>Model</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($predictionHistory as $prediction)
                    <tr class="hover:bg-cassava-50/50 transition-colors">
                        <td class="px-5 py-3 text-gray-600 font-mono text-xs tabular-nums whitespace-nowrap">{{ $prediction->predicted_at?->translatedFormat('d M Y H:i:s') }}</td>
                        <td class="px-5 py-3"><span class="inline-flex items-center text-xs font-bold text-cassava-800 bg-cassava-50 px-2.5 py-1 rounded-lg border border-cassava-200 font-mono">{{ $prediction->device?->device_id ?? 'Unknown' }}</span></td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full ring-1 ring-inset
                                {{ $prediction->predicted_label->value === 'fresh' ? 'bg-emerald-100 text-emerald-800 ring-emerald-200' : ($prediction->predicted_label->value === 'stale' ? 'bg-amber-100 text-amber-800 ring-amber-200' : 'bg-red-100 text-red-800 ring-red-200') }}">
                                {{ $prediction->predicted_label->label() }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right font-mono font-bold tabular-nums text-gray-800">{{ number_format(($prediction->confidence ?? 0) * 100, 1) }}%</td>
                        <td class="px-5 py-3 text-xs text-gray-500 font-mono">{{ $prediction->model_name }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-gray-400">{{ __('ml.history_empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($predictionHistory->hasPages())
        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/60">{{ $predictionHistory->links() }}</div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
const condLabels = { fresh: 'Segar', stale: 'Mulai Busuk', rotten: 'Busuk' };
const condStyles = {
    fresh:  { box: 'border-emerald-300 bg-emerald-50',  text: 'text-emerald-700' },
    stale:  { box: 'border-amber-300 bg-amber-50', text: 'text-amber-700' },
    rotten: { box: 'border-red-300 bg-red-50',       text: 'text-red-700' },
};
const probColors = { fresh: 'from-emerald-400 to-emerald-600', stale: 'from-amber-400 to-amber-600', rotten: 'from-red-400 to-red-600' };

async function runPrediction() {
    const deviceId = document.getElementById('predict-device').value;
    const btn = document.querySelector('[onclick="runPrediction()"]');
    btn.textContent = 'Menganalisis...';
    btn.disabled = true;

    document.getElementById('result-box').classList.add('hidden');
    document.getElementById('result-error').classList.add('hidden');

    try {
        const response = await fetch('{{ route('ml.predict') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ device_id: deviceId }),
        });
        const data = await response.json();

        if (data.success && data.condition) {
            const cond     = data.condition;
            const conf     = (data.confidence * 100).toFixed(1);
            const c        = condStyles[cond] || { box: 'border-gray-200 bg-white', text: 'text-gray-800' };
            const box      = document.getElementById('result-box');

            box.className = `mt-6 p-5 rounded-3xl border-2 ${c.box}`;
            box.classList.remove('hidden');
            document.getElementById('result-condition').className = `text-4xl font-extrabold tracking-tight mb-2 ${c.text}`;
            document.getElementById('result-condition').textContent = condLabels[cond] || cond;
            document.getElementById('result-confidence').textContent = `Kepercayaan: ${conf}%  |  ${data.reading_count} pembacaan dianalisis`;

            // Probability bars
            const probsEl = document.getElementById('result-probs');
            probsEl.innerHTML = '';
            if (data.probabilities) {
                Object.entries(data.probabilities).forEach(([key, val]) => {
                    const pct = (val * 100).toFixed(1);
                    probsEl.innerHTML += `
                        <div>
                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                <span class="font-bold">${condLabels[key] || key}</span>
                                <span class="font-mono font-bold text-gray-700">${pct}%</span>
                            </div>
                            <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-2.5 bg-gradient-to-r ${probColors[key] || 'from-gray-400 to-gray-500'} rounded-full transition-all" style="width:${pct}%"></div>
                            </div>
                        </div>`;
                });
            }

            // Feature values
            const f = data.features;
            document.getElementById('feature-values').innerHTML = `
                <div class="rounded-xl bg-gray-50 px-2.5 py-2 text-center"><span class="text-gray-400 text-[10px] font-bold">MQ-2 μ</span><br/><span class="font-bold text-gray-800">${f.mq2_mean}</span></div>
                <div class="rounded-xl bg-gray-50 px-2.5 py-2 text-center"><span class="text-gray-400 text-[10px] font-bold">MQ-3 μ</span><br/><span class="font-bold text-gray-800">${f.mq3_mean}</span></div>
                <div class="rounded-xl bg-gray-50 px-2.5 py-2 text-center"><span class="text-gray-400 text-[10px] font-bold">MQ-135 μ</span><br/><span class="font-bold text-gray-800">${f.mq135_mean}</span></div>
                <div class="rounded-xl bg-gray-50 px-2.5 py-2 text-center"><span class="text-gray-400 text-[10px] font-bold">MQ-2 σ</span><br/><span class="font-bold text-gray-800">${f.mq2_std}</span></div>
                <div class="rounded-xl bg-gray-50 px-2.5 py-2 text-center"><span class="text-gray-400 text-[10px] font-bold">MQ-3 σ</span><br/><span class="font-bold text-gray-800">${f.mq3_std}</span></div>
                <div class="rounded-xl bg-gray-50 px-2.5 py-2 text-center"><span class="text-gray-400 text-[10px] font-bold">MQ-135 σ</span><br/><span class="font-bold text-gray-800">${f.mq135_std}</span></div>
            `;
        } else {
            document.getElementById('result-error').textContent = data.message || 'Prediksi gagal.';
            document.getElementById('result-error').classList.remove('hidden');
        }
    } catch (err) {
        document.getElementById('result-error').textContent = 'Gagal menghubungi server. Periksa koneksi.';
        document.getElementById('result-error').classList.remove('hidden');
    } finally {
        btn.innerHTML = 'Analisis Sekarang';
        btn.disabled = false;
    }
}
</script>
@endpush
