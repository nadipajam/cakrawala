<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\User;
use App\Support\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $role = trim((string) $request->string('role'));
        $department = trim((string) $request->string('department'));

        $users = User::query()
            ->withCount(['passengers', 'bookings'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($userQuery) use ($search) {
                    $userQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->when(in_array($role, UserRole::values(), true), fn ($query) => $query->where('role', $role))
            ->when($department !== '', fn ($query) => $query->where('department', 'like', "%{$department}%"))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'role' => $role,
            'department' => $department,
            'roleOptions' => UserRole::all(),
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->canManageUsers(), 403);

        return view('admin.users.create', [
            'user' => new User(),
            'roleOptions' => UserRole::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canManageUsers(), 403);

        $data = $this->validateUser($request);

        User::query()->create([
            ...$data,
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('admin.users.index')->with('status', 'User baru berhasil dibuat.');
    }

    public function show(User $user): View
    {
        $user->loadCount(['passengers', 'bookings']);

        $passengers = Passenger::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit(10)
            ->get();

        $bookings = Booking::query()
            ->with(['flight.airline', 'payments', 'details.ticket'])
            ->where('user_id', $user->id)
            ->latest()
            ->limit(10)
            ->get();

        $paymentStats = [
            'paid' => $bookings->flatMap->payments->where('payment_status', 'paid')->count(),
            'pending' => $bookings->flatMap->payments->where('payment_status', 'pending')->count(),
            'failed' => $bookings->flatMap->payments->where('payment_status', 'failed')->count(),
        ];

        $ticketCount = $bookings
            ->flatMap->details
            ->filter(fn ($detail) => $detail->ticket !== null)
            ->count();

        return view('admin.users.show', compact('user', 'passengers', 'bookings', 'paymentStats', 'ticketCount'));
    }

    public function edit(User $user): View
    {
        abort_unless(auth()->user()?->canManageUsers(), 403);

        return view('admin.users.edit', [
            'user' => $user,
            'roleOptions' => UserRole::all(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->canManageUsers(), 403);

        $data = $this->validateUser($request, $user);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status', 'Profil user berhasil diperbarui.');
    }

    protected function validateUser(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:25'],
            'role' => ['required', Rule::in(UserRole::values())],
            'employee_id' => ['nullable', 'string', 'max:50', Rule::unique('users', 'employee_id')->ignore($user?->id)],
            'department' => ['nullable', 'string', 'max:120'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', 'min:8'],
        ]);

        if ($data['role'] === UserRole::CUSTOMER) {
            $data['employee_id'] = null;
            $data['department'] = null;
            $data['job_title'] = null;
        }

        if (in_array($data['role'], [UserRole::STAFF, UserRole::MANAGER, UserRole::ADMIN], true) && empty($data['employee_id'])) {
            $data['employee_id'] = 'EMP-'.now()->format('ymd').'-'.random_int(1000, 9999);
        }

        return $data;
    }
}
