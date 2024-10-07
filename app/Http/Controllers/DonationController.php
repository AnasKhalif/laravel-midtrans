<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Donation;

class DonationController extends Controller
{

    public function __construct()
    {
        \Midtrans\Config::$serverKey = config('services.midtrans.serverKey');
        \Midtrans\Config::$isProduction = config('services.midtrans.isProduction');
        \Midtrans\Config::$isSanitized = config('services.midtrans.isSanitized');
        \Midtrans\Config::$is3ds = config('services.midtrans.is3ds');
    }

    public function index()
    {
        $donations = Donation::orderBy('id', 'desc')->paginate(8);
        return view('welcome', compact('donations'));
    }

    public function create()
    {
        return view('donations');
    }

    public function store(Request $request)
    {
        \DB::transaction(function () use ($request) {
            $donation = Donation::create([
                'donation_code' => 'SANDBOX-' . uniqid(),
                'donor_name' => $request->donor_name,
                'donor_email' => $request->donor_email,
                'donation_type' => $request->donation_type,
                'amount' => floatval($request->amount),
                'note' => $request->note,
            ]);
            $payload = [
                'transaction_details' => [
                    'order_id' => $donation->donation_code,
                    'gross_amount' => $donation->amount,
                ],
                'customer_details' => [
                    'first_name' => $donation->donor_name,
                    'email' => $donation->donor_email,
                ],
                'item_details' => [
                    [
                        'id' => $donation->donation_type,
                        'price' => $donation->amount,
                        'quantity' => 1,
                        'name' => ucwords(str_replace('_', ' ', $donation->donation_type)),
                    ],
                ],
            ];
            $snapToken = \Midtrans\Snap::getSnapToken($payload);
            $donation->snap_token = $snapToken;
            $donation->save();

            $this->response['snap_token'] = $snapToken;
        });
        return response()->json($this->response);
    }

    public function notification()
    {
        $notification = new \Midtrans\Notification();
        \DB::transaction(function () use ($notification) {
            $transactionStatus = $notification->transaction_status;
            $paymentType = $notification->payment_type;
            $orderId = $notification->order_id;
            $fraudStatus = $notification->fraud_status;
            $donation = Donation::where('donation_code', $orderId)->first();

            if ($transactionStatus == 'capture') {
                if ($paymentType == 'credit_card') {
                    if ($fraudStatus == 'challenge') {
                        $donation->setStatusPending();
                    } else {
                        $donation->setStatusSuccess();
                    }
                }
            } else if ($transactionStatus == 'settlement') {
                $donation->setStatusSuccess();
            } else if ($transactionStatus == 'deny') {
                $donation->setStatusFailed();
            } else if ($transactionStatus == 'pending') {
                $donation->setStatusPending();
            } else if ($transactionStatus == 'expire') {
                $donation->setStatusExpired();
            } else if ($transactionStatus == 'cancel') {
                $donation->setStatusFailed();
            }
        });
        return;
    }
}
