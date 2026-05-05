<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SeedFullBirAtcCodes extends Migration
{
    public function up()
    {
        $now = Carbon::now();

        // Full BIR Schedule of ATCs (EWT - Section 2.57.2, RR 2-98 as amended)
        $atcCodes = [
            // ── Professional & Technical Services ──────────────────────
            ['WI010', 'Professional fees - Individual (gross ≤₱3M/yr)',      5.00,  'ewt'],
            ['WI011', 'Professional fees - Individual (gross >₱3M/yr)',      10.00, 'ewt'],
            ['WC010', 'Professional fees - Non-individual/Corporation',      10.00, 'ewt'],

            // ── Talent / Artistic Services ──────────────────────────────
            ['WI020', 'Talent fees - Individual (gross ≤₱3M/yr)',            5.00,  'ewt'],
            ['WI021', 'Talent fees - Individual (gross >₱3M/yr)',           10.00,  'ewt'],
            ['WC020', 'Talent fees - Non-individual',                        10.00, 'ewt'],

            // ── Rentals ─────────────────────────────────────────────────
            ['WI030', 'Rental of real/personal property - Individual',        5.00, 'ewt'],
            ['WC030', 'Rental of real/personal property - Non-individual',    5.00, 'ewt'],

            // ── Contractors / Suppliers ─────────────────────────────────
            ['WI040', 'Income payments to contractors - Individual',          2.00, 'ewt'],
            ['WC040', 'Income payments to contractors - Non-individual',      2.00, 'ewt'],

            // ── Government Transactions ──────────────────────────────────
            ['WI050', 'Income payments by gov\'t office - Individual',        1.00, 'ewt'],
            ['WC050', 'Income payments by gov\'t office - Non-individual',    1.00, 'ewt'],

            // ── Prizes / Winnings ────────────────────────────────────────
            ['WI060', 'Prizes exceeding ₱10,000 - Individual',              20.00, 'ewt'],
            ['WC060', 'Prizes exceeding ₱10,000 - Non-individual',          20.00, 'ewt'],

            // ── Commission / Brokerage ───────────────────────────────────
            ['WI070', 'Gross commissions of customs brokers - Individual',   10.00, 'ewt'],
            ['WC070', 'Gross commissions of customs brokers - Non-individual',10.00,'ewt'],
            ['WI080', 'Gross commissions of insurance agents - Indiv (≤₱720K/yr)', 10.00, 'ewt'],
            ['WI081', 'Gross commissions of insurance agents - Indiv (>₱720K/yr)', 15.00, 'ewt'],
            ['WC080', 'Gross commissions of insurance agents - Non-individual',10.00,'ewt'],
            ['WI090', 'Gross commissions of real estate brokers - Indiv (≤₱720K/yr)', 5.00, 'ewt'],
            ['WI091', 'Gross commissions of real estate brokers - Indiv (>₱720K/yr)', 10.00, 'ewt'],
            ['WC090', 'Gross commissions of real estate brokers - Non-individual', 5.00, 'ewt'],

            // ── Directors' Fees ──────────────────────────────────────────
            ['WI100', 'Directors\' fees and other income - Individual',      15.00, 'ewt'],
            ['WC100', 'Directors\' fees and other income - Non-individual',  15.00, 'ewt'],

            // ── Medical Practitioners ────────────────────────────────────
            ['WI158', 'Fees of medical practitioners - Individual (≤₱3M/yr)',  10.00, 'ewt'],
            ['WI159', 'Fees of medical practitioners - Individual (>₱3M/yr)',  15.00, 'ewt'],
            ['WC158', 'Fees of medical practitioners - Non-individual',         15.00, 'ewt'],

            // ── Other Income Payments ─────────────────────────────────────
            ['WI160', 'Other income payments - Individual',                   1.00, 'ewt'],
            ['WC160', 'Other income payments - Non-individual',               2.00, 'ewt'],

            // ── Real Property Sales ──────────────────────────────────────
            ['WI150', 'Sale of real property (habitually engaged) - Individual',  6.00, 'ewt'],
            ['WC150', 'Sale of real property (habitually engaged) - Non-individual', 6.00, 'ewt'],

            // ── Income Distribution ──────────────────────────────────────
            ['WI120', 'Income distribution to beneficiaries - Individual',   15.00, 'ewt'],
            ['WC120', 'Income distribution to beneficiaries - Non-individual',15.00, 'ewt'],

            // ── REIT Dividends ────────────────────────────────────────────
            ['WC015', 'Income distributed by Real Estate Investment Trust (REIT)',15.00,'ewt'],

            // ── Final Withholding Tax (FWT) ──────────────────────────────
            ['WF010', 'Interest on peso bank deposits - Resident individual',       20.00, 'final'],
            ['WF011', 'Interest on long-term deposits/investments (pre-terminated)', 20.00, 'final'],
            ['WF020', 'Interest on deposits under expanded foreign currency deposit',15.00, 'final'],
            ['WF030', 'Cash/property dividends to individual - Resident',           10.00, 'final'],
            ['WF031', 'Cash/property dividends - NRC/NRA',                          20.00, 'final'],
            ['WF032', 'Cash/property dividends - NRFC',                             15.00, 'final'],
            ['WF040', 'Capital gains on sale of shares not traded in stock exchange',15.00, 'final'],
            ['WF041', 'Capital gains on sale of shares - NRC/NRA',                  25.00, 'final'],
            ['WF050', 'Prizes exceeding ₱10,000 (final WHT)',                       20.00, 'final'],
            ['WF051', 'PCSO/Lotto winnings exceeding ₱10,000',                      20.00, 'final'],
            ['WF060', 'Royalties - literary/musical/artistic works',                10.00, 'final'],
            ['WF061', 'Royalties - books/other literary works',                     10.00, 'final'],
            ['WF062', 'Royalties - other sources',                                  20.00, 'final'],
            ['WF025', 'Income of NRA/NRFC (general)',                               25.00, 'final'],
        ];

        foreach ($atcCodes as $atc) {
            list($code, $name, $rate, $type) = $atc;
            DB::table('tax_codes')->insertOrIgnore([
                'code'      => $code,
                'name'      => $name,
                'rate'      => $rate,
                'type'      => $type,
                'bir_atc'   => $code,
                'is_active' => true,
                'created_at'=> $now,
                'updated_at'=> $now,
            ]);
        }
    }

    public function down()
    {
        $codes = [
            'WI010','WI011','WC010','WI020','WI021','WC020',
            'WI030','WC030','WI040','WC040','WI050','WC050',
            'WI060','WC060','WI070','WC070','WI080','WI081','WC080',
            'WI090','WI091','WC090','WI100','WC100',
            'WI158','WI159','WC158','WI160','WC160',
            'WI150','WC150','WI120','WC120','WC015',
            'WF010','WF011','WF020','WF030','WF031','WF032',
            'WF040','WF041','WF050','WF051','WF060','WF061','WF062','WF025',
        ];
        DB::table('tax_codes')->whereIn('code', $codes)->delete();
    }
}
