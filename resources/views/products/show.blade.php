@extends('layouts.app') 

@section('title', $product->name) 

@section('content') 
<div class="max-w-5xl mx-auto px-4"> 
    <div class="bg-white rounded-lg shadow-md overflow-hidden md:grid 
md:grid-cols-2"> 
        <div class="bg-gray-100 h-80 md:h-full"> 
            @if($product->image) 
                <img 
                    src="{{ asset('storage/' . $product->image) }}" 
                    alt="{{ $product->name }}" 
                    class="w-full h-full object-cover" 
                > 
            @else 
                <div class="w-full h-full flex items-center justify-center text
gray-400"> 
                    Sin imagen 
                </div> 
            @endif 
        </div> 

        <div class="p-6"> 
            <h1 class="text-3xl font-bold mb-2">{{ $product->name }}</h1> 

            <p class="text-sm mb-4 {{ $product->active ? 'text-pink-600' : 
'text-red-600' }}"> 
                {{ $product->active ? 'Disponible' : 'No disponible' }} 
            </p> 

            <p class="text-gray-700 mb-6"> 
                {{ $product->description ?: 'Sin descripcion.' }} 
            </p> 
            @if($product->sizes && count($product->sizes) > 0)
            <div class="mb-6">
            <p class="text-sm font-medium text-gray-700 mb-2">Selecciona tu talla:</p>
            <div class="flex gap-2">
            @foreach($product->sizes as $size)
            <label class="cursor-pointer">
            <input type="radio" name="size" value="{{ $size }}" class="hidden peer">
            <span class="px-3 py-1 border-2 border-gray-300 rounded font-medium peer-checked:border-purple-600 peer-checked:text-purple-600 hover:border-purple-400 transition">
                {{ $size }}
            </span>
        </label>
        @endforeach
    </div>
</div>
@endif

            <div class="mb-6"> 
                <p class="text-3xl font-bold text-purple-600">${{ 
number_format($product->price, 2) }}</p> 
                <p class="text-sm {{ $product->stock > 0 ? 'text-pink-600' : 
'text-red-600' }}"> 
                    {{ $product->stock > 0 ? 'Stock: ' . $product->stock : 'Agotado' 
}} 
                </p> 
            </div> 

            <div class="flex gap-3"> 
                <a 
                    href="{{ route('products.index') }}" 
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg
gray-300 transition" 
                > 
                    Volver 
                </a> 

                @if($product->active && $product->stock > 0) 
                    <form action="{{ route('cart.add', $product) }}" method="POST">
    @csrf
    <div class="flex items-center gap-4 mb-4">
        <div class="flex items-center border-2 border-gray-300 rounded-lg">
            <button 
                type="button"
                onclick="decrementar()"
                class="px-4 py-2 text-xl font-bold text-gray-600 hover:text-purple-600 transition">
                −
            </button>
            <input 
                type="number" 
                name="quantity" 
                id="quantity"
                value="1" 
                min="1" 
                max="{{ $product->stock }}"
                class="w-16 text-center text-lg font-bold border-none outline-none">
            <button 
                type="button"
                onclick="incrementar()"
                class="px-4 py-2 text-xl font-bold text-gray-600 hover:text-purple-600 transition">
                +
            </button>
        </div>
        <button
            type="submit"
            class="flex-1 px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-medium">
            Agregar al carrito
        </button>
    </div>
</form>

<script>
function incrementar() {
    const input = document.getElementById('quantity');
    const max = parseInt(input.getAttribute('max'));
    if (parseInt(input.value) < max) {
        input.value = parseInt(input.value) + 1;
    }
}
function decrementar() {
    const input = document.getElementById('quantity');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
    }
}
</script>
                @endif 
            </div> 
        </div> 
    </div> 
</div> 
@endsection 