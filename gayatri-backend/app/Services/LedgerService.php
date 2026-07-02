<?php

namespace App\Services;

use App\Models\Client;
use App\Models\LedgerEntry;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Client ledger (B2B khata). `clients.outstanding_balance` is always the
 * latest ledger_entries.balance_after for that client — this service is the
 * only place allowed to write either. Sign convention: invoice/debit_note
 * increase what the client owes, payment/credit_note reduce it. Callers
 * always pass a positive magnitude; the sign is applied here so it can't be
 * gotten backwards at the call site.
 */
class LedgerService
{
    private const INCREASES = ['invoice', 'debit_note'];
    private const DECREASES = ['payment', 'credit_note'];

    public function post(Client $client, string $type, float $amount, ?string $refType = null, ?int $refId = null): LedgerEntry
    {
        if (! in_array($type, [...self::INCREASES, ...self::DECREASES], true)) {
            throw new InvalidArgumentException("Unknown ledger entry type: {$type}");
        }

        return DB::transaction(function () use ($client, $type, $amount, $refType, $refId) {
            $locked = Client::where('id', $client->id)->lockForUpdate()->first();

            $signed = in_array($type, self::INCREASES, true) ? $amount : -$amount;
            $balanceAfter = (float) $locked->outstanding_balance + $signed;

            $entry = LedgerEntry::create([
                'client_id' => $locked->id,
                'type' => $type,
                'amount_signed' => $signed,
                'ref_type' => $refType,
                'ref_id' => $refId,
                'balance_after' => $balanceAfter,
            ]);

            $locked->update(['outstanding_balance' => $balanceAfter]);
            $client->outstanding_balance = $balanceAfter;

            return $entry;
        });
    }
}
