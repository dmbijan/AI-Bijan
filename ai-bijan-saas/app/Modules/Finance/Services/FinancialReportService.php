<?php

namespace App\Modules\Finance\Services;

use Illuminate\Support\Facades\DB;
use App\Modules\Finance\Models\JournalEntryLine;
use App\Modules\Finance\Models\ChartOfAccount;

class FinancialReportService
{
    /**
     * Generate Profit & Loss (P&L) Statement.
     * 
     * Format:
     * - Revenue (Pendapatan)
     * - Cost of Goods Sold (Harga Pokok Jualan)
     * = Gross Profit (Untung Kasar)
     * - Operating Expenses (Perbelanjaan Operasi)
     * = Net Profit Before Tax (Untung Bersih Sebelum Cukai)
     * - Tax Expense (Cukai Pendapatan)
     * = Net Profit After Tax (Untung Bersih Selepas Cukai)
     */
    public function generateProfitLoss(string $startDate, string $endDate): array
    {
        // Get all posted journal lines in period
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
                'chart_of_accounts.subtype',
                DB::raw('SUM(journal_entry_lines.debit - journal_entry_lines.credit) as net_amount')
            )
            ->groupBy('chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_accounts.type', 'chart_of_accounts.subtype')
            ->orderBy('chart_of_accounts.sort_order')
            ->orderBy('chart_of_accounts.code');
        
        $accounts = $query->get()->groupBy('type');
        
        // Calculate sections
        $revenue = $this->calculateSection($accounts['revenue'] ?? collect());
        $costOfGoods = $this->calculateSection($accounts['cost_of_goods'] ?? collect());
        $grossProfit = $revenue['total'] - $costOfGoods['total'];
        $operatingExpenses = $this->calculateSection($accounts['expense'] ?? collect());
        $netProfitBeforeTax = $grossProfit - $operatingExpenses['total'];
        $taxExpense = $this->calculateSection($accounts['tax_expense'] ?? collect());
        $netProfitAfterTax = $netProfitBeforeTax - $taxExpense['total'];
        
