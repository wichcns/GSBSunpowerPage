<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactEnquiry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminContactEnquiryController extends Controller
{
    public function index(Request $request): View
    {
        $enquiries = $this->filteredEnquiries($request)->latest()->paginate(20)->withQueryString();
        $summary = ContactEnquiry::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.contact-enquiries.index', compact('enquiries', 'summary'));
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'gsb-contact-enquiries-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($request): void {
            $stream = fopen('php://output', 'w');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['Received at', 'Source', 'Status', 'Name', 'Company', 'Email', 'Phone', 'Services of interest', 'Project location', 'Estimated budget', 'Website', 'Message']);

            $this->filteredEnquiries($request)->latest()->each(function (ContactEnquiry $enquiry) use ($stream): void {
                fputcsv($stream, [
                    $enquiry->created_at->format('Y-m-d H:i:s'), $enquiry->source, $enquiry->status,
                    $enquiry->name, $enquiry->company, $enquiry->email, $enquiry->phone,
                    implode(', ', $enquiry->services ?? []), $enquiry->project_location, $enquiry->budget,
                    $enquiry->website, $enquiry->message,
                ]);
            });
            fclose($stream);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function show(ContactEnquiry $contactEnquiry): View
    {
        return view('admin.contact-enquiries.show', compact('contactEnquiry'));
    }

    public function update(Request $request, ContactEnquiry $contactEnquiry): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:new,in_progress,closed']]);
        $contactEnquiry->update($data);

        return back()->with('success', __('admin.enquiry_status_updated'));
    }

    /**
     * @return Builder<ContactEnquiry>
     */
    private function filteredEnquiries(Request $request): Builder
    {
        return ContactEnquiry::query()
            ->when($request->string('status')->value(), fn ($query, string $status) => $query->where('status', $status))
            ->when($request->string('search')->value(), function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            });
    }
}
