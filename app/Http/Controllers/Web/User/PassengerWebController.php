<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Passenger\StorePassengerRequest;
use App\Http\Requests\Passenger\UpdatePassengerRequest;
use App\Models\Passenger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class PassengerWebController extends Controller
{
    public function index(Request $request): View
    {
        $passengers = $request->user()
            ->passengers()
            ->latest()
            ->get();

        return view('user.passengers.index', [
            'passengers' => $passengers,
        ]);
    }

    public function store(StorePassengerRequest $request): RedirectResponse
    {
        try {
            $request->user()->passengers()->create($request->validated());
        } catch (Throwable $exception) {
            Log::error('Failed to create passenger from web form.', [
                'user_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'passenger' => 'Gagal menambahkan passenger. Silakan coba lagi.',
                ]);
        }

        return redirect()
            ->route('passengers.index')
            ->with('status', 'Passenger berhasil ditambahkan.')
            ->with('status_type', 'success');
    }

    public function update(UpdatePassengerRequest $request, Passenger $passenger): RedirectResponse
    {
        abort_unless($passenger->user_id === $request->user()->id, 403);

        $passenger->update($request->validated());

        return redirect()
            ->route('passengers.index')
            ->with('status', 'Passenger berhasil diperbarui.')
            ->with('status_type', 'success');
    }

    public function destroy(Request $request, Passenger $passenger): RedirectResponse
    {
        abort_unless($passenger->user_id === $request->user()->id, 403);

        $passenger->delete();

        return redirect()
            ->route('passengers.index')
            ->with('status', 'Passenger berhasil dihapus.')
            ->with('status_type', 'success');
    }
}
