<?php

namespace App\Http\Controllers;

use App\Contracts\Services\ActivityLoggerInterface;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecurityController extends Controller
{
    /**
     * The activity logger service.
     *
     * @var ActivityLoggerInterface
     */
    protected $activityLogger;

    /**
     * Create a new controller instance.
     *
     * @param ActivityLoggerInterface $activityLogger
     * @return void
     */
    public function __construct(ActivityLoggerInterface $activityLogger)
    {
        $this->activityLogger = $activityLogger;
    }

    /**
     * Display the security report form.
     *
     * @return View
     */
    public function showReportForm(): View
    {
        return view('security.report');
    }

    /**
     * Handle a security report submission.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitReport(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'description' => 'required|string|min:10',
        ]);

        // Log the security report
        $this->activityLogger->log(
            'security_report_submitted',
            "Laporan aktivitas mencurigakan dari {$validated['name']} ({$validated['email']})",
            null,
            $request->ip(),
            $request->userAgent()
        );

        // Here you would typically notify security team or administrators
        // For now we'll just redirect with a success message

        return redirect()->route('login')
            ->with('status', 'Terima kasih atas laporan Anda. Tim keamanan kami akan menyelidiki hal ini dan menghubungi Anda jika diperlukan.');
    }
}