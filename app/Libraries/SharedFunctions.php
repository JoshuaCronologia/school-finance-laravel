<?php

namespace App\Libraries;

use App\Models\Employee;
use App\Models\CollegeEmployee;
use App\Models\Student;
use App\Services\Users\BranchUser;
use App\Services\Users\User;
use App\Models\SchoolInfo;
use App\Models\CollegeSchoolImage;
use App\Models\CollegeSchoolInfo;
use App\Models\Position;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\UploadedFile;


class SharedFunctions
{
    public static function query_log($builder)
    {
        $query = str_replace(array('?'), array('\'%s\''), $builder->toSql());
        $query = vsprintf($query, $builder->getBindings());
    }

    public static function create_log() {}

    public static function default_msg()
    {
        return [
            'status'    => 0,
            'title'     => "Oops!",
            'text'      => "Something went wrong!",
            'type'      => 'error'
        ];
    }

    public static function success_msg($message = "Successfully!")
    {
        return [
            'status'    => 1,
            'title'     => "Success!",
            'text'      => $message,
            'type'      => 'success'
        ];
    }

    public static function prompt_msg($message = "Invalid!")
    {
        return [
            'status' => 0,
            'title' => 'Oops!',
            'text' => $message,
            'type' => 'error'
        ];
    }

    public static function get_auth($id, $branch_code, $user_type)
    {
        $is_work_from_home = false;
        $position_id = 0;
        $position = null;

        if ($branch_code) {
            $branch = Branch::get($branch_code, false, false, true);
            $branch_name = Branch::get($branch_code, false, false, false)[0]->text;

            if ($user_type == \App\Models\Employee::class) {
                $user = null;

                // Only search K-12 if ID is numeric (K-12 uses integer IDs)
                if (is_numeric($id)) {
                    try {
                        $employee = Employee::on($branch[0]);
                        $user = $employee->where('id', $id)->first();
                    } catch (\Exception $e) {
                        $user = null;
                    }
                }

                if (!empty($user) && is_numeric($id)) {
                    $branch_account = BranchUser::where('parent_id', $id)
                        ->where('parent_type', $user_type)
                        ->where('branch_code', $branch_code)
                        ->first();
                    if ($branch_account) {
                        $user_type = $branch_account->parent_type;
                    }

                    if ($user->img_src == '' || $user->img_src == null) {
                        if (!empty($user->gender)) {
                            if ($user->gender == 'Male' || $user->gender == 'male') {
                                $img = env('FOLDER_NAME') . '/public/assets/img/default-picture.png';
                            } else {
                                $img = env('FOLDER_NAME') . '/public/assets/img/default-picture-girl.png';
                            }
                        } else {
                            $img = env('FOLDER_NAME') . '/public/assets/img/default-picture.png';
                        }
                    } else {
                        try {
                            $school_info = SchoolInfo::on($branch[0])->first();
                            $url = $school_info['file_url'] . "/" . $school_info['active_disk'] . "/School_Files/" . $school_info['category'] . "/" . $school_info['folder_name'] . "/Employees/" . $user->id . "/" . $user->img_src;
                            $img = $url;
                        } catch (\Exception $e) {
                            $img = env('FOLDER_NAME') . '/public/assets/img/default-picture.png';
                        }
                    }

                    $fullname = $user->full_name;
                    $fname = $user->firstname;
                    $mname = $user->middlename;
                    $lname = $user->lastname;
                    $ext_name = $user->ext_name;
                    $email = $user->email;
                    $student_pic = $img;

                } else {
                    // College fallback — ID is UUID, search college employees table
                    $employee = CollegeEmployee::on($branch[1])
                        ->select('employees.*', DB::raw('CONCAT(prefixes.prefix_name, employees.generated_id) as employee_id'))
                        ->leftJoin('prefixes', function ($join) {
                            $join->on('employees.prefix_id', '=', 'prefixes.id');
                        });
                    $user = $employee->where('employees.id', $id)->first();

                    $branch_account = BranchUser::where('parent_id', $id)
                        ->where('parent_type', $user_type)
                        ->where('branch_code', $branch_code)
                        ->first();
                    $fullname = $user->full_name;
                    $fname = $user->fname;
                    $mname = $user->mname;
                    $lname = $user->lname;
                    $ext_name = $user->ext_name;
                    $email = $user->email;
                    $student_pic = $user->picture;
                    $img = $user->picture;
                }
            }
        } else {
            $branch = null;
            $branch_name = '';
            $branch_account = null;
            $user = User::where('id', $id)->first();
            $fullname = $user->name;
            $fname = $user->fname;
            $mname = $user->mname;
            $lname = $user->lname;
            $ext_name = $user->ext_name;
            $email = $user->email;
        }

        $position_id = 0;
        if ($user_type == \App\Models\Employee::class) {
            $id = $user->employee_id;
            $type = 'Employee';
            if ($user->academic_position_id > 0) {
                $position_id = $user->academic_position_id;
            } else if ($user->nonacademic_position_id > 0) {
                $position_id = $user->nonacademic_position_id;
            }
            try {
                $position_model = Position::on($branch[0]);
                $position = $position_model->where('id', $position_id)->first();
            } catch (\Exception $e) {
            }
        }

        if ($user_type == \App\Services\Users\User::class) {
            $student_pic = env('FOLDER_NAME') . '/public/assets/img/default-picture.png';
        }

        return [
            'branch_name' => $branch_name ?? '',
            'branch_account' => $branch_account ?? null,
            'branch_user_id' => isset($branch_account) && $branch_account ? $branch_account->id : null,
            'name' => $fullname ?? '',
            'fname' => $fname ?? '',
            'mname' => $mname ?? '',
            'lname' => $lname ?? '',
            'ext_name' => $ext_name ?? '',
            'email' => $email ?? '',
            'user_type' => $user_type,
            'pic' => isset($student_pic) ? $student_pic : "",
            'id' => $id,
            'img' => isset($img) ? $img : "",
            'is_work_from_home' => $is_work_from_home,
            'position' => isset($position->name) ? $position->name : '',
        ];
    }

