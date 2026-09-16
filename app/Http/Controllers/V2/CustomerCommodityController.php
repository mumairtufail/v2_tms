<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V2\Concerns\ScopesCustomerRecords;
use App\Http\Requests\V2\CustomerCommodityRequest;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerCommodity;
use App\Support\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerCommodityController extends Controller
{
    use ScopesCustomerRecords;

    private const MAX_IMPORT_ROWS = 1000;

    public function store(CustomerCommodityRequest $request, Company $company, Customer $customer)
    {
        $this->ensureCustomerInCompany($company, $customer);

        $commodity = $customer->commodities()->create($request->validated() + ['company_id' => $company->id]);

        // Keep the add row open so several commodities can be entered in a row.
        session()->flash('commodity_keep_adding', true);
        Toast::success("Added {$commodity->description}.");

        return $this->customerTab($company, $customer, 'commodities');
    }

    public function bulkDestroy(Request $request, Company $company, Customer $customer)
    {
        $this->ensureCustomerInCompany($company, $customer);

        $validated = $request->validate([
            'ids' => ['required', 'array', 'max:500'],
            'ids.*' => ['integer'],
        ], [
            'ids.required' => 'Select at least one commodity to delete.',
        ]);

        // Only this customer's rows: ids from other customers are ignored.
        $deleted = $customer->commodities()->whereIn('id', $validated['ids'])->delete();

        Toast::success($deleted === 1 ? 'Deleted 1 commodity.' : "Deleted {$deleted} commodities.");

        return $this->customerTab($company, $customer, 'commodities');
    }

    public function update(CustomerCommodityRequest $request, Company $company, Customer $customer, CustomerCommodity $commodity)
    {
        $this->ensureCustomerInCompany($company, $customer);
        $this->ensureBelongsToCustomer($customer, $commodity);

        $commodity->update($request->validated());

        Toast::success("Saved {$commodity->description}.");

        return $this->customerTab($company, $customer, 'commodities');
    }

    public function destroy(Company $company, Customer $customer, CustomerCommodity $commodity)
    {
        $this->ensureCustomerInCompany($company, $customer);
        $this->ensureBelongsToCustomer($customer, $commodity);

        $commodity->delete();

        Toast::success("Deleted {$commodity->description}.");

        return $this->customerTab($company, $customer, 'commodities');
    }

    public function export(Company $company, Customer $customer): StreamedResponse
    {
        $this->ensureCustomerInCompany($company, $customer);

        $commodities = $customer->commodities()->orderBy('description')->get();

        return $this->csv($this->fileName($customer, 'commodities'), function ($out) use ($commodities) {
            foreach ($commodities as $commodity) {
                fputcsv($out, array_map([$this, 'safeCell'], [
                    $commodity->description,
                    $commodity->type,
                    $commodity->measurement_unit === 'cm_kg' ? 'cm/kg' : 'in/lbs',
                    $commodity->volume,
                    $commodity->weight,
                    $commodity->linear_feet,
                    $commodity->length,
                    $commodity->width,
                    $commodity->height,
                    $commodity->freight_class,
                    $commodity->nmfc,
                    $commodity->sku,
                ]));
            }
        });
    }

    public function template(Company $company, Customer $customer): StreamedResponse
    {
        $this->ensureCustomerInCompany($company, $customer);

        return $this->csv('commodities-template.csv', function ($out) {
            fputcsv($out, ['Coconut Oil', 'skid', 'in/lbs', '', '1500', '1', '40', '48', '40', '70', '', 'SKU-123']);
        });
    }

    public function import(Request $request, Company $company, Customer $customer)
    {
        $this->ensureCustomerInCompany($company, $customer);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:1024'],
        ], [
            'file.required' => 'Choose a CSV file to upload.',
            'file.mimes' => 'Upload the commodities as a .csv file.',
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($handle) ?: [];
        $header = array_map(fn ($col) => Str::snake(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $col))), $header);

        if (! in_array('description', $header, true)) {
            fclose($handle);
            Toast::error('The file needs a "description" column. Download the template to see the expected columns.');

            return $this->customerTab($company, $customer, 'commodities');
        }

        $created = 0;
        $errors = [];
        $rowNumber = 1;

        while (($line = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if ($rowNumber - 1 > self::MAX_IMPORT_ROWS) {
                $errors[] = 'Only the first ' . self::MAX_IMPORT_ROWS . ' rows were read.';
                break;
            }

            if (collect($line)->filter(fn ($value) => trim((string) $value) !== '')->isEmpty()) {
                continue;
            }

            $raw = [];
            foreach ($header as $index => $column) {
                $raw[$column] = isset($line[$index]) ? trim((string) $line[$index]) : null;
            }

            $validator = Validator::make($this->normalizeRow($raw), CustomerCommodityRequest::baseRules());

            if ($validator->fails()) {
                $errors[] = "Row {$rowNumber}: " . $validator->errors()->first();
                continue;
            }

            $customer->commodities()->create($validator->validated() + ['company_id' => $company->id]);
            $created++;
        }

        fclose($handle);

        if ($errors) {
            session()->flash('commodity_import_errors', array_slice($errors, 0, 20));
        }

        $message = $created === 1 ? 'Imported 1 commodity.' : "Imported {$created} commodities.";
        if ($errors) {
            $message .= ' ' . count($errors) . ' ' . Str::plural('row', count($errors)) . ' skipped — see the list below.';
            Toast::warning($message);
        } else {
            Toast::success($message);
        }

        return $this->customerTab($company, $customer, 'commodities');
    }

    private function normalizeRow(array $raw): array
    {
        $blank = fn ($value) => $value === null || $value === '' ? null : $value;

        $type = strtolower((string) $blank($raw['type'] ?? null));
        $unit = strtolower(str_replace([' ', '-'], '', (string) ($raw['unit'] ?? $raw['measurement_unit'] ?? '')));

        return [
            'description' => $raw['description'] ?? null,
            'type' => $type === '' ? null : $type,
            'measurement_unit' => in_array($unit, ['cm/kg', 'cm_kg', 'cmkg', 'metric'], true) ? 'cm_kg' : 'in_lbs',
            'volume' => $blank($raw['volume'] ?? $raw['vol'] ?? null),
            'weight' => $blank($raw['weight'] ?? $raw['wt'] ?? null),
            'linear_feet' => $blank($raw['linear_feet'] ?? $raw['lf'] ?? null),
            'length' => $blank($raw['length'] ?? $raw['lg'] ?? null),
            'width' => $blank($raw['width'] ?? $raw['wd'] ?? null),
            'height' => $blank($raw['height'] ?? $raw['ht'] ?? null),
            'freight_class' => $blank($raw['freight_class'] ?? $raw['class'] ?? null),
            'nmfc' => $blank($raw['nmfc'] ?? null),
            'sku' => $blank($raw['sku'] ?? null),
        ];
    }

    /** Stop spreadsheet apps from treating a cell as a formula. */
    private function safeCell($value): string
    {
        $value = (string) ($value ?? '');

        return $value !== '' && in_array($value[0], ['=', '+', '-', '@'], true) && ! is_numeric($value)
            ? "'" . $value
            : $value;
    }

    private function fileName(Customer $customer, string $suffix): string
    {
        return Str::slug(($customer->short_code ?: $customer->name) . '-' . $suffix) . '.csv';
    }

    private function csv(string $fileName, callable $writeRows): StreamedResponse
    {
        return response()->streamDownload(function () use ($writeRows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, CustomerCommodity::CSV_COLUMNS);
            $writeRows($out);
            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
