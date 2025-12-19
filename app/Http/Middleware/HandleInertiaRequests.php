<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Get flash messages from session
        // Note: session()->get() retrieves flash messages without removing them
        // They are automatically removed after the next request
        $success = $request->session()->get('success');
        $error = $request->session()->get('error');
        
        return [
            ...parent::share($request),
            // Simple flash bag for user-friendly success/error messages.
            // Flash messages are automatically available in session
            'flash' => [
                'success' => $success,
                'error' => $error,
            ],
        ];
    }

    /**
     * Set the root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     */
    public function rootView(Request $request): string
    {
        return parent::rootView($request);
    }
}
