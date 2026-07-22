<?php
namespace App\Http\Controllers;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
class UserController extends Controller {
    public function index(Request $request) {
        $query = User::with('role')->orderBy('name');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('username', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->role_id) {
            $query->where('role_id', $request->role_id);
        }
        
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }
        
        $users = $query->paginate(15)->withQueryString();
        $roles = Role::all();
        
        return view('users.index', compact('users', 'roles'));
    }
    public function create() {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }
    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|email|unique:users',
            'password' => ['required', Rules\Password::defaults()],
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);
        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $user = User::create($validated);
        
        $logData = $user->toArray();
        unset($logData['password']);
        ActivityLogService::created('Users', $user->name, $logData);
        
        return redirect()->route('users.index')->with('toast_success', "User {$user->name} berhasil ditambahkan!");
    }
    public function edit(User $user) {
        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }
    public function update(Request $request, User $user) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
            'password' => ['nullable', Rules\Password::defaults()],
        ]);
        
        $oldData = $user->toArray();
        unset($oldData['password']);
        
        if (empty($validated['password'])) unset($validated['password']);
        else $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active');
        $user->update($validated);
        
        $newData = $user->toArray();
        unset($newData['password']);
        ActivityLogService::updated('Users', $user->name, $oldData, $newData);
        
        return redirect()->route('users.index')->with('toast_success', "User {$user->name} berhasil diperbarui!");
    }
    public function destroy(User $user) {
        if ($user->id === auth()->id()) {
            return back()->with('toast_error', 'Anda tidak dapat menghapus akun sendiri!');
        }
        
        // Prevent deleting users with transaction history
        if (\App\Models\Sale::where('user_id', $user->id)->exists() || \App\Models\Purchase::where('user_id', $user->id)->exists()) {
            return back()->with('toast_error', "User {$user->name} tidak dapat dihapus karena memiliki riwayat transaksi!");
        }
        
        $name = $user->name;
        $oldData = $user->toArray();
        unset($oldData['password']);
        
        $user->delete();
        ActivityLogService::deleted('Users', $name, $oldData);
        
        return redirect()->route('users.index')->with('toast_success', "User {$name} berhasil dihapus!");
    }
    public function toggleStatus(User $user) {
        if ($user->id === auth()->id()) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Anda tidak dapat menonaktifkan akun sendiri!'], 400);
            }
            return back()->with('toast_error', 'Anda tidak dapat menonaktifkan akun sendiri!');
        }
        
        $oldData = $user->toArray();
        unset($oldData['password']);
        
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        $newData = $user->toArray();
        unset($newData['password']);
        
        ActivityLogService::updated('Users', "{$user->name} {$status}", $oldData, $newData);
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $user->is_active,
                'message' => "User {$user->name} berhasil {$status}!"
            ]);
        }
        
        return back()->with('toast_success', "User {$user->name} berhasil {$status}!");
    }
}
