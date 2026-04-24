<?php

namespace App\Observers;

use App\Models\FeeAccountMapping;

class FeeAccountMappingObserver
{
    public function created(FeeAccountMapping $mapping): void
    {
        cache()->forget('fee_account_mappings_view');
    }

    public function updated(FeeAccountMapping $mapping): void
    {
        cache()->forget('fee_account_mappings_view');
    }

    public function deleted(FeeAccountMapping $mapping): void
    {
        cache()->forget('fee_account_mappings_view');
    }
}