    public static function path_to_uploaded_file($path, $public = false)
    {
        $name = File::name($path);
        $extension = File::extension($path);
        $original_name = $name . '.' . $extension;
        $mime_type = File::mimeType($path);
        $size = File::size($path);
        $error = false;
        $test = $public;
        $object = new UploadedFile($path, $original_name, $mime_type, $size, $error, $test);
        return $object;
    }

    public static function get_branch_user($id, $branch)
    {
        $branch_account = BranchUser::where('parent_id', $id)
            ->where('parent_type', \App\Models\Employee::class)
            ->where('branch_code', $branch)
            ->first();
        return $branch_account;
    }

    public static function get_branch_user_by_id($uuid)
    {
        $branch_account = BranchUser::where('id', $uuid)->first();
        return $branch_account;
    }

    public static function get_current_user()
    {
        $user_id = Session::get('user_id');
        $emp_branch = Session::get('branch_code');
        $branch = Branch::get($emp_branch, false, false, true);

        if (Session::get('user_info')['user_type'] == \App\Services\Users\User::class) {
            $type = 'admin';
            $entity = User::find($user_id);
            $entity_name = $entity->fname . ' ' . $entity->lname;
        } else if (Session::get('user_info')['user_type'] == \App\Models\Employee::class) {
            $type = 'employee';
            $entity = Employee::on($branch[0])->where('id', $user_id)->first();
            $entity_name = $entity->firstname . ' ' . $entity->lastname;
        } else if (Session::get('user_info')['user_type'] == \App\Models\Student::class) {
            $type = 'student';
            $entity = Student::on($branch[0])->where('id', $user_id)->first();
            $entity_name = $entity->firstname . ' ' . $entity->lastname;
        }

        $data = [
            'user_id' => $user_id,
            'entity' => $entity,
            'emp_branch' => $emp_branch,
            'entity_name' => $entity_name,
            'type' => $type,
        ];
        return $data;
    }

