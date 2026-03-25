<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bank Reconciliation - <?php echo e($account->account_code); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1a1a1a; padding: 30px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { font-size: 16px; font-weight: bold; text-transform: uppercase; }
        .header h2 { font-size: 12px; font-weight: normal; color: #555; margin-top: 3px; }
        .header .date { font-size: 10px; color: #777; margin-top: 2px; }
        .two-col { width: 100%; margin-bottom: 20px; }
        .two-col td { width: 50%; vertical-align: top; padding: 0 8px; }
        .section { border: 1px solid #ccc; margin-bottom: 15px; }
        .section-header { background: #2c3e50; color: #fff; padding: 6px 10px; font-size: 11px; font-weight: bold; }
        .section-header.bank { background: #2980b9; }
        .section-header.book { background: #27ae60; }
        .section-body { padding: 10px; }
        .line-item { display: flex; justify-content: space-between; padding: 3px 0; font-size: 10px; }
        table.items { width: 100%; border-collapse: collapse; font-size: 9px; }
        table.items th { background: #ecf0f1; padding: 4px 6px; text-align: left; font-size: 8px; text-transform: uppercase; border-bottom: 1px solid #bbb; }
        table.items td { padding: 3px 6px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .indent { padding-left: 15px; }
        .total-line { border-top: 2px solid #333; padding-top: 5px; margin-top: 5px; font-weight: bold; font-size: 11px; }
        .sub-line { border-top: 1px dashed #ccc; padding-top: 3px; margin-top: 3px; }
        .result-box { text-align: center; padding: 10px; margin: 15px 0; font-size: 12px; font-weight: bold; border: 2px solid; }
        .result-balanced { border-color: #27ae60; color: #27ae60; background: #eafaf1; }
        .result-unbalanced { border-color: #c0392b; color: #c0392b; background: #fdedec; }
        .negative { color: #c0392b; }
        .signature-section { margin-top: 40px; }
        .signature-section table { width: 100%; border: none; }
        .signature-section td { border: none; padding: 5px 20px; text-align: center; vertical-align: bottom; width: 33.33%; }
        .signature-line { border-top: 1px solid #333; margin-top: 35px; padding-top: 4px; font-size: 9px; color: #555; }
        .footer { margin-top: 20px; font-size: 8px; color: #888; text-align: center; border-top: 1px solid #ccc; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Bank Reconciliation Statement</h1>
        <h2><?php echo e($account->account_code); ?> - <?php echo e($account->account_name); ?></h2>
        <div class="date">As of <?php echo e(\Carbon\Carbon::parse($asOfDate)->format('F d, Y')); ?></div>
    </div>

    <table class="two-col" cellspacing="0" cellpadding="0">
        <tr>
            
            <td>
                <div class="section">
                    <div class="section-header bank">PER BANK STATEMENT</div>
                    <div class="section-body">
                        <table style="width:100%;">
                            <tr>
                                <td style="font-size:10px;">Bank Statement Balance</td>
                                <td style="text-align:right; font-weight:bold; font-size:11px;">₱<?php echo e(number_format($statementBalance, 2)); ?></td>
                            </tr>
                        </table>

                        <div style="margin-top:10px; font-size:9px; font-weight:bold; color:#555;">ADD: Deposits in Transit</div>
                        <?php $__empty_1 = true; $__currentLoopData = $depositsInTransit; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <table style="width:100%;">
                            <tr>
                                <td class="indent" style="font-size:9px;"><?php echo e(\Carbon\Carbon::parse($dep->posting_date)->format('m/d')); ?> <?php echo e($dep->entry_number); ?></td>
                                <td style="text-align:right; font-size:9px;">₱<?php echo e(number_format($dep->debit, 2)); ?></td>
                            </tr>
                        </table>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="indent" style="font-size:9px; color:#999;">None</div>
                        <?php endif; ?>
                        <table style="width:100%;"><tr>
                            <td class="indent sub-line" style="font-size:9px; font-weight:bold;">Total</td>
                            <td class="sub-line" style="text-align:right; font-size:9px; font-weight:bold;">₱<?php echo e(number_format($totalDepositsTransit, 2)); ?></td>
                        </tr></table>

                        <div style="margin-top:10px; font-size:9px; font-weight:bold; color:#555;">LESS: Outstanding Checks</div>
                        <?php $__empty_1 = true; $__currentLoopData = $outstandingChecks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <table style="width:100%;">
                            <tr>
                                <td class="indent" style="font-size:9px;"><?php echo e(\Carbon\Carbon::parse($chk->posting_date)->format('m/d')); ?> <?php echo e($chk->entry_number); ?> <?php echo e($chk->reference_number ? '('.$chk->reference_number.')' : ''); ?></td>
                                <td style="text-align:right; font-size:9px;" class="negative">(₱<?php echo e(number_format($chk->credit, 2)); ?>)</td>
                            </tr>
                        </table>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="indent" style="font-size:9px; color:#999;">None</div>
                        <?php endif; ?>
                        <table style="width:100%;"><tr>
                            <td class="indent sub-line" style="font-size:9px; font-weight:bold;">Total</td>
                            <td class="sub-line" style="text-align:right; font-size:9px; font-weight:bold;" class="negative">(₱<?php echo e(number_format($totalOutstandingChecks, 2)); ?>)</td>
                        </tr></table>

                        <table style="width:100%;"><tr>
                            <td class="total-line" style="font-size:11px;">Adjusted Bank Balance</td>
                            <td class="total-line" style="text-align:right; font-size:11px;">₱<?php echo e(number_format($adjustedBankBalance, 2)); ?></td>
                        </tr></table>
                    </div>
                </div>
            </td>

            
            <td>
                <div class="section">
                    <div class="section-header book">PER BOOKS</div>
                    <div class="section-body">
                        <table style="width:100%;">
                            <tr>
                                <td style="font-size:10px;">Balance per General Ledger</td>
                                <td style="text-align:right; font-weight:bold; font-size:11px;">₱<?php echo e(number_format($bookBalance, 2)); ?></td>
                            </tr>
                        </table>

                        <div style="margin-top:10px; font-size:9px; font-weight:bold; color:#555;">ADD: Bank Credits (not in books)</div>
                        <div class="indent" style="font-size:9px; color:#999;">Interest earned, direct deposits</div>
                        <table style="width:100%;"><tr>
                            <td class="indent sub-line" style="font-size:9px; font-weight:bold;">Total</td>
                            <td class="sub-line" style="text-align:right; font-size:9px; font-weight:bold;">₱0.00</td>
                        </tr></table>

                        <div style="margin-top:10px; font-size:9px; font-weight:bold; color:#555;">LESS: Bank Debits (not in books)</div>
                        <div class="indent" style="font-size:9px; color:#999;">Bank charges, NSF checks</div>
                        <table style="width:100%;"><tr>
                            <td class="indent sub-line" style="font-size:9px; font-weight:bold;">Total</td>
                            <td class="sub-line" style="text-align:right; font-size:9px; font-weight:bold;">(₱0.00)</td>
                        </tr></table>

                        <table style="width:100%;"><tr>
                            <td class="total-line" style="font-size:11px;">Adjusted Book Balance</td>
                            <td class="total-line" style="text-align:right; font-size:11px;">₱<?php echo e(number_format($bookBalance, 2)); ?></td>
                        </tr></table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    
    <?php if(abs($difference) < 0.01): ?>
        <div class="result-box result-balanced">RECONCILED - Balances Match</div>
    <?php else: ?>
        <div class="result-box result-unbalanced">UNRECONCILED - Difference: ₱<?php echo e(number_format(abs($difference), 2)); ?></div>
    <?php endif; ?>

    
    <div class="signature-section">
        <table>
            <tr>
                <td><div class="signature-line">Prepared by</div></td>
                <td><div class="signature-line">Reviewed by</div></td>
                <td><div class="signature-line">Noted by</div></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        School Finance ERP &mdash; Bank Reconciliation Statement &mdash; Printed on <?php echo e($printedAt); ?>

    </div>
</body>
</html>
<?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/gl/pdf-bank-reconciliation.blade.php ENDPATH**/ ?>