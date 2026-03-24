<?php 

namespace App\Http\Controllers; 

use App\Models\Product; 
use Illuminate\Http\Request; 

class CartController extends Controller 
{ 
    public function index() 
    { 
        $cart = $this->syncCartWithInventory(session()->get('cart', [])); 
        session()->put('cart', $cart); 
        $total = $this->calculateTotal($cart); 

        return view('cart.index', compact('cart', 'total')); 
    } 

    public function add(Request $request, Product $product)
    {
    if (!$product->active) {
        return redirect()->back()->with('error', 'Este producto no está disponible.');
    }

    if ($product->stock < 1) {
        return redirect()->back()->with('error', 'Este producto no tiene stock disponible.');
    }
    if ($product->sizes && count($product->sizes) > 0 && !$request->input('size')){
        return redirect()->back()->with('error', 'Por favor selecciona una talla.');
    }

    $quantity = max(1, (int) $request->input('quantity', 1));
    $size = $request->input('size', null);
    $cartKey = $product->id . '_' . $size;
    $cart = session()->get('cart', []);
    $currentQuantity = $cart[$cartKey]['quantity'] ?? 0;

    if ($currentQuantity + $quantity > $product->stock) {
        return redirect()->back()->with('error', 'No puedes agregar más unidades que el stock disponible.');
    }

    $cart[$cartKey] = [
        'name' => $product->name,
        'price' => $product->price,
        'quantity' => $currentQuantity + $quantity,
        'image' => $product->image,
        'size' => $size,
    ];

    session()->put('cart', $cart);
    return redirect()->back()->with('success', 'Producto agregado al carrito.');
    } 

    public function update(Request $request, $id) 
    { 
        $validated = $request->validate([ 
            'quantity' => ['required', 'integer', 'min:1'], 
        ]); 

        $cart = session()->get('cart', []); 

        if (!isset($cart[$id])) { 
            return redirect()->route('cart.index')->with('error', 'El producto no existe 
en el carrito.'); 
        } 

        $product = Product::find($id); 

        if (!$product || !$product->active || $product->stock < 1) { 
            unset($cart[$id]); 
            session()->put('cart', $cart); 

            return redirect()->route('cart.index')->with('error', 'El producto ya no esta 
disponible y fue retirado del carrito.'); 
        } 

        if ($validated['quantity'] > $product->stock) { 
            return redirect()->route('cart.index')->with('error', 'Solo hay ' . $product
>stock . ' unidades disponibles.'); 
        } 

        $cart[$id] = [ 
            'name' => $product->name, 
            'price' => $product->price, 
            'quantity' => $validated['quantity'], 
            'image' => $product->image, 
        ]; 
        session()->put('cart', $cart); 

        return redirect()->route('cart.index')->with('success', 'Cantidad 
actualizada.'); 
    } 

    public function remove($id) 
    { 
        $cart = session()->get('cart', []); 

        if(isset($cart[$id])) { 
            unset($cart[$id]); 
            session()->put('cart', $cart); 
        } 

        return redirect()->route('cart.index'); 
    } 

    private function calculateTotal($cart) 
    { 
        $total = 0; 
        foreach($cart as $item) { 
            $total += $item['price'] * $item['quantity']; 
        } 
        return $total; 
    } 

    private function syncCartWithInventory(array $cart): array 
    { 
        if (empty($cart)) { 
            return []; 
        } 

        $productIds = array_map(function($key) {
        return explode('_', $key)[0];
        }, array_keys($cart));

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id'); 
        $syncedCart = []; 

        foreach ($cart as $cartKey => $item) { 
            $productId = explode('_', $cartKey) [0];
            $product = $products->get((int) $productId); 

            if (!$product || !$product->active || $product->stock < 1) { 
                continue; 
            } 

            $quantity = max(1, min((int) $item['quantity'], $product->stock)); 

            $syncedCart[$cartKey] = [ 
                'name' => $product->name, 
                'price' => $product->price, 
                'quantity' => $quantity, 
                'image' => $product->image, 
                'size' => $item['size'] ?? null,
            ]; 
        } 

        return $syncedCart; 
    } 
}