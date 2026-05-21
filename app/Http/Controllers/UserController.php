<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        if ($s = $request->input('q')) {
            $query->where(fn($q) => $q->where('name','like',"%$s%")->orWhere('email','like',"%$s%"));
        }
        if ($r = $request->input('role')) {
            $query->where('role', $r);
        }
        if ($request->input('status') === 'suspended') {
            $query->whereNotNull('suspended_at');
        } elseif ($request->input('status') === 'active') {
            $query->whereNull('suspended_at')->where('actif', true);
        }
        $users = $query->withCount('sales')->orderBy('name')->paginate(15)->withQueryString();

        $stats = [
            'total' => User::count(),
            'gerants' => User::where('role','gerant')->count(),
            'vendeurs' => User::where('role','vendeur')->count(),
            'suspendus' => User::whereNotNull('suspended_at')->count(),
        ];

        return view('users.index', compact('users','stats'));
    }

    public function create()
    {
        return view('users.form', ['userModel' => new User(['role'=>'vendeur','actif'=>true])]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'email' => ['required','email','max:150','unique:users,email'],
            'telephone' => ['nullable','string','max:30'],
            'role' => ['required', Rule::in(['gerant','vendeur'])],
            'password' => ['required', Password::min(6)],
            'photo' => ['nullable','image','max:2048'],
            'actif' => ['nullable'],
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['actif'] = $request->boolean('actif', true);
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('users', 'public');
        }
        User::create($data);
        return redirect()->route('users.index')->with('ok', 'Utilisateur créé avec succès.');
    }

    public function edit(User $user)
    {
        return view('users.form', ['userModel' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'email' => ['required','email','max:150','unique:users,email,'.$user->id],
            'telephone' => ['nullable','string','max:30'],
            'role' => ['required', Rule::in(['gerant','vendeur'])],
            'password' => ['nullable', Password::min(6)],
            'photo' => ['nullable','image','max:2048'],
            'actif' => ['nullable'],
        ]);
        if (!empty($data['password'])) $data['password'] = Hash::make($data['password']);
        else unset($data['password']);

        $data['actif'] = $request->boolean('actif', true);

        if ($request->hasFile('photo')) {
            if ($user->photo) Storage::disk('public')->delete($user->photo);
            $data['photo'] = $request->file('photo')->store('users', 'public');
        }
        $user->update($data);
        return redirect()->route('users.index')->with('ok', 'Utilisateur mis à jour.');
    }

    public function suspend(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('err', 'Vous ne pouvez pas vous suspendre vous-même.');
        }
        $user->update(['suspended_at' => $user->suspended_at ? null : now()]);
        return back()->with('ok', $user->suspended_at ? 'Compte suspendu.' : 'Compte réactivé.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('err', 'Vous ne pouvez pas supprimer votre propre compte.');
        }
        if ($user->sales()->exists()) {
            return back()->with('err', 'Impossible : cet utilisateur a des ventes liées. Vous pouvez le suspendre à la place.');
        }
        if ($user->photo) Storage::disk('public')->delete($user->photo);
        $user->delete();
        return back()->with('ok', 'Utilisateur supprimé.');
    }
}
