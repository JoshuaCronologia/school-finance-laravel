<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Journal Voucher - <?php echo e($entry->entry_number); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1a1a1a; padding: 30px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 18px; font-weight: bold; margin-bottom: 2px; text-transform: uppercase; }
        .header h2 { font-size: 14px; font-weight: normal; color: #555; border-bottom: 2px solid #333; padding-bottom: 8px; }
        .meta-table { width: 100%; margin-bottom: 15px; border: none; }
        .meta-table td { padding: 3px 8px; font-size: 10px; border: none; vertical-align: top; }
        .meta-label { font-weight: bold; color: #555; width: 120px; }
        .meta-value { color: #1a1a1a; }
        table.lines { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.lines th { background-color: #2c3e50; color: #fff; padding: 6px 8px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.3px; }
        table.lines td { border: 1px solid #ddd; padding: 5px 8px; font-size: 10px; }
        table.lines tfoot td { background-color: #f0f4f8; font-weight: bold; border-top: 2px solid #333; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .description-box { margin-bottom: 15px; padding: 8px 10px; border: 1px solid #ddd; background: #f9fafb; font-size: 10px; }
        .description-box .label { font-weight: bold; color: #555; }
        .signature-section { margin-top: 40px; }
        .signature-section table { width: 100%; border: none; }
        .signature-section td { border: none; padding: 5px 15px; text-align: center; vertical-align: bottom; }
        .signature-line { border-top: 1px solid #333; margin-top: 30px; padding-top: 4px; font-size: 9px; color: #555; }
        .footer { margin-top: 20px; font-size: 8px; color: #888; text-align: center; border-top: 1px solid #ddd; padding-top: 5px; }
        .status-badge { display: inline-block; padding: 2px 10px; font-size: 9px; font-weight: bold; text-transform: uppercase; border-radius: 3px; }
        .status-posted { background: #d4edda; color: #155724; }
        .status-draft { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Journal Voucher</h1>
        <h2><?php echo e($entry->entry_number); ?></h2>
    </div>

    
    <table class="meta-table">
        <tr>
            <td class="meta-label">JV Number:</td>
            <td class="meta-value"><?php echo e($entry->entry_number); ?></td>
            <td class="meta-label">Entry Date:</td>
            <td class="meta-value"><?php echo e($entry->entry_date->format('F d, Y')); ?></td>
        </tr>
        <tr>
            <td class="meta-label">Journal Type:</td>
            <td class="meta-value"><?php echo e(ucfirst($entry->journal_type)); ?></td>
            <td class="meta-label">Posting Date:</td>
            <td class="meta-value"><?php echo e($entry->posting_date ? $entry->posting_date->format('F d, Y') : '-'); ?></td>
        </tr>
        <tr>
            <td class="meta-label">Reference #:</td>
            <td class="meta-value"><?php echo e($entry->reference_number ?: '-'); ?></td>
            <td class="meta-label">Status:</td>
            <td class="meta-value">
                <span class="status-badge <?php echo e($entry->status === 'posted' ? 'status-posted' : 'status-draft'); ?>">
                    <?php echo e(strtoupper($entry->status)); ?>

                </span>
            </td>
        </tr>
        <tr>
            <td class="meta-label">Department:</td>
            <td class="meta-value"><?php echo e($entry->department->name ?? '-'); ?></td>
            <td class="meta-label">Campus:</td>
            <td class="meta-value"><?php echo e($entry->campus->name ?? '-'); ?></td>
        </tr>
    </table>

    
    <?php if($entry->description): ?>
    <div class="description-box">
        <span class="label">Description:</span> <?php echo e($entry->description); ?>

    </div>
    <?php endif; ?>

    
    <table class="lines">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 12%;">Account Code</th>
                <th style="width: 25%;">Account Name</th>
                <th style="width: 25%;">Description</th>
                <th style="width: 10%;">Department</th>
                <th class="text-right" style="width: 11.5%;">Debit</th>
                <th class="text-right" style="width: 11.5%;">Credit</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $entry->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="text-center"><?php echo e($i + 1); ?></td>
                <td><?php echo e($line->account->account_code ?? '-'); ?></td>
                <td><?php echo e($line->account->account_name ?? '-'); ?></td>
                <td><?php echo e($line->description ?? '-'); ?></td>
                <td><?php echo e($line->department->name ?? '-'); ?></td>
                <td class="text-right"><?php echo e($line->debit > 0 ? '₱' . number_format($line->debit, 2) : '-'); ?></td>
                <td class="text-right"><?php echo e($line->credit > 0 ? '₱' . number_format($line->credit, 2) : '-'); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right">TOTAL</td>
                <td class="text-right"><?php echo e('₱' . number_format($totalDebit, 2)); ?></td>
                <td class="text-right"><?php echo e('₱' . number_format($totalCredit, 2)); ?></td>
            </tr>
        </tfoot>
    </table>

    
    <div class="signature-section">
        <table>
            <tr>
                <td>
                    <div class="signature-line">Prepared by</div>
                </td>
                <td>
                    <div class="signature-line">Checked/Reviewed by</div>
                </td>
                <td>
                    <div class="signature-line">Approved by</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        School Finance ERP &mdash; Journal Voucher &mdash; Printed on <?php echo e($printedAt); ?> by <?php echo e($printedBy); ?>

    </div>
</body>
</html>
<?php /**PATH C:\xampp73\htdocs\school-finance-laravel\resources\views/pages/gl/journal-entries/print-voucher.blade.php ENDPATH**/ ?>