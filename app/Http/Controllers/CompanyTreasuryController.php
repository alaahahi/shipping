<?php

namespace App\Http\Controllers;

use App\Models\CompanyTreasuryEntry;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;

class CompanyTreasuryController extends Controller
{
    protected ?int $userCarContract = null;

    public function __construct()
    {
        $this->userCarContract = UserType::where('name', 'car_contract')->value('id');
    }

    protected function authorizeTreasury(): void
    {
        $user = Auth::user();
        if (!$user || ($this->userCarContract && (int) $user->type_id !== (int) $this->userCarContract)) {
            abort(403, 'غير مسموح');
        }
    }

    public function index()
    {
        $this->authorizeTreasury();

        return Inertia::render('CompanyTreasury/Index');
    }

    public function getEntries(Request $request)
    {
        $this->authorizeTreasury();

        $ownerId = Auth::user()->owner_id;
        $currency = $request->get('currency', '$');
        $from = $request->get('from');
        $to = $request->get('to');

        $query = CompanyTreasuryEntry::where('owner_id', $ownerId)
            ->where('currency', $currency)
            ->orderBy('entry_date', 'asc')
            ->orderBy('id', 'asc');

        if ($from && $to) {
            $query->whereBetween('entry_date', [$from, $to]);
        }

        $entries = $query->get();

        return Response::json([
            'entries' => $entries,
            'balance' => $this->getLastBalance($ownerId, $currency),
            'total_debit' => (float) $entries->sum('debit'),
            'total_credit' => (float) $entries->sum('credit'),
        ], 200);
    }

    public function getSummary()
    {
        $this->authorizeTreasury();

        $ownerId = Auth::user()->owner_id;

        return Response::json([
            'balance_usd' => $this->getLastBalance($ownerId, '$'),
            'balance_iqd' => $this->getLastBalance($ownerId, 'IQD'),
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorizeTreasury();

        $validated = $request->validate([
            'entry_type' => 'required|in:deposit,withdraw',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|in:$,IQD',
            'entry_date' => 'required|date',
            'description' => 'nullable|string|max:500',
        ]);

        $ownerId = Auth::user()->owner_id;
        $amount = (float) $validated['amount'];
        $currency = $validated['currency'];
        $isDeposit = $validated['entry_type'] === 'deposit';

        $entry = CompanyTreasuryEntry::create([
            'owner_id' => $ownerId,
            'user_id' => Auth::id(),
            'entry_date' => $validated['entry_date'],
            'description' => $validated['description'] ?? ($isDeposit ? 'إيداع' : 'سحب'),
            'currency' => $currency,
            'debit' => $isDeposit ? $amount : 0,
            'credit' => $isDeposit ? 0 : $amount,
            'balance' => 0,
        ]);

        $this->recalculateBalances($ownerId, $currency);

        if ($this->getLastBalance($ownerId, $currency) < 0) {
            $entry->delete();
            $this->recalculateBalances($ownerId, $currency);

            return Response::json(['message' => 'الرصيد غير كافٍ لإتمام السحب'], 422);
        }

        $entry->refresh();

        return Response::json($entry, 201);
    }

    public function destroy(Request $request)
    {
        $this->authorizeTreasury();

        $ownerId = Auth::user()->owner_id;
        $entry = CompanyTreasuryEntry::where('owner_id', $ownerId)->findOrFail($request->id);
        $currency = $entry->currency;
        $entry->delete();
        $this->recalculateBalances($ownerId, $currency);

        return Response::json(['message' => 'ok'], 200);
    }

    protected function getLastBalance(int $ownerId, string $currency): float
    {
        $last = CompanyTreasuryEntry::where('owner_id', $ownerId)
            ->where('currency', $currency)
            ->orderBy('entry_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return $last ? (float) $last->balance : 0.0;
    }

    protected function recalculateBalances(int $ownerId, string $currency): void
    {
        $entries = CompanyTreasuryEntry::where('owner_id', $ownerId)
            ->where('currency', $currency)
            ->orderBy('entry_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $balance = 0.0;

        foreach ($entries as $entry) {
            $balance += (float) $entry->debit - (float) $entry->credit;
            $entry->balance = round($balance, 2);
            $entry->saveQuietly();
        }
    }
}