    public static function get_quantity_column($branch_code)
    {
        $col = 'available_quantity';
        if ($branch_code === 'an') {
            $col = 'an_available_quantity';
        } else if ($branch_code === 'fv') {
            $col = 'fv_available_quantity';
        } else if ($branch_code === 'gh') {
            $col = 'gh_available_quantity';
        } else if ($branch_code === 'lp') {
            $col = 'lp_available_quantity';
        } else if ($branch_code === 'sa') {
            $col = 'sa_available_quantity';
        } else {
            $col = 'available_quantity';
        }
        return $col;
    }

    public static function get_branch_user_by_request($request)
    {
        $branch_user = null;
        $entities = [];
        try {
            if ($request->type == 'student') {
                $branch_user = BranchUser::where('parent_id', $request->entity_id)
                    ->where('parent_type', \App\Models\Student::class)
                    ->where('branch_code', $request->branch_code)
                    ->first();
                $branch = Branch::get($branch_user->branch_code, false, false, true);
                $entities = Student::on($branch[0])->where('id', $branch_user->parent_id)->get();
            } else if ($request->type == 'employee') {
                $branch_user = BranchUser::where('parent_id', $request->entity_id)
                    ->where('parent_type', \App\Models\Employee::class)
                    ->where('branch_code', $request->branch_code)
                    ->first();
                $branch = Branch::get($branch_user->branch_code, false, false, true);
                $entities = Employee::on($branch[0])->where('id', $branch_user->parent_id)->get();
            }
        } catch (Exception $e) {
        }
        return [
            'branch_user' => $branch_user,
            'entities' => $entities,
        ];
    }

    public static function get_school()
    {
        $branch = Branch::get("", false, false, false);
        try {
            $branch_code = $branch[0]->platforms[0]['id'];
            if ($branch_code == 'gh_kto12') {
                $branch_code = 'fv_kto12';
            }
            $db = SchoolInfo::on($branch_code)->first();
            $base = $db->file_url . '/' . $db->active_disk . '/School_Files/1/' . $db->folder_name . '/school_profile/';
            $data = [
                'school_name' => $db->school_name,
                'small_logo' => $base . '' . $db->login_img_src,
                'large_logo' => $base . '' . $db->logo_src,
            ];
        } catch (Exception $e) {
            $branch_code = $branch[0]->platforms[1]['id'];
            if ($branch_code == 'gh_kto12') {
                $branch_code = 'fv_kto12';
            }
            $db = CollegeSchoolInfo::on($branch_code)->first();
            $sch_inf = CollegeSchoolImage::on($branch_code)->where('image_type', 2)->first();
            $sch_image = $sch_inf['file_url'] . '/' . $sch_inf['file_name'];
            $data = [
                'school_name' => $db->school_name,
                'small_logo' => $sch_image,
                'large_logo' => $sch_image,
            ];
        }

        return $data;
    }

    public static function send_sms($message, $receiverNo)
    {
        $token_curl = curl_init();
        curl_setopt_array($token_curl, array(
            CURLOPT_URL => 'http://metadata.google.internal/computeMetadata/v1/instance/service-accounts/default/identity?audience=https://asia-southeast1-orangeapps-v2-school.cloudfunctions.net/itextmo-api-public',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Metadata-Flavor: Google'
            ),
        ));
        $token_response = curl_exec($token_curl);
        curl_close($token_curl);

        $sms_post_query = json_encode(['message' => $message, 'numbers' => [$receiverNo]]);
        $sms_curl_header = [
            'Content-type: application/json',
            "Accept: application/json",
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://asia-southeast1-orangeapps-v2-school.cloudfunctions.net/itextmo-api-public");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $sms_curl_header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sms_post_query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        $sms_curl_response = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return $sms_curl_response;
    }
}
