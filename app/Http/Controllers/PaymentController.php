<?php

namespace App\Http\Controllers;

use App\Services\WebHookService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected WebHookService $paymentService) {}

    public function index() {}

    public function show($id) {}

    public function store(Request $request) {}

    public function update(Request $request, $id) {}

    public function destroy($id) {}

    public function KoraPayWebhook(Request $request)
    {
        return $this->paymentService->handleKorapayWebhook($request);
    }
}
