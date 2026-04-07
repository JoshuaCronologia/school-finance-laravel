<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SSO Roles
    |--------------------------------------------------------------------------
    */
    'roles' => [
        'admin',
        'manager',
        'user',
    ],

    /*
    |--------------------------------------------------------------------------
    | SSO Permissions
    |--------------------------------------------------------------------------
    | Assignable to BranchUser records by admin.
    | Maps to sidebar modules via permission_map below.
    */
    'permissions' => [
        'budget',               // Budget Management (dashboard, planning, allocation)
        'accounts payable',     // AP (bills, disbursements, vendors, payments)
        'accounts receivable',  // AR (invoices, collections, customers, aging)
        'general ledger',       // GL (chart of accounts, journal entries, bank recon)
        'reports',              // All reports (trial balance, income statement, etc.)
        'tax',                  // Tax & Compliance (BIR forms)
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Mapping: SSO -> Sidebar
    |--------------------------------------------------------------------------
    | Maps SSO permissions to Spatie permission names used in sidebar @can.
    */
    'permission_map' => [
        'budget' => [
            'budget.view', 'budget.create', 'budget.edit', 'budget.approve',
        ],
        'accounts payable' => [
            'bill.view', 'bill.create', 'bill.approve', 'bill.post',
            'disbursement.view', 'disbursement.create', 'disbursement.approve', 'disbursement.pay',
        ],
        'accounts receivable' => [
            'invoice.view', 'invoice.create',
            'collection.view', 'collection.create',
        ],
        'general ledger' => [
            'je.view', 'je.create', 'je.post', 'je.reverse',
            'period.close',
        ],
        'reports' => [
            'report.view', 'report.export',
        ],
        'tax' => [
            'report.view', 'report.export',
        ],
    ],

];
