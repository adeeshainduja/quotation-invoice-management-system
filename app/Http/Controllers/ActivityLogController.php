<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Activity Log List
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        /*
         * Extra security check.
         * Route middleware also protects this page.
         */
        abort_unless(
            auth()->user()
                && auth()->user()->can('viewActivityLogs'),
            403
        );


        /*
         * Companies for filter
         */
        $companyList = DB::table('companies')
            ->orderBy('name')
            ->get([
                'id',
                'name'
            ]);


        /*
         * Users for filter
         */
        $userList = DB::table('users')
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get([
                'id',
                'name'
            ]);


        /*
         * Entity types currently available
         * in activity_logs.
         */
        $entityTypes = DB::table('activity_logs')
            ->whereNotNull('entity_type')
            ->where('entity_type', '!=', '')
            ->distinct()
            ->orderBy('entity_type')
            ->pluck('entity_type');


        /*
         * Activity logs
         */
        $logs = DB::table('activity_logs')

            ->join(
                'users',
                'activity_logs.user_id',
                '=',
                'users.id'
            )

            ->join(
                'companies',
                'activity_logs.company_id',
                '=',
                'companies.id'
            )


            /*
             * Company filter
             */
            ->when(
                $request->filled('company_id'),
                function ($query) use ($request) {

                    $query->where(
                        'activity_logs.company_id',
                        $request->company_id
                    );
                }
            )


            /*
             * User filter
             */
            ->when(
                $request->filled('user_id'),
                function ($query) use ($request) {

                    $query->where(
                        'activity_logs.user_id',
                        $request->user_id
                    );
                }
            )


            /*
             * Entity filter
             */
            ->when(
                $request->filled('entity_type'),
                function ($query) use ($request) {

                    $query->where(
                        'activity_logs.entity_type',
                        $request->entity_type
                    );
                }
            )


            /*
             * Action search
             */
            ->when(
                $request->filled('action'),
                function ($query) use ($request) {

                    $query->where(
                        'activity_logs.action',
                        'like',
                        '%' . trim($request->action) . '%'
                    );
                }
            )


            /*
             * From date
             */
            ->when(
                $request->filled('date_from'),
                function ($query) use ($request) {

                    $query->whereDate(
                        'activity_logs.created_at',
                        '>=',
                        $request->date_from
                    );
                }
            )


            /*
             * To date
             */
            ->when(
                $request->filled('date_to'),
                function ($query) use ($request) {

                    $query->whereDate(
                        'activity_logs.created_at',
                        '<=',
                        $request->date_to
                    );
                }
            )


            ->select(

                'activity_logs.id',
                'activity_logs.user_id',
                'activity_logs.company_id',
                'activity_logs.entity_type',
                'activity_logs.entity_id',
                'activity_logs.action',
                'activity_logs.created_at',

                'users.name as user_name',
                'users.email as user_email',

                'companies.name as company_name'
            )


            ->orderByDesc(
                'activity_logs.created_at'
            )

            ->orderByDesc(
                'activity_logs.id'
            )

            ->paginate(20)

            ->withQueryString();


        return view(
            'activity-logs.index',
            compact(
                'logs',
                'companyList',
                'userList',
                'entityTypes'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Activity Log Details
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        abort_unless(
            auth()->user()
                && auth()->user()->can('viewActivityLogs'),
            403
        );


        $log = DB::table('activity_logs')

            ->join(
                'users',
                'activity_logs.user_id',
                '=',
                'users.id'
            )

            ->join(
                'companies',
                'activity_logs.company_id',
                '=',
                'companies.id'
            )

            ->where(
                'activity_logs.id',
                $id
            )

            ->select(

                'activity_logs.id',
                'activity_logs.user_id',
                'activity_logs.company_id',
                'activity_logs.entity_type',
                'activity_logs.entity_id',
                'activity_logs.action',
                'activity_logs.old_data',
                'activity_logs.new_data',
                'activity_logs.created_at',

                'users.name as user_name',
                'users.email as user_email',

                'companies.name as company_name'
            )

            ->first();


        abort_if(!$log, 404);


        /*
         * Decode JSON and remove sensitive data
         */
        $oldData = $this->decodeAndRedact(
            $log->old_data
        );

        $newData = $this->decodeAndRedact(
            $log->new_data
        );


        return view(
            'activity-logs.show',
            compact(
                'log',
                'oldData',
                'newData'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Decode JSON
    |--------------------------------------------------------------------------
    */
    private function decodeAndRedact(
        ?string $json
    ): ?array
    {
        if (!$json) {
            return null;
        }


        $data = json_decode(
            $json,
            true
        );


        if (!is_array($data)) {
            return null;
        }


        return $this->redactSensitiveData(
            $data
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Hide Sensitive Information
    |--------------------------------------------------------------------------
    */
    private function redactSensitiveData(
        array $data
    ): array
    {
        $sensitiveKeys = [

            'password',

            'password_confirmation',

            'remember_token',

            'token',

            'access_token',

            'refresh_token',

            'api_key',

            'api_secret',

            'secret',

            'stripe_secret',

            'authorization',

        ];


        foreach ($data as $key => $value) {

            $normalizedKey =
                strtolower(
                    (string) $key
                );


            if (
                in_array(
                    $normalizedKey,
                    $sensitiveKeys,
                    true
                )
            ) {

                $data[$key] =
                    '[REDACTED]';

                continue;
            }


            if (is_array($value)) {

                $data[$key] =
                    $this->redactSensitiveData(
                        $value
                    );
            }
        }


        return $data;
    }
}