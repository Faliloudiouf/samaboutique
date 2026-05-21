<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('nom')->paginate(15);
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.form', ['category' => new Category()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:80', 'unique:categories,nom'],
            'emoji' => ['nullable', 'string', 'max:6'],
            'couleur_fond' => ['nullable', 'string', 'max:12'],
            'couleur_accent' => ['nullable', 'string', 'max:12'],
            'description' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }
        Category::create($data);
        return redirect()->route('categories.index')->with('ok', 'Catégorie créée.');
    }

    public function edit(Category $category)
    {
        return view('categories.form', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:80', 'unique:categories,nom,' . $category->id],
            'emoji' => ['nullable', 'string', 'max:6'],
            'couleur_fond' => ['nullable', 'string', 'max:12'],
            'couleur_accent' => ['nullable', 'string', 'max:12'],
            'description' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);
        if ($request->hasFile('image')) {
            if ($category->image) Storage::disk('public')->delete($category->image);
            $data['image'] = $request->file('image')->store('categories', 'public');
        }
        $category->update($data);
        return redirect()->route('categories.index')->with('ok', 'Catégorie mise à jour.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('err', 'Impossible : cette catégorie contient des produits.');
        }
        $category->delete();
        return back()->with('ok', 'Catégorie supprimée.');
    }
}
