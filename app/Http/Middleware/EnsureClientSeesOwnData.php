<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Invoice;

class EnsureClientSeesOwnData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === 'client') {
            // 1. Block access to admin-only paths entirely
            $path = $request->path();
            if (
                str_starts_with($path, 'clients') || 
                str_starts_with($path, 'settings') || 
                str_starts_with($path, 'activity')
            ) {
                abort(403, 'Unauthorized.');
            }

            // 2. Protect invoice routes: make sure they only see invoices belonging to their client_id
            $invoice = $request->route('invoice');

            if ($invoice) {
                if ($invoice instanceof Invoice) {
                    if ($invoice->client_id !== $user->client_id) {
                        abort(403, 'Unauthorized.');
                    }
                } else {
                    $inv = Invoice::find($invoice);
                    if ($inv && $inv->client_id !== $user->client_id) {
                        abort(403, 'Unauthorized.');
                    }
                }
            }
        }

        return $next($request);
    }
}
