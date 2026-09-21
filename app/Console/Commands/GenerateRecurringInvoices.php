<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\RecurringInvoiceCreated;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateRecurringInvoices extends Command
{
    protected $signature = 'invoices:generate-recurring';

    protected $description =
        'Generate invoices from active recurring schedules';

    public function handle(): int
    {
        $scheduleIds = DB::table('recurring_invoice_schedules')
            ->where('status', 'ACTIVE')
            ->whereDate('next_run_date', '<=', today())
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', today());
            })
            ->pluck('id');

        foreach ($scheduleIds as $scheduleId) {

            $invoiceId = DB::transaction(function () use ($scheduleId) {

                $schedule = DB::table('recurring_invoice_schedules')
                    ->where('id', $scheduleId)
                    ->lockForUpdate()
                    ->first();

                if (
                    !$schedule ||
                    $schedule->status !== 'ACTIVE' ||
                    Carbon::parse($schedule->next_run_date)->isFuture()
                ) {
                    return null;
                }

                if (
                    $schedule->end_date &&
                    Carbon::parse($schedule->next_run_date)
                        ->gt(Carbon::parse($schedule->end_date))
                ) {
                    DB::table('recurring_invoice_schedules')
                        ->where('id', $schedule->id)
                        ->update([
                            'status' => 'INACTIVE',
                            'updated_at' => now(),
                        ]);

                    return null;
                }

                $company = DB::table('companies')
                    ->where('id', $schedule->company_id)
                    ->where('status', 'ACTIVE')
                    ->lockForUpdate()
                    ->first();

                $customer = DB::table('customers')
                    ->where('id', $schedule->customer_id)
                    ->where('company_id', $schedule->company_id)
                    ->where('status', 'ACTIVE')
                    ->first();

                $template = DB::table('company_templates')
                    ->where('id', $schedule->template_id)
                    ->where('company_id', $schedule->company_id)
                    ->where('document_type', 'INVOICE')
                    ->first();

                if (!$company || !$customer || !$template) {
                    return null;
                }

                $data = json_decode(
                    $schedule->invoice_data,
                    true
                );

                $subtotal = 0;
                $discountTotal = 0;
                $taxTotal = 0;
                $itemRows = [];

                foreach ($data['items'] as $index => $item) {

                    $qty = (float) $item['quantity'];
                    $price = (float) $item['unit_price'];

                    $lineSubtotal = round($qty * $price, 2);

                    $discount = 0;

                    if ($item['discount_type'] === 'PERCENTAGE') {
                        $discount = round(
                            $lineSubtotal *
                            (float) $item['discount_value'] /
                            100,
                            2
                        );
                    }

                    if ($item['discount_type'] === 'FIXED') {
                        $discount = min(
                            (float) $item['discount_value'],
                            $lineSubtotal
                        );
                    }

                    $taxable =
                        $lineSubtotal - $discount;

                    $taxAmount = round(
                        $taxable *
                        (float) $item['tax_percentage'] /
                        100,
                        2
                    );

                    $lineTotal =
                        $taxable + $taxAmount;

                    $subtotal += $lineSubtotal;
                    $discountTotal += $discount;
                    $taxTotal += $taxAmount;

                    $itemRows[] = [
                        'sort_order' => $index + 1,
                        'item_name' => $item['item_name'],
                        'description' => $item['description'] ?? null,
                        'quantity' => $qty,
                        'unit' => $item['unit'] ?? null,
                        'unit_price' => $price,
                        'discount_type' => $item['discount_type'],
                        'discount_value' => $item['discount_value'],
                        'discount_amount' => $discount,
                        'tax_percentage' => $item['tax_percentage'],
                        'tax_amount' => $taxAmount,
                        'line_total' => $lineTotal,
                    ];
                }

                $additional =
                    (float) ($data['additional_charges'] ?? 0);

                $grandTotal =
                    $subtotal
                    - $discountTotal
                    + $taxTotal
                    + $additional;

                $invoiceNumber =
                    rtrim($company->invoice_prefix, '-')
                    . '-'
                    . now()->format('Y')
                    . '-'
                    . str_pad(
                        $company->invoice_next_number,
                        4,
                        '0',
                        STR_PAD_LEFT
                    );

                $invoiceDate =
                    Carbon::parse($schedule->next_run_date);

                $dueDate =
                    $invoiceDate
                        ->copy()
                        ->addDays($schedule->due_days);

                $invoiceId = DB::table('invoices')
                    ->insertGetId([
                        'company_id' => $company->id,
                        'customer_id' => $customer->id,
                        'quotation_id' => null,

                        'invoice_number' => $invoiceNumber,

                        'invoice_date' =>
                            $invoiceDate->toDateString(),

                        'due_date' =>
                            $dueDate->toDateString(),

                        'subject' =>
                            $data['subject'] ?? null,

                        'reference' =>
                            $data['reference'] ?? null,

                        'subtotal' => $subtotal,

                        'discount_type' =>
                            $discountTotal > 0
                                ? 'FIXED'
                                : 'NONE',

                        'discount_value' =>
                            $discountTotal,

                        'discount_amount' =>
                            $discountTotal,

                        'tax_percentage' => 0,
                        'tax_amount' => $taxTotal,

                        'additional_charges' =>
                            $additional,

                        'grand_total' =>
                            $grandTotal,

                        'amount_paid' => 0,
                        'balance_amount' => $grandTotal,

                        'status' => 'DRAFT',

                        'notes' =>
                            $data['notes'] ?? null,

                        'terms_conditions' =>
                            $data['terms_conditions'] ?? null,

                        'template_id' => $template->id,

                        'company_snapshot' =>
                            json_encode($company),

                        'customer_snapshot' =>
                            json_encode($customer),

                        'template_snapshot' =>
                            json_encode($template),

                        'created_by' =>
                            $schedule->created_by,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                foreach ($itemRows as $row) {

                    DB::table('invoice_items')->insert([
                        'invoice_id' => $invoiceId,
                        'source_quotation_item_id' => null,
                        ...$row,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('companies')
                    ->where('id', $company->id)
                    ->update([
                        'invoice_next_number' =>
                            $company->invoice_next_number + 1,

                        'updated_at' => now(),
                    ]);

                $nextDate =
                    Carbon::parse($schedule->next_run_date);

                $nextDate = match ($schedule->frequency) {
                    'WEEKLY' => $nextDate->addWeek(),
                    'MONTHLY' => $nextDate->addMonthNoOverflow(),
                    'QUARTERLY' => $nextDate->addMonthsNoOverflow(3),
                    'YEARLY' => $nextDate->addYearNoOverflow(),
                };

                $status =
                    $schedule->end_date &&
                    $nextDate->gt(
                        Carbon::parse($schedule->end_date)
                    )
                        ? 'INACTIVE'
                        : 'ACTIVE';

                DB::table('recurring_invoice_schedules')
                    ->where('id', $schedule->id)
                    ->update([
                        'last_run_date' => now()->toDateString(),
                        'last_invoice_id' => $invoiceId,
                        'next_run_date' => $nextDate->toDateString(),
                        'status' => $status,
                        'updated_at' => now(),
                    ]);

                return $invoiceId;
            });

            if (!$invoiceId) {
                continue;
            }

            $invoice = DB::table('invoices')
                ->where('id', $invoiceId)
                ->first();

            $user = User::find($invoice->created_by);

            if ($user) {
                $user->notify(
                    new RecurringInvoiceCreated($invoice)
                );
            }
        }

        return self::SUCCESS;
    }
}