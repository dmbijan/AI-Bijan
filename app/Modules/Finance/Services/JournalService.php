<?php

namespace App\Modules\Finance\Services;

use Illuminate\Support\Facades\DB;
use App\Modules\Finance\Models\JournalEntry;
use App\Modules\Finance\Models\JournalEntryLine;
use App\Modules\Finance\Models\ChartOfAccount;

class JournalService
{
    /**
     * Create a new journal entry with lines.
     * Ensures debits = credits (double-entry bookkeeping).
     * 
     * @param array $data {
     *     @type string $entry_date
     *     @type string $reference_number
     *     @type string $description
     *     @type string $source_module
     *     @type string $source_id
     *     @type array $lines [
     *         @type int $account_id
     *         @type float $debit
     *         @type float $credit
     *         @type string $description
     *     ]
     * }
     */
    public function createJournalEntry(array $data): JournalEntry
    {
        return DB::transaction(function () use ($data) {
            // Generate entry number
            $entryNumber = JournalEntry::generateEntryNumber();
            
            // Calculate fiscal year and period from entry date
            $entryDate = \Carbon\Carbon::parse($data['entry_date']);
            $fiscalYear = 'FY-' . $entryDate->year;
            $period = $entryDate->format('Y-m');
            
            // Create journal entry header
            $entry = JournalEntry::create([
                'tenant_id' => $data['tenant_id'],
                'entry_number' => $entryNumber,
                'entry_date' => $entryDate,
                'reference_number' => $data['reference_number'] ?? null,
                'description' => $data['description'] ?? null,
                'narration_bm' => $data['narration_bm'] ?? null,
                'fiscal_year' => $fiscalYear,
                'period' => $period,
                'source_module' => $data['source_module'] ?? null,
                'source_id' => $data['source_id'] ?? null,
                'status' => 'draft',
            ]);
            
            // Create journal entry lines
            $lineNumber = 1;
            foreach ($data['lines'] as $lineData) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $lineData['account_id'],
                    'debit' => $lineData['debit'] ?? 0,
                    'credit' => $lineData['credit'] ?? 0,
                    'description' => $lineData['description'] ?? null,
                    'currency' => $lineData['currency'] ?? 'MYR',
                    'exchange_rate' => $lineData['exchange_rate'] ?? 1,
                    'amount_foreign' => $lineData['amount_foreign'] ?? 0,
                    'cost_center' => $lineData['cost_center'] ?? null,
                    'project' => $lineData['project'] ?? null,
                    'line_number' => $lineNumber++,
                ]);
            }
            
            // Validate balanced entry
            if (! $entry->isBalanced()) {
                throw new \InvalidArgumentException(
                    "Journal entry must be balanced. Total Debits: {$entry->total_debits}, Total Credits: {$entry->total_credits}"
                );
            }
            
