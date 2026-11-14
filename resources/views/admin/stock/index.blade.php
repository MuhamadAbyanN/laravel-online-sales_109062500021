@extends('layouts.admin')
@section('title', 'Stok & Barang Masuk')
@section('content')
<div class="row" style="justify-content:space-between">
    <h1>Stok & Barang Masuk</h1>
    <a class="btn" href="{{ route('admin.stock.create') }}">Catat Barang Masuk</a>
</div>

<div class="card">
    <form method="GET" class="row">
        <select class="input" style="max-width:260px" name="product_id">
            <option value="">Semua Produk</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>{{ $product->sku }} - {{ $product->name }}</option>
            @endforeach
        </select>
        <select class="input" style="max-width:160px" name="type">
            <option value="">Semua Tipe</option>
            <option value="IN" @selected(request('type') === 'IN')>Masuk (IN)</option>
            <option value="OUT" @selected(request('type') === 'OUT')>Keluar (OUT)</option>
        </select>
        <button class="btn">Filter</button>
    </form>
</div>

<div class="card">
    <div class="table-scroll">
        <table>
            <tr>
                <th>Waktu</th>
                <th>Produk</th>
                <th>Tipe</th>
                <th>Jumlah</th>
                <th>Harga Beli</th>
                <th>Referensi</th>
                <th>Catatan</th>
                <th>Oleh</th>
            </tr>
            @foreach($movements as $movement)
            <tr>
                <td>{{ $movement->movement_at->format('d/m/Y H:i') }}</td>
                <td>{{ $movement->product->name }}</td>
                <td>{{ $movement->type }}</td>
                <td>{{ $movement->type === 'IN' ? '+' : '-' }}{{ $movement->quantity }}</td>
                <td>{{ $movement->unit_cost !== null ? 'Rp'.number_format($movement->unit_cost, 2, ',', '.') : '-' }}</td>
                <td>{{ $movement->reference ?? '-' }}</td>
                <td>{{ $movement->note ?? '-' }}</td>
                <td>{{ $movement->user?->name ?? 'Sistem' }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    <div style="margin-top:16px">{{ $movements->links() }}</div>
</div>
@endsection