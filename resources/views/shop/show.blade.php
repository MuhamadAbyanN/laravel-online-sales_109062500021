@extends('layouts.app')
@section('title', $product->name)
@section('content')
<p><a href="{{ route('shop.index') }}">← Kembali ke Katalog</a></p>

<div class="card">
    <div class="row" style="align-items:flex-start; gap:24px;">
        <div style="flex:1; min-width:260px;">
            @if($product->image)
                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" style="width:100%; border-radius:8px;">
            @else
                <div style="height:280px; background:#f2f4f3; border-radius:8px; display:flex; align-items:center; justify-content:center;">Tanpa Gambar</div>
            @endif
        </div>
        <div style="flex:1; min-width:260px;">
            <span class="eyebrow">{{ $product->category->name }} · SKU: {{ $product->sku }}</span>
            <h1>{{ $product->name }}</h1>
            <p style="font-size:1.3rem; font-weight:700;">Rp{{ number_format($product->price, 2, ',', '.') }}</p>
            <p class="muted">Stok tersedia: <strong>{{ $product->stock }}</strong></p>
            <p>{{ $product->description ?: 'Tidak ada deskripsi produk.' }}</p>

            @if(Route::has('cart.add'))
                @if($product->stock > 0)
                    <form method="POST" action="{{ route('cart.add', $product) }}" class="row">
                        @csrf
                        <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}" style="max-width:90px" required>
                        <button class="btn">Tambah ke Keranjang</button>
                    </form>
                @else
                    <button class="btn-light" disabled>Stok Habis</button>
                @endif
            @endif
        </div>
    </div>
</div>

@if($relatedProducts->isNotEmpty())
    <h2>Produk Serupa</h2>
    <div class="grid product-grid">
        @foreach($relatedProducts as $related)
            <article class="card product-card">
                <span class="eyebrow">{{ $related->category->name }}</span>
                <h3><a href="{{ route('shop.show', $related) }}">{{ $related->name }}</a></h3>
                <p>Rp{{ number_format($related->price, 2, ',', '.') }}</p>
            </article>
        @endforeach
    </div>
@endif
@endsection