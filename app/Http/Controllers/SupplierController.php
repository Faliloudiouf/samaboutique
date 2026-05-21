<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount('orders')->orderBy('nom')->paginate(15);
        return view('suppliers.index', compact('suppliers'));
    }

    public function create() { return view('suppliers.form', ['supplier' => new Supplier()]); }

    public function store(Request $r)
    {
        Supplier::create($this->v($r));
        return redirect()->route('suppliers.index')->with('ok', 'Fournisseur créé.');
    }

    public function edit(Supplier $supplier) { return view('suppliers.form', compact('supplier')); }

    public function update(Request $r, Supplier $supplier)
    {
        $supplier->update($this->v($r));
        return redirect()->route('suppliers.index')->with('ok', 'Fournisseur mis à jour.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->orders()->exists()) {
            return back()->with('err', 'Impossible : ce fournisseur a des commandes.');
        }
        $supplier->delete();
        return back()->with('ok', 'Fournisseur supprimé.');
    }

    private function v(Request $r): array
    {
        return $r->validate([
            'nom' => ['required','string','max:120'],
            'contact' => ['nullable','string','max:120'],
            'telephone' => ['nullable','string','max:30'],
            'email' => ['nullable','email','max:120'],
            'adresse' => ['nullable','string','max:200'],
        ]);
    }
}
