<x-layouts.admin>
    <div>
        <h1 class="text-xl font-semibold">Users</h1>
        <p class="mt-1 text-sm text-gray-600">Buat akun Admin atau PIC.</p>
    </div>

    <div class="mt-6 rounded-lg bg-white p-4 ring-1 ring-black/5">
        <h2 class="text-base font-semibold text-gray-900">Tambah User</h2>

        <form action="{{ route('admin.users.store') }}" method="POST" class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-4 sm:items-end">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-900">Nama</label>
                <input name="name" type="text" value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm" required>
                @error('name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-900">Email</label>
                <input name="email" type="email" value="{{ old('email') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm" required>
                @error('email') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-900">Role</label>
                <select name="role" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm" required>
                    <option value="pic" @selected(old('role') === 'pic')>PIC</option>
                    <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                </select>
                @error('role') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-900">Password</label>
                <input name="password" type="password" class="mt-1 block w-full rounded-md border-gray-300 text-sm" required>
                @error('password') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
            </div>

            <div class="sm:col-span-4 flex items-center justify-end">
                <x-button type="submit" color="primary">Buat User</x-button>
            </div>
        </form>
    </div>

    <div class="mt-6 overflow-x-auto rounded-lg bg-white ring-1 ring-black/5">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b bg-gray-50 text-gray-700">
                <tr>
                    <th class="px-4 py-3 font-semibold">Nama</th>
                    <th class="px-4 py-3 font-semibold">Email</th>
                    <th class="px-4 py-3 font-semibold">Role</th>
                    <th class="px-4 py-3 font-semibold">Dibuat</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($users as $u)
                    <tr>
                        <td class="px-4 py-3 font-semibold text-gray-900">{{ $u->name }}</td>
                        <td class="px-4 py-3">{{ $u->email }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full bg-gray-200 px-2.5 py-0.5 text-xs font-semibold text-gray-900">{{ $u->role }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $u->created_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-600">Belum ada user.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.admin>

