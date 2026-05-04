<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola PT Aktif</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>
    <style>body { font-family: 'Figtree', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">

<div class="max-w-3xl mx-auto px-4 py-10">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Kelola PT Aktif</h1>
            <p class="text-sm text-gray-400 mt-1">Centang PT yang muncul di dropdown pencarian</p>
        </div>
        <a href="/users" class="text-sm text-gray-400 hover:text-violet-500 transition-colors">← Kembali</a>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 text-green-600 text-sm rounded-xl border border-green-100">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="/companies">
        @csrf

        {{-- Search filter --}}
        <div class="mb-4">
            <input type="text" id="filterInput" placeholder="Cari nama PT..."
                class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-violet-400">
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-5">

            {{-- Select all --}}
            <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-3 bg-gray-50">
                <input type="checkbox" id="selectAll"
                    class="rounded border-gray-300 text-violet-500 focus:ring-violet-400">
                <label for="selectAll" class="text-sm font-medium text-gray-600">Pilih semua</label>
                <span id="countLabel" class="ml-auto text-xs text-gray-400"></span>
            </div>

            {{-- List PT --}}
            <div id="companyList" class="divide-y divide-gray-50 max-h-[500px] overflow-y-auto">
                @foreach($allCompanies as $co)
                <label class="company-row flex items-center gap-3 px-5 py-3 hover:bg-violet-50 cursor-pointer transition-colors"
                    data-name="{{ strtolower($co->Name) }}">
                    <input type="checkbox"
                        name="companies[{{ $co->CompanyId }}]"
                        value="{{ $co->Name }}"
                        {{ in_array((string)$co->CompanyId, $activeIds) ? 'checked' : '' }}
                        class="company-check rounded border-gray-300 text-violet-500 focus:ring-violet-400">
                    <div>
                        <div class="text-sm font-medium text-gray-800">{{ $co->Name }}</div>
                        <div class="text-xs text-gray-400 font-mono">ID: {{ $co->CompanyId }}</div>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        <button type="submit"
            class="px-6 py-2.5 bg-violet-500 hover:bg-violet-600 active:scale-95 text-white text-sm font-medium rounded-xl transition-all shadow-sm shadow-violet-200">
            Simpan
        </button>

    </form>
</div>

<script>
    // Filter search
    document.getElementById('filterInput').addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.company-row').forEach(row => {
            row.style.display = row.dataset.name.includes(q) ? '' : 'none';
        });
        updateCount();
    });

    // Select all
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.company-check').forEach(cb => {
            if (cb.closest('.company-row').style.display !== 'none') {
                cb.checked = this.checked;
            }
        });
        updateCount();
    });

    document.querySelectorAll('.company-check').forEach(cb => {
        cb.addEventListener('change', updateCount);
    });

    function updateCount() {
        const total = document.querySelectorAll('.company-check:checked').length;
        document.getElementById('countLabel').textContent = total + ' PT dipilih';
    }

    updateCount();
</script>

</body>
</html>