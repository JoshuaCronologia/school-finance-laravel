<?php

namespace App\Observers;

use App\Models\FeeAccountMapping;

class FeeAccountMappingObserver
{
    private function clearCache(): void
    {
        cache()->forget('fee_account_mappings_view');
        cache()->forget('fee_mappings_finance_fees');
        cache()->forget('fee_mappings_accounts');
        cache()->forget('fee_mappings_data');
    }

    public function created(FeeAccountMapping $mapping): void { $this->clearCache(); }
    public function updated(FeeAccountMapping $mapping): void { $this->clearCache(); }
    public function deleted(FeeAccountMapping $mapping): void { $this->clearCache(); }
}
