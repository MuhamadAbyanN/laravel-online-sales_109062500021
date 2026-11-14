@extends('layouts.admin')
@section('title', 'Catat Barang Masuk')
@section('content')
<h1>Catat Barang Masuk</h1>
<div class="card">
    <form method="POST" action="{{ route('admin.stock.store') }}">
        @csrf
        <label>Produk
            <select name="product_id" required>
                <option value="">Pilih Produk</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                        {{ $product->sku }} - {{ $product->name }} (Stok saat ini: {{ $product->stock }})
                    </option>
                @endforeach
            </select>
        </label>
        <label>Jumlah Masuk<input class="input" type="number" min="1" name="quantity" value="{{ old('quantity', 1) }}" required></label>
        <label>Harga Beli Satuan (Opsional)<input class="input" type="number" min="0" step="0.01" name="unit_cost" value="{{ old('unit_cost') }}"></label>
        <label>Referensi / No. Faktur (Opsional)<input class="input" name="reference" value="{{ old('reference') }}" placeholder="Contoh: PO-2026-001"></label>
        <label>Tanggal & Waktu Masuk<input class="input" type="datetime-local" name="movement_at" value="{{ old('movement_at', now()->format('Y-m-d\TH:i')) }}" required></label>
        <label>Catatan (Opsional)<textarea class="input" rows="3" name="note">{{ old('note') }}</textarea></label>
        <button class="btn">Simpan Barang Masuk</button>
    </form>
</div>
@endsection