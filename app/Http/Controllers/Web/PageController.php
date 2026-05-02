<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Airline;
use App\Models\Airport;
use App\Models\ContactMessage;
use App\Models\Flight;
use App\Services\PortalNotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct(
        protected PortalNotificationService $notificationService
    ) {
    }

    public function about(): View
    {
        return view('pages.about', [
            'airlines' => Airline::query()->orderBy('name')->limit(12)->get(),
            'airportCount' => Airport::query()->count(),
            'activeFlightCount' => Flight::query()->whereIn('status', ['scheduled', 'delayed'])->count(),
        ]);
    }

    public function contact(): View
    {
        return view('pages.contact', [
            'airports' => Airport::query()->orderBy('city')->limit(6)->get(),
            'recentMessages' => auth()->check()
                ? ContactMessage::query()
                    ->where('user_id', auth()->id())
                    ->latest()
                    ->limit(3)
                    ->get()
                : collect(),
        ]);
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $message = ContactMessage::query()->create([
            ...$data,
            'user_id' => $request->user()?->id,
            'source' => 'website',
            'status' => 'open',
        ]);

        $this->notificationService->contactMessageReceived($message);

        return redirect()
            ->route('contact')
            ->with('status', 'Pesan Anda sudah diterima. Tim Cakrawala akan menindaklanjuti secepatnya.')
            ->with('status_type', 'success');
    }
}