        return [
            'report_type' => 'profit_loss',
            'title' => 'Profit & Loss Statement / Penyata Untung Rugi',
            'period_start' => $startDate,
            'period_end' => $endDate,
            'sections' => [
                'revenue' => [
                    'title' => 'Revenue / Pendapatan',
                    'accounts' => $revenue['accounts'],
                    'total' => round($revenue['total'], 2),
                ],
                'cost_of_goods' => [
                    'title' => 'Cost of Goods Sold / Harga Pokok Jualan',
                    'accounts' => $costOfGoods['accounts'],
                    'total' => round($costOfGoods['total'], 2),
                ],
                'gross_profit' => [
                    'title' => 'Gross Profit / Untung Kasar',
                    'amount' => round($grossProfit, 2),
                ],
                'operating_expenses' => [
                    'title' => 'Operating Expenses / Perbelanjaan Operasi',
                    'accounts' => $operatingExpenses['accounts'],
                    'total' => round($operatingExpenses['total'], 2),
                ],
                'net_profit_before_tax' => [
                    'title' => 'Net Profit Before Tax / Untung Bersih Sebelum Cukai',
                    'amount' => round($netProfitBeforeTax, 2),
                ],
                'tax_expense' => [
                    'title' => 'Tax Expense / Cukai Pendapatan',
                    'accounts' => $taxExpense['accounts'],
                    'total' => round($taxExpense['total'], 2),
                ],
                'net_profit_after_tax' => [
                    'title' => 'Net Profit After Tax / Untung Bersih Selepas Cukai',
                    'amount' => round($netProfitAfterTax, 2),
                ],
            ],
        ];
    }

    /**
     * Generate Balance Sheet.
     * 
     * Format:
     * - Assets (Aset) = Current + Non-Current
     * - Liabilities (Kewajipan) = Current + Non-Current
     * - Equity (Ekuiti)
     * = Total Assets - Total Liabilities - Total Equity (should be 0 if balanced)
     */
    public function generateBalanceSheet(string $asOfDate): array
    {
        // For balance sheet, we need cumulative totals from beginning of time to date
        $query = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('chart_of_accounts', 'journal_entry_lines.account_id', '=', 'chart_of_accounts.id')
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_date', '<=', $asOfDate)
            ->select(
                'chart_of_accounts.code',
                'chart_of_accounts.name',
                'chart_of_accounts.type',
                'chart_of_accounts.subtype',
                DB::raw('SUM(journal_entry_lines.debit - journal_entry_lines.credit) as net_amount')
            )
            ->groupBy('chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_accounts.type', 'chart_of_accounts.subtype')
            ->orderBy('chart_of_accounts.sort_order')
            ->orderBy('chart_of_accounts.code');
        
        $accounts = $query->get()->groupBy('type');
        
        // Calculate sections
        $currentAssets = $this->calculateSectionBySubtype($accounts['asset'] ?? collect(), ['current_asset']);
        $nonCurrentAssets = $this->calculateSectionBySubtype($accounts['asset'] ?? collect(), ['non_current_asset', 'fixed_assets']);
        $totalAssets = $currentAssets['total'] + $nonCurrentAssets['total'];
        
        $currentLiabilities = $this->calculateSectionBySubtype($accounts['liability'] ?? collect(), ['current_liability']);
        $nonCurrentLiabilities = $this->calculateSectionBySubtype($accounts['liability'] ?? collect(), ['non_current_liability']);
        $totalLiabilities = $currentLiabilities['total'] + $nonCurrentLiabilities['total'];
        
        $equity = $this->calculateSection($accounts['equity'] ?? collect());
        
        // Retained earnings = net profit from P&L
        $retainedEarnings = $this->calculateRetainedEarnings($asOfDate);
        
        return [
            'report_type' => 'balance_sheet',
            'title' => 'Balance Sheet / Neraca Kewangan',
            'as_of_date' => $asOfDate,
            'sections' => [
                'assets' => [
                    'title' => 'Assets / Aset',
                    'subsections' => [
                        'current_assets' => [
                            'title' => 'Current Assets / Aset Semasa',
                            'accounts' => $currentAssets['accounts'],
                            'total' => round($currentAssets['total'], 2),
                        ],
                        'non_current_assets' => [
                            'title' => 'Non-Current Assets / Aset Bukan Semasa',
                            'accounts' => $nonCurrentAssets['accounts'],
                            'total' => round($nonCurrentAssets['total'], 2),
                        ],
                    ],
                    'total' => round($totalAssets, 2),
                ],
                'liabilities' => [
                    'title' => 'Liabilities / Kewajipan',
                    'subsections' => [
                        'current_liabilities' => [
                            'title' => 'Current Liabilities / Kewajipan Semasa',
                            'accounts' => $currentLiabilities['accounts'],
                            'total' => round($currentLiabilities['total'], 2),
                        ],
                        'non_current_liabilities' => [
                            'title' => 'Non-Current Liabilities / Kewajipan Bukan Semasa',
                            'accounts' => $nonCurrentLiabilities['accounts'],
                            'total' => round($nonCurrentLiabilities['total'], 2),
                        ],
                    ],
                    'total' => round($totalLiabilities, 2),
                ],
                'equity' => [
                    'title' => 'Equity / Ekuiti',
                    'accounts' => $equity['accounts'],
                    'total' => round($equity['total'], 2),
                ],
                'retained_earnings' => [
                    'title' => 'Retained Earnings / Untung Ditahan',
                    'amount' => round($retainedEarnings, 2),
                ],
            ],
            'totals' => [
                'total_assets' => round($totalAssets, 2),
                'total_liabilities_and_equity' => round($totalLiabilities + $equity['total'] + $retainedEarnings, 2),
                'difference' => round($totalAssets - ($totalLiabilities + $equity['total'] + $retainedEarnings), 2),
            ],
        ];
    }

    /**
     * Calculate section total and accounts.
     */
    protected function calculateSection(\Illuminate\Support\Collection $accounts): array
    {
        $result = [];
        $total = 0;
        
        foreach ($accounts as $account) {
            // For expense/revenue accounts, debit is positive, credit is negative
            // For asset/liability accounts, normal balance direction matters
            $amount = $account->net_amount;
            
            if (abs($amount) > 0.01) {
                $result[] = [
                    'code' => $account->code,
                    'name' => $account->name,
                    'amount' => round($amount, 2),
                ];
                $total += $amount;
            }
        }
        
        return ['accounts' => $result, 'total' => $total];
    }

    /**
     * Calculate section by subtype filter.
     */
    protected function calculateSectionBySubtype(\Illuminate\Support\Collection $accounts, array $subtypes): array
    {
        $filtered = $accounts->filter(fn($a) => in_array($a->subtype, $subtypes));
        return $this->calculateSection($filtered);
    }

    /**
     * Calculate retained earnings up to a date.
     * Sum of all net profit from previous periods.
     */
    protected function calculateRetainedEarnings(string $asOfDate): float
    {
        // Simple approach: sum all revenue and expense accounts up to date
        $query = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('chart_of_accounts', 'journal_entry_lines.account_id', '=', 'chart_of_accounts.id')
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_date', '<=', $asOfDate)
            ->whereIn('chart_of_accounts.type', ['revenue', 'expense'])
            ->select(
                DB::raw('SUM(journal_entry_lines.debit - journal_entry_lines.credit) as net_income')
            );
        
        $result = $query->first();
        return $result ? (float) $result->net_income : 0;
    }

    /**
     * Generate General Ledger report for an account.
     */
    public function getGeneralLedger(int $accountId, string $startDate, string $endDate): array
    {
        $account = ChartOfAccount::findOrFail($accountId);
        
        $entries = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entry_lines.account_id', $accountId)
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_date', '>=', $startDate)
            ->where('journal_entries.entry_date', '<=', $endDate)
            ->select(
                'journal_entries.entry_number',
                'journal_entries.entry_date',
                'journal_entries.reference_number',
                'journal_entries.description',
                'journal_entry_lines.debit',
                'journal_entry_lines.credit',
                'journal_entry_lines.description as line_description'
            )
            ->orderBy('journal_entries.entry_date')
            ->orderBy('journal_entries.id')
            ->get();
        
        $runningBalance = 0;
        $transactions = [];
        
        foreach ($entries as $entry) {
            $runningBalance += $entry->debit - $entry->credit;
            $transactions[] = [
                'entry_number' => $entry->entry_number,
                'date' => $entry->entry_date,
                'reference' => $entry->reference_number,
                'description' => $entry->description,
                'line_description' => $entry->line_description,
                'debit' => round($entry->debit, 2),
                'credit' => round($entry->credit, 2),
                'balance' => round($runningBalance, 2),
            ];
        }
        
        return [
            'account' => [
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
            ],
            'period_start' => $startDate,
            'period_end' => $endDate,
            'opening_balance' => 0, // Would need to calculate from prior periods
            'transactions' => $transactions,
            'closing_balance' => round($runningBalance, 2),
        ];
    }
}
