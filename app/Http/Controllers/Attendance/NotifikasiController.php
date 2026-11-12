<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Notification; // Asumsi model notification sudah ada atau buat baru
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotifikasiController extends Controller
{
    /**
     * Show the notification page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $tipeFilter = $request->query('tipe');

        $query = Notification::orWhere(function ($q) {
            // Asumsi model notification ada dengan relasi ke employee
            $q->where('type', 'like', '%Attendance%');
        });

        if ($tipeFilter) {
            $query->where('type', $tipeFilter);
        }

        $notifikasi = $query->with(['employee'])->latest('created_at')->paginate(20);

        return view('livewire.notifikasi', [
            'notifikasi' => $notifikasi,
            'filterTipeNotif' => $tipeFilter,
        ]);
    }

    /**
     * Mark notification as read.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function baca(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['status_baca' => 'sudah']);

        return response()->json([
            'message' => 'Notifikasi telah dibaca!',
        ]);
    }

    /**
     * Send WhatsApp notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function kirimWhatsApp(Request $request)
    {
        $validated = $request->validate([
            'contact' => 'required|string',
            'pesan' => 'required|string',
        ]);

        // Buka WhatsApp Web
        $url = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $validated['contact']) . '?text=' . urlencode($validated['pesan']);

        return response()->json([
            'message' => 'Buka WhatsApp untuk mengirim notifikasi.',
            'whatsapp_url' => $url,
        ]);
    }

    /**
     * Trigger push notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function triggerPush(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
            'employee_id' => 'sometimes|integer',
        ]);

        // Log trigger push notification
        Log::info('Push notification triggered', [
            'title' => $validated['title'],
            'body' => $validated['body'],
            'employee_id' => $validated->employee_id,
        ]);

        return response()->json([
            'message' => 'Push notification trigger berhasil!',
        ]);
    }
}