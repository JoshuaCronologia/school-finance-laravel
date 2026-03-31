-- ================================================================
-- Foreign Data Wrapper (FDW) Setup for PostgreSQL Cross-DB Joins
-- ================================================================
-- Run this on the ACCOUNTING (main) database.
-- This allows the accounting DB to query SIS branch databases
-- as if the tables were local (via foreign schema references).
--
-- Usage: psql -h YOUR_HOST -U YOUR_USER -d accounting -f setup_fdw.sql
-- ================================================================

-- Step 1: Enable FDW extension
CREATE EXTENSION IF NOT EXISTS postgres_fdw;

-- ================================================================
-- EXAMPLE: Main Branch - K-12 Database
-- ================================================================

-- Step 2: Create foreign server
CREATE SERVER IF NOT EXISTS main_kto12_server
    FOREIGN DATA WRAPPER postgres_fdw
    OPTIONS (host 'YOUR_PG_HOST', port '5432', dbname 'main_kto12');

-- Step 3: Create user mapping (replace with actual credentials)
-- DROP USER MAPPING IF EXISTS FOR current_user SERVER main_kto12_server;
CREATE USER MAPPING FOR current_user
    SERVER main_kto12_server
    OPTIONS (user 'YOUR_DB_USER', password 'YOUR_DB_PASSWORD');

-- Step 4: Create local schema and import foreign tables
CREATE SCHEMA IF NOT EXISTS main_kto12;
IMPORT FOREIGN SCHEMA public
    FROM SERVER main_kto12_server
    INTO main_kto12;

-- ================================================================
-- EXAMPLE: Main Branch - College Database
-- ================================================================

CREATE SERVER IF NOT EXISTS main_college_server
    FOREIGN DATA WRAPPER postgres_fdw
    OPTIONS (host 'YOUR_PG_HOST', port '5432', dbname 'main_college');

CREATE USER MAPPING FOR current_user
    SERVER main_college_server
    OPTIONS (user 'YOUR_DB_USER', password 'YOUR_DB_PASSWORD');

CREATE SCHEMA IF NOT EXISTS main_college;
IMPORT FOREIGN SCHEMA public
    FROM SERVER main_college_server
    INTO main_college;

-- ================================================================
-- After FDW setup, cross-database joins work via schema reference:
--
--   SELECT e.fname, e.lname, b.name
--   FROM main_kto12.employee_db e
--   LEFT JOIN public.branch_users b ON b.parent_id = e.id::text
--   WHERE b.branch_code = 'main';
--
-- IMPORTANT:
-- - If new tables are added to a branch DB, re-run IMPORT FOREIGN SCHEMA
-- - FDW requires direct connections (port 5432), NOT pgbouncer (port 6543)
-- - For Supabase, use the direct connection string, not the pooler
-- ================================================================
