<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $payments = Payment::all();
        return view('payments.index', compact('payments'));
    }

    public function create()
    {
        $tickets = Ticket::all();
        return view('payments.create', compact('tickets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
        ]);

        $ticket = Ticket::findOrFail($request->ticket_id);
        $concert = $ticket->concert;
        $amount = $concert->ticket_price * $ticket->quantity;

        $payment = new Payment();
        $payment->ticket_id = $request->ticket_id;
        $payment->amount = $amount;
        $payment->payment_date = now();
        $payment->payment_status = 'proses';
        $payment->payment_code = 'PAY-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        $payment->save();

        return redirect()->route('payments.index')->with('success', 'Pembayaran berhasil ditambahkan.');
    }


    public function show(Payment $payment)
    {
        return view('payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        $tickets = Ticket::all();
        return view('payments.edit', compact('payment', 'tickets'));
    }

    public function update(Request $request, Payment $payment)
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'payment_status' => 'required|string|max:20',
        ]);

        $ticket = Ticket::findOrFail($request->ticket_id);
        $concert = $ticket->concert;
        $amount = $concert->ticket_price * $ticket->quantity;

        $payment->update([
            'ticket_id' => $request->ticket_id,
            'amount' => $amount,
            'payment_status' => $request->payment_status,
        ]);

        return redirect()->route('payments.index')->with('success', 'Pembayaran berhasil diperbarui.');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return redirect()->route('payments.index')->with('success', 'Pembayaran berhasil dihapus.');
    }
}
