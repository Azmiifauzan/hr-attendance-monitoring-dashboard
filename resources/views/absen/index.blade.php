<!DOCTYPE html>
<html lang="id">
<head>
    <title>Liat Foto Absensi Karyawan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Figtree', sans-serif; }
        #suggestions { position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); z-index: 50; margin-top: 4px; overflow: hidden; }
        .suggestion-item { padding: 10px 16px; cursor: pointer; display: flex; align-items: center; gap: 10px; }
        .suggestion-item:hover { background: #f5f3ff; }
        .suggestion-item:hover .s-name { color: #6d28d9; }
        .s-avatar { width: 30px; height: 30px; border-radius: 50%; background: #ede9fe; color: #7c3aed; font-size: 11px; font-weight: 600; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .s-name { font-size: 13px; font-weight: 500; color: #1f2937; }
        .s-nik { font-size: 11px; color: #9ca3af; font-family: monospace; }
        #divisionSuggestions { position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); z-index: 50; margin-top: 4px; overflow: hidden; max-height: 200px; overflow-y: auto; }
        .div-item { padding: 9px 14px; cursor: pointer; font-size: 13px; color: #374151; }
        .div-item:hover { background: #f5f3ff; color: #6d28d9; }
    </style>
</head>
<body class="min-h-screen" style="background: #f8f7ff">

{{-- Navbar --}}
<div class="border-b border-gray-100 bg-white px-6 py-3 flex items-center justify-between">
    <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 bg-violet-500 rounded-lg flex items-center justify-center">
            <svg viewBox="-48 -48 96 96" width="22" height="22">
                <circle cx="0" cy="-16" r="11" fill="white"/>
                <path d="M-14 -2 Q-18 18 -14 28 L14 28 Q18 18 14 -2 Z" fill="white"/>
                <rect x="10" y="2" width="16" height="12" rx="3" fill="white" opacity="0.85"/>
                <circle cx="18" cy="8" r="3.5" fill="#7F77DD"/>
                <rect x="20" y="3" width="4" height="3" rx="1" fill="white"/>
                <circle cx="26" cy="0" r="2" fill="#FAC775" opacity="0.9"/>
            </svg>
        </div>
        <span class="font-semibold text-gray-800 text-sm">Absensi Foto</span>
    </div>
    <div class="flex items-center gap-3">
        @if(auth()->user()->role === 'admin')
            <a href="/users" class="text-xs bg-violet-500 hover:bg-violet-600 text-white px-3.5 py-1.5 rounded-lg transition-colors font-medium">
                User Management
            </a>
        @endif
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-xs text-gray-400 hover:text-red-400 transition-colors">Keluar</button>
        </form>
    </div>
</div>

{{-- Hero Search --}}
<div class="flex flex-col items-center justify-center px-4 pt-12 pb-8">

    <div class="mb-4">
        <svg viewBox="0 0 120 80" width="100" height="67">
            <rect x="10" y="15" width="100" height="55" rx="8" fill="#ede9fe"/>
            <rect x="20" y="25" width="44" height="35" rx="4" fill="#AFA9EC"/>
            <circle cx="42" cy="37" r="9" fill="#7F77DD"/>
            <circle cx="42" cy="37" r="5" fill="#534AB7"/>
            <rect x="74" y="29" width="24" height="5" rx="2.5" fill="#CECBF6"/>
            <rect x="74" y="38" width="18" height="5" rx="2.5" fill="#CECBF6"/>
            <rect x="74" y="47" width="20" height="5" rx="2.5" fill="#CECBF6"/>
            <circle cx="20" cy="25" r="4" fill="#7F77DD"/>
            <rect x="17" y="13" width="6" height="5" rx="3" fill="#AFA9EC"/>
        </svg>
    </div>

    <h1 class="text-2xl font-semibold text-gray-800 mb-1">Cari Foto Absensi</h1>
    <p class="text-sm text-gray-400 mb-6">Ketik nama atau NIK, pilih divisi dan rentang tanggal</p>

    <form method="GET" id="searchForm" class="w-full max-w-3xl">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">

        {{-- Baris 1: Nama + PT --}}
        <div class="grid grid-cols-2 gap-3 mb-3">

            {{-- Nama / NIK --}}
            <div class="relative">
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Nama / NIK</label>
                <input type="text" name="keyword" id="keywordInput"
                    autocomplete="off"
                    placeholder="Cari nama atau no karyawan..."
                    value="{{ request('keyword') }}"
                    class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent">
                <div id="suggestions" class="hidden"></div>
            </div>

            {{-- PT --}}
            <div class="relative">
                <label class="block text-xs font-medium text-gray-400 mb-1.5">PT</label>
                <input type="text" id="companySearch"
                    autocomplete="off"
                    placeholder="Pilih PT..."
                    value="{{ request('company_name') }}"
                    class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent">
                <input type="hidden" name="company_id" id="companyId" value="{{ request('company_id') }}">
                <input type="hidden" name="company_name" id="companyName" value="{{ request('company_name') }}">
                <div id="companySuggestions" class="hidden" style="position:absolute;top:100%;left:0;right:0;background:white;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.08);z-index:50;margin-top:4px;max-height:200px;overflow-y:auto;">
                    <div class="div-item" onclick="pickCompany('','')">Semua PT</div>
                    @foreach($companies as $co)
                        <div class="div-item" onclick="pickCompany('{{ $co->CompanyId }}','{{ addslashes($co->Name) }}')">
                            {{ $co->Name }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Baris 2: Divisi --}}
        <div class="mb-3">
            <label class="block text-xs font-medium text-gray-400 mb-1.5">Divisi <span class="text-gray-300 normal-case font-normal">(pilih PT dulu)</span></label>
            <div class="relative">
                <input type="text" id="divisionSearch"
                    autocomplete="off"
                    placeholder="Pilih divisi..."
                    value="{{ request('division_name') }}"
                    class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent"
                    {{ !request('company_id') ? 'disabled' : '' }}>
                <input type="hidden" name="division_id" id="divisionId" value="{{ request('division_id') }}">
                <input type="hidden" name="division_name" id="divisionName" value="{{ request('division_name') }}">
                <div id="divisionSuggestions" class="hidden" style="position:absolute;top:100%;left:0;right:0;background:white;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.08);z-index:50;margin-top:4px;max-height:200px;overflow-y:auto;">
                </div>
            </div>
        </div>

        {{-- Baris 3: Tanggal + Cari --}}
        <div class="flex gap-3 items-end">
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Dari Tanggal</label>
                <input type="date" name="tanggal_dari"
                    value="{{ request('tanggal_dari') }}"
                    class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent">
            </div>
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Sampai Tanggal</label>
                <input type="date" name="tanggal_sampai"
                    value="{{ request('tanggal_sampai') }}"
                    class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent">
            </div>
            <button type="submit"
                class="px-6 py-2.5 bg-violet-500 hover:bg-violet-600 active:scale-95 text-white text-sm font-medium rounded-xl transition-all whitespace-nowrap">
                Cari
            </button>
        </div>

    </div>
</form>
</div>

{{-- Hasil --}}
@if(!empty($data))
<div class="max-w-6xl mx-auto px-6 pb-10">
    <p class="text-xs text-gray-400 mb-4 font-medium uppercase tracking-wider">
        {{ count($data) }} hasil ditemukan
    </p>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($data as $row)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer"
            onclick="openModal('{{ $row->ClockRequestId }}', '{{ $row->Latitude ?? '' }}', '{{ $row->Longitude ?? '' }}')">

            <div class="w-full h-40 overflow-hidden rounded-xl bg-gray-100 mb-3">
                <img src="{{ url('/foto/'.$row->ClockRequestId) }}"
                    class="w-full h-full object-cover">
            </div>

            <div class="font-semibold text-gray-800 text-sm truncate">{{ $row->FullName }}</div>
            <div class="text-gray-400 text-xs font-mono mt-0.5">{{ $row->EmployeeNo }}</div>
            <div class="text-gray-400 text-xs truncate">{{ $row->BranchName }}</div>
            @if($row->DivisionName)
                <div class="text-gray-400 text-xs truncate">{{ $row->DivisionName }}</div>
            @endif
            <div class="mt-2 text-xs text-violet-500 font-medium">
                {{ \Carbon\Carbon::parse($row->ClockDate)->format('d M Y') }} · {{ substr($row->ClockTime, 0, 5) }}
            </div>

        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Modal --}}
<div id="modal" class="fixed inset-0 bg-black bg-opacity-80 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl w-[90%] max-w-4xl h-[85vh] flex overflow-hidden">
        <div class="w-1/2 bg-black flex items-center justify-center">
            <img id="modalImg" class="max-h-full max-w-full object-contain">
        </div>
        <div class="w-1/2">
            <iframe id="mapFrame" class="w-full h-full border-0"></iframe>
        </div>
    </div>
</div>

<script>
// ── Autocomplete Nama ──
let debounceTimer;
document.getElementById('keywordInput').addEventListener('input', function() {
    clearTimeout(debounceTimer);
    const q = this.value.trim();
    if (q.length < 2) { document.getElementById('suggestions').classList.add('hidden'); return; }
    debounceTimer = setTimeout(() => fetchSuggestions(q), 300);
});
document.getElementById('keywordInput').addEventListener('blur', function() {
    setTimeout(() => document.getElementById('suggestions').classList.add('hidden'), 200);
});
function fetchSuggestions(q) {
    fetch('/autocomplete?q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {
            const box = document.getElementById('suggestions');
            if (!data.length) { box.classList.add('hidden'); return; }
            box.innerHTML = data.map(d => {
                const initials = d.FullName.split(' ').slice(0,2).map(n => n[0]).join('').toUpperCase();
                return `<div class="suggestion-item" onmousedown="pickSuggestion('${d.FullName.replace(/'/g,"\\'")}')">
                    <div class="s-avatar">${initials}</div>
                    <div><div class="s-name">${d.FullName}</div><div class="s-nik">${d.EmployeeNo}</div></div>
                </div>`;
            }).join('');
            box.classList.remove('hidden');
        });
}
function pickSuggestion(name) {
    document.getElementById('keywordInput').value = name;
    document.getElementById('suggestions').classList.add('hidden');
}

// ── Dropdown PT ──
const compSearch = document.getElementById('companySearch');
const compDropdown = document.getElementById('companySuggestions');
const allCompItems = Array.from(compDropdown.querySelectorAll('.div-item'));

compSearch.addEventListener('focus', () => { filterCompany(compSearch.value); compDropdown.classList.remove('hidden'); });
compSearch.addEventListener('input', function() { filterCompany(this.value); compDropdown.classList.remove('hidden'); });
compSearch.addEventListener('blur', () => { setTimeout(() => compDropdown.classList.add('hidden'), 200); });

function filterCompany(q) {
    allCompItems.forEach(el => {
        el.style.display = el.textContent.trim().toLowerCase().includes(q.toLowerCase()) ? '' : 'none';
    });
}
function pickCompany(id, name) {
    document.getElementById('companyId').value = id;
    document.getElementById('companyName').value = name;
    document.getElementById('companySearch').value = name;
    compDropdown.classList.add('hidden');

    // reset divisi
    document.getElementById('divisionId').value = '';
    document.getElementById('divisionName').value = '';
    document.getElementById('divisionSearch').value = '';
    document.getElementById('divisionSearch').disabled = !id;

    // load divisi baru
    if (id) loadDivisions(id);
    else document.getElementById('divisionSuggestions').innerHTML = '';
}

// ── Dropdown Divisi (dynamic) ──
const divSearch = document.getElementById('divisionSearch');
const divDropdown = document.getElementById('divisionSuggestions');

divSearch.addEventListener('focus', () => { divDropdown.classList.remove('hidden'); });
divSearch.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    divDropdown.querySelectorAll('.div-item').forEach(el => {
        el.style.display = el.textContent.trim().toLowerCase().includes(q) ? '' : 'none';
    });
    divDropdown.classList.remove('hidden');
});
divSearch.addEventListener('blur', () => { setTimeout(() => divDropdown.classList.add('hidden'), 200); });

