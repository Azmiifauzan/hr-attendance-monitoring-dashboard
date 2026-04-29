<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Figtree', sans-serif; }
        .tag { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; background: #ede9fe; color: #6d28d9; border-radius: 999px; font-size: 12px; font-weight: 500; }
        .tag button { background: none; border: none; cursor: pointer; color: #7c3aed; font-size: 14px; line-height: 1; padding: 0; }
        .dropdown-list { position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #e5e7eb; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); max-height: 200px; overflow-y: auto; z-index: 50; margin-top: 4px; }
        .dropdown-item { padding: 9px 14px; font-size: 13px; cursor: pointer; color: #374151; }
        .dropdown-item:hover { background: #f5f3ff; color: #6d28d9; }
        .dropdown-item.selected { background: #f5f3ff; color: #6d28d9; }
        .dropdown-item.selected::after { content: '✓'; float: right; font-weight: 600; }
        input[type=text]:focus, input[type=password]:focus { outline: none; border-color: #a78bfa; box-shadow: 0 0 0 3px rgba(167,139,250,0.15); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

<div class="max-w-3xl mx-auto px-4 py-10">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">User Management</h1>
            <p class="text-sm text-gray-400 mt-1">Kelola akun dan hak akses per PT</p>
        </div>
        <a href="/" class="text-sm text-gray-400 hover:text-violet-500 transition-colors">← Kembali</a>
    </div>

    {{-- Form Tambah / Edit --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-5">
        <div class="flex items-center gap-2 mb-6">
            <div class="w-1 h-4 bg-violet-500 rounded-full"></div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest" id="formTitle">
                {{ isset($user) ? 'Edit user' : 'Tambah user baru' }}
            </p>
        </div>

        <form method="POST" action="{{ isset($user) ? '/users/'.$user->id : '/users' }}" id="userForm">
            @csrf
            @if(isset($user)) @method('PUT') @endif

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Nama Lengkap</label>
                    <input name="name" type="text" placeholder="Budi Santoso"
                        value="{{ $user->name ?? '' }}"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Username</label>
                    <input name="username" type="text" placeholder="budi.santoso"
                        value="{{ $user->username ?? '' }}"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 transition-all">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-400 mb-1.5">
                    Password @if(isset($user)) <span class="text-gray-300 normal-case">(kosongkan jika tidak diganti)</span> @endif
                </label>
                <input name="password" type="password" placeholder="Min. 8 karakter"
                    class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 transition-all">
            </div>

            <div class="mb-6">
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Akses PT</label>
                <div id="tagArea" onclick="document.getElementById('ptSearch').focus()"
                    class="min-h-[46px] w-full px-3 py-2 border border-gray-200 rounded-xl bg-gray-50 flex flex-wrap gap-1.5 cursor-text relative">
                    <span id="tagPlaceholder" class="text-sm text-gray-400 self-center">Pilih PT...</span>
                </div>
                <div class="relative mt-2">
                    <input type="text" id="ptSearch" placeholder="Cari nama PT..."
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-xl bg-white transition-all"
                        oninput="filterPT(this.value)" onfocus="showDropdown()" onblur="setTimeout(hideDropdown, 200)">
                    <div id="ptDropdown" class="dropdown-list hidden">
                        @foreach($companies as $c)
                            <div class="dropdown-item {{ isset($userCompanies) && in_array($c->CompanyId, $userCompanies) ? 'selected' : '' }}"
                                data-id="{{ $c->CompanyId }}"
                                data-name="{{ $c->Name }}"
                                onclick="togglePT('{{ $c->CompanyId }}', '{{ addslashes($c->Name) }}', this)">
                                {{ $c->Name }}
                            </div>
                        @endforeach
                    </div>
                </div>
                <div id="hiddenInputs"></div>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                    class="px-6 py-2.5 bg-violet-500 hover:bg-violet-600 active:scale-95 text-white text-sm font-medium rounded-xl transition-all shadow-sm shadow-violet-200">
                    {{ isset($user) ? 'Update user' : 'Simpan user' }}
                </button>
                @if(isset($user))
                <a href="/users"
                    class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-500 text-sm font-medium rounded-xl transition-all">
                    Batal
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Daftar User --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center gap-2 mb-5">
            <div class="w-1 h-4 bg-violet-500 rounded-full"></div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Daftar user</p>
        </div>

        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left py-2 px-3 text-xs font-medium text-gray-300 uppercase tracking-widest">User</th>
                    <th class="text-left py-2 px-3 text-xs font-medium text-gray-300 uppercase tracking-widest">Username</th>
                    <th class="text-left py-2 px-3 text-xs font-medium text-gray-300 uppercase tracking-widest">Role</th>
                    <th class="text-left py-2 px-3 text-xs font-medium text-gray-300 uppercase tracking-widest">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                <tr class="border-b border-gray-50 hover:bg-violet-50 transition-colors">
                    <td class="py-3 px-3 font-medium text-gray-800">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-violet-100 text-violet-500 text-xs font-semibold flex items-center justify-center">
                                {{ strtoupper(substr($u->name, 0, 2)) }}
                            </div>
                            {{ $u->name }}
                        </div>
                    </td>
                    <td class="py-3 px-3 text-gray-400 font-mono text-xs">{{ $u->username }}</td>
                    <td class="py-3 px-3">
                        @if($u->role === 'admin')
                            <span class="px-2.5 py-1 bg-violet-100 text-violet-600 text-xs font-semibold rounded-full">Admin</span>
                        @else
                            <span class="px-2.5 py-1 bg-gray-100 text-gray-400 text-xs font-medium rounded-full">User</span>
                        @endif
                    </td>
                    <td class="py-3 px-3">
                        <div class="flex gap-2">
                            <a href="/users/{{ $u->id }}/edit"
                                class="px-3 py-1 text-xs bg-violet-50 text-violet-500 hover:bg-violet-100 rounded-lg transition-colors font-medium">
                                Edit
                            </a>
                            <form method="POST" action="/users/{{ $u->id }}" onsubmit="return confirm('Hapus user {{ $u->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="px-3 py-1 text-xs bg-red-50 text-red-400 hover:bg-red-100 rounded-lg transition-colors font-medium">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

<script>
    const selected = {};

    // Pre-fill jika mode edit
    @isset($userCompanies)
        @foreach($companies as $c)
            @if(isset($userCompanies) && in_array($c->CompanyId, $userCompanies))
                selected['{{ $c->CompanyId }}'] = '{{ addslashes($c->Name) }}';
            @endif
        @endforeach
        renderTags();
        syncSelect();
    @endisset

    function showDropdown() {
        document.getElementById('ptDropdown').classList.remove('hidden');
    }
    function hideDropdown() {
        document.getElementById('ptDropdown').classList.add('hidden');
        document.getElementById('ptSearch').value = '';
        filterPT('');
    }
    function filterPT(q) {
        document.querySelectorAll('.dropdown-item').forEach(el => {
            el.style.display = el.dataset.name.toLowerCase().includes(q.toLowerCase()) ? '' : 'none';
        });
    }
    function togglePT(id, name, el) {
        if (selected[id]) {
            delete selected[id];
            el.classList.remove('selected');
        } else {
            selected[id] = name;
            el.classList.add('selected');
        }
        renderTags();
        syncSelect();
    }
    function removeTag(id) {
        delete selected[id];
        document.querySelector(`.dropdown-item[data-id="${id}"]`)?.classList.remove('selected');
        renderTags();
        syncSelect();
    }
    function renderTags() {
        const area = document.getElementById('tagArea');
        const placeholder = document.getElementById('tagPlaceholder');
        area.querySelectorAll('.tag').forEach(t => t.remove());
        const ids = Object.keys(selected);
        placeholder.style.display = ids.length ? 'none' : '';
        ids.forEach(id => {
            const tag = document.createElement('span');
            tag.className = 'tag';
            tag.innerHTML = `${selected[id]} <button type="button" onclick="removeTag('${id}')">×</button>`;
            area.appendChild(tag);
        });
    }
    function syncSelect() {
        const container = document.getElementById('hiddenInputs');
        container.innerHTML = '';
        Object.keys(selected).forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'companies[]';
            input.value = id;
            container.appendChild(input);
        });
    }
</script>

</body>
</html>