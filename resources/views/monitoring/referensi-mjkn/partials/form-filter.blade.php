<form method="GET" action="" class="mb-4 flex gap-2 items-end">
    <div>
        <label for="tanggal_awal" class="block text-sm font-medium">Tanggal Awal</label>
        <input type="date" id="tanggal_awal" name="tanggal_awal"
            value="{{ request('tanggal_awal', $tanggal_awal ?? now()->format('Y-m-d')) }}" class="border rounded p-1">
    </div>
    <div>
        <label for="tanggal_akhir" class="block text-sm font-medium">Tanggal Akhir</label>
        <input type="date" id="tanggal_akhir" name="tanggal_akhir"
            value="{{ request('tanggal_akhir', $tanggal_akhir ?? now()->format('Y-m-d')) }}" class="border rounded p-1">
    </div>
    <div>
        <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded">
            Tampilkan
        </button>
    </div>
</form>
