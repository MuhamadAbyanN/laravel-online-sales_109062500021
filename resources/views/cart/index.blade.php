@extends('layouts.app')
@section('title', 'Keranjang Belanja')
@section('content')
<div class="section-heading">
    <h1>Keranjang Belanja</h1>
    @if(!empty($items))
        <form method="POST" action="{{ route('cart.clear') }}">
            @csrf
            @method('DELETE')
            <button class="btn-danger">Kosongkan Keranjang</button>
        </form>
    @endif
</div>

@if(empty($items))
    <div class="empty-state">
        <h3>Keranjangmu masih kosong</h3>
        <p class="muted">Pilih produk dari katalog untuk mulai berbelanja.</p>
        <a class="btn" href="{{ route('shop.index') }}" style="margin-top:12px">Jelajahi Produk</a>
    </div>
@else
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    @php($product = $item['product'])
                    <tr>
                        <td>
                            <a href="{{ route('shop.show', $product) }}"><strong>{{ $product->name }}</strong></a>
                            <div class="muted">{{ $product->category->name }} · Stok: {{ $product->stock }}</div>
                        </td>
                        <td>Rp{{ number_format($product->price, 2, ',', '.') }}</td>
                        <td>
                            <form method="POST" action="{{ route('cart.update', $product) }}">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" max="{{ $product->stock }}" style="width:80px" required>
                                <button class="btn-light">Ubah</button>
                            </form>
                        </td>
                        <td><strong>Rp{{ number_format($item['line_total'], 2, ',', '.') }}</strong></td>
                        <td>
                            <form method="POST" action="{{ route('cart.remove', $product) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card" style="max-width:420px; margin-left:auto;">
        <div class="summary-line">
            <span>Total Belanja</span>
            <strong>Rp{{ number_format($subtotal, 2, ',', '.') }}</strong>
        </div>
        <div class="row" style="margin-top:16px; justify-content:space-between;">
            <a class="btn-light" href="{{ route('shop.index') }}">Lanjut Belanja</a>
            @if(Route::has('checkout.index'))
                <a class="btn" href="{{ route('checkout.index') }}">Lanjut Checkout</a>
            @endif
        </div>
    </div>
@endif
@endsection