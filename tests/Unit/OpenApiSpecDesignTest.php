<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

#[CoversNothing]
class OpenApiSpecDesignTest extends TestCase
{
    public function testDesignTemplateIsAnObjectOfParts(): void
    {
        $design = $this->schemas()['Design']['properties']['design'];

        $this->assertSame('object', $design['type']);

        foreach (['includes', 'header', 'body', 'product', 'task', 'footer'] as $part) {
            $this->assertArrayHasKey($part, $design['properties']);
        }
    }

    public function testDocumentRequestsAcceptADesignId(): void
    {
        $schemas = $this->schemas();

        foreach (['InvoiceRequest', 'QuoteRequest', 'CreditRequest', 'PurchaseOrderRequest', 'RecurringInvoiceRequest'] as $name) {
            $this->assertArrayHasKey('design_id', $schemas[$name]['properties'], $name);
        }
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
