@extends('layouts.admin')
@section('title', 'Layanan Ekspedisi')
@section('content')
<h1>Layanan Ekspedisi</h1>

<div class="card">
    <form method="POST" action="{{ route('admin.shipping.store') }}">
        @csrf
        <div class="row">
            <label>Kurir<input class="input" name="courier" placeholder="Contoh: JNE" required></label>
            <label>Layanan<input class="input" name="service" placeholder="Contoh: REG" required></label>
            <label>Estimasi (ETA)<input class="input" name="eta" placeholder="Contoh: 2-3 hari"></label>
            <label>Ongkos Kirim<input class="input" type="number" min="0" step="0.01" name="cost" value="0" required></label>
        </div>
        <label><input type="checkbox" name="is_active" value="1" checked> Aktif</label>
        <button class="btn">Tambah Layanan</button>
    </form>
</div>

<div class="card">
    <div class="table-scroll">
        <table>
            <tr>
                <th>Kurir & Layanan</th>
                <th>Estimasi</th>
                <th>Ongkir</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            @foreach($services as $service)
            <tr>
                <td colspan="4">
                    <form class="row" method="POST" action="{{ route('admin.shipping.update', $service) }}">
                        @csrf
                        @method('PUT')
                        <input class="input" style="max-width:130px" name="courier" value="{{ $service->courier }}" required>
                        <input class="input" style="max-width:130px" name="service" value="{{ $service->service }}" required>
                        <input class="input" style="max-width:130px" name="eta" value="{{ $service->eta }}" placeholder="ETA">
                        <input class="input" style="max-width:140px" type="number" min="0" step="0.01" name="cost" value="{{ $service->cost }}" required>
                        <label style="margin:0"><input type="checkbox" name="is_active" value="1" @checked($service->is_active)> Aktif</label>
                        <button class="btn">Simpan</button>
                    </form>
                </td>
                <td>
                    <form method="POST" action="{{ route('admin.shipping.destroy', $service) }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger">Nonaktifkan</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection