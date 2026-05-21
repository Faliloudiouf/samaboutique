<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($s = $request->input('q')) {
            $query->where(function ($q) use ($s) {
                $q->where('nom', 'like', "%$s%")
                  ->orWhere('reference', 'like', "%$s%");
            });
        }
        if ($cat = $request->input('category')) {
            $query->where('category_id', $cat);
        }
        if ($request->input('stock') === 'rupture') {
            $query->where('stock', '<=', 0);
        } elseif ($request->input('stock') === 'alerte') {
            $query->where('stock', '>', 0)->whereColumn('stock', '<=', 'seuil_alerte');
        }

        $products = $query->orderBy('nom')->paginate(12)->withQueryString();
        $categories = Category::orderBy('nom')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        return view('products.form', [
            'product' => new Product(['seuil_alerte' => 5, 'stock' => 0, 'actif' => true]),
            'categories' => Category::orderBy('nom')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['image'] = $this->handleImage($request);
        Product::create($data);
        return redirect()->route('products.index')->with('ok', 'Produit ajouté.');
    }

    public function edit(Product $product)
    {
        return view('products.form', [
            'product' => $product,
            'categories' => Category::orderBy('nom')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateData($request, $product);
        if ($img = $this->handleImage($request)) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $data['image'] = $img;
        }
        $product->update($data);
        return redirect()->route('products.index')->with('ok', 'Produit mis à jour.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) Storage::disk('public')->delete($product->image);
        $product->delete();
        return back()->with('ok', 'Produit supprimé.');
    }

    private function validateData(Request $request, ?Product $product = null): array
    {
        $unique = 'unique:products,reference' . ($product ? ',' . $product->id : '');
        return $request->validate([
            'reference' => ['required', 'string', 'max:40', $unique],
            'nom' => ['required', 'string', 'max:120'],
            'emoji' => ['nullable', 'string', 'max:6'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'prix_achat' => ['nullable', 'numeric', 'min:0'],
            'prix_vente' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'seuil_alerte' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'actif' => ['nullable', 'boolean'],
        ]) + ['actif' => $request->boolean('actif')];
    }

    private function handleImage(Request $request): ?string
    {
        if ($request->hasFile('image')) {
            return $request->file('image')->store('products', 'public');
        }
        return null;
    }
}
