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
    | These are permissions assignable to BranchUser records.
    | They control what SIS users can do in the Accounting system.
    */
    'permissions' => [
        'access rights',        // manage who has access to the system
        'setup',                // system configuration, ID printing setup, request setup
        'request',              // manage ID requests
        'announcement',         // create announcements
        'announcement history', // view past announcements
        'contacts',             // manage contacts
        'accounting',           // access accounting module
        'test',                 // test module
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Mapping: SSO -> Sidebar
    |--------------------------------------------------------------------------
    | Maps SSO permissions to the Spatie permission names used in sidebar @can.
    | When an SSO user has 'accounting', they also get 'budget.view', 'je.view', etc.
    */
    'permission_map' => [
        'accounting' => [
            'budget.view', 'budget.create', 'budget.edit',
            'disbursement.view', 'disbursement.create',
            'bill.view', 'bill.create',
            'invoice.view', 'invoice.create',
            'collection.view', 'collection.create',
            'je.view', 'je.create',
            'report.view', 'report.export',
        ],
        'setup' => [
            'settings.manage',
            'period.close',
        ],
        'access rights' => [
            'settings.manage',
            'audit.view',
        ],
    ],

];
