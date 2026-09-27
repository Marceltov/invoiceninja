<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

#[CoversNothing]
class OpenApiSpecRequiredFieldsTest extends TestCase
{
    /**
     * Must match the `required` rules of the matching Store*Request class;
     * date and due_date are `sometimes` there and get defaults.
     */
    public static function requestSchemas(): array
    {
        return [
            'QuoteRequest (StoreQuoteRequest)' => ['QuoteRequest', ['client_id']],
            'PurchaseOrderRequest (StorePurchaseOrderRequest)' => ['PurchaseOrderRequest', ['vendor_id']],
            'RecurringInvoiceRequest (StoreRecurringInvoiceRequest)' => ['RecurringInvoiceRequest', ['client_id', 'frequency_id']],
        ];
    }

    #[DataProvider('requestSchemas')]
    public function testRequiredFieldsMatchValidation(string $schema, array $required): void
    {
        $this->assertSame($required, $this->schemas()[$schema]['required']);
    }

    private function schemas(): array
    {
        $root = dirname(__DIR__, 2) . '/openapi/components';
        $files = glob("{$root}/schemas/*.yaml");
        sort($files);

        $yaml = "components:\n" . file_get_contents("{$root}/schemas.yaml");

        foreach ($files as $file) {
            $yaml .= "\n" . file_get_contents($file);
        }

        return Yaml::parse($yaml)['components']['schemas'];
    }
}
