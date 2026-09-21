<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TemplateController extends Controller
{
    public function index(Request $request)
    {
        $companyList = DB::table('companies')
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get(['id', 'name']);

        $companyId = $request->integer('company_id')
            ?: optional($companyList->first())->id;

        $templates = DB::table('company_templates')
            ->join(
                'companies',
                'company_templates.company_id',
                '=',
                'companies.id'
            )
            ->when(
                $companyId,
                fn ($query) =>
                    $query->where(
                        'company_templates.company_id',
                        $companyId
                    ),
                fn ($query) =>
                    $query->whereRaw('1 = 0')
            )
            ->when(
                $request->filled('document_type'),
                fn ($query) =>
                    $query->where(
                        'company_templates.document_type',
                        $request->document_type
                    )
            )
            ->select(
                'company_templates.id',
                'company_templates.company_id',
                'company_templates.document_type',
                'company_templates.template_name',
                'company_templates.show_logo',
                'company_templates.show_bank_details',
                'company_templates.show_vat',
                'company_templates.show_signature',
                'company_templates.is_default',
                'company_templates.created_at',
                'companies.name as company_name'
            )
            ->orderByDesc('company_templates.is_default')
            ->orderBy('company_templates.document_type')
            ->orderBy('company_templates.template_name')
            ->paginate(10)
            ->withQueryString();

        return view('templates.index', compact(
            'companyList',
            'companyId',
            'templates'
        ));
    }


    public function create(Request $request)
    {
        $companyList = DB::table('companies')
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get(['id', 'name']);

        $companyId = $request->integer('company_id')
            ?: optional($companyList->first())->id;

        $company = null;

        if ($companyId) {
            $company = DB::table('companies')
                ->where('id', $companyId)
                ->where('status', 'ACTIVE')
                ->first();
        }

        return view('templates.create', compact(
            'companyList',
            'companyId',
            'company'
        ));
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id' =>
                'required|exists:companies,id',

            'document_type' =>
                'required|in:QUOTATION,INVOICE',

            'template_name' =>
                'required|string|max:255',

            'header_text' =>
                'nullable|string',

            'footer_text' =>
                'nullable|string',

            'terms_conditions' =>
                'nullable|string',
        ]);


        $company = DB::table('companies')
            ->where('id', $data['company_id'])
            ->where('status', 'ACTIVE')
            ->first();

        if (!$company) {
            return back()
                ->withInput()
                ->withErrors([
                    'company_id' =>
                        'Selected company is not active.'
                ]);
        }


        $showLogo =
            $request->boolean('show_logo');

        $showBankDetails =
            $request->boolean('show_bank_details');

        $showVat =
            $request->boolean('show_vat');

        $showSignature =
            $request->boolean('show_signature');

        $isDefault =
            $request->boolean('is_default');


        DB::transaction(function () use (
            $data,
            $showLogo,
            $showBankDetails,
            $showVat,
            $showSignature,
            $isDefault
        ) {

            /*
             * Only one default template per company
             * and document type.
             */
            if ($isDefault) {

                DB::table('company_templates')
                    ->where(
                        'company_id',
                        $data['company_id']
                    )
                    ->where(
                        'document_type',
                        $data['document_type']
                    )
                    ->update([
                        'is_default' => 0,
                        'updated_at' => now(),
                    ]);
            }


            DB::table('company_templates')
                ->insert([

                    'company_id' =>
                        $data['company_id'],

                    'document_type' =>
                        $data['document_type'],

                    'template_name' =>
                        $data['template_name'],

                    'header_text' =>
                        $data['header_text'] ?? null,

                    'footer_text' =>
                        $data['footer_text'] ?? null,

                    'terms_conditions' =>
                        $data['terms_conditions'] ?? null,

                    'show_logo' =>
                        $showLogo ? 1 : 0,

                    'show_bank_details' =>
                        $showBankDetails ? 1 : 0,

                    'show_vat' =>
                        $showVat ? 1 : 0,

                    'show_signature' =>
                        $showSignature ? 1 : 0,

                    /*
                     * Keep null until we add advanced
                     * visual template configuration.
                     */
                    'template_config' =>
                        null,

                    'is_default' =>
                        $isDefault ? 1 : 0,

                    'created_at' =>
                        now(),

                    'updated_at' =>
                        now(),
                ]);
        });


        return redirect()
            ->route('templates.index', [
                'company_id' =>
                    $data['company_id']
            ])
            ->with(
                'success',
                'Template created successfully.'
            );
    }
}