<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,

            // SSO middleware
            'check_auth'          => \App\Http\Middleware\CheckAuth::class,
            'check_permission'    => \App\Http\Middleware\CheckPermission::class,
            'admin_only'          => \App\Http\Middleware\AdminOnly::class,
            'employee_only'       => \App\Http\Middleware\EmployeeOnly::class,
            'student_only'        => \App\Http\Middleware\StudentOnly::class,
            'admin_employee_only' => \App\Http\Middleware\AdminEmployeeOnly::class,
            'employee_student_only' => \App\Http\Middleware\EmployeeStudentOnly::class,
            'api.key'             => \App\Http\Middleware\ApiKeyAuth::class,
        ]);

        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