            // Refresh to get relationships
            return $entry->fresh()->load('lines.account');
        });
    }

    /**
     * Post a draft journal entry (change status to posted).
     * Re-posts if already posted (reverse + new entry).
     */
    public function postJournalEntry(JournalEntry $entry): JournalEntry
    {
        return DB::transaction(function () use ($entry) {
            if ($entry->status === 'posted') {
                // Reverse original entry
                $this->reverseJournalEntry($entry);
                
                // Create new entry
                $newEntry = $entry->replicate();
                $newEntry->entry_number = JournalEntry::generateEntryNumber();
                $newEntry->status = 'posted';
                $newEntry->save();
                
                foreach ($entry->lines as $line) {
                    $newLine = $line->replicate();
                    $newLine->journal_entry_id = $newEntry->id;
                    $newLine->is_posted = true;
                    $newLine->save();
                }
                
                return $newEntry->fresh()->load(['lines.account', 'approver']);
            }
            
            $entry->status = 'posted';
            $entry->approved_at = now();
            $entry->save();
            
            return $entry->fresh()->load(['lines.account', 'approver']);
        });
    }

    /**
     * Cancel a journal entry (soft cancel, don't delete).
     */
    public function cancelJournalEntry(JournalEntry $entry): void
    {
        DB::transaction(function () use ($entry) {
            if ($entry->status === 'posted') {
                $this->reverseJournalEntry($entry);
            }
            
            $entry->status = 'cancelled';
            $entry->save();
        });
    }

    /**
     * Reverse a posted journal entry (for corrections).
     * Creates opposite entries on current date.
     */
    protected function reverseJournalEntry(JournalEntry $originalEntry): void
    {
        $reversalEntry = JournalEntry::create([
            'tenant_id' => $originalEntry->tenant_id,
            'entry_number' => JournalEntry::generateEntryNumber(),
            'entry_date' => now(),
            'reference_number' => 'REV-' . $originalEntry->entry_number,
            'description' => 'Reversal of ' . $originalEntry->entry_number,
            'fiscal_year' => 'FY-' . now()->year,
            'period' => now()->format('Y-m'),
            'source_module' => 'finance',
            'source_id' => $originalEntry->id,
            'status' => 'posted',
        ]);
        
        foreach ($originalEntry->lines as $line) {
            JournalEntryLine::create([
                'journal_entry_id' => $reversalEntry->id,
                'account_id' => $line->account_id,
                'debit' => $line->credit,  // Swap debit/credit
                'credit' => $line->debit,
                'description' => 'Reversal: ' . ($line->description ?? ''),
                'currency' => $line->currency,
                'exchange_rate' => $line->exchange_rate,
                'amount_foreign' => $line->amount_foreign,
                'cost_center' => $line->cost_center,
                'project' => $line->project,
                'line_number' => $line->line_number,
            ]);
        }
    }

    /**
     * Get trial balance for a period.
     * Returns all accounts with debit/credit totals.
     */
    public function getTrialBalance(string $startDate, string $endDate): array
    {
        $query = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('chart_of_accounts', 'journal_entry_lines.account_id', '=', 'chart_of_accounts.id')
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_date', '>=', $startDate)
            ->where('journal_entries.entry_date', '<=', $endDate)
            ->select(
                'chart_of_accounts.code',
                'chart_of_accounts.name',
                'chart_of_accounts.type',
                DB::raw('SUM(journal_entry_lines.debit) as total_debit'),
                DB::raw('SUM(journal_entry_lines.credit) as total_credit')
            )
            ->groupBy('chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_accounts.type');
        
        $balances = $query->get();
        
        $totalDebits = 0;
        $totalCredits = 0;
        
        $results = [];
        foreach ($balances as $balance) {
            $totalDebits += $balance->total_debit;
            $totalCredits += $balance->total_credit;
            
            $results[] = [
                'code' => $balance->code,
                'name' => $balance->name,
                'type' => $balance->type,
                'debit' => round($balance->total_debit, 2),
                'credit' => round($balance->total_credit, 2),
                'balance' => $balance->total_debit - $balance->total_credit,
            ];
        }
        
        return [
            'entries' => $results,
            'total_debits' => round($totalDebits, 2),
            'total_credits' => round($totalCredits, 2),
            'balanced' => abs($totalDebits - $totalCredits) < 0.01,
            'period_start' => $startDate,
            'period_end' => $endDate,
        ];
    }

    /**
     * Auto-create journal entries from business events.
     * Called by other modules (Payroll, SCM, etc.)
     */
    public function autoJournalFromEvent(string $eventType, array $context): JournalEntry
    {
        return match ($eventType) {
            // Payroll events
            'payroll_run_completed' => $this->autoJournalPayroll($context),
            'overtime_approved' => $this->autoJournalOvertime($context),
            
            // SCM events
            'purchase_order_confirmed' => $this->autoJournalPurchaseOrder($context),
            'invoice_received' => $this->autoJournalInvoiceReceived($context),
            'payment_made' => $this->autoJournalPaymentMade($context),
            
            // Sales events
            'invoice_issued' => $this->autoJournalInvoiceIssued($context),
            'payment_received' => $this->autoJournalPaymentReceived($context),
            
            default => throw new \InvalidArgumentException("Unknown event type: {$eventType}")
        };
    }

    /**
     * Auto journal for payroll run completion.
     * Dr: Salary Expense, EPF Employee, PERKESO Employee, Tax
     * Cr: EPF Employer, PERKESO Employer, EIS, Bank (net pay)
     */
    protected function autoJournalPayroll(array $context): JournalEntry
    {
        // Implementation will pull from payroll run data
        // This is a skeleton - actual implementation depends on Payroll module data
        throw new \RuntimeException("Auto journal payroll not yet implemented");
    }

    /**
     * Auto journal for overtime approval.
     * Dr: Overtime Expense
     * Cr: Accrued Payroll Liability
     */
    protected function autoJournalOvertime(array $context): JournalEntry
    {
        throw new \RuntimeException("Auto journal overtime not yet implemented");
    }

    /**
     * Auto journal for purchase order confirmation.
     * Dr: Inventory/Purchase Expense
     * Cr: Accounts Payable
     */
    protected function autoJournalPurchaseOrder(array $context): JournalEntry
    {
        throw new \RuntimeException("Auto journal purchase order not yet implemented");
    }

    /**
     * Auto journal for invoice received from supplier.
     * Dr: Purchase/Expense
     * Cr: Accounts Payable
     */
    protected function autoJournalInvoiceReceived(array $context): JournalEntry
    {
        throw new \RuntimeException("Auto journal invoice received not yet implemented");
    }

    /**
     * Auto journal for payment made to supplier.
     * Dr: Accounts Payable
     * Cr: Bank/Cash
     */
    protected function autoJournalPaymentMade(array $context): JournalEntry
    {
        throw new \RuntimeException("Auto journal payment made not yet implemented");
    }

    /**
     * Auto journal for customer invoice issued.
     * Dr: Accounts Receivable
     * Cr: Revenue
     * Cr: SST (if applicable)
     */
    protected function autoJournalInvoiceIssued(array $context): JournalEntry
    {
        throw new \RuntimeException("Auto journal invoice issued not yet implemented");
    }

    /**
     * Auto journal for payment received from customer.
     * Dr: Bank/Cash
     * Cr: Accounts Receivable
     */
    protected function autoJournalPaymentReceived(array $context): JournalEntry
    {
        throw new \RuntimeException("Auto journal payment received not yet implemented");
    }
}
