<?php

namespace App\Services;

use Carbon\Carbon;

class ReconciliationService
{
    public function reconcile(array $bankRecord, array $ledgerRecord): array
    {
        $amountDiff = round(abs($bankRecord['amount'] - $ledgerRecord['amount']), 2);
        $daysDiff = abs(Carbon::parse($bankRecord['date'])->diffInDays(Carbon::parse($ledgerRecord['date'])));
        $textSim = $this->calculateTextSimilarity($bankRecord['description'], $ledgerRecord['description']);
        
        $refMatch = str_contains(strtolower($ledgerRecord['reference']), strtolower($bankRecord['reference'])) ||
                    str_contains(strtolower($bankRecord['reference']), strtolower($ledgerRecord['reference']));

        $exactAmount = ($amountDiff === 0.0);
        $exactRef = $refMatch && strlen($bankRecord['reference']) > 3;

        if ($exactAmount && $exactRef && $daysDiff <= 2) {
            return [
                'match_score' => 100.0,
                'status' => 'AUTOMATIC_MATCH',
                'is_deterministic_exact' => true,
                'amount_diff' => 0.0,
                'days_diff' => $daysDiff,
                'text_similarity' => round($textSim * 100, 1),
                'explanation' => 'Exact match confirmed: Matching transaction reference and identical amounts within 48-hour posting window.'
            ];
        }

        $amountScore = $exactAmount ? 100.0 : max(0.0, (1.0 - sqrt($amountDiff / max(abs($bankRecord['amount']), 1.0))) * 100);
        $textScore = $textSim * 100.0;
        $dateScore = max(0.0, (1.0 - ($daysDiff / 7.0)) * 100.0);

        $totalScore = ($amountScore * 0.50) + ($textScore * 0.30) + ($dateScore * 0.20);
        if ($refMatch) {
            $totalScore = min(100.0, $totalScore + 10.0);
        }

        $finalScore = round($totalScore, 1);

        if ($finalScore >= 90.0) {
            $status = 'AUTOMATIC_MATCH';
            $explanation = "High confidence match ({$finalScore}%). Amounts match with " . round($textSim * 100) . "% description similarity.";
        } elseif ($finalScore >= 60.0) {
            $status = 'NEEDS_HUMAN_REVIEW';
            $explanation = $amountDiff > 0 
                ? sprintf("Flagged for human review: Amount variance of $%.2f detected between bank and ledger records.", $amountDiff)
                : "Flagged for human review: Amount matches, but text description confidence is low (" . round($textSim * 100) . "%).";
        } else {
            $status = 'NO_MATCH';
            $explanation = 'Transaction pair rejected: Insufficient correlation across amount, reference, and transaction timing.';
        }

        return [
            'match_score' => $finalScore,
            'status' => $status,
            'is_deterministic_exact' => false,
            'amount_diff' => $amountDiff,
            'days_diff' => $daysDiff,
            'text_similarity' => round($textSim * 100, 1),
            'explanation' => $explanation
        ];
    }

    private function calculateTextSimilarity(string $str1, string $str2): float
    {
        similar_text(strtolower(trim($str1)), strtolower(trim($str2)), $percent);
        return $percent / 100;
    }
}