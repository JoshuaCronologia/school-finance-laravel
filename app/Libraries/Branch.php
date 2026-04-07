<?php

namespace App\Libraries;

class Branch
{
    /**
     * Get branch info from creds.json.
     *
     * @param string $b_code      Filter by branch code (empty = all)
     * @param bool   $all_branches Include "All Branches" option
     * @param bool   $all_platforms Include "All Platforms" option per branch
     * @param bool   $is_list      Return flat array of connection names instead of objects
     */
    public static function get($b_code = "", $all_branches = false, $all_platforms = false, $is_list = false)
    {
        $path        = 'app' . DIRECTORY_SEPARATOR . 'Libraries';
        $cred_path   = str_replace($path, 'creds.json', __DIR__);
        $json_config = json_decode(file_get_contents($cred_path), true);
        $config_key  = env('DB_CONFIG_KEY', 'local');
        $rs          = [];
        $list        = [];

        if (array_key_exists($config_key, $json_config)) {
            $config   = $json_config[$config_key];
            $branches = $config['databases']['branches'];

            if ($all_branches) {
                $all_branch = [['code' => 'all', 'id' => 0, 'name' => 'All Branches', 'school_types' => ['1', '2']]];
                $branches   = array_merge($all_branch, $branches);
            }

            foreach ($branches as $branch) {
                $code = $branch['code'];
                $sch_id = $branch['id'];
                $name = $branch['name'];

                try {
                    $college_only = empty($branch['kto12']['database']) ? 1 : 0;
                } catch (\Exception $e) {
                    $college_only = 1;
                }

                $platforms = [];
                $cons      = [];

                if ($all_platforms && count($branch['school_types']) > 1) {
                    $platforms[] = [
                        'id'   => $code . '_all',
                        'text' => 'All Platforms',
                    ];
                }

                if (in_array('1', $branch['school_types'])) {
                    $platforms[] = [
                        'id'   => $code . '_kto12',
                        'text' => 'Kto12',
                    ];
                    $cons[] = $code . '_kto12';
                }

                if (in_array('2', $branch['school_types'])) {
                    $platforms[] = [
                        'id'   => $code . '_college',
                        'text' => 'College',
                    ];
                    $cons[] = $code . '_college';
                }

                $branchObj = [
                    'id'           => $code,
                    'sch_id'       => $sch_id,
                    'text'         => $name,
                    'platforms'    => $platforms,
                    'college_only' => $college_only,
                ];

                if ($b_code !== "" && $code == $b_code) {
                    $rs   = [(object) $branchObj];
                    $list = $cons;
                    break;
                }

                $rs[]  = (object) $branchObj;
                $list  = array_merge($list, $cons);
            }
        }

        return $is_list ? $list : collect($rs);
    }

    /**
     * Get connection names for a platform_id.
     *
     * Examples:
     *   "all_all"      → all branches, all platforms
     *   "all_kto12"    → all branches, kto12 only
     *   "pcc_all"      → pcc branch, all platforms
     *   "pcc_kto12"    → single connection
     */
    public static function connections($platform_id)
    {
        $connections = [];

        if ($platform_id === "all_all") {
            return Branch::get("", false, false, true);
        } elseif ($platform_id === "all_kto12") {
            $cons = Branch::get("", false, false, true);
            foreach ($cons as $con) {
                if (strpos($con, '_kto12') !== false) {
                    $connections[] = $con;
                }
            }
        } elseif ($platform_id === "all_college") {
            $cons = Branch::get("", false, false, true);
            foreach ($cons as $con) {
                if (strpos($con, '_college') !== false) {
                    $connections[] = $con;
                }
            }
        } elseif (strpos($platform_id, '_all') !== false) {
            $parts = explode('_all', $platform_id);
            if (count($parts) > 1) {
                $branch_code = $parts[0];
                $connections = Branch::get($branch_code, false, false, true);
            }
        } else {
            $connections = [$platform_id];
        }

        return $connections;
    }
}
