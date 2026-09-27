<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

#[CoversNothing]
class OpenApiSpecRequestBodyTest extends TestCase
{
    // Actions that read no input, despite the store/update name.
    private const BODYLESS = [
        'updateClientTaxData', // ClientController::updateTaxData only triggers a refresh
    ];

    public function testEveryStoreAndUpdateOperationDocumentsARequestBody(): void
    {
        $missing = [];

        foreach ($this->pathSources() as $path) {
            $document = Yaml::parse("paths:\n" . $this->withoutPathsKey($path));

            foreach ($document['paths'] as $openapi_path => $methods) {
                foreach ($methods as $method => $operation) {
                    if (! in_array($method, ['post', 'put', 'patch'], true) || ! is_array($operation)) {
                        continue;
                    }

                    $operation_id = $operation['operationId'] ?? '';

                    if (in_array($operation_id, self::BODYLESS, true)) {
                        continue;
                    }

                    if (preg_match('/^(store|update)[A-Z]/', $operation_id) && ! isset($operation['requestBody'])) {
                        $missing[] = "{$operation_id} ({$method} {$openapi_path})";
                    }
                }
            }
        }

        $this->assertSame([], $missing, 'Write operations without a requestBody cannot send any fields');
    }

    private function pathSources(): array
    {
        $root = dirname(__DIR__, 2) . '/openapi';
        $files = glob("{$root}/paths/*.yaml");
        sort($files);

        return ["{$root}/paths.yaml", ...$files];
    }

    private function withoutPathsKey(string $file): string
    {
        return preg_replace('/^paths:\s*\n/', '', file_get_contents($file));
    }
}