function loadDivisions(companyId) {
    fetch('/get-divisions?company_id=' + companyId)
        .then(r => r.json())
        .then(data => {
            divDropdown.innerHTML = `<div class="div-item" onclick="pickDivision('','')">Semua divisi</div>`;
            data.forEach(d => {
                divDropdown.innerHTML += `<div class="div-item" onclick="pickDivision('${d.Id}','${d.Name.replace(/'/g,"\\'")}')">
                    ${d.Name}
                </div>`;
            });
        });
}
function pickDivision(id, name) {
    document.getElementById('divisionId').value = id;
    document.getElementById('divisionName').value = name;
    document.getElementById('divisionSearch').value = name;
    divDropdown.classList.add('hidden');
}

// Pre-load divisi kalau PT sudah dipilih sebelumnya
@if(request('company_id'))
    loadDivisions('{{ request('company_id') }}');
@endif

// ── Modal ──
function openModal(id, lat, lng) {
    document.getElementById('modal').classList.remove('hidden');
    document.getElementById('modalImg').src = '/foto/' + id;
    if (lat && lng) {
        document.getElementById('mapFrame').src = `https://www.google.com/maps?q=${lat},${lng}&hl=id&z=16&output=embed`;
    } else {
        document.getElementById('mapFrame').src = '';
    }
}
document.getElementById('modal').addEventListener('click', function(e) {
    if (e.target.id === 'modal') this.classList.add('hidden');
});
</script>

</body>
</html>